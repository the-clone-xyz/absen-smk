<script setup>
import { Head, Link } from "@inertiajs/vue3";
import {
    AcademicCapIcon,
    QrCodeIcon,
    ClockIcon,
    ShieldCheckIcon,
    ArrowRightIcon,
    Bars3Icon,
    MapPinIcon,
    DevicePhoneMobileIcon,
    DocumentCheckIcon,
    UserGroupIcon,
} from "@heroicons/vue/24/outline";
import { ref } from "vue";

// MENERIMA DATA DARI CONTROLLER
defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    schoolName: String, // <--- Data Nama Sekolah Dinamis
});

const features = [
    {
        title: "Geofencing Area",
        desc: "Siswa hanya bisa absen di dalam radius sekolah yang ditentukan.",
        icon: MapPinIcon,
    },
    {
        title: "Akses Mobile",
        desc: "Aplikasi responsif yang dapat diakses melalui Smartphone & Tablet.",
        icon: DevicePhoneMobileIcon,
    },
    {
        title: "Laporan Otomatis",
        desc: "Rekap kehadiran harian dan bulanan tergenerate secara otomatis.",
        icon: DocumentCheckIcon,
    },
    {
        title: "Multi-User",
        desc: "Portal khusus untuk Siswa, Guru, dan Administrator Sekolah.",
        icon: UserGroupIcon,
    },
];
</script>

