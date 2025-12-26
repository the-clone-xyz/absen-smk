<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref, onMounted, onBeforeUnmount, computed, nextTick } from "vue";
import { Html5Qrcode } from "html5-qrcode";
import {
    MapPinIcon,
    QrCodeIcon,
    FaceSmileIcon,
    CameraIcon,
    ArrowPathIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    SignalIcon, // Icon baru
} from "@heroicons/vue/24/solid";

// --- PROPS ---
const props = defineProps({
    schoolSettings: Object, // { lat, lng, radius }
});

// State
const mode = ref("face");
const coordinates = ref(null);
const currentDistance = ref(0);
const accuracy = ref(0);
const locationError = ref(null);
const videoRef = ref(null);
const photoPreview = ref(null);
const qrResult = ref(null);
const scannerError = ref(null);
const isGpsLoading = ref(true); // Indikator loading GPS

// Variable Stream
let html5QrCode = null;
let currentStream = null;
let geoWatcherId = null;

const form = useForm({
    type: "face",
    latitude: "",
    longitude: "",
    photo: null,
    qr_code: "",
});

// --- UTILS ---
const stopAllCameras = async () => {
    if (currentStream) {
        currentStream.getTracks().forEach((track) => track.stop());
        currentStream = null;
    }
    if (html5QrCode) {
        try {
            if (html5QrCode.isScanning) await html5QrCode.stop();
            html5QrCode.clear();
        } catch (err) {}
    }
};

const calculateDistance = (lat1, lon1, lat2, lon2) => {
    const R = 6371e3;
    const φ1 = (lat1 * Math.PI) / 180;
    const φ2 = (lat2 * Math.PI) / 180;
    const Δφ = ((lat2 - lat1) * Math.PI) / 180;
    const Δλ = ((lon2 - lon1) * Math.PI) / 180;
    const a =
        Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
        Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return Math.round(R * c);
};

// Helper Update Posisi
const updatePosition = (position) => {
    isGpsLoading.value = false;
    locationError.value = null;
    coordinates.value = position.coords;
    accuracy.value = Math.round(position.coords.accuracy);

    form.latitude = position.coords.latitude;
    form.longitude = position.coords.longitude;

    if (props.schoolSettings) {
        currentDistance.value = calculateDistance(
            position.coords.latitude,
            position.coords.longitude,
            props.schoolSettings.lat,
            props.schoolSettings.lng
        );
    }
};

// --- GPS LOGIC (DIPERCEPAT) ---
const startLocationTracking = () => {
    if (!navigator.geolocation) {
        locationError.value = "Browser tidak mendukung GPS.";
        return;
    }

    isGpsLoading.value = true;

    // STRATEGI 1: Ambil lokasi Cache (Cepat - Hitungan detik)
    navigator.geolocation.getCurrentPosition(
        (position) => {
            console.log("Lokasi Cepat didapat");
            updatePosition(position);
        },
        (err) => {
            console.log("Lokasi cepat gagal, menunggu GPS akurasi tinggi...");
        },
        {
            maximumAge: 60000, // Terima lokasi cache sampai 1 menit lalu
            timeout: 3000, // Tunggu cuma 3 detik
            enableHighAccuracy: false,
        }
    );

    // STRATEGI 2: Nyalakan Watcher (Akurasi Tinggi - Realtime)
    // Ini akan menimpa data lokasi cepat jika sudah terkunci
    if (geoWatcherId) navigator.geolocation.clearWatch(geoWatcherId);

    geoWatcherId = navigator.geolocation.watchPosition(
        (position) => updatePosition(position),
        (error) => {
            // Hanya tampilkan error jika belum pernah dapat lokasi sama sekali
            if (!coordinates.value) {
                console.error(error);
                locationError.value =
                    "Gagal mengunci GPS. Pastikan izin lokasi aktif.";
                isGpsLoading.value = false;
            }
        },
        {
            enableHighAccuracy: true, // Wajib ON untuk presisi absen
            timeout: 15000,
            maximumAge: 0, // Jangan terima cache untuk validasi final
        }
    );
};

const isWithinRadius = computed(() => {
    const maxRadius = props.schoolSettings?.radius || 50;
    // Jika belum ada koordinat, anggap false
    if (!coordinates.value) return false;
    return currentDistance.value <= maxRadius + 20; // Toleransi 20m
});

