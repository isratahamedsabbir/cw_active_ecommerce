<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderDetailMetaCapiFieldTest extends TestCase
{
    public function test_orders_table_has_meta_capi_columns()
    {
        $this->assertTrue(Schema::hasColumn('orders', 'meta_purchase_event_id'));
        $this->assertTrue(Schema::hasColumn('orders', 'meta_purchase_synced_at'));
    }

    public function test_order_detail_endpoint_exposes_meta_purchase_event_id()
    {
        $user = \App\Models\User::first();

        if (!$user) {
            $this->markTestSkipped('No user found to attach a test order to');
        }

        $order = new \App\Models\Order;
        $order->user_id = $user->id;
        $order->code = 'TESTDET' . uniqid();
        $order->date = strtotime('now');
        $order->payment_status = 'unpaid';
        $order->payment_type = 'cash_on_delivery';
        $order->delivery_viewed = '0';
        $order->payment_status_viewed = '0';
        $order->grand_total = 15.00;
        $order->shipping_address = [];
        $order->order_from = 'app';
        $order->meta_purchase_event_id = 'fixed-test-event-id';
        $order->save();

        $this->getJson('/api/v2/order/' . $order->code)
            ->assertOk()
            ->assertJson(['meta_purchase_event_id' => 'fixed-test-event-id']);

        $order->delete();
    }
}
