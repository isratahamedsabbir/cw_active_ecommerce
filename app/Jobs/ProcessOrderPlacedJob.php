<?php

namespace App\Jobs;

use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\User;
use App\Services\TelegramService;
use App\Utility\NotificationUtility;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessOrderPlacedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId, public int $userId, public bool $isAuthUser = false)
    {
    }

    public function handle(TelegramService $telegramService): void
    {
        $order = Order::find($this->orderId);
        $user  = User::find($this->userId);

        if (!$order || !$user) {
            return;
        }

        $customerAddress = json_decode($order->customer_address, true) ?: [];

        try {
            $telegramService->sendMessage("New order placed.\n Order code: {$order->code},\n Name: " . ($customerAddress['name'] ?? '') . ",\n Phone: " . ($customerAddress['phone'] ?? '') . ",\n Email: " . ($customerAddress['email'] ?? '') . ", \n Additional Info: {$order->additional_info}");
        } catch (\Throwable $e) {
            Log::warning('Order telegram notification failed', ['order_code' => $order->code, 'error' => $e->getMessage()]);
        }

        if ($user->email) {
            try {
                Mail::to($user->email)->send(new OrderPlacedMail($order));
            } catch (\Throwable $e) {
                Log::error('Order mail failed', ['order_code' => $order->code, 'error' => $e->getMessage()]);
            }
        }

        if ($this->isAuthUser) {
            try {
                NotificationUtility::sendOrderPlacedNotification($order);
            } catch (\Throwable $e) {
                Log::error('Order placed notification failed', ['order_code' => $order->code, 'error' => $e->getMessage()]);
            }
        }
    }
}
