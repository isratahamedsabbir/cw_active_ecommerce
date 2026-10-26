<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\User;
use Tests\TestCase;

class FacebookCapiSettingsTest extends TestCase
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

    public function test_admin_can_enable_conversion_api_toggle()
    {
        $admin = User::where('user_type', 'admin')->first();

        if (!$admin) {
            $this->markTestSkipped('No admin user found');
        }

        $this->setBusinessSetting('facebook_pixel', 0);

        // 'types' is intentionally empty so overWriteEnvFile() has nothing to
        // iterate — this test must not mutate the real .env file on disk.
        $this->actingAs($admin)->post(route('facebook_pixel.update'), [
            'types' => [],
            'facebook_pixel' => '1',
            'facebook_conversion_api' => '1',
        ])->assertStatus(302);

        $this->assertDatabaseHas('business_settings', [
            'type' => 'facebook_conversion_api',
            'value' => 1,
        ]);
    }

    public function test_admin_can_disable_conversion_api_toggle()
    {
        $admin = User::where('user_type', 'admin')->first();

        if (!$admin) {
            $this->markTestSkipped('No admin user found');
        }

        $this->setBusinessSetting('facebook_pixel', 0);
        $this->setBusinessSetting('facebook_conversion_api', 1);

        $this->actingAs($admin)->post(route('facebook_pixel.update'), [
            'types' => [],
        ])->assertStatus(302);

        $this->assertDatabaseHas('business_settings', [
            'type' => 'facebook_conversion_api',
            'value' => 0,
        ]);
    }
}
