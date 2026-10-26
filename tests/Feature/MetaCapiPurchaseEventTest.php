<?php

namespace Tests\Feature;

use App\Jobs\SendMetaCapiEventJob;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MetaCapiPurchaseEventTest extends TestCase
{
    /**
     * send_meta_capi_purchase_event() is gated on MetaConversionApiService::isEnabled(),
     * so the helper must be configured/enabled for these tests to exercise it.
     */
    protected function enableCapi(): void
    {
        config([
            'services.meta_capi.pixel_id' => '123456',
            'services.meta_capi.access_token' => 'test-token',
        ]);

        // BusinessSetting is fully guarded (no $fillable/$guarded), so mass-assignment
        // helpers throw — find the row and assign attributes manually.
        $setting = BusinessSetting::where('type', 'facebook_conversion_api')->first();
        if (!$setting) {
            $setting = new BusinessSetting;
            $setting->type = 'facebook_conversion_api';
        }
        $setting->value = 1;
        $setting->save();

        // get_setting() reads through a long-lived cache entry.
        Cache::forget('business_settings');
    }

    protected function makeOrder(): Order
    {
        $this->enableCapi();

        $user = User::first();

        if (!$user) {
            $this->markTestSkipped('No user found to attach a test order to');
        }

        $order = new Order;
        $order->user_id = $user->id;
        $order->code = 'TESTPUR' . uniqid();
        $order->date = strtotime('now');
        $order->payment_status = 'unpaid';
        $order->payment_type = 'cash_on_delivery';
        $order->delivery_viewed = '0';
        $order->payment_status_viewed = '0';
        $order->grand_total = 42.50;
        // Matches production: CombinedOrder stores a plain JSON string that is copied
        // verbatim into Order's array-cast column, so the value round-trips as a string.
        $order->shipping_address = json_encode(['email' => 'shipper@example.com', 'phone' => '+1 555 123 0000']);
        $order->order_from = 'app';
        $order->save();

        return $order;
    }

    public function test_first_call_generates_event_id_and_dispatches_job()
    {
        Bus::fake();

        $order = $this->makeOrder();

        send_meta_capi_purchase_event($order);

        $order->refresh();
        $this->assertNotNull($order->meta_purchase_event_id);

        Bus::assertDispatched(SendMetaCapiEventJob::class);

        $order->delete();
    }

    public function test_second_call_is_a_no_op_once_synced()
    {
        Bus::fake();

        $order = $this->makeOrder();
        $order->meta_purchase_synced_at = now();
        $order->save();

        send_meta_capi_purchase_event($order);

        Bus::assertNotDispatched(SendMetaCapiEventJob::class);

        $order->delete();
    }

    public function test_reuses_existing_event_id_on_repeated_unsynced_calls()
    {
        Bus::fake();

        $order = $this->makeOrder();

        send_meta_capi_purchase_event($order);
        $order->refresh();
        $firstEventId = $order->meta_purchase_event_id;

        send_meta_capi_purchase_event($order);
        $order->refresh();

        $this->assertEquals($firstEventId, $order->meta_purchase_event_id);

        $order->delete();
    }
}
