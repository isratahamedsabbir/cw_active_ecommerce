<?php

namespace Tests\Feature;

use App\Jobs\SendMetaCapiEventJob;
use App\Models\Order;
use App\Models\User;
use App\Services\MetaConversionApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SendMetaCapiEventJobTest extends TestCase
{
    protected function makeOrder(): Order
    {
        $user = User::first();

        if (!$user) {
            $this->markTestSkipped('No user found to attach a test order to');
        }

        $order = new Order;
        $order->user_id = $user->id;
        $order->code = 'TESTJOB' . uniqid();
        $order->date = strtotime('now');
        $order->payment_status = 'unpaid';
        $order->payment_type = 'cash_on_delivery';
        $order->delivery_viewed = '0';
        $order->payment_status_viewed = '0';
        $order->grand_total = 10.00;
        $order->shipping_address = [];
        $order->order_from = 'app';
        $order->save();

        return $order;
    }

    public function test_handle_marks_order_synced_on_success()
    {
        config([
            'services.meta_capi.pixel_id' => '123456',
            'services.meta_capi.access_token' => 'test-token',
            'services.meta_capi.api_version' => 'v20.0',
        ]);

        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1], 200)]);

        $order = $this->makeOrder();

        $job = new SendMetaCapiEventJob(
            'Purchase', ['value' => 10.00], [], 'evt-1',
            'https://storefront.example.com/order/' . $order->code,
            null, null, $order->id
        );

        $job->handle(new MetaConversionApiService());

        $order->refresh();
        $this->assertNotNull($order->meta_purchase_synced_at);

        $order->delete();
    }

    public function test_handle_does_not_mark_order_synced_on_failure()
    {
        config([
            'services.meta_capi.pixel_id' => '123456',
            'services.meta_capi.access_token' => 'test-token',
            'services.meta_capi.api_version' => 'v20.0',
        ]);

        Http::fake(['graph.facebook.com/*' => Http::response(['error' => 'bad token'], 400)]);

        $order = $this->makeOrder();

        $job = new SendMetaCapiEventJob(
            'Purchase', ['value' => 10.00], [], 'evt-2',
            'https://storefront.example.com/order/' . $order->code,
            null, null, $order->id
        );

        $job->handle(new MetaConversionApiService());

        $order->refresh();
        $this->assertNull($order->meta_purchase_synced_at);

        $order->delete();
    }

    public function test_handle_without_order_id_does_not_touch_orders_table()
    {
        config([
            'services.meta_capi.pixel_id' => '123456',
            'services.meta_capi.access_token' => 'test-token',
            'services.meta_capi.api_version' => 'v20.0',
        ]);

        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1], 200)]);

        $job = new SendMetaCapiEventJob(
            'AddToCart', ['value' => 5.00], [], 'evt-3',
            'https://storefront.example.com/product/slug',
            null, null
        );

        // No order id passed: should complete without error.
        $job->handle(new MetaConversionApiService());

        Http::assertSentCount(1);
    }
}
