<?php

namespace Tests\Unit;

use App\Models\ConversionHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversionHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function createConversion(array $overrides = []): ConversionHistory
    {
        return ConversionHistory::create(array_merge([
            'visitor_id' => 'test-visitor',
            'font_names' => 'TestFont',
            'zip_path' => 'fonts_out/testfont.zip',
            'file_count' => 1,
            'status' => ConversionHistory::STATUS_PENDING,
        ], $overrides));
    }

    // ── Status Helpers ──

    public function test_is_pending(): void
    {
        $conversion = $this->createConversion(['status' => ConversionHistory::STATUS_PENDING]);
        $this->assertTrue($conversion->isPending());
        $this->assertFalse($conversion->isProcessing());
        $this->assertFalse($conversion->isCompleted());
        $this->assertFalse($conversion->isFailed());
    }

    public function test_is_processing(): void
    {
        $conversion = $this->createConversion(['status' => ConversionHistory::STATUS_PROCESSING]);
        $this->assertTrue($conversion->isProcessing());
        $this->assertFalse($conversion->isPending());
    }

    public function test_is_completed(): void
    {
        $conversion = $this->createConversion(['status' => ConversionHistory::STATUS_COMPLETED]);
        $this->assertTrue($conversion->isCompleted());
        $this->assertFalse($conversion->isFailed());
    }

    public function test_is_failed(): void
    {
        $conversion = $this->createConversion(['status' => ConversionHistory::STATUS_FAILED]);
        $this->assertTrue($conversion->isFailed());
        $this->assertFalse($conversion->isCompleted());
    }

    // ── Mark Methods ──

    public function test_mark_as_updates_status(): void
    {
        $conversion = $this->createConversion();
        $this->assertEquals(ConversionHistory::STATUS_PENDING, $conversion->status);

        $conversion->markAs(ConversionHistory::STATUS_PROCESSING);
        $this->assertDatabaseHas('conversion_histories', [
            'id' => $conversion->id,
            'status' => ConversionHistory::STATUS_PROCESSING,
        ]);
    }

    public function test_mark_failed_sets_status_and_error(): void
    {
        $conversion = $this->createConversion();

        $conversion->markFailed('Something went wrong');

        $this->assertDatabaseHas('conversion_histories', [
            'id' => $conversion->id,
            'status' => ConversionHistory::STATUS_FAILED,
            'error_message' => 'Something went wrong',
        ]);
    }

    // ── Full Path Accessor ──

    public function test_full_path_resolves_correctly(): void
    {
        $conversion = $this->createConversion(['zip_path' => 'fonts_out/myfont.zip']);

        $this->assertEquals(
            storage_path('app/fonts_out/myfont.zip'),
            $conversion->full_path
        );
    }

    // ── Fillable Attributes ──

    public function test_fillable_attributes(): void
    {
        $conversion = $this->createConversion([
            'visitor_id' => 'visitor-123',
            'font_names' => 'Font1, Font2',
            'zip_path' => 'fonts_out/font1.zip',
            'file_count' => 2,
            'status' => ConversionHistory::STATUS_COMPLETED,
            'error_message' => null,
        ]);

        $this->assertEquals('visitor-123', $conversion->visitor_id);
        $this->assertEquals('Font1, Font2', $conversion->font_names);
        $this->assertEquals('fonts_out/font1.zip', $conversion->zip_path);
        $this->assertEquals(2, $conversion->file_count);
        $this->assertEquals(ConversionHistory::STATUS_COMPLETED, $conversion->status);
        $this->assertNull($conversion->error_message);
    }

    // ── Database Defaults ──

    public function test_status_defaults_to_pending_via_database(): void
    {
        $conversion = ConversionHistory::create([
            'visitor_id' => 'test',
            'font_names' => 'Test',
            'zip_path' => '',
            'file_count' => 1,
        ]);

        $this->assertDatabaseHas('conversion_histories', [
            'id' => $conversion->id,
            'status' => 'pending',
        ]);
    }

    public function test_error_message_defaults_to_null(): void
    {
        $conversion = $this->createConversion();
        $this->assertNull($conversion->error_message);
    }
}
