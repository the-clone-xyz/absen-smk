<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // 1. Cek User Login (Sudah pasti login karena ada middleware 'auth')
        if (!$request->user()) {
            return redirect('/');
        }
        
        // 2. Tentukan role yang dibutuhkan (Bisa 'student' atau 'teacher|admin')
        $requiredRoles = explode('|', $role);
        $userRole = $request->user()->role;
        
        // 3. Cek apakah role user saat ini TIDAK termasuk dalam daftar yang dibutuhkan
        if (!in_array($userRole, $requiredRoles)) {
            
            // Logika Pengalihan (Memutus Loop)
            if ($userRole === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            if ($userRole === 'teacher') {
                return redirect()->route('teacher.dashboard');
            }
            
            // Jika siswa tapi tersesat ke rute guru
            return redirect()->route('dashboard'); 
        }

        return $next($request);
    }
}