<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

import {
    CalendarDaysIcon,
    ClipboardDocumentCheckIcon,
    DocumentTextIcon,
    UserCircleIcon,
    ClockIcon,
    BookOpenIcon,
    UserIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from "@heroicons/vue/24/outline";

// Props dari Controller
const props = defineProps({
    statistik: {
        type: Object,
        default: () => ({ hadir: 0, izin: 0, total: 0 }),
    },
    auth: Object,
});

const user = usePage().props.auth.user;

// 1. LOGIKA SAPAAN
const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 11) return "Selamat Pagi 🌞";
    if (hour < 15) return "Selamat Siang ☀️";
    if (hour < 18) return "Selamat Sore 🌇";
    return "Selamat Malam 🌙";
});

// 2. DATA DUMMY JADWAL PELAJARAN (Nanti ambil dari DB)
const todaySchedule = [
    {
        id: 1,
        time: "07:15 - 08:30",
        subject: "Matematika",
        teacher: "Budi Santoso, S.Pd",
    },
    {
        id: 2,
        time: "08:30 - 10:00",
        subject: "Bahasa Indonesia",
        teacher: "Siti Aminah, S.Pd",
    },
    {
        id: 3,
        time: "10:15 - 11:45",
        subject: "Pemrograman Web",
        teacher: "Yogi Syahputra, S.Kom",
    },
];

// 3. LOGIKA KALENDER SEDERHANA
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

    // Isi kotak kosong sebelum tanggal 1
    for (let i = 0; i < firstDay; i++) days.push(null);

    // Isi tanggal
    for (let i = 1; i <= daysInMonth; i++) {
        // Simulasi: Tanggal Genap ada pelajaran (Titik Hijau)
        days.push({ date: i, hasEvent: i % 2 === 0 });
    }
    return days;
});

const isToday = (date) => {
    const today = new Date();
    return (
        date === today.getDate() &&
        currentMonth.value === today.getMonth() &&
        currentYear.value === today.getFullYear()
    );
};
</script>

