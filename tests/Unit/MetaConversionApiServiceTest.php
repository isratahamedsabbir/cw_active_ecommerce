<?php

namespace Tests\Unit;

use App\Services\MetaConversionApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaConversionApiServiceTest extends TestCase
{
    public function test_hash_email_normalizes_case_and_whitespace()
    {
        $service = new MetaConversionApiService();
        $expected = hash('sha256', 'user@example.com');

        $this->assertEquals($expected, $service->hashEmail(' User@Example.com '));
        $this->assertEquals($expected, $service->hashEmail('USER@EXAMPLE.COM'));
    }

    public function test_hash_phone_strips_non_digit_characters()
    {
        $service = new MetaConversionApiService();
        $expected = hash('sha256', '15551234567');

        $this->assertEquals($expected, $service->hashPhone('+1 555 123 4567'));
        $this->assertEquals($expected, $service->hashPhone('15551234567'));
    }

    public function test_send_posts_hashed_user_data_and_passthrough_fields()
    {
        config([
            'services.meta_capi.pixel_id' => '123456',
            'services.meta_capi.access_token' => 'test-token',
            'services.meta_capi.api_version' => 'v20.0',
            'services.meta_capi.test_event_code' => null,
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1], 200),
        ]);

        $service = new MetaConversionApiService();

        $result = $service->send(
            'AddToCart',
            ['currency' => 'USD', 'value' => 19.99],
            [
                'email' => ' User@Example.com ',
                'phone' => '+1 555 123 4567',
                'fbp' => 'fb.1.111.222',
                'fbc' => 'fb.1.111.333',
            ],
            'event-id-1',
            'https://storefront.example.com/product/slug',
            '203.0.113.5',
            'Mozilla/5.0'
        );

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            $body = $request->data();
            $eventData = $body['data'][0];

            return $request->url() === 'https://graph.facebook.com/v20.0/123456/events'
                && $body['access_token'] === 'test-token'
                && !array_key_exists('test_event_code', $body)
                && $eventData['event_name'] === 'AddToCart'
                && $eventData['event_id'] === 'event-id-1'
                && $eventData['event_source_url'] === 'https://storefront.example.com/product/slug'
                && $eventData['action_source'] === 'website'
                && $eventData['user_data']['em'][0] === hash('sha256', 'user@example.com')
                && $eventData['user_data']['ph'][0] === hash('sha256', '15551234567')
                && $eventData['user_data']['fbp'] === 'fb.1.111.222'
                && $eventData['user_data']['fbc'] === 'fb.1.111.333'
                && $eventData['user_data']['client_ip_address'] === '203.0.113.5'
                && $eventData['user_data']['client_user_agent'] === 'Mozilla/5.0'
                && $eventData['custom_data']['currency'] === 'USD';
        });
    }

    public function test_send_includes_test_event_code_when_configured()
    {
        config([
            'services.meta_capi.pixel_id' => '123456',
            'services.meta_capi.access_token' => 'test-token',
            'services.meta_capi.api_version' => 'v20.0',
            'services.meta_capi.test_event_code' => 'TEST12345',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1], 200),
        ]);

        (new MetaConversionApiService())->send(
            'ViewContent', [], [], 'event-id-2', 'https://storefront.example.com/', null, null
        );

        Http::assertSent(function ($request) {
            return $request->data()['test_event_code'] === 'TEST12345';
        });
    }

    public function test_send_returns_false_without_throwing_on_failure()
    {
        config([
            'services.meta_capi.pixel_id' => '123456',
            'services.meta_capi.access_token' => 'test-token',
            'services.meta_capi.api_version' => 'v20.0',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response(['error' => 'invalid token'], 400),
        ]);

        $result = (new MetaConversionApiService())->send(
            'ViewContent', [], [], 'event-id-3', 'https://storefront.example.com/', null, null
        );

        $this->assertFalse($result);
    }
}
