<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import {
    UserGroupIcon,
    AcademicCapIcon,
    BookOpenIcon, // Pastikan ini ada
    CalendarDaysIcon,
    QrCodeIcon,
    Cog6ToothIcon,
    ChartBarIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    UserIcon,
    ChartPieIcon,
    ClockIcon,
    PresentationChartLineIcon,
    IdentificationIcon,
    ChevronRightIcon as ArrowRightIcon,
} from "@heroicons/vue/24/solid";
import { Bar, Doughnut, Line } from "vue-chartjs";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    PointElement,
    LineElement,
    Filler,
} from "chart.js";

// Regiter komponen Chart.js
ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    PointElement,
    LineElement,
    Filler
);

const props = defineProps({
    stats: Object,
    chart: Object,
    genderChart: Object,
    growthChart: Object,
});

const user = usePage().props.auth.user;

// --- LOGIC PERSENTASE GENDER ---
const genderPercentages = computed(() => {
    const data = props.genderChart?.data || [0, 0];
    const valL = parseInt(data[0]) || 0;
    const valP = parseInt(data[1]) || 0;
    const total = valL + valP;
    if (total === 0) return [0, 0];
    return [Math.round((valL / total) * 100), Math.round((valP / total) * 100)];
});

// --- MENU ADMIN ---
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
        color: "text-amber-600",
        bg: "bg-amber-50",
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
        color: "text-emerald-600",
        bg: "bg-emerald-50",
    },
    // --- FITUR BARU: PERPUSTAKAAN ---
    {
        name: "Perpustakaan",
        desc: "Kelola E-Book",
        icon: BookOpenIcon, // Icon Buku
        route: route("ebooks.index"), // Route ke halaman Library
        color: "text-cyan-600",
        bg: "bg-cyan-50",
    },
    // --------------------------------
    {
        name: "QR Generator",
        desc: "Buat Token",
        icon: QrCodeIcon,
        route: route("admin.settings.qr"),
        color: "text-teal-600", // Ubah warna agar beda dengan Perpustakaan
        bg: "bg-teal-50",
    },
    {
        name: "Kartu Pelajar",
        desc: "Desain Kartu Pelajar",
        icon: IdentificationIcon,
        route: route("admin.card.designer"),
        color: "text-blue-600", // Ubah warna agar beda dengan Perpustakaan
        bg: "bg-teal-50",
    },
    {
        name: "Pengaturan",
        desc: "Sistem Sekolah",
        icon: Cog6ToothIcon,
        route: route("admin.settings.attendance"),
        color: "text-slate-600",
        bg: "bg-slate-50",
    },
]);

// --- CONFIG BAR CHART (KEHADIRAN) ---
const barData = {
    labels: props.chart?.labels || [],
    datasets: [
        {
            label: "Hadir",
            backgroundColor: "#3b82f6",
            borderRadius: 6,
            barThickness: 25,
            data: props.chart?.data || [],
        },
    ],
};
const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: {
            beginAtZero: true,
            grid: { borderDash: [4, 4], color: "#f3f4f6" },
        },
        x: { grid: { display: false } },
    },
};

// --- CONFIG LINE CHART (PERTUMBUHAN SISWA) ---
const lineData = {
    labels: props.growthChart?.labels || [],
    datasets: [
        {
            label: "Siswa Baru",
            borderColor: "#10b981", // Hijau Emerald
            backgroundColor: "rgba(16, 185, 129, 0.1)",
            data: props.growthChart?.data || [],
            tension: 0.4,
            fill: true,
            pointBackgroundColor: "#fff",
            pointBorderColor: "#10b981",
            pointBorderWidth: 2,
            pointRadius: 4,
        },
    ],
};
const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: {
            beginAtZero: true,
            grid: { borderDash: [4, 4], color: "#f3f4f6" },
            ticks: { stepSize: 1 },
        },
        x: { grid: { display: false } },
    },
};

// --- CONFIG DONUT CHART (GENDER) ---
const doughnutData = {
    labels: ["Laki-laki", "Perempuan"],
    datasets: [
        {
            backgroundColor: ["#3b82f6", "#ec4899"],
            data: props.genderChart?.data || [0, 0],
            borderWidth: 0,
            hoverOffset: 4,
        },
    ],
};
const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    cutout: "75%",
};

