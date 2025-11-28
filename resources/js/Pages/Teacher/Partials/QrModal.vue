<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import QrcodeVue from "qrcode.vue";
import {
    ArrowPathIcon,
    XMarkIcon,
    CursorArrowRaysIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    show: Boolean,
    scheduleId: Number,
    subjectName: String,
    className: String,
});

const emit = defineEmits(["close"]);

const currentQrToken = ref("");
const timeLeft = ref(15);
const isLoadingToken = ref(false);
let tokenInterval = null;
let countdownInterval = null;

const fetchClassToken = () => {
    if (!currentQrToken.value) isLoadingToken.value = true;
    fetch(route("teacher.classroom.qr_token", props.scheduleId))
        .then((res) => res.json())
        .then((data) => {
            if (data.token) {
                currentQrToken.value = data.token;
                timeLeft.value = 15;
            }
        })
        .finally(() => {
            isLoadingToken.value = false;
        });
};

const startTimer = () => {
    fetchClassToken();
    tokenInterval = setInterval(fetchClassToken, 15000);
    countdownInterval = setInterval(() => {
        if (timeLeft.value > 0) timeLeft.value--;
    }, 1000);
};

const stopTimer = () => {
    clearInterval(tokenInterval);
    clearInterval(countdownInterval);
};

// Watch perubahan prop 'show' agar timer nyala/mati otomatis (Optional, but good)
// Disini kita pakai onMounted sederhana karena modal di-v-if di parent

onMounted(() => {
    startTimer();
});

onUnmounted(() => {
    stopTimer();
});
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4"
        @click="emit('close')"
    >
        <div
            class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-4xl w-full flex flex-col md:flex-row transform transition-all scale-100"
            @click.stop
        >
            <div
                class="md:w-1/2 bg-slate-900 p-10 flex flex-col items-center justify-center relative overflow-hidden"
            >
                <div class="absolute top-0 left-0 w-full h-1.5 bg-slate-800">
                    <div
                        class="h-full bg-blue-500 transition-all duration-1000 ease-linear"
                        :style="`width: ${(timeLeft / 15) * 100}%`"
                    ></div>
                </div>
                <div
                    class="bg-white p-4 rounded-2xl shadow-2xl relative z-10 min-h-[260px] flex items-center justify-center"
                >
                    <div
                        v-if="isLoadingToken"
                        class="text-slate-400 flex flex-col items-center animate-pulse"
                    >
                        <ArrowPathIcon class="w-10 h-10 animate-spin mb-2" />
                        <span class="text-xs">Memuat Kode...</span>
                    </div>
                    <QrcodeVue
                        v-else
                        :value="currentQrToken"
                        :size="240"
                        level="H"
                    />
                </div>
                <p
                    class="text-blue-200/60 text-[10px] mt-6 font-mono tracking-[0.3em] uppercase flex items-center gap-2"
                >
                    <span
                        class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"
                    ></span>
                    Secure Token Active
                </p>
            </div>

            <div class="md:w-1/2 p-10 flex flex-col justify-center bg-white">
                <div class="mb-auto">
                    <span
                        class="text-[10px] font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase tracking-wider border border-blue-100"
                        >Absensi Mapel</span
                    >
                    <h3 class="text-3xl font-extrabold text-gray-900 mt-4 mb-2">
                        {{ subjectName }}
                    </h3>
                    <p class="text-gray-500 text-sm">{{ className }}</p>
                </div>

                <div class="space-y-4 mb-8">
                    <div
                        class="flex items-start gap-4 bg-gray-50 p-4 rounded-xl border border-gray-100"
                    >
                        <div
                            class="bg-white p-2 rounded-lg text-gray-600 shadow-sm"
                        >
                            <CursorArrowRaysIcon class="w-5 h-5" />
                        </div>
                        <div>
                            <h5 class="font-bold text-gray-800 text-sm">
                                Instruksi Siswa
                            </h5>
                            <p
                                class="text-xs text-gray-500 leading-relaxed mt-1"
                            >
                                Siswa membuka menu <b>Scan QR</b> dan
                                mengarahkan kamera ke layar ini.
                            </p>
                        </div>
                    </div>
                </div>

                <button
                    @click="emit('close')"
                    class="w-full py-3.5 bg-slate-900 text-white rounded-xl text-sm font-bold hover:bg-slate-800 transition shadow-lg flex items-center justify-center gap-2"
                >
                    <XMarkIcon class="w-4 h-4" /> Tutup Layar
                </button>
            </div>
        </div>
    </div>
</template>
