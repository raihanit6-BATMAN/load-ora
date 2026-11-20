<?php

namespace Tests\Feature;

use App\Livewire\Dispatches\DispatchIndex;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Dispatch;
use App\Models\Goods;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_dispatch_list(): void
    {
        $user = User::factory()->staff()->create();
        $dispatches = Dispatch::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/dispatches');

        $response->assertStatus(200);
        foreach ($dispatches as $dispatch) {
            $response->assertSee($dispatch->dispatch_number);
        }
    }

    public function test_user_can_create_dispatch_via_livewire(): void
    {
        $user = User::factory()->staff()->create();
        $client = Client::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $goods = Goods::factory()->create();

        Livewire::actingAs($user)
            ->test(DispatchIndex::class)
            ->set('dispatch_number', 'DSP-20261119-9999')
            ->set('client_id', $client->id)
            ->set('vehicle_id', $vehicle->id)
            ->set('driver_name', 'Mizanur Rahman')
            ->set('goods_id', $goods->id)
            ->set('loading_date', '2026-11-19')
            ->set('loading_quantity', '32.450')
            ->set('destination_location', 'Site Alpha')
            ->set('note', 'Initial test consignment')
            ->call('store')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('dispatches', [
            'dispatch_number' => 'DSP-20261119-9999',
            'client_id' => $client->id,
            'vehicle_id' => $vehicle->id,
            'driver_name' => 'Mizanur Rahman',
            'goods_id' => $goods->id,
            'loading_quantity' => 32.450,
            'status' => 'pending',
            'pending_quantity' => 32.450,
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'dispatch.created',
            'user_id' => $user->id,
        ]);
    }

    public function test_dispatch_creates_vehicle_with_split_prefix_and_manual_number(): void
    {
        $user = User::factory()->staff()->create();
        $client = Client::factory()->create();
        $goods = Goods::factory()->create();

        Livewire::actingAs($user)
            ->test(DispatchIndex::class)
            ->call('openCreateModal')
            ->set('dispatch_number', 'DSP-20261119-7001')
            ->set('client_id', $client->id)
            ->set('vehicle_prefix', 'DMT')
            ->set('vehicle_number_input', '11-1234')
            ->set('driver_name', 'Salam Mia')
            ->set('goods_id', $goods->id)
            ->set('loading_date', '2026-11-19')
            ->set('loading_quantity', '28.500')
            ->set('destination_location', 'P-Block')
            ->call('store')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        // Confirm vehicle was automatically registered as active
        $this->assertDatabaseHas('vehicles', [
            'vehicle_number' => 'DMT 11-1234',
            'status' => 'active',
        ]);

        $vehicle = Vehicle::where('vehicle_number', 'DMT 11-1234')->first();
        $this->assertNotNull($vehicle);

        // Confirm dispatch linked to the newly registered vehicle
        $this->assertDatabaseHas('dispatches', [
            'dispatch_number' => 'DSP-20261119-7001',
            'vehicle_id' => $vehicle->id,
            'driver_name' => 'Salam Mia',
        ]);
    }

    public function test_dispatch_creates_vehicle_with_other_prefix(): void
    {
        $user = User::factory()->staff()->create();
        $client = Client::factory()->create();
        $goods = Goods::factory()->create();

        Livewire::actingAs($user)
            ->test(DispatchIndex::class)
            ->call('openCreateModal')
            ->set('dispatch_number', 'DSP-20261119-7002')
            ->set('client_id', $client->id)
            ->set('vehicle_prefix', 'Other')
            ->set('vehicle_number_input', 'CUSTOM-TRUCK-88')
            ->set('goods_id', $goods->id)
            ->set('loading_date', '2026-11-19')
            ->set('loading_quantity', '22.000')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('vehicles', [
            'vehicle_number' => 'CUSTOM-TRUCK-88',
            'status' => 'active',
        ]);

        $vehicle = Vehicle::where('vehicle_number', 'CUSTOM-TRUCK-88')->first();
        $this->assertDatabaseHas('dispatches', [
            'dispatch_number' => 'DSP-20261119-7002',
            'vehicle_id' => $vehicle->id,
        ]);
    }

    public function test_dispatch_uses_existing_vehicle_without_duplicate(): void
    {
        $user = User::factory()->staff()->create();
        $existing = Vehicle::factory()->create([
            'vehicle_number' => 'BMT 16-6520',
            'status' => 'active',
        ]);
        $goods = Goods::factory()->create();

        Livewire::actingAs($user)
            ->test(DispatchIndex::class)
            ->call('openCreateModal')
            ->set('dispatch_number', 'DSP-20261119-7003')
            ->set('vehicle_prefix', 'BMT')
            ->set('vehicle_number_input', '16-6520')
            ->set('goods_id', $goods->id)
            ->set('loading_date', '2026-11-19')
            ->set('loading_quantity', '31.000')
            ->call('store')
            ->assertHasNoErrors();

        // Exactly 1 vehicle with this number exists
        $this->assertEquals(1, Vehicle::where('vehicle_number', 'BMT 16-6520')->count());

        $this->assertDatabaseHas('dispatches', [
            'dispatch_number' => 'DSP-20261119-7003',
            'vehicle_id' => $existing->id,
        ]);
    }

    public function test_dispatch_creation_validates_required_fields(): void
    {
        $user = User::factory()->staff()->create();

        Livewire::actingAs($user)
            ->test(DispatchIndex::class)
            ->set('dispatch_number', '')
            ->set('vehicle_id', null)
            ->set('goods_id', null)
            ->set('loading_date', '')
            ->set('loading_quantity', '')
            ->call('store')
            ->assertHasErrors(['dispatch_number', 'vehicle_id', 'goods_id', 'loading_date', 'loading_quantity']);
    }

    public function test_admin_can_update_dispatch(): void
    {
        $admin = User::factory()->admin()->create();
        $dispatch = Dispatch::factory()->create([
            'loading_quantity' => 30.000,
            'destination_location' => 'Old Site',
        ]);

        Livewire::actingAs($admin)
            ->test(DispatchIndex::class)
            ->call('openEditModal', $dispatch->id)
            ->set('loading_quantity', '35.500')
            ->set('destination_location', 'Updated Site')
            ->call('update')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('dispatches', [
            'id' => $dispatch->id,
            'loading_quantity' => 35.500,
            'destination_location' => 'Updated Site',
        ]);
    }

    public function test_admin_can_delete_dispatch(): void
    {
        $admin = User::factory()->admin()->create();
        $dispatch = Dispatch::factory()->create();

        Livewire::actingAs($admin)
            ->test(DispatchIndex::class)
            ->call('delete', $dispatch->id)
            ->assertDispatched('toast');

        $this->assertSoftDeleted('dispatches', [
            'id' => $dispatch->id,
        ]);
    }

    public function test_staff_cannot_delete_dispatch(): void
    {
        $staff = User::factory()->staff()->create();
        $dispatch = Dispatch::factory()->create();

        Livewire::actingAs($staff)
            ->test(DispatchIndex::class)
            ->call('delete', $dispatch->id)
            ->assertForbidden();

        $this->assertNotSoftDeleted('dispatches', [
            'id' => $dispatch->id,
        ]);
    }

    public function test_dispatch_modals_hide_dispatch_number_and_slip_number(): void
    {
        $user = User::factory()->staff()->create();
        $dispatch = Dispatch::factory()->create();

        // 1. Create Modal: Open create modal, confirm Dispatch # input is not rendered in form
        $createTest = Livewire::actingAs($user)
            ->test(DispatchIndex::class)
            ->call('openCreateModal');

        $createTest->assertSee('New Material Dispatch')
            ->assertSee('Loading Date')
            ->assertSee('Transport Vehicle')
            ->assertDontSee('Slip #')
            ->assertDontSee('Weighbridge Slip');

        // Confirm there is no input labeled "Dispatch #" in the modal
        $createHtml = $createTest->html();
        $this->assertStringNotContainsString('<label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Dispatch #</label>', $createHtml);

        // 2. Edit Modal: Open edit modal, confirm Dispatch # input is not rendered in form
        $editTest = Livewire::actingAs($user)
            ->test(DispatchIndex::class)
            ->call('openEditModal', $dispatch->id);

        $editTest->assertSee('Edit Material Dispatch')
            ->assertDontSee('Slip #');

        $editHtml = $editTest->html();
        $this->assertStringNotContainsString('<label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Dispatch #</label>', $editHtml);
    }
}