// --- KALENDER ---
const currentDate = new Date();
const currentMonth = currentDate.getMonth();
const currentYear = currentDate.getFullYear();
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
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
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
            <div
                class="flex flex-col md:flex-row md:items-center justify-between gap-4"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="p-3 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl text-white shadow-xl shadow-blue-500/20"
                    >
                        <UserGroupIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h2
                            class="font-black text-3xl text-gray-800 leading-tight tracking-tight"
                        >
                            Dashboard
                        </h2>
                        <p class="text-sm text-gray-500 font-medium mt-1">
                            Selamat datang,
                            <span class="text-blue-600">{{ user.name }}</span
                            >!
                        </p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-3 bg-white px-5 py-2.5 rounded-2xl shadow-sm border border-gray-100"
                >
                    <ClockIcon class="w-5 h-5 text-blue-500" />
                    <span class="text-sm font-bold text-gray-700 capitalize">
                        {{
                            new Date().toLocaleDateString("id-ID", {
                                weekday: "long",
                                day: "numeric",
                                month: "long",
                                year: "numeric",
                            })
                        }}
                    </span>
                </div>
            </div>
        </template>

        <div
            class="py-10 px-4 sm:px-6 lg:px-8 max-w-[100rem] mx-auto space-y-10"
        >
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <div class="lg:col-span-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            class="bg-white rounded-[2rem] p-8 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.05)] border border-gray-100 relative overflow-hidden group hover:translate-y-[-5px] transition-all duration-300"
                        >
                            <div
                                class="absolute top-0 right-0 w-32 h-32 bg-blue-50/50 rounded-bl-[100%] -mr-10 -mt-10 transition-transform group-hover:scale-110"
                            ></div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-5 mb-6">
                                    <div
                                        class="p-4 bg-blue-100 text-blue-600 rounded-2xl shadow-inner"
                                    >
                                        <UserGroupIcon class="w-8 h-8" />
                                    </div>
                                    <div>
                                        <p
                                            class="text-gray-400 text-xs font-bold uppercase tracking-wider"
                                        >
                                            Total Siswa
                                        </p>
                                        <h3
                                            class="text-4xl font-black text-gray-800 mt-1"
                                        >
                                            {{ props.stats.students }}
                                        </h3>
                                    </div>
                                </div>
                                <Link
                                    :href="route('admin.students.index')"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200"
                                >
                                    Kelola Data
                                    <ArrowRightIcon class="w-4 h-4" />
                                </Link>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-[2rem] p-8 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.05)] border border-gray-100 relative overflow-hidden group hover:translate-y-[-5px] transition-all duration-300"
                        >
                            <div
                                class="absolute top-0 right-0 w-32 h-32 bg-purple-50/50 rounded-bl-[100%] -mr-10 -mt-10 transition-transform group-hover:scale-110"
                            ></div>
                            <div class="relative z-10">
                                <div class="flex items-center gap-5 mb-6">
                                    <div
                                        class="p-4 bg-purple-100 text-purple-600 rounded-2xl shadow-inner"
                                    >
                                        <AcademicCapIcon class="w-8 h-8" />
                                    </div>
                                    <div>
                                        <p
                                            class="text-gray-400 text-xs font-bold uppercase tracking-wider"
                                        >
                                            Total Guru
                                        </p>
                                        <h3
                                            class="text-4xl font-black text-gray-800 mt-1"
                                        >
                                            {{ props.stats.teachers }}
                                        </h3>
                                    </div>
                                </div>
                                <Link
                                    :href="route('admin.teachers.index')"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 text-white text-sm font-bold rounded-xl hover:bg-purple-700 transition shadow-lg shadow-purple-200"
                                >
                                    Kelola Data
                                    <ArrowRightIcon class="w-4 h-4" />
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white rounded-[2.5rem] p-8 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.05)] border border-gray-100 h-[380px] flex flex-col"
                    >
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h4
                                    class="font-bold text-xl text-gray-800 flex items-center gap-2"
                                >
                                    <ChartBarIcon
                                        class="w-6 h-6 text-blue-500"
                                    />
                                    Tren Kehadiran
                                </h4>
                                <p
                                    class="text-sm text-gray-400 mt-1 font-medium"
                                >
                                    Statistik siswa hadir 7 hari terakhir
                                </p>
                            </div>
                            <div
                                class="bg-gray-50 px-4 py-2 rounded-xl text-xs font-bold text-gray-500 border border-gray-100"
                            >
                                Minggu Ini
                            </div>
                        </div>
                        <div class="flex-grow relative w-full">
                            <Bar :data="barData" :options="barOptions" />
                        </div>
                    </div>

                    <div
                        class="bg-white rounded-[2.5rem] p-8 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.05)] border border-gray-100 h-[380px] flex flex-col"
                    >
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h4
                                    class="font-bold text-xl text-gray-800 flex items-center gap-2"
                                >
                                    <PresentationChartLineIcon
                                        class="w-6 h-6 text-emerald-500"
                                    />
                                    Pertumbuhan Siswa
                                </h4>
                                <p
                                    class="text-sm text-gray-400 mt-1 font-medium"
                                >
                                    Tren pendaftaran siswa baru per tahun
                                </p>
                            </div>
                            <div
                                class="bg-emerald-50 px-4 py-2 rounded-xl text-xs font-bold text-emerald-600 border border-emerald-100"
                            >
                                Tahunan
                            </div>
                        </div>
                        <div class="flex-grow relative w-full">
                            <Line :data="lineData" :options="lineOptions" />
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-8">
                    <div
                        class="bg-white rounded-[2.5rem] p-8 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.05)] border border-gray-100"
                    >
                        <div class="flex justify-between items-center mb-6">
                            <span class="font-bold text-xl text-gray-800"
                                >{{ monthNames[currentMonth] }}
                                {{ currentYear }}</span
                            >
                            <div class="flex gap-1">
                                <button
                                    class="p-2 hover:bg-gray-100 rounded-xl text-gray-400 hover:text-gray-800 transition"
                                >
                                    <ChevronLeftIcon class="w-5 h-5" />
                                </button>
                                <button
                                    class="p-2 hover:bg-gray-100 rounded-xl text-gray-400 hover:text-gray-800 transition"
                                >
                                    <ChevronRightIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                        <div
                            class="grid grid-cols-7 text-center text-xs text-gray-400 font-bold mb-4 uppercase tracking-wide"
                        >
                            <span
                                v-for="d in [
                                    'Min',
                                    'Sen',
                                    'Sel',
                                    'Rab',
                                    'Kam',
                                    'Jum',
                                    'Sab',
                                ]"
                                :key="d"
                                >{{ d }}</span
                            >
                        </div>
                        <div
                            class="grid grid-cols-7 gap-3 text-center text-sm font-bold text-gray-700"
                        >
                            <div
                                v-for="(day, i) in calendarDays"
                                :key="i"
                                class="aspect-square flex items-center justify-center rounded-2xl transition cursor-default"
                                :class="
                                    day
                                        ? day.isToday
                                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-300'
                                            : 'hover:bg-gray-50'
                                        : ''
                                "
                            >
                                {{ day ? day.date : "" }}
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white rounded-[2.5rem] p-8 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.05)] border border-gray-100 flex flex-col justify-between"
                    >
                        <div class="mb-4">
                            <h4
                                class="font-bold text-xl text-gray-800 flex items-center gap-2"
                            >
                                <ChartPieIcon class="w-6 h-6 text-pink-500" />
                                Komposisi
                            </h4>
                            <p class="text-sm text-gray-400 font-medium">
                                Rasio Siswa L/P
                            </p>
                        </div>

                        <div
                            class="relative h-64 w-full flex justify-center items-center my-4"
                        >
                            <Doughnut
                                :data="doughnutData"
                                :options="doughnutOptions"
                            />
                            <div
                                class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none pb-2"
                            >
                                <span
                                    class="text-4xl font-black text-gray-800 leading-none"
                                    >{{ props.stats.students }}</span
                                >
                                <span
                                    class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mt-1"
                                    >Total Siswa</span
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div
                                class="bg-blue-50 rounded-2xl p-4 border border-blue-100 flex flex-col items-center text-center"
                            >
                                <span
                                    class="text-xs font-bold text-gray-400 uppercase mb-1"
                                    >Laki-laki</span
                                >
                                <span
                                    class="text-2xl font-black text-blue-600"
                                    >{{ props.genderChart?.data[0] }}</span
                                >
                                <span
                                    class="text-[10px] font-bold text-white bg-blue-400 px-2 py-0.5 rounded-full mt-1"
                                    >{{ genderPercentages[0] }}%</span
                                >
                            </div>
                            <div
                                class="bg-pink-50 rounded-2xl p-4 border border-pink-100 flex flex-col items-center text-center"
                            >
                                <span
                                    class="text-xs font-bold text-gray-400 uppercase mb-1"
                                    >Perempuan</span
                                >
                                <span
                                    class="text-2xl font-black text-pink-500"
                                    >{{ props.genderChart?.data[1] }}</span
                                >
                                <span
                                    class="text-[10px] font-bold text-white bg-pink-400 px-2 py-0.5 rounded-full mt-1"
                                    >{{ genderPercentages[1] }}%</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h3
                    class="font-bold text-2xl text-gray-800 mb-8 flex items-center gap-4"
                >
                    <span
                        class="w-2 h-8 bg-blue-600 rounded-full shadow-lg shadow-blue-400/50"
                    ></span>
                    Akses Cepat
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <Link
                        v-for="menu in adminMenu"
                        :key="menu.name"
                        :href="menu.route"
                        class="group bg-white p-6 rounded-[2rem] shadow-[0_4px_20px_-5px_rgba(0,0,0,0.03)] border border-gray-100 hover:border-blue-200 hover:shadow-[0_10px_30px_-5px_rgba(37,99,235,0.15)] transition-all duration-300 flex flex-col gap-5 items-start relative overflow-hidden"
                    >
                        <div
                            class="absolute top-0 right-0 w-24 h-24 bg-gray-50 rounded-bl-[100%] -mr-8 -mt-8 transition-transform group-hover:scale-150 group-hover:bg-blue-50/50"
                        ></div>
                        <div
                            :class="`w-14 h-14 rounded-2xl flex items-center justify-center transition-transform duration-300 group-hover:rotate-6 group-hover:scale-110 shadow-sm ${menu.bg} ${menu.color} relative z-10`"
                        >
                            <component :is="menu.icon" class="w-7 h-7" />
                        </div>
                        <div class="relative z-10">
                            <h5
                                class="font-bold text-gray-800 text-lg group-hover:text-blue-600 transition-colors"
                            >
                                {{ menu.name }}
                            </h5>
                            <p class="text-sm text-gray-400 mt-1 font-medium">
                                {{ menu.desc }}
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
