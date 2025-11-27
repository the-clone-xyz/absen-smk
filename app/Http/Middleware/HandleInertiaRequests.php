<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {

        return [
            ...parent::share($request),
            // --- TEMPEL KODE INI UNTUK CEK ---
        // dd('Middleware Jalan! Cek Flash: ', $request->session()->get('success'));
        // ---------------------------------
            
            // Data User Login
            'auth' => [
                'user' => $request->user(),
            ],
            

            // --- INI BAGIAN PENTING YANG DITAMBAHKAN ---
            // Mengirim pesan session (flash) ke Vue agar bisa dibaca SweetAlert
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
            ],
        ];
    }
}