<template>
    <Head title="Dashboard Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2
                        class="font-bold text-2xl text-green-900 leading-tight font-serif"
                    >
                        {{ greeting }}
                    </h2>
                    <p class="text-sm text-gray-500 flex items-center gap-1">
                        <UserIcon class="w-3 h-3" /> {{ user.name }}
                    </p>
                </div>
                <div class="text-right">
                    <span
                        class="text-xs font-bold bg-green-100 text-green-700 px-3 py-1 rounded-full border border-green-200 shadow-sm"
                    >
                        Siswa Aktif
                    </span>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <div
                            class="bg-gradient-to-br from-green-700 to-green-600 rounded-2xl p-6 shadow-lg text-white relative overflow-hidden"
                        >
                            <div
                                class="absolute right-0 top-0 opacity-10 transform translate-x-4 -translate-y-4"
                            >
                                <ClockIcon class="w-40 h-40" />
                            </div>
                            <div class="relative z-10">
                                <h3 class="text-xl font-bold opacity-90">
                                    Sudah absen hari ini?
                                </h3>
                                <p class="text-green-100 text-sm mb-6 max-w-md">
                                    Disiplin adalah kunci kesuksesan. Lakukan
                                    presensi sebelum jam 07:00 WIB untuk
                                    menghindari poin keterlambatan.
                                </p>
                                <Link
                                    :href="route('attendance.index')"
                                    class="inline-block w-full sm:w-auto px-8 py-3 bg-white text-green-800 font-bold rounded-xl shadow-lg hover:bg-green-50 hover:scale-105 transition transform duration-200 text-center"
                                >
                                    📸 SCAN ABSEN SEKARANG
                                </Link>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div
                                class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 text-center hover:shadow-md transition"
                            >
                                <p
                                    class="text-3xl font-extrabold text-green-600"
                                >
                                    {{ props.statistik.hadir }}
                                </p>
                                <p
                                    class="text-xs text-gray-500 font-bold uppercase tracking-wider mt-1"
                                >
                                    Hadir
                                </p>
                            </div>
                            <div
                                class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 text-center hover:shadow-md transition"
                            >
                                <p
                                    class="text-3xl font-extrabold text-blue-600"
                                >
                                    {{ props.statistik.izin }}
                                </p>
                                <p
                                    class="text-xs text-gray-500 font-bold uppercase tracking-wider mt-1"
                                >
                                    Izin/Sakit
                                </p>
                            </div>
                            <div
                                class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 text-center hover:shadow-md transition"
                            >
                                <p
                                    class="text-3xl font-extrabold text-purple-600"
                                >
                                    {{ props.statistik.total }}
                                </p>
                                <p
                                    class="text-xs text-gray-500 font-bold uppercase tracking-wider mt-1"
                                >
                                    Total
                                </p>
                            </div>
                        </div>

                        <div>
                            <h3
                                class="font-bold text-gray-800 text-lg mb-4 flex items-center gap-2"
                            >
                                <span
                                    class="w-1 h-6 bg-green-500 rounded-full"
                                ></span>
                                Menu Utama
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <Link
                                    :href="route('attendance.rekap')"
                                    class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-3 hover:border-green-500 hover:shadow-md transition group cursor-pointer"
                                >
                                    <div
                                        class="w-14 h-14 bg-yellow-50 rounded-full flex items-center justify-center text-yellow-600 group-hover:bg-yellow-500 group-hover:text-white transition duration-300"
                                    >
                                        <CalendarDaysIcon class="w-7 h-7" />
                                    </div>
                                    <span
                                        class="font-bold text-gray-700 text-sm group-hover:text-green-700"
                                        >Riwayat Absen</span
                                    >
                                </Link>

                                <Link
                                    :href="route('attendance.izin')"
                                    class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-3 hover:border-green-500 hover:shadow-md transition group cursor-pointer"
                                >
                                    <div
                                        class="w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition duration-300"
                                    >
                                        <ClipboardDocumentCheckIcon
                                            class="w-7 h-7"
                                        />
                                    </div>
                                    <span
                                        class="font-bold text-gray-700 text-sm group-hover:text-green-700"
                                        >Ajukan Izin</span
                                    >
                                </Link>

                                <button
                                    class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-3 hover:border-green-500 hover:shadow-md transition group cursor-not-allowed opacity-70"
                                >
                                    <div
                                        class="w-14 h-14 bg-purple-50 rounded-full flex items-center justify-center text-purple-600"
                                    >
                                        <DocumentTextIcon class="w-7 h-7" />
                                    </div>
                                    <span
                                        class="font-bold text-gray-700 text-sm"
                                        >E-Raport</span
                                    >
                                </button>

                                <Link
                                    :href="route('profile.edit')"
                                    class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center gap-3 hover:border-green-500 hover:shadow-md transition group cursor-pointer"
                                >
                                    <div
                                        class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center text-gray-600 group-hover:bg-gray-800 group-hover:text-white transition duration-300"
                                    >
                                        <UserCircleIcon class="w-7 h-7" />
                                    </div>
                                    <span
                                        class="font-bold text-gray-700 text-sm group-hover:text-green-700"
                                        >Profil Saya</span
                                    >
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
                        >
                            <div
                                class="bg-green-50 px-6 py-4 border-b border-green-100 flex justify-between items-center"
                            >
                                <span class="font-bold text-green-800"
                                    >{{ monthNames[currentMonth] }}
                                    {{ currentYear }}</span
                                >
                                <div class="flex gap-2">
                                    <button
                                        class="p-1 hover:bg-green-200 rounded text-green-700"
                                    >
                                        <ChevronLeftIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        class="p-1 hover:bg-green-200 rounded text-green-700"
                                    >
                                        <ChevronRightIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-7 text-center mb-2">
                                    <span
                                        v-for="day in [
                                            'M',
                                            'S',
                                            'S',
                                            'R',
                                            'K',
                                            'J',
                                            'S',
                                        ]"
                                        :key="day"
                                        class="text-xs font-bold text-gray-400"
                                        >{{ day }}</span
                                    >
                                </div>
                                <div
                                    class="grid grid-cols-7 gap-1 text-center text-sm"
                                >
                                    <div
                                        v-for="(day, index) in calendarDays"
                                        :key="index"
                                        class="h-9 flex flex-col items-center justify-center rounded-lg transition cursor-default relative"
                                        :class="
                                            day
                                                ? isToday(day.date)
                                                    ? 'bg-green-600 text-white font-bold shadow-md'
                                                    : 'hover:bg-gray-50 text-gray-700'
                                                : ''
                                        "
                                    >
                                        <span v-if="day">{{ day.date }}</span>

                                        <span
                                            v-if="
                                                day &&
                                                day.hasEvent &&
                                                !isToday(day.date)
                                            "
                                            class="w-1.5 h-1.5 bg-green-500 rounded-full absolute bottom-1"
                                        ></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
                        >
                            <div
                                class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-white"
                            >
                                <h3
                                    class="font-bold text-gray-800 flex items-center gap-2"
                                >
                                    <BookOpenIcon
                                        class="w-5 h-5 text-green-600"
                                    />
                                    Jadwal Hari Ini
                                </h3>
                                <span
                                    class="text-[10px] font-bold bg-gray-100 text-gray-500 px-2 py-1 rounded uppercase"
                                    >{{
                                        new Date().toLocaleDateString("id-ID", {
                                            weekday: "long",
                                        })
                                    }}</span
                                >
                            </div>

                            <div class="divide-y divide-gray-50">
                                <div
                                    v-for="schedule in todaySchedule"
                                    :key="schedule.id"
                                    class="p-4 hover:bg-green-50/30 transition flex items-start gap-4"
                                >
                                    <div class="flex-shrink-0 w-14 text-center">
                                        <span
                                            class="text-xs font-bold text-gray-500 block"
                                            >{{
                                                schedule.time.split(" - ")[0]
                                            }}</span
                                        >
                                        <div
                                            class="w-0.5 h-3 bg-gray-300 mx-auto my-0.5"
                                        ></div>
                                        <span
                                            class="text-xs text-gray-400 block"
                                            >{{
                                                schedule.time.split(" - ")[1]
                                            }}</span
                                        >
                                    </div>

                                    <div>
                                        <h4
                                            class="font-bold text-gray-800 text-sm"
                                        >
                                            {{ schedule.subject }}
                                        </h4>
                                        <p
                                            class="text-xs text-green-600 flex items-center gap-1 mt-1"
                                        >
                                            <UserIcon class="w-3 h-3" />
                                            {{ schedule.teacher }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-3 text-center">
                                <Link
                                    href="#"
                                    class="text-xs font-bold text-blue-600 hover:underline"
                                    >Lihat Jadwal Lengkap</Link
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
