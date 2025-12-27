<?php

namespace App\Http\Controllers; // <--- PASTIKAN INI ROOT

use App\Http\Controllers\Controller;
use App\Models\CardTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class CardTemplateController extends Controller
{
    // ... (Isi fungsi index dan store biarkan sama seperti sebelumnya) ...
    public function index()
    {
        $template = CardTemplate::where('is_active', true)->first();
        // Pastikan file Vue ada di resources/js/Pages/Admin/Card/Designer.vue
        return Inertia::render('Admin/Card/Designer', [
            'template' => $template
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'elements' => 'required|json',
            'background' => 'nullable|image',
            'background_back' => 'nullable|image',
        ]);

        $template = CardTemplate::firstOrNew(['is_active' => true]);
        
       if ($request->hasFile('background_back')) {
        if ($template->background_back_path && Storage::disk('public')->exists(str_replace('storage/', '', $template->background_back_path))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $template->background_back_path));
        }
        // Simpan file baru
        $pathBack = $request->file('background_back')->store('card-assets', 'public');
        $template->background_back_path = 'storage/' . $pathBack;
    }

        $template->elements = $request->elements;
        if (!$template->name) {
        $template->name = 'Template Utama';
        }
        
        $template->save();

        return back()->with('success', 'Desain tersimpan!');
    }
}