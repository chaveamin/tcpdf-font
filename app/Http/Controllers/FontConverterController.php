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
        if (!in_array($extension, ['ttf', 'otf'])) {
            return back()->with('error', 'فقط فایل های ttf و otf پشتیبانی میشود.');
        }

        $fontPath = $file->getRealPath();

        $outDir = storage_path('app/fonts_out/');
        if (!file_exists($outDir)) {
            mkdir($outDir, 0777, true);
        }

        $fontname = \TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32, $outDir);

        if (!$fontname) {
            return back()->with('error', 'خطا در تبدیل فونت. ممکن است فایل خراب یا پشتیبانی نمیشود.');
        }

        $zipPath = storage_path('app/fonts_out/' . $fontname . '.zip');
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $filesToZip = [
                $fontname . '.php',
                $fontname . '.z',
                $fontname . '.ctg.z'
            ];
            
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
            return back()->with('error', 'خطا در ایجاد فایل زیپ.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}