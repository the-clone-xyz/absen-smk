<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted, computed } from "vue";
import QrcodeVue from "qrcode.vue";
import {
    UserGroupIcon,
    CheckBadgeIcon,
    QrCodeIcon,
    CameraIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationCircleIcon,
    CalendarDaysIcon,
    ClockIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    ArrowPathIcon,
    XMarkIcon,
    CursorArrowRaysIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    absensiGrouped: Object, // Data Absensi per Kelas
    statistik: Object,
    qrToken: String,
    jadwal: Array,
    jadwalKalender: Object,
});

// State
const showQRModal = ref(false);
const currentQrToken = ref("");
const timeLeft = ref(15);
let intervalId = null;
let countdownInterval = null;

// --- LOGIKA JADWAL (BERKEDIP) ---
const isApproaching = (startTime) => {
    const now = new Date();
    const [hours, minutes] = startTime.split(":");
    const scheduleTime = new Date();
    scheduleTime.setHours(hours, minutes, 0);
    const diffMinutes = (scheduleTime - now) / 1000 / 60;
    return diffMinutes > 0 && diffMinutes <= 10;
};

// --- LOGIKA KALENDER ---
const currentDate = new Date();
const currentMonth = ref(currentDate.getMonth());
const currentYear = ref(currentDate.getFullYear());
const monthNames = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
];
const dayNames = [
    "Minggu",
    "Senin",
    "Selasa",
    "Rabu",
    "Kamis",
    "Jumat",
    "Sabtu",
];

const selectedDate = ref(null);
const selectedSchedule = ref([]);

const calendarDays = computed(() => {
    const days = [];
    const firstDay = new Date(
        currentYear.value,
        currentMonth.value,
        1
    ).getDay();
    const daysInMonth = new Date(
        currentYear.value,
        currentMonth.value + 1,
        0
    ).getDate();

    for (let i = 0; i < firstDay; i++) days.push(null);

    for (let i = 1; i <= daysInMonth; i++) {
        const dateObj = new Date(currentYear.value, currentMonth.value, i);
        const dayName = dayNames[dateObj.getDay()];
        const hasSchedule =
            props.jadwalKalender && props.jadwalKalender[dayName]
                ? true
                : false;

        days.push({
            date: i,
            dayName: dayName,
            hasSchedule: hasSchedule,
            isToday:
                i === new Date().getDate() &&
                currentMonth.value === new Date().getMonth(),
        });
    }
    return days;
});

const selectDate = (day) => {
    if (!day || !day.hasSchedule) {
        selectedDate.value = null;
        return;
    }
    selectedDate.value = day.date;
    selectedSchedule.value = props.jadwalKalender[day.dayName] || [];
};

