<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\MetaConversionApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMetaCapiEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $eventName;
    protected array $customData;
    protected array $userData;
    protected string $eventId;
    protected string $eventSourceUrl;
    protected ?string $ip;
    protected ?string $userAgent;
    protected ?int $markSyncedOrderId;

    public function __construct(
        string $eventName,
        array $customData,
        array $userData,
        string $eventId,
        string $eventSourceUrl,
        ?string $ip,
        ?string $userAgent,
        ?int $markSyncedOrderId = null
    ) {
        $this->eventName = $eventName;
        $this->customData = $customData;
        $this->userData = $userData;
        $this->eventId = $eventId;
        $this->eventSourceUrl = $eventSourceUrl;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->markSyncedOrderId = $markSyncedOrderId;
    }

    public function handle(MetaConversionApiService $service): void
    {
        $success = $service->send(
            $this->eventName,
            $this->customData,
            $this->userData,
            $this->eventId,
            $this->eventSourceUrl,
            $this->ip,
            $this->userAgent
        );

        if ($success && $this->markSyncedOrderId) {
            Order::where('id', $this->markSyncedOrderId)->update([
                'meta_purchase_synced_at' => now(),
            ]);
        }
    }
}