// --- CAMERA LOGIC ---
const startCamera = async () => {
    if (mode.value !== "face") return;
    await stopAllCameras();
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user" },
        });
        currentStream = stream;
        if (videoRef.value) videoRef.value.srcObject = stream;
    } catch (err) {
        console.error("Gagal akses kamera", err);
    }
};

const takePhoto = () => {
    const canvas = document.createElement("canvas");
    canvas.width = videoRef.value.videoWidth;
    canvas.height = videoRef.value.videoHeight;
    const ctx = canvas.getContext("2d");
    ctx.translate(canvas.width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(videoRef.value, 0, 0);

    canvas.toBlob((blob) => {
        const file = new File([blob], "selfie.jpg", { type: "image/jpeg" });
        form.photo = file;
        photoPreview.value = URL.createObjectURL(blob);
    }, "image/jpeg");
};

// --- QR LOGIC ---
const startQRScanner = async () => {
    scannerError.value = null;
    await stopAllCameras();
    await nextTick();
    html5QrCode = new Html5Qrcode("reader");
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0,
    };

    html5QrCode
        .start(
            { facingMode: "environment" },
            config,
            onScanSuccess,
            onScanFailure
        )
        .catch(() => {
            html5QrCode.start(
                { facingMode: "user" },
                config,
                onScanSuccess,
                onScanFailure
            );
        });
};

const onScanSuccess = (decodedText) => {
    stopAllCameras();
    qrResult.value = decodedText;
    form.qr_code = decodedText;
    submitAbsen();
};

const onScanFailure = () => {};

const switchMode = async (newMode) => {
    mode.value = newMode;
    form.type = newMode;
    photoPreview.value = null;
    qrResult.value = null;
    form.clearErrors();
    await stopAllCameras();

    if (newMode === "face") {
        setTimeout(startCamera, 500);
    } else {
        startQRScanner();
    }
};

const submitAbsen = () => {
    form.post(route("attendance.store"), {
        forceFormData: true,
        onSuccess: () => stopAllCameras(),
        onError: () => {
            if (mode.value === "qr") startQRScanner();
        },
    });
};

onMounted(() => {
    startLocationTracking();
    startCamera();
});

onBeforeUnmount(() => {
    stopAllCameras();
    if (geoWatcherId) navigator.geolocation.clearWatch(geoWatcherId);
});
</script>

