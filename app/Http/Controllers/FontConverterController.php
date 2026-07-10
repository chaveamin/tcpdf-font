<?php

namespace App\Http\Controllers;

use App\Models\ConversionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use ZipArchive;

class FontConverterController extends Controller
{
    private function getVisitorId(Request $request): string
    {
        $visitorId = $request->cookie('visitor_id');

        if (!$visitorId) {
            $visitorId = bin2hex(random_bytes(16));
        }

        return $visitorId;
    }

    public function index(Request $request)
    {
        $visitorId = $this->getVisitorId($request);
        $conversions = ConversionHistory::where('visitor_id', $visitorId)->latest()->take(10)->get();

        Cookie::queue('visitor_id', $visitorId, 60 * 24 * 365);

        return view('converter', compact('conversions'));
    }

    public function convert(Request $request)
    {
        $request->validate([
            'font'   => 'required|array|min:1|max:20',
            'font.*' => 'required|file|max:10240',
        ]);

        $tempDir = storage_path('app/temp_fonts/');
        $outDir  = storage_path('app/fonts_out/');
        if (!file_exists($tempDir)) { mkdir($tempDir, 0775, true); }
        if (!file_exists($outDir)) { mkdir($outDir, 0775, true); }

        $convertedNames = [];
        $originalNames = [];
        $errors = [];

        foreach ($request->file('font') as $file) {
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['ttf'])) {
                $errors[] = $file->getClientOriginalName() . ': فقط فایل‌های ttf پشتیبانی می‌شوند.';
                continue;
            }

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFontName = preg_replace('/[^a-z0-9]/', '', strtolower($originalName));
            $tempFileName = $safeFontName . '.' . $extension;
            $fontPath = $tempDir . $tempFileName;

            $file->move($tempDir, $tempFileName);

