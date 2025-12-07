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
} from "@heroicons/vue/24/solid";

// --- PROPS DARI CONTROLLER ---
const props = defineProps({
    schoolSettings: Object, // { lat, lng, radius }
});

// State
const mode = ref("face");
const coordinates = ref(null);
const currentDistance = ref(0);
const accuracy = ref(0); // Tingkat akurasi GPS (meter)
const locationError = ref(null);
const videoRef = ref(null);
const photoPreview = ref(null);
const qrResult = ref(null);
const scannerError = ref(null);

// VARIABLE STREAM & GPS
let html5QrCode = null;
let currentStream = null;
let geoWatcherId = null; // ID untuk pemantau GPS

const form = useForm({
    type: "face",
    latitude: "",
    longitude: "",
    photo: null,
    qr_code: "",
});

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

// --- REALTIME TRACKING ---
const startLocationTracking = () => {
    if (navigator.geolocation) {
        // Hapus watcher lama jika ada
        if (geoWatcherId) navigator.geolocation.clearWatch(geoWatcherId);

        locationError.value = "Sedang mencari satelit GPS...";

        // Gunakan watchPosition agar lokasi terus di-update real-time
        geoWatcherId = navigator.geolocation.watchPosition(
            (position) => {
                locationError.value = null; // Reset error jika berhasil
                coordinates.value = position.coords;
                accuracy.value = Math.round(position.coords.accuracy);

                form.latitude = position.coords.latitude;
                form.longitude = position.coords.longitude;

                // Hitung Jarak Realtime
                if (props.schoolSettings) {
                    currentDistance.value = calculateDistance(
                        position.coords.latitude,
                        position.coords.longitude,
                        props.schoolSettings.lat,
                        props.schoolSettings.lng
                    );
                }
            },
            (error) => {
                console.error(error);
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        locationError.value = "Izin lokasi ditolak browser.";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        locationError.value = "Sinyal GPS tidak ditemukan.";
                        break;
                    case error.TIMEOUT:
                        locationError.value = "Waktu habis mencari sinyal.";
                        break;
                    default:
                        locationError.value = "Gagal mengambil lokasi.";
                }
            },
            // Setting High Accuracy
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0,
            }
        );
    } else {
        locationError.value = "Browser tidak mendukung GPS.";
    }
};

const isWithinRadius = computed(() => {
    const maxRadius = props.schoolSettings?.radius || 50;
    return currentDistance.value <= maxRadius;
});

const startCamera = async () => {
    if (mode.value !== "face") return;
    await stopAllCameras();
    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: true,
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
    canvas.getContext("2d").drawImage(videoRef.value, 0, 0);
    canvas.toBlob((blob) => {
        const file = new File([blob], "selfie.jpg", { type: "image/jpeg" });
        form.photo = file;
        photoPreview.value = URL.createObjectURL(blob);
    }, "image/jpeg");
};

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
        startLocationTracking(); // Nyalakan GPS lagi
        setTimeout(startCamera, 500);
    } else {
        if (geoWatcherId) navigator.geolocation.clearWatch(geoWatcherId); // Matikan GPS saat mode QR (hemat batre)
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
                            class="w-full h-full object-cover"
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
                            class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex items-center gap-2 px-4 py-2 rounded-full backdrop-blur-md bg-black/40 border border-white/20 text-white text-sm font-medium z-20 whitespace-nowrap"
                        >
                            <MapPinIcon class="w-4 h-4 text-yellow-400" />
                            <span v-if="coordinates"
                                >Jarak: {{ currentDistance }}m (±{{
                                    accuracy
                                }}m)</span
                            >
                            <span v-else class="animate-pulse"
                                >Mencari Satelit...</span
                            >
                        </div>
                    </div>

                    <div
                        v-else
                        class="absolute inset-0 w-full h-full flex flex-col items-center justify-center"
                    >
                        <div id="reader" class="w-full h-full bg-black"></div>
                        <p
                            class="absolute bottom-10 text-white/70 text-sm z-20 bg-black/30 px-3 py-1 rounded-full"
                        >
                            Arahkan QR Code ke dalam kotak
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
                            <h3 class="text-2xl font-bold text-gray-800">
                                {{
                                    mode === "face"
                                        ? "Verifikasi Lokasi"
                                        : "Scan Kode QR"
                                }}
                            </h3>
                            <p class="text-gray-500 text-sm mt-1">
                                {{
                                    mode === "face"
                                        ? "Sistem sedang memastikan Anda di sekolah."
                                        : "Scan kode QR dari Admin."
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
                                    class="text-xs font-bold text-gray-500 uppercase"
                                    >Akurasi GPS</span
                                >
                                <span
                                    class="text-xs font-mono bg-white px-2 py-1 rounded border"
                                    :class="
                                        accuracy > 20
                                            ? 'text-red-600'
                                            : 'text-green-600'
                                    "
                                    >±{{ accuracy }}m</span
                                >
                            </div>

                            <div
                                class="w-full bg-gray-200 rounded-full h-2.5 mb-2 overflow-hidden"
                            >
                                <div
                                    class="h-2.5 rounded-full transition-all duration-500"
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
                                ></div>
                            </div>

                            <p
                                class="text-xs"
                                :class="
                                    isWithinRadius
                                        ? 'text-green-600 font-bold'
                                        : 'text-red-600'
                                "
                            >
                                {{
                                    isWithinRadius
                                        ? "✅ Lokasi Sesuai"
                                        : `⛔ Kejauhan: ${currentDistance}m (Max ${
                                              props.schoolSettings?.radius || 50
                                          }m)`
                                }}
                            </p>
                        </div>

                        <div class="pt-4">
                            <div v-if="mode === 'face'" class="space-y-3">
                                <div v-if="!photoPreview">
                                    <button
                                        @click="takePhoto"
                                        type="button"
                                        class="w-full bg-green-700 text-white font-bold py-4 rounded-xl hover:bg-green-800 shadow-lg shadow-green-700/30 flex items-center justify-center gap-2 transition-transform active:scale-95"
                                    >
                                        <CameraIcon class="w-6 h-6" /> Ambil
                                        Selfie
                                    </button>
                                </div>
                                <div v-else class="grid grid-cols-2 gap-3">
                                    <button
                                        @click="photoPreview = null"
                                        type="button"
                                        class="bg-gray-100 text-gray-700 font-bold py-3 rounded-xl hover:bg-gray-200 flex items-center justify-center gap-2"
                                    >
                                        <ArrowPathIcon class="w-5 h-5" /> Ulang
                                    </button>
                                    <button
                                        @click="submitAbsen"
                                        :disabled="
                                            form.processing || !isWithinRadius
                                        "
                                        class="bg-green-700 text-white font-bold py-3 rounded-xl hover:bg-green-800 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                    >
                                        {{
                                            form.processing
                                                ? "Mengirim..."
                                                : "Kirim"
                                        }}
                                        <CheckCircleIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                            <div v-else class="text-center">
                                <p
                                    class="text-sm text-gray-400 animate-pulse bg-gray-50 p-3 rounded-lg"
                                >
                                    Kamera aktif. Silakan scan...
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
</style>