<template>
    <Head title="Absensi Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-green-800">
                Terminal Absensi
            </h2>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div
                class="max-w-6xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col lg:flex-row min-h-[600px]"
            >
                <div
                    class="lg:w-7/12 bg-black relative flex flex-col items-center justify-center min-h-[400px] lg:min-h-full overflow-hidden"
                >
                    <div
                        v-if="mode === 'face'"
                        class="absolute inset-0 w-full h-full"
                    >
                        <video
                            v-show="!photoPreview"
                            ref="videoRef"
                            autoplay
                            playsinline
                            class="w-full h-full object-cover transform scale-x-[-1]"
                        ></video>
                        <img
                            v-if="photoPreview"
                            :src="photoPreview"
                            class="w-full h-full object-cover"
                        />

                        <div
                            v-if="!photoPreview"
                            class="absolute inset-0 grid grid-cols-3 grid-rows-3 pointer-events-none opacity-30 z-10"
                        >
                            <div
                                class="border-r border-b border-gray-400"
                            ></div>
                            <div
                                class="border-r border-b border-gray-400"
                            ></div>
                            <div class="border-b border-gray-400"></div>
                            <div
                                class="border-r border-b border-gray-400"
                            ></div>
                            <div
                                class="border-r border-b border-gray-400"
                            ></div>
                            <div class="border-b border-gray-400"></div>
                            <div class="border-r border-gray-400"></div>
                            <div class="border-r border-gray-400"></div>
                            <div></div>
                        </div>

                        <div
                            class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex items-center gap-2 px-4 py-2 rounded-full backdrop-blur-md bg-black/60 border border-white/20 text-white text-sm font-medium z-20 whitespace-nowrap shadow-lg"
                        >
                            <div
                                v-if="!coordinates"
                                class="flex items-center gap-2"
                            >
                                <span class="relative flex h-3 w-3">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"
                                    ></span>
                                    <span
                                        class="relative inline-flex rounded-full h-3 w-3 bg-red-500"
                                    ></span>
                                </span>
                                <span class="animate-pulse"
                                    >Mencari GPS...</span
                                >
                            </div>
                            <div v-else class="flex items-center gap-2">
                                <MapPinIcon
                                    class="w-4 h-4"
                                    :class="
                                        isWithinRadius
                                            ? 'text-green-400'
                                            : 'text-red-400'
                                    "
                                />
                                <span
                                    >{{ currentDistance }}m (Akurasi ±{{
                                        accuracy
                                    }}m)</span
                                >
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="absolute inset-0 w-full h-full flex flex-col items-center justify-center bg-gray-900"
                    >
                        <div id="reader" class="w-full h-full"></div>
                        <div
                            class="absolute inset-0 pointer-events-none border-[50px] border-black/50 z-10"
                        ></div>
                        <div
                            class="absolute z-20 w-64 h-64 border-4 border-green-500 rounded-2xl animate-pulse"
                        ></div>
                        <p
                            class="absolute bottom-10 text-white font-bold text-sm z-20 bg-black/60 px-4 py-2 rounded-full backdrop-blur-sm"
                        >
                            Arahkan QR Code Guru ke kotak
                        </p>
                    </div>
                </div>

                <div
                    class="lg:w-5/12 flex flex-col bg-white border-l border-gray-100 relative z-30"
                >
                    <div class="flex border-b border-gray-100">
                        <button
                            @click="switchMode('face')"
                            class="flex-1 py-5 text-sm font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition-all"
                            :class="
                                mode === 'face'
                                    ? 'bg-green-50 text-green-700 border-b-4 border-green-600'
                                    : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600'
                            "
                        >
                            <FaceSmileIcon class="w-5 h-5" /> Absen Wajah
                        </button>
                        <button
                            @click="switchMode('qr')"
                            class="flex-1 py-5 text-sm font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition-all"
                            :class="
                                mode === 'qr'
                                    ? 'bg-green-50 text-green-700 border-b-4 border-green-600'
                                    : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600'
                            "
                        >
                            <QrCodeIcon class="w-5 h-5" /> Absen QR
                        </button>
                    </div>

                    <div
                        class="flex-1 p-6 flex flex-col justify-center space-y-6"
                    >
                        <div class="text-center lg:text-left">
                            <h3
                                class="text-2xl font-bold text-gray-800 flex items-center gap-2 justify-center lg:justify-start"
                            >
                                <span v-if="mode === 'face'"
                                    >📸 Verifikasi Lokasi</span
                                >
                                <span v-else>📱 Scan Kode QR</span>
                            </h3>
                            <p class="text-gray-500 text-sm mt-1">
                                {{
                                    mode === "face"
                                        ? "Pastikan Anda berada di area sekolah."
                                        : "Gunakan untuk absen cepat atau kegiatan."
                                }}
                            </p>
                        </div>

                        <div
                            v-if="
                                form.errors.location ||
                                form.errors.qr ||
                                locationError
                            "
                            class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg animate-pulse"
                        >
                            <div
                                class="flex items-center gap-2 text-red-700 font-bold text-sm"
                            >
                                <ExclamationTriangleIcon class="w-5 h-5" />
                                Gagal Absen
                            </div>
                            <p class="text-red-600 text-xs mt-1">
                                {{
                                    form.errors.location ||
                                    form.errors.qr ||
                                    locationError
                                }}
                            </p>
                        </div>

                        <div
                            v-if="mode === 'face'"
                            class="bg-gray-50 rounded-xl p-4 border border-gray-100"
                        >
                            <div class="flex justify-between items-center mb-2">
                                <span
                                    class="text-xs font-bold text-gray-500 uppercase flex items-center gap-1"
                                >
                                    <SignalIcon class="w-3 h-3" /> Sinyal GPS
                                </span>
                                <span
                                    class="text-xs font-mono bg-white px-2 py-1 rounded border shadow-sm"
                                    :class="
                                        accuracy > 20
                                            ? 'text-amber-600 border-amber-200'
                                            : 'text-green-600 border-green-200'
                                    "
                                >
                                    ±{{ accuracy }}m
                                </span>
                            </div>

                            <div
                                class="w-full bg-gray-200 rounded-full h-2.5 mb-2 overflow-hidden relative"
                            >
                                <div
                                    class="h-2.5 rounded-full transition-all duration-1000 ease-out relative"
                                    :class="
                                        isWithinRadius
                                            ? 'bg-green-500'
                                            : 'bg-red-500'
                                    "
                                    :style="`width: ${Math.min(
                                        (currentDistance /
                                            ((props.schoolSettings?.radius ||
                                                50) *
                                                2)) *
                                            100,
                                        100
                                    )}%`"
                                >
                                    <div
                                        class="absolute inset-0 bg-white/30 w-full h-full animate-[shimmer_2s_infinite]"
                                    ></div>
                                </div>
                            </div>

                            <p
                                class="text-xs font-bold transition-colors duration-300"
                                :class="
                                    isWithinRadius
                                        ? 'text-green-600'
                                        : 'text-red-600'
                                "
                            >
                                {{
                                    isWithinRadius
                                        ? "✅ Lokasi Valid (Dalam Radius)"
                                        : `⛔ Terlalu Jauh (${currentDistance}m)`
                                }}
                            </p>
                        </div>

                        <div
                            v-else
                            class="bg-blue-50 rounded-xl p-4 border border-blue-100 text-center"
                        >
                            <p class="text-blue-800 text-sm font-bold">
                                🚀 Mode Cepat
                            </p>
                            <p class="text-blue-600 text-xs mt-1">
                                GPS tidak wajib valid untuk metode ini.
                            </p>
                        </div>

                        <div class="pt-2">
                            <div v-if="mode === 'face'" class="space-y-3">
                                <div v-if="!photoPreview">
                                    <button
                                        @click="takePhoto"
                                        type="button"
                                        :disabled="
                                            !coordinates || !isWithinRadius
                                        "
                                        class="w-full font-bold py-4 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all duration-300"
                                        :class="{
                                            'bg-green-600 text-white hover:bg-green-700 hover:shadow-green-700/40 active:scale-95 cursor-pointer':
                                                coordinates && isWithinRadius,
                                            'bg-gray-300 text-gray-500 cursor-not-allowed shadow-none':
                                                !coordinates || !isWithinRadius,
                                        }"
                                    >
                                        <span
                                            v-if="!coordinates"
                                            class="flex items-center gap-2"
                                        >
                                            <svg
                                                class="animate-spin h-5 w-5"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle
                                                    class="opacity-25"
                                                    cx="12"
                                                    cy="12"
                                                    r="10"
                                                    stroke="currentColor"
                                                    stroke-width="4"
                                                ></circle>
                                                <path
                                                    class="opacity-75"
                                                    fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                ></path>
                                            </svg>
                                            Mencari Lokasi...
                                        </span>
                                        <span
                                            v-else-if="!isWithinRadius"
                                            class="flex items-center gap-2"
                                        >
                                            <ExclamationTriangleIcon
                                                class="w-5 h-5"
                                            />
                                            Lokasi Kejauhan
                                        </span>
                                        <span
                                            v-else
                                            class="flex items-center gap-2"
                                        >
                                            <CameraIcon class="w-6 h-6" /> Ambil
                                            Selfie
                                        </span>
                                    </button>
                                </div>

                                <div
                                    v-else
                                    class="grid grid-cols-2 gap-3 animate-fade-in-up"
                                >
                                    <button
                                        @click="photoPreview = null"
                                        type="button"
                                        class="bg-gray-100 text-gray-700 font-bold py-3 rounded-xl hover:bg-gray-200 flex items-center justify-center gap-2 transition-colors"
                                    >
                                        <ArrowPathIcon class="w-5 h-5" /> Ulang
                                    </button>
                                    <button
                                        @click="submitAbsen"
                                        :disabled="form.processing"
                                        class="bg-green-700 text-white font-bold py-3 rounded-xl hover:bg-green-800 shadow-lg flex items-center justify-center gap-2 transition-all"
                                    >
                                        <span v-if="form.processing"
                                            >Mengirim...</span
                                        >
                                        <span
                                            v-else
                                            class="flex items-center gap-2"
                                            ><CheckCircleIcon class="w-5 h-5" />
                                            Kirim</span
                                        >
                                    </button>
                                </div>
                            </div>

                            <div v-else class="text-center">
                                <div
                                    v-if="form.processing"
                                    class="text-sm font-bold text-green-600 animate-pulse"
                                >
                                    Memproses data...
                                </div>
                                <p
                                    v-else
                                    class="text-sm text-gray-400 animate-pulse bg-gray-50 p-3 rounded-lg"
                                >
                                    Kamera aktif. Silakan scan QR...
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
#reader {
    width: 100%;
    height: 100%;
    position: relative;
}
#reader video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
}
@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}
.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out;
}
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
