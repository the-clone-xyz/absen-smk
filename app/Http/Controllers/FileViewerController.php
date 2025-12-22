<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class FileViewerController extends Controller
{
    /**
     * Handle preview file universal.
     * URL: /viewer?path=folder/file.pdf
     */
    public function show(Request $request)
    {
        $path = $request->query('path'); 
        
        if (!$path) {
            abort(404, 'File tidak ditemukan.');
        }

        // Validasi Ekstensi
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
        
        if (!in_array($extension, $allowed)) {
            abort(403, 'Format file ini tidak didukung oleh Viewer.');
        }

        // Cek Fisik File
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File fisik tidak ditemukan di server.');
        }

        // Render Vue
        return Inertia::render('Viewer/Index', [
            'fileUrl' => asset('storage/' . $path),
            'fileName' => basename($path),
            'extension' => $extension,
            'downloadUrl' => route('viewer.download', ['path' => $path]) 
        ]);
    }

    public function download(Request $request)
    {
        $path = $request->query('path');
        if (!Storage::disk('public')->exists($path)) abort(404);
        return Storage::disk('public')->download($path);
    }
}