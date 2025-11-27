<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import {
    UserGroupIcon,
    AcademicCapIcon,
    BookOpenIcon,
    CalendarDaysIcon,
    QrCodeIcon,
    Cog6ToothIcon,
    UsersIcon,
    ChartBarIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    CheckCircleIcon,
    UserIcon,
} from "@heroicons/vue/24/solid";
import { Bar } from "vue-chartjs";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
} from "chart.js";

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale
);

const props = defineProps({
    stats: Object,
    chart: Object,
});

const user = usePage().props.auth.user;

// 1. MENU ADMIN (GRID BAWAH)
const adminMenu = computed(() => [
    {
        name: "Data Siswa",
        desc: "Kelola Siswa",
        icon: UserGroupIcon,
        route: route("admin.students.index"),
        color: "text-blue-600",
        bg: "bg-blue-50",
    },
    {
        name: "Data Guru",
        desc: "Kelola Guru",
        icon: UserIcon,
        route: route("admin.teachers.index"),
        color: "text-purple-600",
        bg: "bg-purple-50",
    },
    {
        name: "Manajemen Kelas",
        desc: "Atur Kelas",
        icon: AcademicCapIcon,
        route: route("admin.classes.index"),
        color: "text-yellow-600",
        bg: "bg-yellow-50",
    },
    {
        name: "Mata Pelajaran",
        desc: "Atur Mapel",
        icon: BookOpenIcon,
        route: route("admin.subjects.index"),
        color: "text-pink-600",
        bg: "bg-pink-50",
    },
    {
        name: "Jadwal Pelajaran",
        desc: "Plotting KBM",
        icon: CalendarDaysIcon,
        route: route("admin.schedules.index"),
        color: "text-indigo-600",
        bg: "bg-indigo-50",
    },
    {
        name: "Laporan Absen",
        desc: "Rekap Data",
        icon: ChartBarIcon,
        route: route("admin.attendance.report", { role: "student" }),
        color: "text-green-600",
        bg: "bg-green-50",
    },
    {
        name: "QR Generator",
        desc: "Buat Token",
        icon: QrCodeIcon,
        route: route("admin.settings.qr"),
        color: "text-gray-600",
        bg: "bg-gray-100",
    },
    {
        name: "Pengaturan",
        desc: "Sistem Sekolah",
        icon: Cog6ToothIcon,
        route: route("admin.settings.attendance"),
        color: "text-red-600",
        bg: "bg-red-50",
    },
]);

// 2. KONFIGURASI CHART
const chartData = {
    labels: props.chart?.labels || [],
    datasets: [
        {
            label: "Total Kehadiran Siswa",
            backgroundColor: "#dc2626", // Merah Admin
            borderRadius: 4,
            data: props.chart?.data || [],
        },
    ],
};
const chartOptions = { responsive: true, maintainAspectRatio: false };

// 3. KALENDER
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
        days.push({ date: i, isToday: i === new Date().getDate() });
    return days;
});
</script>

