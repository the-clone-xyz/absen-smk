<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted } from "vue";
import QrcodeVue from "qrcode.vue";
import {
    QrCodeIcon,
    ArrowPathIcon,
    PrinterIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    initialToken: String,
});

const currentToken = ref(props.initialToken);
const timeLeft = ref(15); // Hitung mundur visual
let timerInterval = null;
let countdownInterval = null;

// Fungsi Ambil Token Baru
const fetchNewToken = () => {
    fetch(route("admin.settings.get_qr"))
        .then((res) => res.json())
        .then((data) => {
            currentToken.value = data.token;
            timeLeft.value = 15; // Reset hitung mundur
        })
        .catch((err) => console.error("Gagal refresh token:", err));
};

// Lifecycle
onMounted(() => {
    // 1. Refresh Token setiap 15 detik
    timerInterval = setInterval(fetchNewToken, 15000);

    // 2. Hitung mundur visual (detik)
    countdownInterval = setInterval(() => {
        if (timeLeft.value > 0) timeLeft.value--;
    }, 1000);
});

onUnmounted(() => {
    clearInterval(timerInterval);
    clearInterval(countdownInterval);
});

// Fungsi Cetak (Untuk ditempel darurat, tapi tidak disarankan karena statis)
const printQR = () => {
    window.print();
};
</script>

<template>
    <Head title="QR Code Absensi" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-bold text-xl text-red-800 leading-tight flex items-center gap-2"
            >
                <QrCodeIcon class="w-6 h-6" /> Generator QR Code Master
            </h2>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div
                    class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg shadow-sm print:hidden"
                >
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm text-blue-700 font-bold">
                                Mode Absensi Massal
                            </p>
                            <p class="text-xs text-blue-600">
                                Halaman ini digunakan untuk absensi masal
                                (Apel/Upacara). Token berubah otomatis setiap 15
                                detik. Pastikan layar terlihat jelas oleh siswa.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white shadow-2xl rounded-2xl p-8 text-center border border-gray-200 relative overflow-hidden"
                >
                    <div
                        class="absolute top-0 left-0 h-1 bg-red-500 transition-all duration-1000 ease-linear"
                        :style="`width: ${(timeLeft / 15) * 100}%`"
                    ></div>

                    <h3
                        class="text-2xl font-extrabold text-gray-800 mb-2 uppercase tracking-wider"
                    >
                        Scan Untuk Absen
                    </h3>
                    <p class="text-gray-500 text-sm mb-6">
                        Arahkan kamera HP ke kode di bawah ini
                    </p>

                    <div
                        class="inline-block p-4 bg-white border-4 border-gray-800 rounded-xl shadow-lg mb-6 relative group"
                    >
                        <QrcodeVue
                            :value="currentToken"
                            :size="350"
                            level="H"
                        />

                        <div
                            class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-20"
                        >
                            <QrCodeIcon class="w-20 h-20 text-gray-800" />
                        </div>
                    </div>

                    <div class="max-w-lg mx-auto">
                        <p
                            class="text-xs text-gray-400 mb-1 uppercase font-bold"
                        >
                            Token Hash:
                        </p>
                        <div
                            class="bg-gray-100 p-3 rounded-lg border border-gray-300 font-mono text-xs break-all text-gray-600 select-all"
                        >
                            {{ currentToken }}
                        </div>
                    </div>

                    <div
                        class="mt-8 flex justify-center items-center gap-2 text-sm font-bold text-red-600 animate-pulse"
                    >
                        <ArrowPathIcon class="w-5 h-5 animate-spin" />
                        Refresh dalam {{ timeLeft }} detik...
                    </div>

                    <div
                        class="mt-8 pt-6 border-t border-gray-100 print:hidden"
                    >
                        <button
                            @click="printQR"
                            class="text-gray-400 hover:text-gray-600 text-xs flex items-center justify-center gap-1 mx-auto transition"
                        >
                            <PrinterIcon class="w-4 h-4" /> Cetak Halaman Ini
                            (Hanya untuk Darurat)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    nav,
    header,
    .print\:hidden {
        display: none !important;
    }
    body {
        background: white;
    }
    .shadow-2xl {
        box-shadow: none !important;
        border: none !important;
    }
}
</style>
