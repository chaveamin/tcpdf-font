<?php

namespace Tests\Feature;

use App\Jobs\ConvertFontJob;
use App\Models\ConversionHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FontConverterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    // ── Index Page ──

    public function test_index_returns_200(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_index_sets_visitor_id_cookie(): void
    {
        $this->get('/')->assertCookie('visitor_id');
    }

    // ── Convert (Upload) ──

    public function test_convert_requires_font_files(): void
    {
        $this->postJson('/convert')->assertStatus(422);
    }

    public function test_convert_rejects_non_ttf_files(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->postJson('/convert', [
            'font' => [$file],
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message']);
    }

    public function test_convert_accepts_valid_ttf_upload(): void
    {
        Bus::fake();

        $file = UploadedFile::fake()->create('myfont.ttf', 100, 'font/ttf');

        $response = $this->postJson('/convert', [
            'font' => [$file],
        ]);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'conversion_id',
                'status_url',
                'font_names',
                'file_count',
            ]);

        Bus::assertDispatched(ConvertFontJob::class);
    }

    public function test_convert_limits_to_20_files(): void
    {
        Bus::fake();

        $files = [];
        for ($i = 0; $i < 21; $i++) {
            $files[] = UploadedFile::fake()->create("font{$i}.ttf", 100, 'font/ttf');
        }

        $this->postJson('/convert', ['font' => $files])->assertStatus(422);
    }

    public function test_convert_dispatches_job_with_correct_data(): void
    {
        Bus::fake();

        $file = UploadedFile::fake()->create('vazirmatn.ttf', 100, 'font/ttf');

        $this->postJson('/convert', ['font' => [$file]]);

        Bus::assertDispatched(ConvertFontJob::class, function ($job) {
            return $job->mode === 'upload'
                && $job->conversion instanceof ConversionHistory
                && count($job->uploadFiles) === 1;
        });
    }

    public function test_convert_creates_conversion_history_record(): void
    {
        Bus::fake();

        $file = UploadedFile::fake()->create('test.ttf', 100, 'font/ttf');

        $this->postJson('/convert', ['font' => [$file]]);

        $this->assertDatabaseHas('conversion_histories', [
            'font_names' => 'test.ttf',
            'file_count' => 1,
            'status' => ConversionHistory::STATUS_PENDING,
        ]);
    }

    public function test_convert_stores_temp_files(): void
    {
        Bus::fake();

        $file = UploadedFile::fake()->create('arial.ttf', 100, 'font/ttf');

        $this->postJson('/convert', ['font' => [$file]]);

        $tempPath = storage_path('app/temp_fonts/arial.ttf');
        $this->assertFileExists($tempPath);
    }

    // ── Google Fonts Search ──

    public function test_search_google_fonts_requires_api_key(): void
    {
        config(['services.google_fonts.key' => null]);

        $this->getJson('/google-fonts/search?q=roboto')
            ->assertStatus(500)
            ->assertJson(['message' => 'کلید API گوگل فونت تنظیم نشده است.']);
    }

    public function test_search_google_fonts_returns_results(): void
    {
        config(['services.google_fonts.key' => 'test-key']);

        Http::fake([
            'googleapis.com/*' => Http::response([
                'items' => [
                    ['family' => 'Roboto', 'variants' => ['100', '300', '400'], 'category' => 'sans-serif'],
                    ['family' => 'Roboto Slab', 'variants' => ['100', '400'], 'category' => 'serif'],
                ],
            ]),
        ]);

        $response = $this->getJson('/google-fonts/search?q=roboto');

        $response->assertOk()
            ->assertJsonCount(2)
            ->assertJsonFragment(['family' => 'Roboto']);
    }

    public function test_search_google_fonts_filters_by_query(): void
    {
        config(['services.google_fonts.key' => 'test-key']);

        Http::fake([
            'googleapis.com/*' => Http::response([
                'items' => [
                    ['family' => 'Roboto', 'variants' => ['400'], 'category' => 'sans-serif'],
                    ['family' => 'Open Sans', 'variants' => ['400'], 'category' => 'sans-serif'],
                ],
            ]),
        ]);

        $response = $this->getJson('/google-fonts/search?q=roboto');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['family' => 'Roboto']);
    }

    public function test_search_google_fonts_handles_api_failure(): void
    {
        config(['services.google_fonts.key' => 'test-key']);

        Http::fake([
            'googleapis.com/*' => Http::response([], 500),
        ]);

        $this->getJson('/google-fonts/search?q=test')
            ->assertStatus(502);
    }

    // ── Google Fonts Convert ──

    public function test_convert_google_font_requires_api_key(): void
    {
        config(['services.google_fonts.key' => null]);

        $this->postJson('/google-fonts/convert', [
            'family' => 'Roboto',
            'variant' => '400',
        ])->assertStatus(500);
    }

    public function test_convert_google_font_requires_family_and_variant(): void
    {
        config(['services.google_fonts.key' => 'test-key']);

        $this->postJson('/google-fonts/convert', [])->assertStatus(422);
        $this->postJson('/google-fonts/convert', ['family' => 'Roboto'])->assertStatus(422);
        $this->postJson('/google-fonts/convert', ['variant' => '400'])->assertStatus(422);
    }

    public function test_convert_google_font_dispatches_job(): void
    {
        Bus::fake();
        config(['services.google_fonts.key' => 'test-key']);

        $response = $this->postJson('/google-fonts/convert', [
            'family' => 'Roboto',
            'variant' => '400',
        ]);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'conversion_id',
                'status_url',
                'font_names',
            ]);

        Bus::assertDispatched(ConvertFontJob::class, function ($job) {
            return $job->mode === 'google'
                && $job->family === 'Roboto'
                && $job->variant === '400';
        });
    }

    // ── Status Endpoint ──

    public function test_status_returns_403_for_wrong_visitor(): void
    {
        $conversion = ConversionHistory::create([
            'visitor_id' => 'owner-visitor',
            'font_names' => 'TestFont',
            'zip_path' => '',
            'file_count' => 1,
            'status' => ConversionHistory::STATUS_PENDING,
        ]);

        $this->getJson("/conversion/{$conversion->id}/status")
            ->assertStatus(403);
    }

    // ── Download Endpoint ──

    public function test_download_returns_403_for_wrong_visitor(): void
    {
        $conversion = ConversionHistory::create([
            'visitor_id' => 'owner-visitor',
            'font_names' => 'TestFont',
            'zip_path' => 'fonts_out/test.zip',
            'file_count' => 1,
            'status' => ConversionHistory::STATUS_COMPLETED,
        ]);

        $this->get("/download/{$conversion->id}")
            ->assertStatus(403);
    }

    // ── Throttle Middleware ──

    public function test_convert_endpoint_is_throttled(): void
    {
        Bus::fake();

        // Exhaust the throttle limit (default 60/min for 'upload')
        for ($i = 0; $i < 60; $i++) {
            $file = UploadedFile::fake()->create("font{$i}.ttf", 100, 'font/ttf');
            $this->postJson('/convert', ['font' => [$file]]);
        }

        $file = UploadedFile::fake()->create('extra.ttf', 100, 'font/ttf');
        $this->postJson('/convert', ['font' => [$file]])
            ->assertStatus(429);
    }
}