<template>
    <Head title="Dashboard Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-red-100 rounded-xl text-red-600 shadow-sm">
                    <UsersIcon class="w-8 h-8" />
                </div>
                <div>
                    <h2
                        class="font-extrabold text-2xl text-red-900 leading-none tracking-tight"
                    >
                        Administrator Panel
                    </h2>
                    <p
                        class="text-xs text-red-400 font-medium uppercase tracking-widest mt-1"
                    >
                        Pusat Kontrol Sistem Sekolah
                    </p>
                </div>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
                <div
                    class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 p-5 h-full flex flex-col"
                >
                    <div class="flex justify-between items-center mb-4">
                        <span class="font-bold text-gray-700"
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
                                        ? 'bg-red-600 text-white font-bold shadow'
                                        : 'hover:bg-gray-50'
                                    : ''
                            "
                        >
                            {{ day ? day.date : "" }}
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 grid grid-cols-2 gap-4">
                    <div
                        class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-5 text-white shadow-lg flex flex-col justify-between relative overflow-hidden group"
                    >
                        <UserGroupIcon
                            class="w-24 h-24 absolute -right-4 -bottom-4 opacity-20 group-hover:scale-110 transition"
                        />
                        <div>
                            <p class="text-blue-100 text-sm font-medium">
                                Total Siswa
                            </p>
                            <p class="text-4xl font-bold mt-1">
                                {{ props.stats.students }}
                            </p>
                        </div>
                        <Link
                            :href="route('admin.students.index')"
                            class="mt-4 text-xs bg-white/20 w-fit px-3 py-1 rounded-lg hover:bg-white/30 transition"
                            >Kelola Data &rarr;</Link
                        >
                    </div>

                    <div
                        class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg flex flex-col justify-between relative overflow-hidden group"
                    >
                        <AcademicCapIcon
                            class="w-24 h-24 absolute -right-4 -bottom-4 opacity-20 group-hover:scale-110 transition"
                        />
                        <div>
                            <p class="text-purple-100 text-sm font-medium">
                                Total Guru
                            </p>
                            <p class="text-4xl font-bold mt-1">
                                {{ props.stats.teachers }}
                            </p>
                        </div>
                        <Link
                            :href="route('admin.teachers.index')"
                            class="mt-4 text-xs bg-white/20 w-fit px-3 py-1 rounded-lg hover:bg-white/30 transition"
                            >Kelola Data &rarr;</Link
                        >
                    </div>

                    <div
                        class="col-span-2 bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="p-3 bg-yellow-50 rounded-full text-yellow-600"
                            >
                                <BookOpenIcon class="w-8 h-8" />
                            </div>
                            <div>
                                <p
                                    class="text-gray-500 text-xs font-bold uppercase"
                                >
                                    Akademik
                                </p>
                                <div class="flex gap-4 mt-1">
                                    <span
                                        class="text-sm font-bold text-gray-800"
                                        >{{ props.stats.classes }} Kelas</span
                                    >
                                    <span class="text-gray-300">|</span>
                                    <span
                                        class="text-sm font-bold text-gray-800"
                                        >{{ props.stats.subjects }} Mapel</span
                                    >
                                </div>
                            </div>
                        </div>
                        <Link
                            :href="route('admin.classes.index')"
                            class="text-gray-400 hover:text-red-600 transition"
                            ><ChevronRightIcon class="w-6 h-6"
                        /></Link>
                    </div>
                </div>

                <div
                    class="lg:col-span-4 bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col"
                >
                    <h4
                        class="font-bold text-gray-700 text-sm mb-4 flex items-center gap-2"
                    >
                        <ChartBarIcon class="w-5 h-5 text-red-600" /> Tren
                        Kehadiran
                    </h4>
                    <div class="flex-grow relative min-h-[200px]">
                        <Bar :data="chartData" :options="chartOptions" />
                    </div>
                </div>
            </div>

            <div>
                <h3
                    class="font-bold text-gray-800 text-lg mb-4 flex items-center gap-2"
                >
                    <span class="w-1 h-6 bg-red-600 rounded-full"></span> Akses
                    Cepat
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <Link
                        v-for="menu in adminMenu"
                        :key="menu.name"
                        :href="menu.route"
                        class="group bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-red-300 hover:shadow-md transition flex items-center gap-4"
                    >
                        <div
                            :class="`w-12 h-12 rounded-xl flex items-center justify-center transition ${menu.bg} ${menu.color} group-hover:scale-110`"
                        >
                            <component :is="menu.icon" class="w-6 h-6" />
                        </div>
                        <div>
                            <h5
                                class="font-bold text-gray-800 text-sm group-hover:text-red-700 transition"
                            >
                                {{ menu.name }}
                            </h5>
                            <p class="text-xs text-gray-400">{{ menu.desc }}</p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
