<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_elements(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@loadora.test',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        
        $content = $response->getContent();
        
        // Assert Orange + Quick-Add Button exists and points to /dispatches?create=true
        $this->assertStringContainsString('bg-orange-500', $content);
        $this->assertStringContainsString('/dispatches?create=true', $content);
        
        // Assert Notification Dropdown with Alpine.js data & alerts
        $this->assertStringContainsString('Notifications', $content);
        $this->assertStringContainsString('markAllAsRead()', $content);
        $this->assertStringContainsString('loadora_read_alerts', $content);
        
        // Assert Mobile 2-column KPI grid
        $this->assertStringContainsString('grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-5', $content);
        
        // Assert Donut & Wave Chart canvases with responsive sizing
        $this->assertStringContainsString('goodsDonutChart', $content);
        $this->assertStringContainsString('monthlyTrendChart', $content);
        $this->assertStringContainsString('h-36 sm:h-44 lg:h-52', $content);
        
        // Assert responsive table wrappers prevent horizontal page overflow
        $this->assertStringContainsString('overflow-x-auto mt-2 -mx-3.5 sm:mx-0 px-3.5 sm:px-0', $content);
    }
}
