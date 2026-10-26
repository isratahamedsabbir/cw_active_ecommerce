<?php

namespace Tests\Feature;

use App\Jobs\SendMetaCapiEventJob;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class MetaCapiEventEndpointTest extends TestCase
{
    /**
     * BusinessSetting has no $fillable/$guarded override, so it is fully guarded
     * and mass-assignment helpers (firstOrCreate/updateOrCreate) throw. Follow the
     * codebase convention of finding the row and assigning attributes manually.
     */
    protected function setBusinessSetting(string $type, $value): void
    {
        $setting = BusinessSetting::where('type', $type)->first();
        if (!$setting) {
            $setting = new BusinessSetting;
            $setting->type = $type;
        }
        $setting->value = $value;
        $setting->save();
    }

    protected function enableCapi(): void
    {
        config([
            'services.meta_capi.pixel_id' => '123456',
            'services.meta_capi.access_token' => 'test-token',
        ]);

        $this->setBusinessSetting('facebook_conversion_api', 1);
    }

    public function test_rejects_event_name_outside_whitelist()
    {
        $this->postJson('/api/v2/meta-capi/event', [
            'event_name' => 'Purchase',
            'event_id' => 'evt-1',
            'event_source_url' => 'https://storefront.example.com/',
        ])->assertStatus(422);
    }

    public function test_rejects_missing_required_fields()
    {
        $this->postJson('/api/v2/meta-capi/event', [
            'event_name' => 'AddToCart',
        ])->assertStatus(422);
    }

    public function test_dispatches_job_for_whitelisted_event_when_enabled()
    {
        Bus::fake();
        $this->enableCapi();

        $this->postJson('/api/v2/meta-capi/event', [
            'event_name' => 'AddToCart',
            'event_id' => 'evt-2',
            'event_source_url' => 'https://storefront.example.com/product/slug',
            'user_data' => ['email' => 'user@example.com'],
            'custom_data' => ['currency' => 'USD', 'value' => 9.99],
        ])->assertOk()
          ->assertJson(['result' => true]);

        Bus::assertDispatched(SendMetaCapiEventJob::class);
    }

    public function test_does_not_dispatch_job_when_capi_disabled()
    {
        Bus::fake();

        $this->setBusinessSetting('facebook_conversion_api', 0);

        $this->postJson('/api/v2/meta-capi/event', [
            'event_name' => 'ViewContent',
            'event_id' => 'evt-3',
            'event_source_url' => 'https://storefront.example.com/product/slug',
        ])->assertOk()
          ->assertJson(['result' => true]);

        Bus::assertNotDispatched(SendMetaCapiEventJob::class);
    }
}