<template>
    <Head title="Sistem Presensi Digital" />

    <div
        class="min-h-screen bg-slate-50 font-sans selection:bg-emerald-500 selection:text-white relative overflow-x-hidden"
    >
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div
                class="absolute inset-0 bg-[linear-gradient(to_right,#10b9811a_1px,transparent_1px),linear-gradient(to_bottom,#10b9811a_1px,transparent_1px)] bg-[size:40px_40px] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_110%)]"
            ></div>

            <div
                class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-3xl h-[500px] bg-emerald-300/20 blur-[100px] rounded-full"
            ></div>
        </div>

        <nav
            class="fixed w-full z-50 top-0 transition-all duration-300 bg-white/70 backdrop-blur-lg border-b border-emerald-100"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg flex items-center justify-center text-white shadow-lg shadow-emerald-500/30"
                        >
                            <AcademicCapIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <span
                                class="block text-lg sm:text-xl font-extrabold text-slate-800 tracking-tight leading-none uppercase truncate max-w-[200px] sm:max-w-md"
                            >
                                {{ schoolName }}
                            </span>
                            <span
                                class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest"
                                >Sistem Akademik Terpadu</span
                            >
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:gap-4">
                        <Link
                            :href="route('login')"
                            class="text-sm font-semibold text-slate-600 hover:text-emerald-600 transition"
                        >
                            Masuk Guru / Siswa
                        </Link>
                        <Link
                            :href="route('login')"
                            class="group relative px-6 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-full shadow-xl hover:bg-emerald-600 transition-all duration-300 flex items-center gap-2 overflow-hidden"
                        >
                            <span class="relative z-10">Akses Portal</span>
                            <ArrowRightIcon
                                class="w-4 h-4 relative z-10 group-hover:translate-x-1 transition-transform"
                            />
                            <div
                                class="absolute inset-0 bg-emerald-600 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300"
                            ></div>
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <main class="relative z-10 pt-32 pb-16 lg:pt-48 lg:pb-32">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-bold uppercase tracking-wider mb-8 animate-fade-in-up"
                >
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"
                        ></span>
                        <span
                            class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"
                        ></span>
                    </span>
                    Live Presensi System v2.0
                </div>

                <h1
                    class="text-5xl md:text-7xl font-extrabold text-slate-900 tracking-tight mb-6 leading-tight"
                >
                    Absensi Digital <br class="hidden md:block" />
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-teal-500"
                        >Berbasis Karakter</span
                    >
                </h1>

                <p
                    class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto mb-10 leading-relaxed"
                >
                    Platform presensi modern yang mengintegrasikan
                    <span class="font-bold text-slate-800">QR Code</span>,
                    <span class="font-bold text-slate-800">Validasi Wajah</span
                    >, dan
                    <span class="font-bold text-slate-800">Geolokasi</span>
                    untuk menciptakan kedisiplinan dan transparansi di
                    lingkungan <b>{{ schoolName }}</b
                    >.
                </p>

                <div
                    class="flex flex-col sm:flex-row gap-4 justify-center items-center"
                >
                    <Link
                        :href="route('login')"
                        class="w-full sm:w-auto px-8 py-4 bg-emerald-600 text-white rounded-xl font-bold shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 hover:scale-105 transition-all duration-300 flex items-center justify-center gap-2"
                    >
                        <QrCodeIcon class="w-5 h-5" />
                        Mulai Absen Sekarang
                    </Link>
                    <a
                        href="#fitur"
                        class="w-full sm:w-auto px-8 py-4 bg-white text-slate-700 border border-slate-200 rounded-xl font-bold hover:bg-slate-50 hover:border-emerald-200 transition-all duration-300"
                    >
                        Pelajari Fitur
                    </a>
                </div>

                <div class="mt-16 relative mx-auto max-w-4xl">
                    <div
                        class="absolute -inset-1 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl blur opacity-20 animate-pulse"
                    ></div>
                    <div
                        class="relative bg-white rounded-2xl border border-slate-200 shadow-2xl p-2 md:p-4 grid grid-cols-1 md:grid-cols-3 gap-4"
                    >
                        <div
                            class="bg-slate-50 p-6 rounded-xl text-left border border-slate-100"
                        >
                            <div
                                class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-4"
                            >
                                <ClockIcon class="w-6 h-6" />
                            </div>
                            <h3 class="font-bold text-slate-800">
                                Real-time Data
                            </h3>
                            <p class="text-sm text-slate-500 mt-1">
                                Pemantauan kehadiran siswa & guru detik ini
                                juga.
                            </p>
                        </div>

                        <div
                            class="bg-gradient-to-b from-emerald-500 to-green-600 p-6 rounded-xl text-center text-white shadow-lg transform md:-translate-y-8 md:scale-110 transition hover:scale-115"
                        >
                            <div
                                class="bg-white p-3 rounded-lg inline-block mb-4 shadow-sm"
                            >
                                <QrCodeIcon class="w-16 h-16 text-slate-800" />
                            </div>
                            <h3 class="font-bold text-lg">Scan & Go</h3>
                            <p class="text-emerald-100 text-xs mt-2">
                                Cukup 3 detik untuk mencatat kehadiran Anda.
                            </p>
                            <div
                                class="mt-4 text-xs font-mono bg-emerald-700/50 py-1 px-3 rounded-full inline-block"
                            >
                                Secure Token Active
                            </div>
                        </div>

                        <div
                            class="bg-slate-50 p-6 rounded-xl text-left border border-slate-100"
                        >
                            <div
                                class="w-10 h-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center mb-4"
                            >
                                <ShieldCheckIcon class="w-6 h-6" />
                            </div>
                            <h3 class="font-bold text-slate-800">
                                Anti-Fake GPS
                            </h3>
                            <p class="text-sm text-slate-500 mt-1">
                                Validasi lokasi akurat dengan radius sekolah.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <section
            id="fitur"
            class="py-20 bg-white border-t border-slate-100 relative"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-extrabold text-slate-900">
                        Teknologi Pendidikan Terdepan
                    </h2>
                    <p class="text-slate-500 mt-4">
                        Kami menggabungkan hardware dan software untuk kemudahan
                        administrasi.
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8"
                >
                    <div
                        v-for="(item, index) in features"
                        :key="index"
                        class="group p-6 rounded-2xl bg-slate-50 hover:bg-emerald-50 border border-slate-100 hover:border-emerald-200 transition-all duration-300"
                    >
                        <div
                            class="w-12 h-12 rounded-xl bg-white border border-slate-200 shadow-sm flex items-center justify-center mb-4 group-hover:scale-110 transition-transform"
                        >
                            <component
                                :is="item.icon"
                                class="w-6 h-6 text-emerald-600"
                            />
                        </div>
                        <h3 class="font-bold text-slate-800 mb-2">
                            {{ item.title }}
                        </h3>
                        <p class="text-sm text-slate-500 leading-relaxed">
                            {{ item.desc }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <footer class="py-8 bg-white border-t border-slate-200 text-center">
            <p class="text-slate-500 text-sm">
                &copy; 2025
                <span class="font-bold text-emerald-700">{{ schoolName }}</span
                >. All rights reserved.
            </p>
        </footer>
    </div>
</template>

<style scoped>
/* Animasi Halus */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out;
}
</style>
