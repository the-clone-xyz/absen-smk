<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted, computed } from "vue";
import QrcodeVue from "qrcode.vue";
import {
    QrCodeIcon,
    ArrowPathIcon,
    PrinterIcon,
    ViewfinderCircleIcon,
    InformationCircleIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    initialToken: String,
});

const currentToken = ref(props.initialToken);
const timeLeft = ref(15);
let timerInterval = null;
let countdownInterval = null;

// Hitung persentase waktu untuk progress bar width
const progressPercentage = computed(() => (timeLeft.value / 15) * 100);

const fetchNewToken = () => {
    fetch(route("admin.settings.get_qr"))
        .then((res) => res.json())
        .then((data) => {
            currentToken.value = data.token;
            timeLeft.value = 15;
        })
        .catch((err) => console.error("Gagal refresh token:", err));
};

onMounted(() => {
    timerInterval = setInterval(fetchNewToken, 15000);
    countdownInterval = setInterval(() => {
        if (timeLeft.value > 0) timeLeft.value--;
    }, 1000);
});

onUnmounted(() => {
    clearInterval(timerInterval);
    clearInterval(countdownInterval);
});

const printQR = () => {
    window.print();
};
</script>

<template>
    <Head title="QR Code Absensi Master" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-2"
            >
                <ViewfinderCircleIcon class="w-6 h-6 text-blue-600" /> Generator
                QR Code Master
            </h2>
        </template>

        <div
            class="min-h-[calc(100vh-65px)] bg-grid-pattern py-8 px-4 sm:px-6 lg:px-8 flex items-center justify-center"
        >
            <div class="max-w-6xl w-full mx-auto relative z-10">
                <div
                    class="bg-blue-600/90 backdrop-blur-sm text-white p-4 rounded-2xl shadow-lg mb-8 flex items-start gap-4 border border-blue-400/30 print:hidden animate-fade-in-down"
                >
                    <div class="bg-white/20 p-2 rounded-full shrink-0">
                        <InformationCircleIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <h4 class="text-lg font-bold mb-1">
                            Mode Absensi Massal Aktif
                        </h4>
                        <p
                            class="text-sm text-blue-50 leading-relaxed opacity-90"
                        >
                            Halaman ini dirancang untuk ditampilkan di layar
                            besar (proyektor) saat apel atau kegiatan bersama.
                            Token keamanan diperbarui otomatis setiap 15 detik
                            untuk mencegah kecurangan.
                        </p>
                    </div>
                </div>

                <div
                    class="bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row border border-gray-100 print:shadow-none print:border-none"
                >
                    <div
                        class="lg:w-5/12 bg-slate-900 relative p-8 flex flex-col items-center justify-center min-h-[450px] overflow-hidden group"
                    >
                        <div
                            class="absolute top-0 left-0 w-full h-1.5 bg-slate-800"
                        >
                            <div
                                class="h-full bg-blue-500 transition-all duration-1000 ease-linear shadow-[0_0_10px_rgba(59,130,246,0.7)]"
                                :style="`width: ${progressPercentage}%`"
                            ></div>
                        </div>

                        <div
                            class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-blue-900/20 via-slate-900 to-slate-900 opacity-50"
                        ></div>
                        <QrCodeIcon
                            class="absolute text-blue-500/5 w-64 h-64 animate-pulse-slow pointer-events-none"
                        />

                        <div class="relative z-10 p-1">
                            <div
                                class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-blue-500 rounded-tl-lg -mt-2 -ml-2 transition-all group-hover:m-0"
                            ></div>
                            <div
                                class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-blue-500 rounded-tr-lg -mt-2 -mr-2 transition-all group-hover:m-0"
                            ></div>
                            <div
                                class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-blue-500 rounded-bl-lg -mb-2 -ml-2 transition-all group-hover:m-0"
                            ></div>
                            <div
                                class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-blue-500 rounded-br-lg -mb-2 -mr-2 transition-all group-hover:m-0"
                            ></div>

                            <div
                                class="bg-white p-4 rounded-xl shadow-inner-blue relative overflow-hidden"
                            >
                                <div
                                    class="absolute inset-0 bg-gradient-to-b from-transparent via-blue-400/20 to-transparent h-[20%] w-full animate-scan-down pointer-events-none print:hidden"
                                ></div>
                                <QrcodeVue
                                    :value="currentToken"
                                    :size="280"
                                    level="H"
                                    class="mix-blend-multiply"
                                />
                            </div>
                        </div>

                        <p
                            class="text-blue-200/70 text-xs mt-6 font-mono tracking-widest uppercase"
                        >
                            Secure Tokenized QR
                        </p>
                    </div>

                    <div
                        class="lg:w-7/12 p-8 lg:p-12 flex flex-col justify-center bg-white relative"
                    >
                        <div class="mb-auto">
                            <h3
                                class="text-3xl font-extrabold text-gray-900 mb-3 tracking-tight"
                            >
                                Scan untuk Presensi
                            </h3>
                            <p
                                class="text-gray-500 text-lg mb-8 leading-relaxed"
                            >
                                Silakan buka aplikasi sekolah di HP Anda, pilih
                                menu <strong>Scan QR</strong>, dan arahkan
                                kamera ke kode di samping.
                            </p>

                            <div
                                class="bg-gray-50 p-5 rounded-xl border-2 border-dashed border-gray-200 mb-8"
                            >
                                <p
                                    class="text-xs text-gray-400 mb-2 uppercase font-bold flex items-center gap-2"
                                >
                                    <span
                                        class="w-2 h-2 bg-green-500 rounded-full animate-ping"
                                    ></span>
                                    Live Token Hash
                                </p>
                                <div
                                    class="font-mono text-sm text-gray-700 break-all select-all bg-white p-3 rounded border border-gray-100 shadow-sm"
                                >
                                    {{ currentToken }}
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex flex-col sm:flex-row items-center justify-between gap-6 pt-6 border-t border-gray-100"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="relative w-12 h-12 flex items-center justify-center bg-blue-50 rounded-full text-blue-600"
                                >
                                    <ArrowPathIcon
                                        class="w-6 h-6 animate-spin-slow"
                                        :class="{
                                            'text-red-500': timeLeft <= 3,
                                        }"
                                    />
                                    <span
                                        class="absolute font-bold text-sm"
                                        :class="{
                                            'text-red-600 scale-110':
                                                timeLeft <= 3,
                                        }"
                                        >{{ timeLeft }}</span
                                    >
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-700">
                                        Auto Refresh
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Memperbarui kode dalam
                                        {{ timeLeft }} detik.
                                    </p>
                                </div>
                            </div>

                            <button
                                @click="printQR"
                                class="print:hidden group flex items-center gap-2 px-6 py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition shadow-lg hover:shadow-xl active:scale-95"
                            >
                                <PrinterIcon
                                    class="w-5 h-5 group-hover:text-blue-300 transition"
                                />
                                <span>Cetak Halaman</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Pola Garis Kotak-kotak Modern */
