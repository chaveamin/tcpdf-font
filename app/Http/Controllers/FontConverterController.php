<?php

namespace App\Http\Controllers;

use App\Models\ConversionHistory;
use Illuminate\Http\Request;
use ZipArchive;

class FontConverterController extends Controller
{
    public function index()
    {
        $conversions = ConversionHistory::latest()->take(10)->get();

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

        $zipName = count($convertedNames) === 1 ? $convertedNames[0] : 'tcpdf_fonts';
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

        ConversionHistory::create([
            'font_names' => implode(', ', $originalNames),
            'zip_path'   => 'fonts_out/' . $zipName . '.zip',
            'file_count' => count($convertedNames),
        ]);

        $response = response()->download($zipPath);

        if ($errors) {
            $response->header('X-Conversion-Warnings', implode("\n", $errors));
        }

        return $response;
    }

    public function download(ConversionHistory $conversion)
    {
        $fullPath = $conversion->full_path;

        if (!file_exists($fullPath)) {
            abort(404, 'فایل یافت نشد.');
        }

        $zipName = basename($conversion->zip_path);

        return response()->download($fullPath, $zipName);
    }
}
