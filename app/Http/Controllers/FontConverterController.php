<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ZipArchive;

class FontConverterController extends Controller
{
    public function index()
    {
        return view('converter');
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

        $response = response()->download($zipPath)->deleteFileAfterSend(true);

        if ($errors) {
            $response->header('X-Conversion-Warnings', implode("\n", $errors));
        }

        return $response;
    }
}