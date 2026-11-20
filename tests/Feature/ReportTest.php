<?php

namespace Tests\Feature;

use App\Models\Dispatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_builder_page_loads_for_authenticated_users(): void
    {
        $user = User::factory()->staff()->create();

        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);
        $response->assertSee('Reports &amp; Analytics', false);
    }

    public function test_csv_export_returns_streamed_csv_response(): void
    {
        $user = User::factory()->staff()->create();
        $dispatch = Dispatch::factory()->create([
            'loading_quantity' => 45.500,
        ]);

        $response = $this->actingAs($user)->get('/reports/export/csv');

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));

        // Verify CSV content stream
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('"Dispatch Number"', $content);
        $this->assertStringContainsString('"Loading Date"', $content);
        $this->assertStringContainsString($dispatch->dispatch_number, $content);
        $this->assertStringContainsString('TOTALS', $content);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'report.exported_csv',
        ]);
    }

    public function test_pdf_export_returns_pdf_document(): void
    {
        $user = User::factory()->staff()->create();
        $dispatch = Dispatch::factory()->create([
            'loading_quantity' => 38.200,
        ]);

        $response = $this->actingAs($user)->get('/reports/export/pdf');

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'application/pdf'));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'report.exported_pdf',
        ]);
    }
}
