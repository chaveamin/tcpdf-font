<?php

namespace App\Http\Controllers;

use App\Jobs\ConvertFontJob;
use App\Models\ConversionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;

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
        if (!file_exists($tempDir)) { mkdir($tempDir, 0775, true); }

        $uploadFiles = [];
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

            $file->move($tempDir, $tempFileName);
            $uploadFiles[$file->getClientOriginalName()] = $tempFileName;
        }

        if (empty($uploadFiles)) {
            return response()->json(['message' => $errors ? implode("\n", $errors) : 'هیچ فایل TTF معتبری آپلود نشد.'], 422);
        }

        $conversion = ConversionHistory::create([
            'visitor_id' => $this->getVisitorId($request),
            'font_names' => implode(', ', array_keys($uploadFiles)),
            'zip_path'   => '',
            'file_count' => count($uploadFiles),
            'status'     => ConversionHistory::STATUS_PENDING,
        ]);

        ConvertFontJob::dispatch($conversion, 'upload', $uploadFiles);

        $data = [
            'conversion_id' => $conversion->id,
            'status_url'    => route('converter.status', $conversion),
            'font_names'    => implode(', ', array_keys($uploadFiles)),
            'file_count'    => count($uploadFiles),
        ];

        if ($errors) {
            $data['warnings'] = $errors;
        }

        return response()->json($data, 202);
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

        $conversion = ConversionHistory::create([
            'visitor_id' => $this->getVisitorId($request),
            'font_names' => $request->input('family') . ' ' . $request->input('variant'),
            'zip_path'   => '',
            'file_count' => 1,
            'status'     => ConversionHistory::STATUS_PENDING,
        ]);

        ConvertFontJob::dispatch(
            $conversion,
            'google',
            [],
            $request->input('family'),
            $request->input('variant'),
        );

        return response()->json([
            'conversion_id' => $conversion->id,
            'status_url'    => route('converter.status', $conversion),
            'font_names'    => $conversion->font_names,
        ], 202);
    }

    public function status(ConversionHistory $conversion, Request $request)
    {
        if ($conversion->visitor_id !== $this->getVisitorId($request)) {
            abort(403);
        }

        $response = [
            'status'        => $conversion->status,
            'conversion_id' => $conversion->id,
        ];

        if ($conversion->isCompleted()) {
            $response['download_url'] = route('converter.download', $conversion);
            $response['font_names']   = $conversion->font_names;
            $response['file_count']   = $conversion->file_count;
        }

        if ($conversion->isFailed()) {
            $response['error_message'] = $conversion->error_message;
        }

        return response()->json($response);
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
