<?php

namespace App\Http\Controllers; // <--- INI WAJIB ROOT, JANGAN ADA TAMBAHAN \Student

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CardTemplate;
use App\Models\SystemSetting;
use Inertia\Inertia;
use Carbon\Carbon;

class CardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Pastikan user punya data siswa
        $student = $user->student; 
        
        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Data siswa tidak ditemukan.');
        }

        $template = CardTemplate::where('is_active', true)->first();
        $settings = SystemSetting::first();

        Carbon::setLocale('id');
        $ttl = ($student->pob ?? '-') . ', ' . ($student->dob ? Carbon::parse($student->dob)->translatedFormat('d F Y') : '-');

        $cardData = [
            'nama' => strtoupper($user->name),
            'nisn' => $student->nisn ?? '-',
            'nis'  => $student->nis ?? '-',
            'kelas' => $student->kelas ? $student->kelas->name : '-',
            'ttl' => $ttl,
            'alamat' => $student->address ?? '-',
            'foto' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'gender' => $student->gender ?? '-',
            
            // Data Sekolah
            'sekolah' => strtoupper($settings->school_name ?? 'SMK TAMANSISWA'),
            'alamat_sekolah' => $settings->school_address ?? 'Jl. Kartini No. 1 Lubuk Pakam',
            'website' => $settings->school_website ?? 'www.smk.sch.id',
            'kepsek' => $settings->headmaster_name ?? 'Kepala Sekolah',
            'nip_kepsek' => $settings->headmaster_nip ?? '-',
            'tanggal_cetak' => Carbon::now()->translatedFormat('d F Y'),
        ];

        // Pastikan folder Student dan Card ada di resources/js/Pages/
        return Inertia::render('Student/Card', [
            'cardData' => $cardData,
            'template' => $template,
        ]);
    }
}