<?php

namespace Tests\Feature;

use App\Livewire\Receipts\ReceiptIndex;
use App\Models\Dispatch;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_receipt_list(): void
    {
        $user = User::factory()->staff()->create();
        $receipt = Receipt::factory()->create();

        $response = $this->actingAs($user)->get('/receipts');

        $response->assertStatus(200);
        $response->assertSee($receipt->receipt_number);
    }

    public function test_user_can_record_receipt_and_dispatch_status_updates(): void
    {
        $user = User::factory()->staff()->create();
        $dispatch = Dispatch::factory()->create([
            'loading_quantity' => 30.500,
            'status' => 'pending',
            'pending_quantity' => 30.500,
        ]);

        Livewire::actingAs($user)
            ->test(ReceiptIndex::class)
            ->set('dispatch_id', $dispatch->id)
            ->set('receiving_date', '2026-11-19')
            ->set('received_quantity', '30.200')
            ->set('unloading_location', 'Yard B')
            ->set('remarks', 'Slight transit loss')
            ->call('store')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        // Verify receipt created with auto-increment sequence and deduction = 30.500 - 30.200 = 0.300
        $this->assertDatabaseHas('receipts', [
            'dispatch_id' => $dispatch->id,
            'sequence_number' => 1,
            'receipt_number' => '#1',
            'received_quantity' => 30.200,
            'deduction_quantity' => 0.300,
            'receipt_status' => 'shortage',
        ]);

        // Verify dispatch totals reconciled automatically
        $dispatch->refresh();
        $this->assertEquals(30.200, (float) $dispatch->total_received_quantity);
        $this->assertEquals(0.300, (float) $dispatch->total_deduction_quantity);
        $this->assertEquals('received', $dispatch->status);
        $this->assertEquals(0.000, (float) $dispatch->pending_quantity);
    }

    public function test_receipts_auto_increment_sequentially_with_lock(): void
    {
        $dispatch = Dispatch::factory()->create(['loading_quantity' => 100.000]);
        $user = User::factory()->admin()->create();

        $r1 = Receipt::create([
            'dispatch_id' => $dispatch->id,
            'receiving_date' => '2026-11-19',
            'received_quantity' => 20.000,
            'deduction_quantity' => 0.000,
            'created_by' => $user->id,
        ]);

        $r2 = Receipt::create([
            'dispatch_id' => $dispatch->id,
            'receiving_date' => '2026-11-19',
            'received_quantity' => 30.000,
            'deduction_quantity' => 0.000,
            'created_by' => $user->id,
        ]);

        $r3 = Receipt::create([
            'dispatch_id' => $dispatch->id,
            'receiving_date' => '2026-11-19',
            'received_quantity' => 50.000,
            'deduction_quantity' => 0.000,
            'created_by' => $user->id,
        ]);

        $this->assertEquals('#1', $r1->receipt_number);
        $this->assertEquals(1, $r1->sequence_number);

        $this->assertEquals('#2', $r2->receipt_number);
        $this->assertEquals(2, $r2->sequence_number);

        $this->assertEquals('#3', $r3->receipt_number);
        $this->assertEquals(3, $r3->sequence_number);
    }

    public function test_admin_can_delete_receipt_and_dispatch_recalculates(): void
    {
        $admin = User::factory()->admin()->create();
        $dispatch = Dispatch::factory()->create(['loading_quantity' => 40.000]);
        $receipt = Receipt::factory()->create([
            'dispatch_id' => $dispatch->id,
            'received_quantity' => 40.000,
            'deduction_quantity' => 0.000,
        ]);

        $dispatch->refresh();
        $this->assertEquals('received', $dispatch->status);

        Livewire::actingAs($admin)
            ->test(ReceiptIndex::class)
            ->call('delete', $receipt->id)
            ->assertDispatched('toast');

        $this->assertSoftDeleted('receipts', ['id' => $receipt->id]);

        $dispatch->refresh();
        $this->assertEquals('pending', $dispatch->status);
        $this->assertEquals(0.000, (float) $dispatch->total_received_quantity);
        $this->assertEquals(40.000, (float) $dispatch->pending_quantity);
    }

    public function test_staff_cannot_delete_receipt(): void
    {
        $staff = User::factory()->staff()->create();
        $receipt = Receipt::factory()->create();

        Livewire::actingAs($staff)
            ->test(ReceiptIndex::class)
            ->call('delete', $receipt->id)
            ->assertForbidden();

        $this->assertNotSoftDeleted('receipts', [
            'id' => $receipt->id,
        ]);
    }
}