const tanggalHariIni = computed(() => {
    return new Date().toLocaleDateString("id-ID", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
});

const updateStatus = (id, status) => {
    if (confirm(`Ubah status menjadi ${status}?`)) {
        router.patch(route("teacher.attendance.approve", id), {
            status: status,
        });
    }
};

const fetchNewToken = () => {
    fetch(route("teacher.qr.token"))
        .then((res) => res.json())
        .then((data) => {
            if (data.token) {
                currentQrToken.value = data.token;
                timeLeft.value = 15;
            }
        });
};

// Toggle Modal QR (Digunakan oleh card tengah)
const toggleQrModal = (show) => {
    showQRModal.value = show;
    if (show) {
        fetchNewToken();
        intervalId = setInterval(fetchNewToken, 15000);
        countdownInterval = setInterval(() => {
            if (timeLeft.value > 0) timeLeft.value--;
        }, 1000);
    } else {
        clearInterval(intervalId);
        clearInterval(countdownInterval);
    }
};

onMounted(() => {
    // Kita tidak start interval otomatis di sini lagi, hanya saat modal dibuka
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
    if (countdownInterval) clearInterval(countdownInterval);
});
</script>

<template>
    <Head title="Panel Guru" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-xl text-green-800 leading-tight">
                    Panel Guru
                </h2>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
                    <div
                        class="lg:col-span-4 bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col h-[350px]"
                    >
                        <h3
                            class="font-bold text-gray-700 mb-3 flex items-center gap-2 flex-shrink-0"
                        >
                            <ClockIcon class="w-5 h-5 text-blue-600" /> Jadwal
                            Hari Ini
                        </h3>

                        <div
                            class="overflow-y-auto flex-grow pr-2 space-y-3 custom-scrollbar"
                        >
                            <div v-if="jadwal && jadwal.length > 0">
                                <div
                                    v-for="item in jadwal"
                                    :key="item.id"
                                    class="p-3 rounded-lg border-l-4 relative hover:shadow-md transition mb-3"
                                    :class="[
                                        item.is_done
                                            ? 'bg-gray-50 border-gray-400 opacity-70'
                                            : 'bg-blue-50 border-blue-500',
                                        !item.is_done &&
                                        isApproaching(item.start_time)
                                            ? 'animate-pulse border-red-500 bg-red-50'
                                            : '',
                                    ]"
                                >
                                    <div
                                        v-if="
                                            !item.is_done &&
                                            isApproaching(item.start_time)
                                        "
                                        class="absolute top-2 right-2 text-[9px] font-bold text-red-600 bg-white px-2 py-0.5 rounded-full animate-bounce"
                                    >
                                        SEGERA MULAI!
                                    </div>

                                    <div
                                        class="flex justify-between items-start"
                                    >
                                        <div>
                                            <p
                                                class="font-bold text-sm text-gray-800"
                                            >
                                                {{ item.subject.name }}
                                            </p>
                                            <p class="text-xs text-gray-600">
                                                {{ item.class.name }}
                                            </p>
                                        </div>
                                        <span
                                            class="text-[10px] font-mono bg-white px-1.5 py-0.5 rounded border"
                                            >{{
                                                item.start_time.substring(0, 5)
                                            }}</span
                                        >
                                    </div>
                                    <div class="mt-2 flex justify-end">
                                        <Link
                                            v-if="!item.is_done"
                                            :href="
                                                route(
                                                    'teacher.classroom.show',
                                                    item.id
                                                )
                                            "
                                            class="text-[10px] bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 font-bold z-10 relative"
                                        >
                                            Mulai Kelas
                                        </Link>
                                        <span
                                            v-else
                                            class="text-[10px] text-gray-400 font-bold flex items-center gap-1"
                                            ><CheckCircleIcon class="w-3 h-3" />
                                            Selesai</span
                                        >
                                    </div>
                                </div>
                            </div>
                            <div
                                v-else
                                class="text-center py-10 text-gray-400 text-sm italic"
                            >
                                Tidak ada jadwal mengajar hari ini.
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 flex flex-col gap-4 h-[350px]">
                        <div
                            class="bg-gradient-to-br from-green-700 to-green-600 rounded-2xl p-6 text-center text-white shadow-lg relative overflow-hidden group flex-grow flex flex-col justify-center items-center"
                        >
                            <div
                                class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"
                            ></div>
                            <CameraIcon
                                class="w-32 h-32 absolute -right-6 -bottom-6 opacity-20 group-hover:rotate-12 transition duration-500"
                            />

                            <h3 class="text-xl font-bold relative z-10 mb-2">
                                Absensi Guru
                            </h3>
                            <p
                                class="text-xs text-green-100 mb-6 relative z-10 max-w-xs"
                            >
                                Scan QR Code di meja piket/admin untuk mencatat
                                kehadiran Anda.
                            </p>
                            <Link
                                :href="route('attendance.index')"
                                class="relative z-10 inline-flex items-center gap-2 bg-white text-green-800 px-8 py-3 rounded-xl font-bold shadow-md hover:bg-green-50 transition transform active:scale-95 w-full justify-center"
                            >
                                <QrCodeIcon class="w-5 h-5" /> SCAN KEHADIRAN
                                SAYA
                            </Link>
                        </div>

                        <div class="grid grid-cols-2 gap-2 h-1/4">
                            <div
                                class="bg-white p-2 rounded-xl border border-green-100 text-center flex flex-col justify-center"
                            >
                                <p
                                    class="text-[10px] text-gray-500 uppercase font-bold"
                                >
                                    Hadir
                                </p>
                                <p class="text-lg font-bold text-green-600">
                                    {{ statistik.hadir }}
                                </p>
                            </div>
                            <div
                                class="bg-white p-2 rounded-xl border border-yellow-100 text-center flex flex-col justify-center"
                            >
                                <p
                                    class="text-[10px] text-gray-500 uppercase font-bold"
                                >
                                    Pending
                                </p>
                                <p class="text-lg font-bold text-yellow-600">
                                    {{ statistik.pending }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="lg:col-span-4 bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col h-[350px] relative"
                    >
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-gray-700 text-sm"
                                >{{ monthNames[currentMonth] }}
                                {{ currentYear }}</span
                            >
                            <div class="flex gap-1">
                                <button class="p-1 hover:bg-gray-100 rounded">
                                    <ChevronLeftIcon class="w-4 h-4" />
                                </button>
                                <button class="p-1 hover:bg-gray-100 rounded">
                                    <ChevronRightIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-7 text-center text-[10px] text-gray-400 font-bold mb-1"
                        >
                            <span
                                v-for="d in ['M', 'S', 'S', 'R', 'K', 'J', 'S']"
                                :key="d"
                                >{{ d }}</span
                            >
                        </div>
                        <div
                            class="grid grid-cols-7 gap-1 text-center text-xs flex-grow content-start overflow-y-auto custom-scrollbar"
                        >
                            <div
                                v-for="(day, i) in calendarDays"
                                :key="i"
                                @click="selectDate(day)"
                                class="h-8 flex flex-col items-center justify-center rounded-lg relative transition cursor-pointer"
                                :class="[
                                    !day
                                        ? ''
                                        : day.isToday
                                        ? 'bg-green-600 text-white font-bold'
                                        : day.hasSchedule
                                        ? 'bg-green-50 text-green-800 hover:bg-green-100'
                                        : 'hover:bg-gray-50 text-gray-400',
                                    selectedDate === (day ? day.date : -1)
                                        ? 'ring-2 ring-green-500'
                                        : '',
                                ]"
                            >
                                {{ day ? day.date : "" }}
                                <span
                                    v-if="
                                        day && day.hasSchedule && !day.isToday
                                    "
                                    class="w-1 h-1 bg-green-500 rounded-full absolute bottom-1"
                                ></span>
                            </div>
                        </div>

                        <div
                            v-if="selectedDate"
                            class="absolute top-12 left-4 right-4 bottom-4 bg-white/95 backdrop-blur-sm border border-green-200 shadow-lg rounded-xl p-4 z-10 animate-fade-in overflow-y-auto"
                        >
                            <div
                                class="flex justify-between items-center mb-2 border-b pb-2"
                            >
                                <h5 class="font-bold text-xs text-green-800">
                                    Jadwal Tgl {{ selectedDate }}
                                </h5>
                                <button
                                    @click="selectedDate = null"
                                    class="text-gray-400 hover:text-red-500"
                                >
                                    <XMarkIcon class="w-5 h-5" />
                                </button>
                            </div>
                            <div v-if="selectedSchedule.length > 0">
                                <div
                                    v-for="s in selectedSchedule"
                                    :key="s.id"
                                    class="mb-2 text-xs border-b border-gray-100 pb-1 last:border-0"
                                >
                                    <p class="font-bold text-gray-700">
                                        {{ s.subject.name }}
                                    </p>
                                    <p class="text-gray-500">
                                        {{ s.class.name }} •
                                        {{ s.start_time.substring(0, 5) }}
                                    </p>
                                </div>
                            </div>
                            <div
                                v-else
                                class="text-xs text-gray-400 italic text-center py-4"
                            >
                                Tidak ada jadwal.
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200"
                >
                    <div
                        class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center"
                    >
                        <h3 class="font-bold text-gray-700">
                            Absensi Masuk Hari Ini
                        </h3>
                        <span
                            class="text-xs text-gray-500 bg-white border px-2 py-1 rounded"
                            >{{ tanggalHariIni }}</span
                        >
                    </div>

                    <div class="p-0">
                        <div
                            v-if="
                                !absensiGrouped ||
                                Object.keys(absensiGrouped).length === 0
                            "
                            class="text-center py-8 text-gray-400 text-sm"
                        >
                            Belum ada data absensi masuk.
                        </div>

                        <div
                            v-for="(group, className) in absensiGrouped"
                            :key="className"
                            class="border-b border-gray-100 last:border-0"
                        >
                            <div
                                class="bg-gray-100/50 px-6 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider flex items-center gap-2"
                            >
                                <UserGroupIcon class="w-4 h-4" />
                                {{ className }}
                            </div>

                            <div class="divide-y divide-gray-50">
                                <div
                                    v-for="item in group"
                                    :key="item.id"
                                    class="px-6 py-3 flex items-center justify-between hover:bg-blue-50/30 transition"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold"
                                        >
                                            {{
                                                item.user.name
                                                    .substring(0, 2)
                                                    .toUpperCase()
                                            }}
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-bold text-gray-800"
                                            >
                                                {{ item.user.name }}
                                            </p>
                                            <p
                                                class="text-[10px] text-gray-400"
                                            >
                                                {{ item.time_in }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <span
                                            class="px-2 py-0.5 text-[10px] font-bold rounded border"
                                            :class="{
                                                'bg-green-50 text-green-700 border-green-200':
                                                    item.status === 'Hadir',
                                                'bg-blue-50 text-blue-700 border-blue-200':
                                                    item.status === 'Sakit',
                                                'bg-yellow-50 text-yellow-700 border-yellow-200':
                                                    item.status === 'Izin',
                                            }"
                                        >
                                            {{ item.status }}
                                        </span>

                                        <div
                                            v-if="
                                                item.approval_status ===
                                                    'pending' &&
                                                item.status !== 'Hadir'
                                            "
                                            class="flex gap-1"
                                        >
                                            <button
                                                @click="
                                                    updateStatus(
                                                        item.id,
                                                        'approved'
                                                    )
                                                "
                                                class="p-1 bg-green-100 text-green-600 rounded hover:bg-green-200"
                                                title="Terima"
                                            >
                                                <CheckCircleIcon
                                                    class="w-4 h-4"
                                                />
                                            </button>
                                            <button
                                                @click="
                                                    updateStatus(
                                                        item.id,
                                                        'rejected'
                                                    )
                                                "
                                                class="p-1 bg-red-100 text-red-600 rounded hover:bg-red-200"
                                                title="Tolak"
                                            >
                                                <XCircleIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="showQRModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4 transition-opacity"
            @click="toggleQrModal(false)"
        >
            <div
                class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-4xl w-full flex flex-col md:flex-row transform transition-all scale-100"
                @click.stop
            >
                <div
                    class="md:w-1/2 bg-slate-900 p-10 flex flex-col items-center justify-center relative overflow-hidden"
                >
                    <div
                        class="absolute top-0 left-0 w-full h-1.5 bg-slate-800"
                    >
                        <div
                            class="h-full bg-blue-500 transition-all duration-1000 ease-linear box-shadow-glow"
                            :style="`width: ${(timeLeft / 15) * 100}%`"
                        ></div>
                    </div>

                    <div
                        class="bg-white p-4 rounded-2xl shadow-2xl relative z-10 min-h-[260px] flex items-center justify-center"
                    >
                        <div
                            v-if="!currentQrToken"
                            class="text-slate-400 flex flex-col items-center animate-pulse"
                        >
                            <ArrowPathIcon
                                class="w-10 h-10 animate-spin mb-2"
                            />
                            <span class="text-xs">Memuat Token...</span>
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
                        Secure Daily Token
                    </p>

                    <p
                        class="text-[9px] text-slate-600 mt-2 font-mono break-all max-w-[200px] text-center"
                    >
                        {{ currentQrToken }}
                    </p>
                </div>

                <div
                    class="md:w-1/2 p-10 flex flex-col justify-center bg-white"
                >
                    <div class="mb-auto">
                        <span
                            class="text-[10px] font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase tracking-wider border border-blue-100"
                        >
                            Absensi Harian
                        </span>
                        <h3
                            class="text-3xl font-extrabold text-gray-900 mt-4 mb-2"
                        >
                            Scan Kehadiran
                        </h3>
                        <p class="text-gray-500 text-sm leading-relaxed">
                            Siswa melakukan scan pada kode ini untuk mencatat
                            kehadiran harian (masuk sekolah).
                        </p>
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
                                    Petunjuk
                                </h5>
                                <p
                                    class="text-xs text-gray-500 leading-relaxed mt-1"
                                >
                                    Pastikan siswa menggunakan menu
                                    <b>"Scan QR Code"</b> di aplikasi mereka.
                                </p>
                            </div>
                        </div>
                    </div>

                    <button
                        @click="toggleQrModal(false)"
                        class="w-full py-3.5 bg-slate-900 text-white rounded-xl text-sm font-bold hover:bg-slate-800 transition shadow-lg flex items-center justify-center gap-2"
                    >
                        <XMarkIcon class="w-4 h-4" /> Tutup Layar
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
.animate-fade-in {
    animation: fadeIn 0.2s ease-out;
}
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
