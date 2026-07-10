<?php

namespace App\Jobs;

use App\Models\ConversionHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class ConvertFontJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public ConversionHistory $conversion,
        public string $mode,
        public array $uploadFiles = [],
        public ?string $family = null,
        public ?string $variant = null,
    ) {}

    public function handle(): void
    {
        $this->conversion->markAs(ConversionHistory::STATUS_PROCESSING);

        try {
            $convertedNames = [];
            $originalNames = [];

            if ($this->mode === 'google') {
                $result = $this->convertGoogleFont();
                $convertedNames = $result['converted'];
                $originalNames = $result['originals'];
            } else {
                $result = $this->convertUploadedFonts();
                $convertedNames = $result['converted'];
                $originalNames = $result['originals'];
            }

            if (empty($convertedNames)) {
                throw new \RuntimeException('تبدیل هیچ فونتی موفقیت‌آمیز نبود.');
            }

            $zipPath = $this->createZip($convertedNames, $originalNames);

            $this->conversion->update([
                'zip_path' => 'fonts_out/' . basename($zipPath),
                'font_names' => implode(', ', $originalNames),
            ]);

            $this->conversion->markAs(ConversionHistory::STATUS_COMPLETED);
        } catch (\Exception $e) {
            Log::error("Font conversion failed: {$e->getMessage()}", [
                'conversion_id' => $this->conversion->id,
            ]);
            $this->conversion->markFailed($e->getMessage());
            $this->cleanupTempFiles();
        }
    }

    private function convertUploadedFonts(): array
    {
        $tempDir = storage_path('app/temp_fonts/');
        $outDir  = storage_path('app/fonts_out/');
        if (!file_exists($outDir)) { mkdir($outDir, 0775, true); }

        $convertedNames = [];
        $originalNames = [];

        foreach ($this->uploadFiles as $originalName => $tempFileName) {
            $fontPath = $tempDir . $tempFileName;

            if (!file_exists($fontPath)) {
                Log::warning("Temp font file not found: {$fontPath}");
                continue;
            }

            $fontname = \TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32, $outDir);

            if (file_exists($fontPath)) {
                unlink($fontPath);
            }

            if (!$fontname) {
                Log::warning("Font conversion failed for: {$originalName}");
                continue;
            }

            $convertedNames[] = $fontname;
            $originalNames[] = $originalName;
        }

        return ['converted' => $convertedNames, 'originals' => $originalNames];
    }

    private function convertGoogleFont(): array
    {
        $tempDir = storage_path('app/temp_fonts/');
        $outDir  = storage_path('app/fonts_out/');
        if (!file_exists($tempDir)) { mkdir($tempDir, 0775, true); }
        if (!file_exists($outDir)) { mkdir($outDir, 0775, true); }

        $cssUrl = "https://fonts.googleapis.com/css2?family=" . urlencode($this->family) . ":wght@" . $this->variant;

        $cssResponse = Http::timeout(15)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ])->get($cssUrl);

        if ($cssResponse->failed()) {
            throw new \RuntimeException('خطا در دریافت فونت از گوگل.');
        }

        $css = $cssResponse->body();
        preg_match_all('/url\((https:\/\/[^)]+\.ttf)\)/', $css, $matches);

        if (empty($matches[1])) {
            throw new \RuntimeException('فایل TTF یافت نشد.');
        }

        $ttfUrl = $matches[1][0];

        $ttfResponse = Http::timeout(30)->get($ttfUrl);

        if ($ttfResponse->failed()) {
            throw new \RuntimeException('خطا در دانلود فایل فونت.');
        }

        $safeFontName = preg_replace('/[^a-z0-9]/', '', strtolower($this->family)) . '_' . preg_replace('/[^a-z0-9]/', '', strtolower($this->variant));
        $tempFileName = $safeFontName . '.ttf';
        $fontPath = $tempDir . $tempFileName;

        file_put_contents($fontPath, $ttfResponse->body());

        $fontname = \TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32, $outDir);

        if (file_exists($fontPath)) {
            unlink($fontPath);
        }

        if (!$fontname) {
            throw new \RuntimeException('تبدیل فونت ناموفق بود.');
        }

        $originalName = $this->family . ' ' . $this->variant;

        return ['converted' => [$fontname], 'originals' => [$originalName]];
    }

    private function createZip(array $convertedNames, array $originalNames): string
    {
        $outDir = storage_path('app/fonts_out/');

        $zipName = count($convertedNames) === 1
            ? strtolower(pathinfo($originalNames[0], PATHINFO_FILENAME))
            : 'tcpdf_fonts';
        $zipPath = $outDir . $zipName . '.zip';

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('خطا در ایجاد فایل زیپ.');
        }

        foreach ($convertedNames as $fontname) {
            foreach ([$fontname . '.php', $fontname . '.z', $fontname . '.ctg.z'] as $f) {
                $fp = $outDir . $f;
                if (file_exists($fp)) {
                    $zip->addFile($fp, $f);
                }
            }
        }

        $zip->close();

        foreach ($convertedNames as $fontname) {
            foreach ([$fontname . '.php', $fontname . '.z', $fontname . '.ctg.z'] as $f) {
                $fp = $outDir . $f;
                if (file_exists($fp)) {
                    unlink($fp);
                }
            }
        }

        return $zipPath;
    }

    private function cleanupTempFiles(): void
    {
        $tempDir = storage_path('app/temp_fonts/');

        if ($this->mode === 'upload') {
            foreach ($this->uploadFiles as $tempFileName) {
                $path = $tempDir . $tempFileName;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        } else {
            $safeFontName = preg_replace('/[^a-z0-9]/', '', strtolower($this->family)) . '_' . preg_replace('/[^a-z0-9]/', '', strtolower($this->variant));
            $path = $tempDir . $safeFontName . '.ttf';
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}
