<?php

namespace Tests\Feature;

use App\Models\CustomReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomReviewCrudTest extends TestCase
{
    public function test_admin_can_store_custom_review()
    {
        $admin = User::where('user_type', 'admin')->first();
        if (!$admin) {
            $this->markTestSkipped('No admin user found');
        }

        $response = $this->actingAs($admin)->post('/admin/website/custom-review/store', [
            'name' => 'Feature Test Review',
            'thumbnail' => '',
            'video_link' => 'https://youtube.com/watch?v=test',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('custom_reviews', [
            'name' => 'Feature Test Review',
        ]);
    }

    public function test_admin_can_destroy_custom_review()
    {
        $admin = User::where('user_type', 'admin')->first();
        if (!$admin) {
            $this->markTestSkipped('No admin user found');
        }

        $review = CustomReview::create([
            'name' => 'Delete Me',
            'thumbnail' => null,
            'video_link' => null,
        ]);

        $this->actingAs($admin)->delete('/admin/website/custom-review/destroy/' . $review->id);

        $this->assertDatabaseMissing('custom_reviews', ['id' => $review->id]);
    }

    public function test_admin_can_update_status()
    {
        $admin = User::where('user_type', 'admin')->first();
        if (!$admin) {
            $this->markTestSkipped('No admin user found');
        }

        $review = CustomReview::create([
            'name' => 'Status Toggle',
            'thumbnail' => null,
            'video_link' => null,
            'status' => 0,
        ]);

        $this->actingAs($admin)->post('/admin/website/custom-review/status', [
            'id' => $review->id,
            'status' => 1,
        ])->assertOk();

        $this->assertDatabaseHas('custom_reviews', [
            'id' => $review->id,
            'status' => 1,
        ]);
    }
}
