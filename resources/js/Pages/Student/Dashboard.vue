<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import {
    CalendarDaysIcon,
    ClipboardDocumentCheckIcon,
    ClipboardDocumentListIcon,
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
    jadwal: Array,
    riwayatAbsen: Object,
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

    for (let i = 0; i < firstDay; i++) days.push(null);

    for (let i = 1; i <= daysInMonth; i++) {
        // Format tanggal agar cocok dengan data dari database (YYYY-MM-DD)
        const dateStr = `${currentYear.value}-${String(
            currentMonth.value + 1
        ).padStart(2, "0")}-${String(i).padStart(2, "0")}`;
        const riwayat = props.riwayatAbsen ? props.riwayatAbsen[dateStr] : null;
        days.push({ date: i, status: riwayat ? riwayat.status : null });
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
                <div>
                    <h2
                        class="font-extrabold text-2xl text-gray-800 leading-tight"
                    >
                        Dashboard
                    </h2>
                    <p class="text-sm text-gray-500">
                        Selamat datang di Sistem Informasi Sekolah
                    </p>
                </div>
                <Link
                    :href="route('attendance.index')"
                    class="bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 flex items-center gap-2 text-sm font-bold shadow-lg shadow-green-600/30 transition transform hover:-translate-y-1"
                >
                    <QrCodeIcon class="w-5 h-5" /> SCAN ABSEN
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-8">
                        <div
                            class="bg-gradient-to-br from-green-600 to-emerald-800 rounded-3xl p-8 shadow-xl text-white relative overflow-hidden"
                        >
                            <div
                                class="absolute right-0 top-0 opacity-10 transform translate-x-10 -translate-y-10"
                            >
                                <ClockIcon class="w-48 h-48" />
                            </div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-4 mb-6">
                                    <div
                                        class="bg-white/20 p-3 rounded-full backdrop-blur-sm border border-white/20"
                                    >
                                        <UserIcon class="w-8 h-8 text-white" />
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold">
                                            Halo, {{ auth.user.name }}!
                                        </h3>
                                        <p class="text-green-100 text-sm">
                                            {{ auth.user.email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <div
                                        class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/10 text-center hover:bg-white/20 transition"
                                    >
                                        <p class="text-3xl font-extrabold">
                                            {{ statistik.hadir }}
                                        </p>
                                        <p
                                            class="text-xs uppercase font-bold text-green-200 tracking-wider"
                                        >
                                            Hadir
                                        </p>
                                    </div>
                                    <div
                                        class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/10 text-center hover:bg-white/20 transition"
                                    >
                                        <p class="text-3xl font-extrabold">
                                            {{ statistik.sakit }}
                                        </p>
                                        <p
                                            class="text-xs uppercase font-bold text-green-200 tracking-wider"
                                        >
                                            Izin/Sakit
                                        </p>
                                    </div>
                                    <div
                                        class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/10 text-center hover:bg-white/20 transition"
                                    >
                                        <p class="text-3xl font-extrabold">
                                            {{ statistik.total }}
                                        </p>
                                        <p
                                            class="text-xs uppercase font-bold text-green-200 tracking-wider"
                                        >
                                            Total Hari
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
                                    class="w-1.5 h-6 bg-green-500 rounded-full"
                                ></span>
                                Akses Cepat
                            </h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <Link
                                    :href="route('student.attendance.rekap')"
                                    class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:border-green-500 hover:shadow-lg hover:-translate-y-1 transition flex flex-col items-center gap-3 text-center group"
                                >
                                    <div
                                        class="w-14 h-14 bg-yellow-50 rounded-full flex items-center justify-center text-yellow-600 group-hover:bg-yellow-500 group-hover:text-white transition shadow-sm"
                                    >
                                        <CalendarDaysIcon class="w-7 h-7" />
                                    </div>
                                    <span
                                        class="text-sm font-bold text-gray-700"
                                        >Riwayat Absen</span
                                    >
                                </Link>
                                <Link
                                    :href="route('student.attendance.izin')"
                                    class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:border-green-500 hover:shadow-lg hover:-translate-y-1 transition flex flex-col items-center gap-3 text-center group"
                                >
                                    <div
                                        class="w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition shadow-sm"
                                    >
                                        <ClipboardDocumentCheckIcon
                                            class="w-7 h-7"
                                        />
                                    </div>
                                    <span
                                        class="text-sm font-bold text-gray-700"
                                        >Ajukan Izin</span
                                    >
                                </Link>
                                <Link
                                    :href="route('student.card')"
                                    class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:border-green-500 hover:shadow-lg hover:-translate-y-1 transition flex flex-col items-center gap-3 text-center group"
                                >
                                    <div
                                        class="w-14 h-14 bg-purple-50 rounded-full flex items-center justify-center text-purple-600 group-hover:bg-purple-500 group-hover:text-white transition shadow-sm"
                                    >
                                        <ClipboardDocumentListIcon
                                            class="w-7 h-7"
                                        />
                                    </div>
                                    <span
                                        class="text-sm font-bold text-gray-700"
                                        >Kartu Pelajar</span
                                    >
                                </Link>
                                <Link
                                    :href="route('profile.edit')"
                                    class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:border-green-500 hover:shadow-lg hover:-translate-y-1 transition flex flex-col items-center gap-3 text-center group"
                                >
                                    <div
                                        class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 group-hover:bg-gray-800 group-hover:text-white transition shadow-sm"
                                    >
                                        <UserCircleIcon class="w-7 h-7" />
                                    </div>
                                    <span
                                        class="text-sm font-bold text-gray-700"
                                        >Profil Saya</span
                                    >
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div
                            class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden"
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
                                    class="text-[10px] font-bold bg-green-50 text-green-700 px-2.5 py-1 rounded-full uppercase tracking-wide"
                                    >{{
                                        new Date().toLocaleDateString("id-ID", {
                                            weekday: "long",
                                        })
                                    }}</span
                                >
                            </div>
                            <div
                                class="p-2 max-h-[350px] overflow-y-auto custom-scrollbar"
                            >
                                <div
                                    v-if="jadwal.length === 0"
                                    class="p-8 text-center flex flex-col items-center justify-center text-gray-400"
                                >
                                    <BookOpenIcon
                                        class="w-12 h-12 mb-2 opacity-20"
                                    />
                                    <p class="text-sm">
                                        Tidak ada jadwal pelajaran.
                                    </p>
                                </div>
                                <div
                                    v-for="item in jadwal"
                                    :key="item.id"
                                    class="relative pl-6 py-3 hover:bg-gray-50 rounded-lg transition group"
                                >
                                    <div
                                        class="absolute left-3 top-0 bottom-0 w-0.5 bg-gray-100 group-hover:bg-green-200 transition"
                                    ></div>
                                    <div
                                        class="absolute left-[9px] top-1/2 -mt-1.5 w-3 h-3 rounded-full border-2 border-white bg-green-500 shadow-sm z-10"
                                    ></div>

                                    <Link
                                        :href="
                                            route(
                                                'student.classroom.show',
                                                item.id
                                            )
                                        "
                                        class="block"
                                    >
                                        <div
                                            class="flex justify-between items-center pr-4"
                                        >
                                            <div>
                                                <h4
                                                    class="font-bold text-gray-800 text-sm group-hover:text-green-700 transition"
                                                >
                                                    {{ item.subject.name }}
                                                </h4>
                                                <p
                                                    class="text-xs text-gray-500 flex items-center gap-1 mt-0.5"
                                                >
                                                    <UserIcon class="w-3 h-3" />
                                                    {{ item.teacher.user.name }}
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <span
                                                    class="text-xs font-mono font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded"
                                                    >{{
                                                        item.start_time.substring(
                                                            0,
                                                            5
                                                        )
                                                    }}</span
                                                >
                                                <p
                                                    class="text-[10px] text-gray-400 mt-1"
                                                >
                                                    s/d
                                                    {{
                                                        item.end_time.substring(
                                                            0,
                                                            5
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden"
                        >
                            <div
                                class="bg-gradient-to-r from-green-50 to-white px-6 py-4 border-b border-gray-100 flex justify-between items-center"
                            >
                                <span class="font-bold text-green-900 text-lg"
                                    >{{ monthNames[currentMonth] }}
                                    {{ currentYear }}</span
                                >
                                <div class="flex gap-1">
                                    <button
                                        @click="prevMonth"
                                        class="p-1.5 hover:bg-white rounded-full text-green-700 shadow-sm"
                                    >
                                        <ChevronLeftIcon class="w-5 h-5" />
                                    </button>
                                    <button
                                        @click="nextMonth"
                                        class="p-1.5 hover:bg-white rounded-full text-green-700 shadow-sm"
                                    >
                                        <ChevronRightIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                            <div class="p-5">
                                <div class="grid grid-cols-7 text-center mb-4">
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
                                    class="grid grid-cols-7 gap-2 text-center text-sm"
                                >
                                    <div
                                        v-for="(day, index) in calendarDays"
                                        :key="index"
                                        class="h-10 flex flex-col items-center justify-center rounded-lg relative transition hover:bg-gray-50"
                                        :class="
                                            day && isToday(day.date)
                                                ? 'bg-green-600 text-white font-bold shadow-md hover:bg-green-700'
                                                : 'text-gray-700'
                                        "
                                    >
                                        <span v-if="day">{{ day.date }}</span>
                                        <span
                                            v-if="day && day.status"
                                            class="w-2 h-2 rounded-full absolute -bottom-1 border-2 border-white"
                                            :class="{
                                                'bg-green-500':
                                                    day.status === 'Hadir',
                                                'bg-yellow-500':
                                                    day.status === 'Izin' ||
                                                    day.status === 'Sakit',
                                                'bg-red-500':
                                                    day.status === 'Alpha',
                                            }"
                                        ></span>
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

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
}
</style>
