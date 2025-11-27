<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import {
    PrinterIcon,
    ArrowLeftIcon,
    AcademicCapIcon,
} from "@heroicons/vue/24/solid";
import { computed } from "vue";

const page = usePage();
// Data Dummy untuk NISN/NIS & TTL karena belum ada di database
const user = computed(() => {
    return (
        {
            ...page.props.auth.user,
            nisn: "0081234567",
            nis: "232410150",
            ttl: "Jakarta, 12 Mei 2008",
            jurusan: "Rekayasa Perangkat Lunak",
        } || {
            name: "Memuat...",
            nisn: "0000000000",
            nis: "000000000",
            id: 0,
            role: "student",
        }
    );
});

const qrUrl = computed(() => {
    const content = `SISWA-${user.value.nisn}-${user.value.nis}`;
    return `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${content}`;
});

const printCard = () => {
    window.print();
};
</script>

<template>
    <Head title="Kartu Pelajar Digital" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4 print:hidden">
                <Link
                    :href="route('dashboard')"
                    class="p-2 rounded-full hover:bg-gray-200 transition text-gray-500"
                >
                    <ArrowLeftIcon class="w-5 h-5" />
                </Link>
                <h2 class="font-bold text-xl text-green-800 leading-tight">
                    Kartu Tanda Pelajar
                </h2>
            </div>
        </template>

        <div
            class="py-12 flex flex-col items-center justify-center min-h-[80vh]"
        >
            <div
                id="kartu-pelajar"
                class="relative w-[420px] h-[260px] rounded-2xl overflow-hidden shadow-2xl border-[2px] border-yellow-400/80 select-none print:shadow-none"
            >
                <svg
                    class="absolute inset-0 w-full h-full pointer-events-none"
                    viewBox="0 0 420 260"
                    preserveAspectRatio="none"
                >
                    <defs>
                        <radialGradient
                            id="modernGreenGrad"
                            cx="0%"
                            cy="0%"
                            r="140%"
                            fx="0%"
                            fy="0%"
                        >
                            <stop offset="0%" stop-color="#16a34a" />
                            <stop offset="40%" stop-color="#15803d" />
                            <stop offset="100%" stop-color="#052e16" />
                        </radialGradient>

                        <pattern
                            id="diagLines"
                            width="12"
                            height="12"
                            patternUnits="userSpaceOnUse"
                            patternTransform="rotate(30)"
                        >
                            <line
                                x1="0"
                                y1="0"
                                x2="0"
                                y2="12"
                                stroke="rgba(255,255,255,0.03)"
                                stroke-width="1"
                            />
                        </pattern>

                        <linearGradient id="sheen" x1="0" x2="1" y1="0" y2="1">
                            <stop
                                offset="0%"
                                stop-color="#ffffff"
                                stop-opacity="0.1"
                            />
                            <stop
                                offset="50%"
                                stop-color="#ffffff"
                                stop-opacity="0"
                            />
                            <stop
                                offset="100%"
                                stop-color="#000000"
                                stop-opacity="0.05"
                            />
                        </linearGradient>
                    </defs>

                    <rect
                        x="0"
                        y="0"
                        width="420"
                        height="260"
                        fill="url(#modernGreenGrad)"
                    />
                    <rect
                        x="0"
                        y="0"
                        width="420"
                        height="260"
                        fill="url(#diagLines)"
                    />
                    <rect
                        x="0"
                        y="0"
                        width="420"
                        height="260"
                        fill="url(#sheen)"
                        style="mix-blend-mode: overlay"
                    />
                </svg>

                <div
                    class="flex items-center gap-3 px-5 py-3 bg-white/10 backdrop-blur-lg border-b border-white/10 relative z-10"
                >
                    <img
                        src="/logo.png"
                        class="h-10 w-10 drop-shadow-lg filter brightness-110"
                        onerror="this.style.display='none'"
                    />
                    <div>
                        <div class="flex items-center gap-1.5">
                            <AcademicCapIcon
                                class="w-5 h-5 text-yellow-300 drop-shadow-sm"
                            />
                            <h1
                                class="text-[17px] font-extrabold uppercase text-yellow-300 tracking-wider font-serif leading-none drop-shadow-sm"
                            >
                                SMK TAMANSISWA
                            </h1>
                        </div>
                        <p
                            class="text-[9px] font-medium text-green-100/80 uppercase tracking-[0.3em] mt-1 ml-0.5"
                        >
                            Kartu Pelajar Digital
                        </p>
                    </div>
                </div>

                <div
                    class="grid grid-cols-[90px_1fr_75px] gap-4 px-5 py-4 relative z-10 h-full items-start mt-1"
                >
                    <div
                        class="w-[90px] h-[110px] rounded-xl overflow-hidden border-[2px] border-white/90 shadow-lg bg-white/10 backdrop-blur-sm relative group"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent pointer-events-none z-10"
                        ></div>
                        <img
                            :src="`https://ui-avatars.com/api/?name=${user.name}&background=random&color=fff&size=200&bold=true`"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        />
                    </div>

                    <div class="text-white min-w-0 space-y-2.5 pt-0.5">
                        <div>
                            <p
                                class="text-[9px] font-semibold text-green-200/80 uppercase tracking-widest mb-0.5"
                            >
                                Nama Lengkap
                            </p>
                            <p
                                class="text-[15px] font-bold leading-tight capitalize truncate tracking-wide drop-shadow-sm"
                            >
                                {{ user.name }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p
                                    class="text-[9px] font-semibold text-green-200/80 uppercase tracking-widest mb-0.5"
                                >
                                    NISN
                                </p>
                                <p
                                    class="font-mono text-[12px] font-medium tracking-wider"
                                >
                                    {{ user.nisn }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-[9px] font-semibold text-green-200/80 uppercase tracking-widest mb-0.5"
                                >
                                    NIS
                                </p>
                                <p
                                    class="font-mono text-[12px] font-medium tracking-wider"
                                >
                                    {{ user.nis }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2.5">
                            <div>
                                <p
                                    class="text-[9px] font-semibold text-green-200/80 uppercase tracking-widest mb-0.5"
                                >
                                    Tempat, Tgl Lahir
                                </p>
                                <p
                                    class="text-[11px] font-medium tracking-wide"
                                >
                                    {{ user.ttl }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-[9px] font-semibold text-green-200/80 uppercase tracking-widest mb-0.5"
                                >
                                    Kompetensi Keahlian
                                </p>
                                <p
                                    class="text-[11px] font-bold tracking-wide text-yellow-200/90"
                                >
                                    {{ user.jurusan }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col items-end pt-1">
                        <div
                            class="bg-white/95 p-1.5 rounded-xl shadow-md backdrop-blur-sm relative overflow-hidden"
                        >
                            <div
                                class="absolute top-0 right-0 w-3 h-3 bg-yellow-400/50 blur-[8px]"
                            ></div>
                            <img
                                :src="qrUrl"
                                class="w-[75px] h-[75px] relative z-10"
                                alt="QR"
                            />
                        </div>
                        <span
                            class="text-[8px] text-green-100/90 mt-2 text-center w-full font-semibold tracking-wider uppercase"
                            >Scan Validasi</span
                        >
                    </div>
                </div>

                <div
                    class="absolute bottom-0 left-0 w-full px-5 py-2.5 bg-[#022c22]/30 text-green-50/90 text-[9px] backdrop-blur-md border-t border-white/10 relative z-10 flex justify-between items-center uppercase font-bold tracking-[0.2em]"
                >
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"
                            ></span>
                            <span
                                class="relative inline-flex rounded-full h-2 w-2 bg-green-500"
                            ></span>
                        </span>
                        <span>Status: Aktif</span>
                    </div>
                    <span class="opacity-80">Valid: 2025/2026</span>
                </div>
            </div>

            <div class="mt-10 print:hidden">
                <button
                    @click="printCard"
                    class="flex items-center gap-3 bg-gradient-to-r from-green-700 to-green-600 text-white px-10 py-3.5 rounded-full hover:from-green-800 hover:to-green-700 hover:scale-[1.02] transition-all duration-300 shadow-xl hover:shadow-green-800/30 font-bold text-sm tracking-wider uppercase group"
                >
                    <PrinterIcon class="w-5 h-5 group-hover:animate-pulse" />
                    Cetak Kartu Pelajar
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #kartu-pelajar,
    #kartu-pelajar * {
        visibility: visible;
    }
    #kartu-pelajar {
        position: fixed;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        margin: 0;
        border: 0;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        box-shadow: none !important;
        border-radius: 16px !important; /* Pastikan rounded corner tercetak */
    }
    nav,
    header,
    .print\:hidden {
        display: none !important;
    }
}
</style>
