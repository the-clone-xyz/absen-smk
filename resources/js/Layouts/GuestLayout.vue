<script setup>
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div
        class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-green-900 relative overflow-hidden"
    >
        <img
            class="absolute inset-0 w-full h-full object-cover object-center opacity-30 scale-105"
            src="https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80"
            alt="Background Sekolah"
        />

        <div
            class="absolute inset-0 bg-gradient-to-b from-green-900/90 via-green-800/80 to-green-900/90"
        ></div>

        <div
            class="w-full sm:max-w-md mt-6 px-8 py-8 bg-white shadow-[0_20px_50px_rgba(0,0,0,0.3)] overflow-hidden sm:rounded-2xl relative z-10 border border-white/20"
        >
            <div class="flex flex-col items-center mb-6">
                <Link href="/">
                    <img
                        src="/logo.png"
                        class="h-20 w-auto drop-shadow-md hover:scale-105 transition"
                        alt="Logo Tamansiswa"
                    />
                </Link>
                <h2 class="mt-4 text-2xl font-serif font-bold text-green-900">
                    Selamat Datang
                </h2>
                <p class="text-sm text-gray-500">
                    Silakan masuk untuk presensi
                </p>
            </div>

            <slot />
        </div>

        <div class="relative z-10 mt-8 text-green-200/60 text-xs">
            &copy; SMK Tamansiswa - Presensi Digital
        </div>
    </div>
</template>
