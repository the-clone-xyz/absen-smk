<?php

namespace App\Http\Controllers;

use App\Models\Ebook;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class EbookController extends Controller
{
    public function index(Request $request)
    {
        $query = Ebook::query();
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('author', 'like', '%' . $request->search . '%');
        }

        return Inertia::render('Ebook/Library', [
            // PERUBAHAN DI SINI: paginate(10)
            'ebooks' => $query->latest()->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
            'canManage' => Auth::user()->role === 'admin' 
        ]);
    }

    // UPLOAD STANDAR (SIMPLE & STABIL)
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') abort(403);

        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'cover' => 'nullable|image|max:102400', // Max 100MB
            'file' => 'required|mimes:pdf|max:102400', // Max 100MB
        ]);

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('ebook_covers', 'public');
        }

        $filePath = $request->file('file')->store('ebook_files', 'public');

        Ebook::create([
            'title' => $request->title,
            'author' => $request->author,
            'publisher' => 'Unknown',
            'description' => $request->description,
            'cover' => $coverPath,
            'file_path' => $filePath,
        ]);

        return back()->with('success', 'E-Book berhasil diunggah!');
    }

    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') abort(403);
        $ebook = Ebook::findOrFail($id);
        if ($ebook->cover) Storage::disk('public')->delete($ebook->cover);
        if ($ebook->file_path) Storage::disk('public')->delete($ebook->file_path);
        $ebook->delete();
        return back()->with('success', 'Buku dihapus.');
    }

    public function read(Ebook $ebook)
    {
        $extension = pathinfo($ebook->file_path, PATHINFO_EXTENSION);

        return Inertia::render('Viewer/Index', [
            'fileUrl' => asset('storage/' . $ebook->file_path),
            'fileName' => $ebook->title,
            'extension' => strtolower($extension),
            'downloadUrl' => asset('storage/' . $ebook->file_path),
        ]);
    }

    public function readFlip(Ebook $ebook)
    {
        $extension = pathinfo($ebook->file_path, PATHINFO_EXTENSION);

        if (strtolower($extension) !== 'pdf') {
            return back()->with('error', 'Mode Flipbook hanya tersedia untuk file PDF.');
        }

        return Inertia::render('Viewer/Flipbook', [
            'fileUrl' => asset('storage/' . $ebook->file_path),
            'fileName' => $ebook->title,
            'extension' => 'pdf',
        ]);
    }
}