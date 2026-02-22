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
            'font' => 'required|file|max:10240',
        ]);

        $file = $request->file('font');
        
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['ttf'])) {
            return response()->json(['message' => 'فقط فایل های ttf پشتیبانی میشوند.'], 422);
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFontName = preg_replace('/[^a-z0-9]/', '', strtolower($originalName));
        $tempFileName = $safeFontName . '.' . $extension;
        
        $tempPath = $file->storeAs('temp_fonts', $tempFileName);
        $fontPath = storage_path('app/' . $tempPath);

        $outDir = storage_path('app/fonts_out/');
        if (!file_exists($outDir)) {
            mkdir($outDir, 0777, true);
        }

        $fontname = \TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32, $outDir);

        if (file_exists($fontPath)) {
            unlink($fontPath);
        }

        if (!$fontname) {
            return response()->json(['message' => 'فایل فونت ممکن است خراب شده باشد'], 500);
        }

        $zipPath = storage_path('app/fonts_out/' . $fontname . '.zip');
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $filesToZip = [$fontname . '.php', $fontname . '.z', $fontname . '.ctg.z'];
            
            foreach ($filesToZip as $f) {
                $fp = $outDir . $f;
                if (file_exists($fp)) {
                    $zip->addFile($fp, $f);
                }
            }
            $zip->close();
            
            foreach ($filesToZip as $f) {
                $fp = $outDir . $f;
                if (file_exists($fp)) {
                    unlink($fp);
                }
            }
        } else {
            return response()->json(['message' => 'خطا در تبدیل فونت'], 500);
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}