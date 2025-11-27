<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue"; // Import computed
import {
    UserGroupIcon,
    AcademicCapIcon,
    BookOpenIcon,
    CalendarDaysIcon,
    QrCodeIcon,
    Cog6ToothIcon,
    UsersIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    stats: Object,
});

// Gunakan 'computed' agar nilai statValue selalu update jika props berubah
const adminMenu = computed(() => [
    {
        name: "Manajemen Pengguna",
        description: "Guru, Siswa, dan Pengaturan Peran (Role)",
        icon: UserGroupIcon,
        route: route("admin.users.index"),
        statValue: (props.stats?.students || 0) + (props.stats?.teachers || 0), // Pakai Optional Chaining (?.) biar gak error kalau null
        statLabel: "Total User",
    },
    {
        name: "Data Mata Pelajaran",
        description: "Tambah, Edit, Hapus Mata Pelajaran",
        icon: BookOpenIcon,
        route: route("admin.subjects.index"),
        statValue: props.stats?.subjects || 0,
        statLabel: "Total Mapel",
    },
    {
        name: "Manajemen Kelas",
        description: "Pengaturan Kelas dan Jurusan",
        icon: AcademicCapIcon,
        route: route("admin.classes.index"),
        statValue: props.stats?.classes || 0,
        statLabel: "Total Kelas",
    },
    {
        name: "Rekap Absensi",
        description: "Laporan Detail Absensi Siswa dan Guru",
        icon: CalendarDaysIcon,
        route: route("admin.attendance.report"), // Placeholder (Belum ada rute rekap admin)
        statValue: "Lihat",
        statLabel: "Laporan",
    },
    {
        name: "Generator QR Code",
        description: "Buat & Reset Token Absensi Harian",
        icon: QrCodeIcon,
        route: route("admin.settings.qr"), // Pastikan rute ini ada atau ganti sementara
        statValue: "Buat",
        statLabel: "Token",
    },
    {
        name: "Pengaturan Sistem",
        description: "Waktu Absen, Lokasi, dan Lainnya",
        icon: Cog6ToothIcon,
        route: route("admin.settings.attendance"), // Pastikan rute ini ada atau ganti sementara
        statValue: "Atur",
        statLabel: "Sistem",
    },
    {
        name: "Data Siswa",
        description: "Input Siswa, Buat Akun Otomatis",
        icon: UserGroupIcon, // Pastikan icon diimport
        route: route("admin.students.index"), // Link ke halaman yang baru kita buat
        statValue: "Kelola",
        statLabel: "Action",
    },
    // Tambahkan item ini ke dalam array adminMenu:
    {
        name: "Jadwal Pelajaran",
        description: "Plotting Guru, Mapel, dan Kelas",
        icon: CalendarDaysIcon,
        route: route("admin.schedules.index"),
        statValue: "Atur",
        statLabel: "Jadwal",
    },
    {
        name: "Manajemen Guru", // Ganti nama menu
        description: "Data Pengajar & Akun",
        icon: UserGroupIcon,
        route: route("admin.teachers.index"), // <--- Link ke index guru
        statValue: props.stats.teachers,
        statLabel: "Total Guru",
    },
]);
</script>

<template>
    <Head title="Dashboard Admin" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-bold text-2xl text-red-800 leading-tight flex items-center gap-2"
            >
                <UsersIcon class="w-8 h-8 text-red-600" /> Dashboard
                Administrator
            </h2>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <Link
                        v-for="menu in adminMenu"
                        :key="menu.name"
                        :href="menu.route"
                        class="bg-white p-6 rounded-xl shadow-lg border border-red-200 transition duration-300 hover:shadow-xl hover:border-red-400 flex flex-col justify-between h-full group"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex flex-col">
                                <span
                                    class="text-3xl font-extrabold text-gray-900 group-hover:text-red-700 transition"
                                >
                                    {{ menu.statValue }}
                                </span>
                                <span
                                    class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-1"
                                >
                                    {{ menu.statLabel }}
                                </span>
                            </div>
                            <component
                                :is="menu.icon"
                                class="w-12 h-12 text-red-500/80 p-2.5 bg-red-50 rounded-lg group-hover:bg-red-100 transition"
                            />
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-100">
                            <h3
                                class="text-lg font-bold text-red-800 group-hover:underline decoration-2 underline-offset-4"
                            >
                                {{ menu.name }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 leading-snug">
                                {{ menu.description }}
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
