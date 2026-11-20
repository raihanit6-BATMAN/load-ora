<?php

namespace Tests\Feature;

use App\Models\Dispatch;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_all_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $routes = [
            '/dashboard',
            '/dispatches',
            '/receipts',
            '/vehicles',
            '/goods',
            '/clients',
            '/reports',
            '/import',
            '/users',
            '/activity',
            '/settings',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($admin)->get($route);
            $response->assertStatus(200);
        }
    }

    public function test_staff_can_access_operational_routes(): void
    {
        $staff = User::factory()->staff()->create();

        $allowedRoutes = [
            '/dashboard',
            '/dispatches',
            '/receipts',
            '/vehicles',
            '/goods',
            '/clients',
            '/reports',
        ];

        foreach ($allowedRoutes as $route) {
            $response = $this->actingAs($staff)->get($route);
            $response->assertStatus(200);
        }
    }

    public function test_staff_cannot_access_admin_exclusive_routes(): void
    {
        $staff = User::factory()->staff()->create();

        $forbiddenRoutes = [
            '/import',
            '/users',
            '/activity',
            '/settings',
        ];

        foreach ($forbiddenRoutes as $route) {
            $response = $this->actingAs($staff)->get($route);
            $response->assertStatus(403);
        }
    }

    public function test_staff_cannot_delete_dispatch_via_policy(): void
    {
        $staff = User::factory()->staff()->create();
        $admin = User::factory()->admin()->create();
        $dispatch = Dispatch::factory()->create();

        // Policy checks
        $this->assertFalse(Gate::forUser($staff)->allows('delete', $dispatch));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $dispatch));
    }

    public function test_staff_cannot_edit_old_dispatch_beyond_24_hours(): void
    {
        $staff = User::factory()->staff()->create();
        $admin = User::factory()->admin()->create();

        $recentDispatch = Dispatch::factory()->create([
            'created_at' => now()->subHours(2),
        ]);

        $oldDispatch = Dispatch::factory()->create([
            'created_at' => now()->subHours(48),
        ]);

        // Recent dispatch: Staff can update, Admin can update
        $this->assertTrue(Gate::forUser($staff)->allows('update', $recentDispatch));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $recentDispatch));

        // Old dispatch: Staff cannot update, Admin can update
        $this->assertFalse(Gate::forUser($staff)->allows('update', $oldDispatch));
        $this->assertTrue(Gate::forUser($admin)->allows('update', $oldDispatch));
    }

    public function test_staff_cannot_delete_receipt(): void
    {
        $staff = User::factory()->staff()->create();
        $admin = User::factory()->admin()->create();
        $receipt = Receipt::factory()->create();

        $this->assertFalse(Gate::forUser($staff)->allows('delete', $receipt));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $receipt));
    }
}
