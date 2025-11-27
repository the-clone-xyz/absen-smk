<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: Boolean, // Saya tambahkan ini buat jaga-jaga
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Sistem Absensi Tamansiswa" />

    <div
        class="min-h-screen bg-gray-50 font-sans selection:bg-green-700 selection:text-white"
    >
        <nav
            class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-xl border-b border-green-100 shadow-[0_8px_30px_rgb(0,0,0,0.05)] transition"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-3">
                        <img
                            src="https://upload.wikimedia.org/wikipedia/commons/thumb/d/d5/Logo_Tamansiswa.svg/1200px-Logo_Tamansiswa.svg.png"
                            alt="Logo Tamansiswa"
                            class="h-10 w-auto drop-shadow-sm"
                        />
                        <div class="flex flex-col">
                            <span
                                class="text-lg font-serif font-bold tracking-tight text-green-900 leading-none"
                            >
                                SMK TAMANSISWA
                            </span>
                            <span
                                class="text-xs text-green-600 font-medium tracking-wide"
                            >
                                Luhur Budi, Berprestasi
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="px-4 py-2 text-sm font-semibold text-green-800 bg-green-100 rounded-lg hover:bg-green-200 transition-all"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="px-4 py-2 text-sm font-bold text-white bg-green-700 rounded-lg hover:bg-green-800 transition shadow-md shadow-green-700/30"
                            >
                                Login
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="px-5 py-2 bg-white text-green-900 text-sm rounded-full font-semibold hover:bg-green-100 transition shadow"
                            >
                                Daftar Baru
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <header
            class="relative min-h-screen flex items-center justify-center pt-20 pb-12 overflow-hidden"
        >
            <img
                class="absolute inset-0 w-full h-full object-cover object-center scale-105 blur-[2px]"
                src="https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80"
                alt="Gedung Sekolah"
            />

            <div
                class="absolute inset-0 bg-gradient-to-b from-green-900/80 via-green-900/60 to-green-800/90"
            ></div>

            <div
                class="relative z-10 max-w-4xl mx-auto px-6 text-center animate-fadeIn"
            >
                <span
                    class="inline-block py-2 px-6 rounded-full bg-white/10 border border-white/20 text-green-100 text-xs font-semibold tracking-widest mb-6 backdrop-blur-sm shadow-md"
                >
                    SISTEM INFORMASI AKADEMIK TERPADU
                </span>

                <h1
                    class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-white leading-tight drop-shadow-xl font-serif"
                >
                    Absensi Digital <br />
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-green-300 to-yellow-200"
                    >
                        Berbasis Karakter
                    </span>
                </h1>

                <p
                    class="mt-6 max-w-2xl mx-auto text-lg md:text-xl text-green-100 leading-relaxed opacity-90"
                >
                    Presensi modern yang terintegrasi, akurat, cepat, dan
                    transparan. Dirancang khusus untuk lingkungan Tamansiswa.
                </p>

                <div
                    class="mt-10 flex flex-col sm:flex-row gap-4 justify-center"
                >
                    <Link
                        :href="route('login')"
                        class="px-8 py-4 text-lg font-bold rounded-xl bg-white text-green-900 shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all"
                    >
                        Mulai Absen Sekarang
                    </Link>

                    <a
                        href="#fitur"
                        class="px-8 py-4 border-2 border-white/40 text-lg font-semibold rounded-xl text-white hover:bg-white/10 backdrop-blur-lg transition-all"
                    >
                        Pelajari Fitur
                    </a>
                </div>
            </div>
        </header>

        <section id="fitur" class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-6">
                <div class="lg:grid lg:grid-cols-2 lg:gap-20 items-center">
                    <div class="relative mb-16 lg:mb-0">
                        <div
                            class="absolute -top-12 -left-12 w-44 h-44 bg-green-100 rounded-full opacity-50 blur-xl"
                        ></div>
                        <div
                            class="absolute -bottom-12 -right-12 w-60 h-60 bg-yellow-50 rounded-full opacity-50 blur-xl"
                        ></div>

                        <img
                            class="relative rounded-3xl shadow-2xl border-8 border-white"
                            src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&w=800&q=80"
                        />

                        <div
                            class="absolute -bottom-6 -left-6 bg-white p-5 rounded-2xl shadow-xl border-l-8 border-green-600 backdrop-blur-xl"
                        >
                            <p
                                class="text-sm text-gray-500 uppercase font-medium tracking-wider"
                            >
                                Kehadiran
                            </p>
                            <p class="text-xl font-extrabold text-gray-900">
                                Realtime Monitoring
                            </p>
                        </div>
                    </div>

                    <div>
                        <h2
                            class="text-green-700 font-bold uppercase tracking-widest text-sm mb-4"
                        >
                            Keunggulan Sistem
                        </h2>

                        <h3
                            class="text-4xl font-serif font-bold text-gray-900 mb-8 leading-tight"
                        >
                            Teknologi Presensi Modern<br />
                            Untuk Sekolah Berkarakter
                        </h3>

                        <p class="text-lg text-gray-600 mb-12">
                            Mengintegrasikan nilai luhur Tamansiswa dengan
                            inovasi teknologi agar tercipta suasana sekolah yang
                            disiplin dan harmonis.
                        </p>

                        <div class="space-y-10">
                            <div class="flex items-start gap-4 group">
                                <div
                                    class="h-14 w-14 flex items-center justify-center rounded-2xl bg-green-700 text-white text-2xl shadow-lg group-hover:scale-110 transition"
                                >
                                    🌍
                                </div>
                                <div>
                                    <h4
                                        class="text-xl font-bold text-gray-900 group-hover:text-green-700 transition"
                                    >
                                        Validasi Geolocation
                                    </h4>
                                    <p class="mt-3 text-gray-500">
                                        Presensi hanya dapat dilakukan pada
                                        radius tertentu di lingkungan sekolah.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4 group">
                                <div
                                    class="h-14 w-14 flex items-center justify-center rounded-2xl bg-green-700 text-white text-2xl shadow-lg group-hover:scale-110 transition"
                                >
                                    📷
                                </div>
                                <div>
                                    <h4
                                        class="text-xl font-bold text-gray-900 group-hover:text-green-700 transition"
                                    >
                                        Autentikasi Visual
                                    </h4>
                                    <p class="mt-3 text-gray-500">
                                        Selfie otomatis memastikan kehadiran
                                        yang valid dan mencegah titip absen.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="bg-green-900 text-white pt-12 pb-8 mt-32 relative z-10">
            <div
                class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-8 mb-8 border-b border-green-800 pb-8"
            >
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <img
                            src="https://upload.wikimedia.org/wikipedia/commons/thumb/d/d5/Logo_Tamansiswa.svg/1200px-Logo_Tamansiswa.svg.png"
                            class="h-12 w-auto"
                        />
                        <span class="text-xl font-serif font-bold">
                            SMK Tamansiswa
                        </span>
                    </div>
                    <p class="text-green-200">
                        Mencetak generasi berkarakter, disiplin, dan mandiri.
                    </p>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-4">Tautan Cepat</h4>
                    <ul class="space-y-2 text-green-200">
                        <li>
                            <a href="/" class="hover:text-white"
                                >Website Sekolah</a
                            >
                        </li>
                        <li>
                            <a href="/" class="hover:text-white">Portal Guru</a>
                        </li>
                        <li>
                            <a href="/" class="hover:text-white"
                                >Jadwal Pelajaran</a
                            >
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-4">Kontak</h4>
                    <p class="text-green-200">📍 Jl. Tamansiswa No. 123</p>
                    <p class="text-green-200">📞 (021) 1234-5678</p>
                </div>
            </div>

            <div
                class="max-w-7xl mx-auto px-6 text-center md:text-left flex flex-col md:flex-row justify-between text-green-300/80"
            >
                <p>
                    &copy; {{ new Date().getFullYear() }} Tim IT SMK Tamansiswa.
                </p>
                <p>Dikembangkan dengan Laravel + Vue.js</p>
            </div>
        </footer>
    </div>
</template>
