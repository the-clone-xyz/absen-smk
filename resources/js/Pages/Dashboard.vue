<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
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
    QrCodeIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    auth: Object,
    statistik: Object,
    jadwal: Array, // Data Jadwal Realtime
    riwayatAbsen: Object, // Data Riwayat Bulanan
});

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

// Navigasi Bulan
const prevMonth = () => {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
};

const nextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
};

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

    // Isi kotak kosong di awal bulan
    for (let i = 0; i < firstDay; i++) days.push(null);

    // Isi tanggal
    for (let i = 1; i <= daysInMonth; i++) {
        // Format tanggal YYYY-MM-DD agar cocok dengan key di database
        const dateStr = `${currentYear.value}-${String(
            currentMonth.value + 1
        ).padStart(2, "0")}-${String(i).padStart(2, "0")}`;

        // Cek data riwayat
        const riwayat = props.riwayatAbsen ? props.riwayatAbsen[dateStr] : null;

        days.push({
            date: i,
            status: riwayat ? riwayat.status : null,
        });
    }
    return days;
});

const isToday = (day) => {
    const today = new Date();
    return (
        day === today.getDate() &&
        currentMonth.value === today.getMonth() &&
        currentYear.value === today.getFullYear()
    );
};
</script>

<template>
    <Head title="Dashboard Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col md:flex-row justify-between items-center gap-4"
            >
                <h2
                    class="font-extrabold text-2xl text-green-900 leading-none tracking-tight"
                >
                    Dashboard Siswa
                </h2>

                <Link
                    :href="route('attendance.index')"
                    class="bg-green-600 text-white px-6 py-2.5 rounded-xl hover:bg-green-700 flex items-center gap-2 text-sm font-bold shadow-lg shadow-green-200 transition transform active:scale-95"
                >
                    <QrCodeIcon class="w-5 h-5" /> SCAN ABSEN
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <div
                            class="bg-gradient-to-br from-green-600 to-emerald-700 rounded-2xl p-6 shadow-xl text-white relative overflow-hidden"
                        >
                            <div
                                class="absolute right-0 top-0 opacity-10 transform translate-x-4 -translate-y-4"
                            >
                                <ClockIcon class="w-40 h-40" />
                            </div>

                            <div class="relative z-10">
                                <h3 class="text-xl font-bold opacity-95">
                                    Halo, {{ auth.user.name }}! 👋
                                </h3>
                                <p
                                    class="text-green-100 text-sm mb-6 max-w-md mt-1"
                                >
                                    Jangan lupa absen sebelum jam 07:00 WIB agar
                                    tidak terlambat.
                                </p>

                                <div class="grid grid-cols-3 gap-3">
                                    <div
                                        class="bg-white/10 backdrop-blur-sm p-3 rounded-xl border border-white/10 text-center"
                                    >
                                        <p class="text-2xl font-bold">
                                            {{ statistik.hadir }}
                                        </p>
                                        <p
                                            class="text-[10px] uppercase font-bold text-green-100"
                                        >
                                            Hadir
                                        </p>
                                    </div>
                                    <div
                                        class="bg-white/10 backdrop-blur-sm p-3 rounded-xl border border-white/10 text-center"
                                    >
                                        <p class="text-2xl font-bold">
                                            {{ statistik.sakit }}
                                        </p>
                                        <p
                                            class="text-[10px] uppercase font-bold text-green-100"
                                        >
                                            Izin
                                        </p>
                                    </div>
                                    <div
                                        class="bg-white/10 backdrop-blur-sm p-3 rounded-xl border border-white/10 text-center"
                                    >
                                        <p class="text-2xl font-bold">
                                            {{ statistik.total }}
                                        </p>
                                        <p
                                            class="text-[10px] uppercase font-bold text-green-100"
                                        >
                                            Total
                                        </p>
                                    </div>
                                </div>
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
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <Link
                                    :href="route('attendance.rekap')"
                                    class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-green-500 hover:shadow-md transition flex flex-col items-center gap-2 text-center group"
                                >
                                    <div
                                        class="w-12 h-12 bg-yellow-50 rounded-full flex items-center justify-center text-yellow-600 group-hover:bg-yellow-500 group-hover:text-white transition"
                                    >
                                        <CalendarDaysIcon class="w-6 h-6" />
                                    </div>
                                    <span
                                        class="text-xs font-bold text-gray-600"
                                        >Riwayat</span
                                    >
                                </Link>
                                <Link
                                    :href="route('attendance.izin')"
                                    class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-green-500 hover:shadow-md transition flex flex-col items-center gap-2 text-center group"
                                >
                                    <div
                                        class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition"
                                    >
                                        <ClipboardDocumentCheckIcon
                                            class="w-6 h-6"
                                        />
                                    </div>
                                    <span
                                        class="text-xs font-bold text-gray-600"
                                        >Izin</span
                                    >
                                </Link>
                                <div
                                    class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col items-center gap-2 text-center opacity-60 cursor-not-allowed"
                                >
                                    <div
                                        class="w-12 h-12 bg-purple-50 rounded-full flex items-center justify-center text-purple-600"
                                    >
                                        <DocumentTextIcon class="w-6 h-6" />
                                    </div>
                                    <span
                                        class="text-xs font-bold text-gray-600"
                                        >E-Raport</span
                                    >
                                </div>
                                <Link
                                    :href="route('profile.edit')"
                                    class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-green-500 hover:shadow-md transition flex flex-col items-center gap-2 text-center group"
                                >
                                    <div
                                        class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 group-hover:bg-gray-800 group-hover:text-white transition"
                                    >
                                        <UserCircleIcon class="w-6 h-6" />
                                    </div>
                                    <span
                                        class="text-xs font-bold text-gray-600"
                                        >Profil</span
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
                                        @click="prevMonth"
                                        class="p-1 hover:bg-green-200 rounded text-green-700"
                                    >
                                        <ChevronLeftIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="nextMonth"
                                        class="p-1 hover:bg-green-200 rounded text-green-700"
                                    >
                                        <ChevronRightIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-7 text-center mb-2">
                                    <span
                                        v-for="d in [
                                            'M',
                                            'S',
                                            'S',
                                            'R',
                                            'K',
                                            'J',
                                            'S',
                                        ]"
                                        :key="d"
                                        class="text-xs font-bold text-gray-400"
                                        >{{ d }}</span
                                    >
                                </div>
                                <div
                                    class="grid grid-cols-7 gap-1 text-center text-sm"
                                >
                                    <div
                                        v-for="(day, index) in calendarDays"
                                        :key="index"
                                        class="h-9 flex flex-col items-center justify-center rounded-lg relative"
                                        :class="
                                            day
                                                ? isToday(day.date)
                                                    ? 'bg-green-600 text-white font-bold shadow-md'
                                                    : 'text-gray-700'
                                                : ''
                                        "
                                    >
                                        <span v-if="day">{{ day.date }}</span>

                                        <span
                                            v-if="day && day.status"
                                            class="w-1.5 h-1.5 rounded-full absolute bottom-1"
                                            :class="{
                                                'bg-green-500':
                                                    day.status === 'Hadir',
                                                'bg-yellow-500':
                                                    day.status === 'Izin' ||
                                                    day.status === 'Sakit',
                                                'bg-red-500':
                                                    day.status === 'Alpha',
                                            }"
                                        >
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="mt-3 flex justify-center gap-3 text-[10px] text-gray-500"
                                >
                                    <div class="flex items-center gap-1">
                                        <span
                                            class="w-2 h-2 rounded-full bg-green-500"
                                        ></span>
                                        Hadir
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span
                                            class="w-2 h-2 rounded-full bg-yellow-500"
                                        ></span>
                                        Izin
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
                                >
                                    {{
                                        new Date().toLocaleDateString("id-ID", {
                                            weekday: "long",
                                        })
                                    }}
                                </span>
                            </div>

                            <div
                                class="divide-y divide-gray-50 max-h-[300px] overflow-y-auto"
                            >
                                <div
                                    v-if="jadwal.length === 0"
                                    class="p-6 text-center text-gray-400 text-xs italic"
                                >
                                    Tidak ada jadwal pelajaran.
                                </div>

                                <div
                                    v-for="item in jadwal"
                                    :key="item.id"
                                    class="p-4 hover:bg-green-50/30 transition flex items-start gap-4"
                                >
                                    <div class="flex-shrink-0 w-14 text-center">
                                        <span
                                            class="text-xs font-bold text-gray-500 block"
                                            >{{
                                                item.start_time.substring(0, 5)
                                            }}</span
                                        >
                                        <div
                                            class="w-0.5 h-3 bg-gray-300 mx-auto my-0.5"
                                        ></div>
                                        <span
                                            class="text-xs text-gray-400 block"
                                            >{{
                                                item.end_time.substring(0, 5)
                                            }}</span
                                        >
                                    </div>
                                    <div>
                                        <h4
                                            class="font-bold text-gray-800 text-sm"
                                        >
                                            {{ item.subject.name }}
                                        </h4>
                                        <p
                                            class="text-xs text-green-600 flex items-center gap-1 mt-1"
                                        >
                                            <UserIcon class="w-3 h-3" />
                                            {{ item.teacher.user.name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
cam