.bg-grid-pattern {
    background-color: #f1f5f9; /* Slate-100 base */
    background-image: linear-gradient(
            rgba(71, 85, 105, 0.07) 1px,
            transparent 1px
        ),
        linear-gradient(90deg, rgba(71, 85, 105, 0.07) 1px, transparent 1px);
    background-size: 30px 30px; /* Ukuran kotak */
    background-position: center center;
}

/* Animasi Kustom */
@keyframes scan-down {
    0% {
        top: -20%;
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        top: 100%;
        opacity: 0;
    }
}
.animate-scan-down {
    animation: scan-down 2.5s ease-in-out infinite;
}

@keyframes spin-slow {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
.animate-spin-slow {
    animation: spin-slow 3s linear infinite;
}

@keyframes pulse-slow {
    0%,
    100% {
        opacity: 0.05;
        transform: scale(1);
    }
    50% {
        opacity: 0.15;
        transform: scale(1.1);
    }
}
.animate-pulse-slow {
    animation: pulse-slow 4s ease-in-out infinite;
}

@keyframes fade-in-down {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in-down {
    animation: fade-in-down 0.6s ease-out;
}

.shadow-inner-blue {
    box-shadow: inset 0 0 20px rgba(59, 130, 246, 0.15);
}

/* Styles Khusus Print */
@media print {
    nav,
    header,
    .print\:hidden,
    .bg-grid-pattern {
        display: none !important;
        background: none !important;
        padding: 0 !important;
    }
    body,
    .min-h-\[calc\(100vh-65px\)\] {
        background: white !important;
        height: auto !important;
    }
    .shadow-2xl,
    .shadow-lg,
    .border {
        box-shadow: none !important;
        border: none !important;
    }
    .bg-slate-900 {
        background: white !important;
        color: black !important;
        border: 2px solid #eee;
    }
    .text-blue-200\/70,
    .bg-blue-500 {
        color: #666 !important;
        background: #333 !important;
    }
}
</style>
