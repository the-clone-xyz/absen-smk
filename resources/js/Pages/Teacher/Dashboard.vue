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
} from "@heroicons/vue/24/solid";

const props = defineProps({
    absensi: Array,
    statistik: Object,
    qrToken: String,
    jadwal: Array,
});

// State
const showQRModal = ref(false);
const currentQrToken = ref(props.qrToken);
const timeLeft = ref(15);
let intervalId = null;
let countdownInterval = null;

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
    for (let i = 1; i <= daysInMonth; i++)
        days.push({
            date: i,
            isToday:
                i === new Date().getDate() &&
                currentMonth.value === new Date().getMonth(),
        });
    return days;
});
// -----------------------

const tanggalHariIni = computed(() => {
    return new Date().toLocaleDateString("id-ID", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
});

const updateStatus = (id, status) => {
    if (confirm(`Yakin ingin mengubah status menjadi ${status}?`)) {
        router.patch(route("teacher.attendance.approve", id), {
            status: status,
        });
    }
};

const fetchNewToken = () => {
    fetch(route("teacher.qr.token"))
        .then((res) => res.json())
        .then((data) => {
            if (data && data.token) {
                currentQrToken.value = data.token;
                timeLeft.value = 15;
            }
        })
        .catch((err) => console.error("Gagal refresh token:", err));
};

onMounted(() => {
    fetchNewToken();
    intervalId = setInterval(fetchNewToken, 15000);
    countdownInterval = setInterval(() => {
        if (timeLeft.value > 0) timeLeft.value--;
    }, 1000);
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
            <h2 class="font-bold text-xl text-green-800 leading-tight">
                Panel Guru
            </h2>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
                    <div class="lg:col-span-4 flex flex-col gap-4">
                        <div
                            class="bg-white rounded-xl shadow-sm border border-gray-200 p-4"
                        >
                            <h3
                                class="font-bold text-gray-700 mb-3 flex items-center gap-2"
                            >
                                <ClockIcon class="w-5 h-5 text-blue-600" />
                                Jadwal Hari Ini
                            </h3>

                            <div v-if="jadwal.length > 0" class="space-y-3">
                                <div
                                    v-for="item in jadwal"
                                    :key="item.id"
                                    class="p-3 rounded-lg border-l-4 relative hover:shadow-md transition"
                                    :class="
                                        item.is_done
                                            ? 'bg-gray-50 border-gray-400'
                                            : 'bg-blue-50 border-blue-500'
                                    "
                                >
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
                                            class="text-[10px] bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 font-bold"
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
                                class="text-center py-8 text-gray-400 text-sm italic"
                            >
                                Tidak ada jadwal mengajar hari ini.
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div
                                class="bg-white p-3 rounded-xl border border-green-100 text-center"
                            >
                                <p
                                    class="text-xs text-gray-500 uppercase font-bold"
                                >
                                    Hadir
                                </p>
                                <p class="text-xl font-bold text-green-600">
                                    {{ statistik.hadir }}
                                </p>
                            </div>
                            <div
                                class="bg-white p-3 rounded-xl border border-yellow-100 text-center"
                            >
                                <p
                                    class="text-xs text-gray-500 uppercase font-bold"
                                >
                                    Pending
                                </p>
                                <p class="text-xl font-bold text-yellow-600">
                                    {{ statistik.pending }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-4 flex flex-col gap-4">
                        <div
                            class="bg-gradient-to-br from-green-700 to-green-600 rounded-2xl p-6 text-center text-white shadow-lg relative overflow-hidden group"
                        >
                            <div
                                class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"
                            ></div>
                            <CameraIcon
                                class="w-24 h-24 absolute -right-4 -bottom-4 opacity-20 group-hover:scale-110 transition"
                            />

                            <h3 class="text-lg font-bold relative z-10 mb-1">
                                Absensi Guru
                            </h3>
                            <p
                                class="text-xs text-green-100 mb-4 relative z-10"
                            >
                                Sudah sampai sekolah? Scan QR Admin sekarang.
                            </p>

                            <Link
                                :href="route('attendance.index')"
                                class="relative z-10 inline-flex items-center gap-2 bg-white text-green-800 px-6 py-3 rounded-xl font-bold shadow-md hover:bg-green-50 transition transform active:scale-95"
                            >
                                <QrCodeIcon class="w-5 h-5" /> SCAN KEHADIRAN
                            </Link>
                        </div>

                        <div
                            class="bg-white border-2 border-dashed border-green-200 rounded-2xl p-6 text-center hover:border-green-400 transition group cursor-pointer"
                            @click="showQRModal = true"
                        >
                            <QrCodeIcon
                                class="w-12 h-12 text-green-500 mx-auto mb-2 group-hover:scale-110 transition"
                            />
                            <h3 class="font-bold text-gray-800">
                                Buka Absen Kelas
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">
                                Tampilkan QR Code di proyektor agar siswa bisa
                                absen.
                            </p>
                        </div>
                    </div>

                    <div
                        class="lg:col-span-4 bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col h-full"
                    >
                        <div class="flex justify-between items-center mb-4">
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
                            class="grid grid-cols-7 text-center text-xs text-gray-400 font-bold mb-2"
                        >
                            <span
                                v-for="d in ['M', 'S', 'S', 'R', 'K', 'J', 'S']"
                                :key="d"
                                >{{ d }}</span
                            >
                        </div>
                        <div
                            class="grid grid-cols-7 gap-1 text-center text-xs flex-grow"
                        >
                            <div
                                v-for="(day, i) in calendarDays"
                                :key="i"
                                class="h-8 flex items-center justify-center rounded-lg"
                                :class="
                                    day
                                        ? day.isToday
                                            ? 'bg-green-600 text-white font-bold shadow'
                                            : 'hover:bg-gray-50 text-gray-600'
                                        : ''
                                "
                            >
                                {{ day ? day.date : "" }}
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
                            Data Absensi Masuk
                        </h3>
                        <span
                            class="text-xs text-gray-500 bg-white border px-2 py-1 rounded"
                            >{{ tanggalHariIni }}</span
                        >
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                    >
                                        Siswa
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                    >
                                        Info
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="item in absensi"
                                    :key="item.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-bold text-gray-900"
                                        >
                                            {{ item.user.name }}
                                        </div>
                                        <div class="text-[10px] text-gray-400">
                                            {{ item.time_in }}
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center"
                                    >
                                        <span
                                            class="px-2 py-1 text-xs font-bold rounded-full border"
                                            :class="{
                                                'bg-green-100 text-green-800 border-green-200':
                                                    item.status === 'Hadir',
                                                'bg-blue-100 text-blue-800 border-blue-200':
                                                    item.status === 'Sakit',
                                                'bg-yellow-100 text-yellow-800 border-yellow-200':
                                                    item.status === 'Izin',
                                            }"
                                        >
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-xs text-gray-500 max-w-[150px] truncate"
                                    >
                                        {{ item.description }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center"
                                    >
                                        <div
                                            v-if="
                                                item.approval_status ===
                                                    'pending' &&
                                                item.status !== 'Hadir'
                                            "
                                            class="flex justify-center gap-2"
                                        >
                                            <button
                                                @click="
                                                    updateStatus(
                                                        item.id,
                                                        'approved'
                                                    )
                                                "
                                                class="p-1 bg-green-100 text-green-600 rounded hover:bg-green-200"
                                            >
                                                <CheckCircleIcon
                                                    class="w-5 h-5"
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
                                            >
                                                <XCircleIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                        <div
                                            v-else
                                            class="text-[10px] text-gray-400 italic"
                                        >
                                            Selesai
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="absensi.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-6 py-8 text-center text-gray-400 text-sm"
                                    >
                                        Belum ada data absensi masuk.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="showQRModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4"
            @click="showQRModal = false"
        >
            <div
                class="bg-white rounded-3xl shadow-2xl overflow-hidden max-w-4xl w-full flex flex-col md:flex-row"
                @click.stop
            >
                <div
                    class="md:w-1/2 bg-slate-900 p-8 flex flex-col items-center justify-center relative overflow-hidden"
                >
                    <div class="absolute top-0 left-0 w-full h-1 bg-slate-800">
                        <div
                            class="h-full bg-green-500 transition-all duration-1000 ease-linear"
                            :style="`width: ${(timeLeft / 15) * 100}%`"
                        ></div>
                    </div>
                    <div
                        class="bg-white p-4 rounded-xl shadow-lg relative z-10"
                    >
                        <QrcodeVue
                            :value="currentQrToken"
                            :size="220"
                            level="H"
                        />
                    </div>
                    <p
                        class="text-green-400/80 text-xs mt-4 font-mono tracking-widest uppercase"
                    >
                        Secure Token
                    </p>
                </div>
                <div class="md:w-1/2 p-8 flex flex-col justify-center bg-white">
                    <h3 class="text-2xl font-extrabold text-gray-800 mb-2">
                        Scan untuk Absen
                    </h3>
                    <p class="text-gray-500 text-sm mb-6">
                        Arahkan kamera HP siswa ke kode di samping.
                    </p>
                    <div
                        class="bg-green-50 p-4 rounded-xl border border-green-100 mb-6 text-xs text-green-700"
                    >
                        Kode berubah otomatis setiap <b>15 detik</b>.
                    </div>
                    <button
                        @click="showQRModal = false"
                        class="w-full py-3 text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-lg text-sm font-bold transition"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