            $fontname = \TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32, $outDir);

            if (file_exists($fontPath)) {
                unlink($fontPath);
            }

            if (!$fontname) {
                $errors[] = $file->getClientOriginalName() . ': تبدیل ناموفق بود.';
                continue;
            }

            $convertedNames[] = $fontname;
            $originalNames[] = $file->getClientOriginalName();
        }

        if (empty($convertedNames)) {
            return response()->json(['message' => $errors ? implode("\n", $errors) : 'هیچ فایلی آپلود نشد.'], 422);
        }

        return $this->createZipAndRespond($convertedNames, $originalNames, $errors, $request);
    }

    public function searchGoogleFonts(Request $request)
    {
        $apiKey = config('services.google_fonts.key');

        if (!$apiKey) {
            return response()->json(['message' => 'کلید API گوگل فونت تنظیم نشده است.'], 500);
        }

        $query = strtolower($request->input('q', ''));

        try {
            $response = Http::timeout(15)->get('https://www.googleapis.com/webfonts/v1/webfonts', [
                'key'  => $apiKey,
                'sort' => 'popularity',
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'خطا در اتصال به گوگل فونت: ' . $e->getMessage()], 502);
        }

        if ($response->failed()) {
            return response()->json(['message' => 'خطا در دریافت اطلاعات از گوگل فونت.'], 502);
        }

        $data = $response->json();
        $fonts = collect($data['items'] ?? []);

        if ($query) {
            $fonts = $fonts->filter(fn ($font) => str_contains(strtolower($font['family']), $query));
        }

        $fonts = $fonts->take(20)->map(function ($font) {
            return [
                'family'   => $font['family'],
                'variants' => $font['variants'],
                'category' => $font['category'],
            ];
        })->values();

        return response()->json($fonts);
    }

    public function convertGoogleFont(Request $request)
    {
        $apiKey = config('services.google_fonts.key');

        if (!$apiKey) {
            return response()->json(['message' => 'کلید API گوگل فونت تنظیم نشده است.'], 500);
        }

        $request->validate([
            'family'  => 'required|string',
            'variant' => 'required|string',
        ]);

        $family = $request->input('family');
        $variant = $request->input('variant');

        $tempDir = storage_path('app/temp_fonts/');
        $outDir  = storage_path('app/fonts_out/');
        if (!file_exists($tempDir)) { mkdir($tempDir, 0775, true); }
        if (!file_exists($outDir)) { mkdir($outDir, 0775, true); }

        // Build the Google Fonts CSS URL to get the actual TTF URL
        $cssUrl = "https://fonts.googleapis.com/css2?family=" . urlencode($family) . ":wght@" . $variant;

        try {
            $cssResponse = Http::timeout(15)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            ])->get($cssUrl);
        } catch (\Exception $e) {
            return response()->json(['message' => 'خطا در اتصال به گوگل: ' . $e->getMessage()], 502);
        }

        if ($cssResponse->failed()) {
            return response()->json(['message' => 'خطا در دریافت فونت از گوگل.'], 502);
        }

        // Parse CSS to find the TTF url
        $css = $cssResponse->body();
        preg_match_all('/url\((https:\/\/[^)]+\.ttf)\)/', $css, $matches);

        if (empty($matches[1])) {
            return response()->json(['message' => 'فایل TTF یافت نشد.'], 502);
        }

        $ttfUrl = $matches[1][0];

        // Download the TTF
        try {
            $ttfResponse = Http::timeout(30)->get($ttfUrl);
        } catch (\Exception $e) {
            return response()->json(['message' => 'خطا در دانلود فونت: ' . $e->getMessage()], 502);
        }

        if ($ttfResponse->failed()) {
            return response()->json(['message' => 'خطا در دانلود فایل فونت.'], 502);
        }

        $safeFontName = preg_replace('/[^a-z0-9]/', '', strtolower($family)) . '_' . preg_replace('/[^a-z0-9]/', '', strtolower($variant));
        $tempFileName = $safeFontName . '.ttf';
        $fontPath = $tempDir . $tempFileName;

        file_put_contents($fontPath, $ttfResponse->body());

        $fontname = \TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32, $outDir);

        if (file_exists($fontPath)) {
            unlink($fontPath);
        }

        if (!$fontname) {
            return response()->json(['message' => 'تبدیل فونت ناموفق بود.'], 500);
        }

        $originalName = $family . ' ' . $variant;

        return $this->createZipAndRespond([$fontname], [$originalName], [], $request);
    }

    private function createZipAndRespond(array $convertedNames, array $originalNames, array $errors, Request $request)
    {
        $tempDir = storage_path('app/temp_fonts/');
        $outDir  = storage_path('app/fonts_out/');

        $zipName = count($convertedNames) === 1
            ? strtolower(pathinfo($originalNames[0], PATHINFO_FILENAME))
            : 'tcpdf_fonts';
        $zipPath = storage_path('app/fonts_out/' . $zipName . '.zip');
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($convertedNames as $fontname) {
                $filesToZip = [$fontname . '.php', $fontname . '.z', $fontname . '.ctg.z'];
                foreach ($filesToZip as $f) {
                    $fp = $outDir . $f;
                    if (file_exists($fp)) {
                        $zip->addFile($fp, $f);
                    }
                }
            }
            $zip->close();

            foreach ($convertedNames as $fontname) {
                $filesToZip = [$fontname . '.php', $fontname . '.z', $fontname . '.ctg.z'];
                foreach ($filesToZip as $f) {
                    $fp = $outDir . $f;
                    if (file_exists($fp)) {
                        unlink($fp);
                    }
                }
            }
        } else {
            return response()->json(['message' => 'خطا در تبدیل فونت'], 500);
        }

        $conversion = ConversionHistory::create([
            'visitor_id' => $this->getVisitorId($request),
            'font_names' => implode(', ', $originalNames),
            'zip_path'   => 'fonts_out/' . $zipName . '.zip',
            'file_count' => count($convertedNames),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            $data = [
                'download_url' => route('converter.download', $conversion),
                'font_names'   => implode(', ', $originalNames),
                'file_count'   => count($convertedNames),
            ];

            if ($errors) {
                $data['warnings'] = $errors;
            }

            return response()->json($data);
        }

        $response = response()->download($zipPath);

        if ($errors) {
            $response->header('X-Conversion-Warnings', implode("\n", $errors));
        }

        return $response;
    }

    public function download(ConversionHistory $conversion, Request $request)
    {
        if ($conversion->visitor_id !== $this->getVisitorId($request)) {
            abort(403);
        }

        $fullPath = $conversion->full_path;

        if (!file_exists($fullPath)) {
            abort(404, 'فایل یافت نشد.');
        }

        $zipName = basename($conversion->zip_path);

        return response()->download($fullPath, $zipName);
    }
}
