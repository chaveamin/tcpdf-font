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
            'font' => 'required|file|max:10240', // Max 10MB
        ]);

        $file = $request->file('font');
        
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['ttf', 'otf'])) {
            return back()->with('error', 'Only .ttf and .otf files are supported.');
        }

        $fontPath = $file->getRealPath();

        $outDir = storage_path('app/fonts_out/');
        if (!file_exists($outDir)) {
            mkdir($outDir, 0777, true);
        }

        $fontname = \TCPDF_FONTS::addTTFfont($fontPath, 'TrueTypeUnicode', '', 32, $outDir);

        if (!$fontname) {
            return back()->with('error', 'TCPDF failed to convert the font. The file might be corrupted or unsupported.');
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
            return back()->with('error', 'Failed to create the ZIP archive.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}