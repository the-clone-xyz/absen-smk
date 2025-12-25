<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import {
    UserGroupIcon,
    CheckBadgeIcon,
    ChartBarIcon,
    XCircleIcon,
    BellAlertIcon,
    XMarkIcon,
    QrCodeIcon,
    CameraIcon,
    BookOpenIcon,
    CalendarDaysIcon,
    ClipboardDocumentListIcon,
    AcademicCapIcon,
    ArrowRightIcon,
    ClockIcon, // Tambahan icon
} from "@heroicons/vue/24/solid";

import TeacherSchedule from "./Partials/TeacherSchedule.vue";
import ApprovalList from "./Partials/ApprovalList.vue";
import TeacherCalendar from "./Partials/TeacherCalendar.vue";

const props = defineProps({
    auth: Object,
    absensiGrouped: Object,
    statistik: Object,
    jadwal: Array,
    jadwalKalender: Object,
    kelas: {
        type: Array,
        default: () => [
            {
                id: 1,
                name: "X TKJ 1",
                subject: "Dasar Kejuruan",
                student_count: 32,
            },
            {
                id: 2,
                name: "XI RPL 2",
                subject: "Pemrograman Web",
                student_count: 30,
            },
            {
                id: 3,
                name: "XII MM 1",
                subject: "Desain Grafis",
                student_count: 28,
            },
        ],
    },
});

const showApprovalModal = ref(false);
const pendingCount = computed(() => {
    if (!props.absensiGrouped) return 0;
    return Object.values(props.absensiGrouped).flat().length;
});
</script>

<template>
    <Head title="Panel Guru" />

    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col md:flex-row justify-between items-center gap-4"
            >
                <div>
                    <h2
                        class="font-black text-2xl md:text-3xl text-slate-800 tracking-tight"
                    >
                        Panel Guru
                    </h2>
                    <p class="text-sm text-slate-500 mt-1 font-medium">
                        Selamat mengajar, {{ auth.user.name }}! 🚀
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        @click="showApprovalModal = true"
                        class="relative p-2.5 rounded-xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all group"
                    >
                        <BellAlertIcon
                            class="w-6 h-6 text-slate-400 group-hover:text-indigo-600 transition-colors"
                        />
                        <span
                            v-if="pendingCount > 0"
                            class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 ring-2 ring-white text-[10px] font-bold text-white animate-bounce shadow-sm"
                        >
                            {{ pendingCount }}
                        </span>
                    </button>
                    <div class="hidden md:block text-right px-4">
                        <p
                            class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"
                        >
                            Tahun Ajaran
                        </p>
                        <span
                            class="inline-block mt-1 px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-full"
                        >
                            2025/2026 Ganjil
                        </span>
                    </div>
                </div>
            </div>
        </template>

        <div
            class="min-h-screen bg-[#F8FAFC] py-8 px-4 sm:px-6 lg:px-8 space-y-8"
        >
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-4 hover:-translate-y-1 transition-all duration-300"
                >
                    <div
                        class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center shrink-0"
                    >
                        <UserGroupIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <p
                            class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5"
                        >
                            Total Siswa
                        </p>
                        <p class="text-2xl font-black text-slate-800">
                            {{ statistik?.total_siswa || "-" }}
                        </p>
                    </div>
                </div>

                <div
                    class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-4 hover:-translate-y-1 transition-all duration-300"
                >
                    <div
                        class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0"
                    >
                        <CheckBadgeIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <p
                            class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5"
                        >
                            Hadir
                        </p>
                        <p class="text-2xl font-black text-slate-800">
                            {{ statistik?.hadir || "0" }}
                        </p>
                    </div>
                </div>

                <div
                    class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-4 hover:-translate-y-1 transition-all duration-300"
                >
                    <div
                        class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0"
                    >
                        <ChartBarIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <p
                            class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5"
                        >
                            Izin / Sakit
                        </p>
                        <p class="text-2xl font-black text-slate-800">
                            {{
                                (statistik?.izin || 0) + (statistik?.sakit || 0)
                            }}
                        </p>
                    </div>
                </div>

                <div
                    class="bg-white p-5 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-4 hover:-translate-y-1 transition-all duration-300"
                >
                    <div
                        class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center shrink-0"
                    >
                        <XCircleIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <p
                            class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5"
                        >
                            Alpha
                        </p>
                        <p class="text-2xl font-black text-slate-800">
                            {{ statistik?.alpha || "0" }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 items-start">
                <div class="xl:col-span-2 space-y-8">
                    <div>
                        <h3
                            class="font-bold text-xl text-slate-800 mb-4 flex items-center gap-2"
                        >
                            <span
                                class="w-1.5 h-6 bg-indigo-500 rounded-full"
                            ></span>
                            Kelas Saya
                        </h3>

                        <div
                            v-if="kelas.length > 0"
                            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4"
                        >
                            <div
                                v-for="item in kelas"
                                :key="item.id"
                                class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all group cursor-pointer relative overflow-hidden"
                            >
                                <div
                                    class="absolute top-0 right-0 w-16 h-16 bg-indigo-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-125"
                                ></div>
                                <div class="relative z-10">
                                    <div
                                        class="flex justify-between items-start mb-3"
                                    >
                                        <div
                                            class="w-10 h-10 rounded-xl bg-white border border-indigo-50 text-indigo-600 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform"
                                        >
                                            <AcademicCapIcon class="w-5 h-5" />
                                        </div>
                                        <span
                                            class="text-[10px] font-bold bg-slate-50 text-slate-500 px-2 py-1 rounded-full border border-slate-100"
                                        >
                                            {{ item.student_count || 0 }} Siswa
                                        </span>
                                    </div>
                                    <h4
                                        class="font-bold text-lg text-slate-800 mb-0.5 group-hover:text-indigo-600 transition-colors"
                                    >
                                        {{ item.name }}
                                    </h4>
                                    <p
                                        class="text-xs text-slate-500 mb-3 line-clamp-1"
                                    >
                                        {{ item.subject || "Wali Kelas" }}
                                    </p>

                                    <Link
                                        :href="route('admin.classes.index')"
                                        class="text-xs font-bold text-indigo-600 flex items-center gap-1 hover:gap-2 transition-all"
                                    >
                                        Lihat Detail
                                        <ArrowRightIcon class="w-3 h-3" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="bg-white p-8 rounded-3xl border border-dashed border-slate-200 text-center"
                        >
                            <p class="text-slate-400 text-sm">
                                Belum ada kelas yang ditugaskan.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col"
                        >
                            <div
                                class="px-5 py-4 border-b border-slate-50 flex items-center gap-2 bg-white sticky top-0 z-10"
                            >
                                <ClockIcon class="w-5 h-5 text-indigo-500" />
                                <h3 class="font-bold text-slate-800">
                                    Jadwal Hari Ini
                                </h3>
                            </div>
                            <div class="p-4 flex-grow">
                                <TeacherSchedule :jadwal="jadwal" />
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col"
                        >
                            <div
                                class="px-5 py-4 border-b border-slate-50 flex items-center gap-2 bg-white"
                            >
                                <CalendarDaysIcon
                                    class="w-5 h-5 text-indigo-500"
                                />
                                <h3 class="font-bold text-slate-800">
                                    Kalender Akademik
                                </h3>
                            </div>
                            <div
                                class="p-2 flex-grow flex items-center justify-center"
                            >
                                <TeacherCalendar
                                    :jadwalKalender="jadwalKalender"
                                    class="w-full"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div
                        class="relative overflow-hidden bg-white rounded-3xl p-6 shadow-xl shadow-indigo-100 border border-white group hover:-translate-y-1 transition-all"
                    >
                        <div
                            class="absolute top-0 right-0 -mr-8 -mt-8 w-40 h-40 bg-indigo-50 rounded-full blur-3xl opacity-60"
                        ></div>
                        <div
                            class="relative z-10 flex flex-col items-center text-center"
                        >
                            <div
                                class="mb-3 inline-flex p-3 rounded-2xl bg-gradient-to-br from-indigo-50 to-white shadow-sm border border-indigo-50"
                            >
                                <QrCodeIcon class="w-8 h-8 text-indigo-600" />
                            </div>
                            <h3
                                class="text-lg font-black text-slate-800 tracking-tight mb-1"
                            >
                                Presensi Guru
                            </h3>
                            <p
                                class="text-xs text-slate-500 mb-4 max-w-[200px]"
                            >
                                Scan kode QR untuk mencatat kehadiran.
                            </p>
                            <Link
                                :href="route('attendance.index')"
                                class="w-full py-3 px-6 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all flex items-center justify-center gap-2"
                            >
                                <CameraIcon class="w-5 h-5" /> Buka Scanner
                            </Link>
                        </div>
                    </div>

                    <div
                        class="relative overflow-hidden bg-white rounded-3xl p-6 shadow-lg shadow-orange-100 border border-slate-50 group hover:-translate-y-1 transition-all"
                    >
                        <div
                            class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-orange-400 to-amber-500"
                        ></div>
                        <div class="flex items-center gap-4 mb-4">
                            <div
                                class="p-3 rounded-2xl bg-orange-50 text-orange-600"
                            >
                                <ClipboardDocumentListIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">
                                    Tugas & PR
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Kelola nilai & tugas siswa
                                </p>
                            </div>
                        </div>
                        <button
                            class="block w-full py-2.5 px-4 rounded-xl bg-white border-2 border-orange-100 text-orange-600 font-bold text-sm text-center hover:bg-orange-50 hover:border-orange-200 transition-all"
                        >
                            Buat Tugas Baru
                        </button>
                    </div>

                    <div
                        class="relative overflow-hidden bg-white rounded-3xl p-6 shadow-lg shadow-cyan-100 border border-slate-50 group hover:-translate-y-1 transition-all"
                    >
                        <div
                            class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cyan-400 to-blue-500"
                        ></div>
                        <div class="flex items-center gap-4 mb-4">
                            <div
                                class="p-3 rounded-2xl bg-cyan-50 text-cyan-600"
                            >
                                <BookOpenIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">
                                    Perpustakaan
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Bahan ajar & E-Book
                                </p>
                            </div>
                        </div>
                        <Link
                            :href="route('ebooks.index')"
                            class="block w-full py-2.5 px-4 rounded-xl bg-white border-2 border-cyan-100 text-cyan-700 font-bold text-sm text-center hover:bg-cyan-50 hover:border-cyan-200 transition-all"
                        >
                            Buka Koleksi
                        </Link>
                    </div>

                    <div
                        class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 flex items-center justify-between"
                    >
                        <div>
                            <p
                                class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1"
                            >
                                Status Anda
                            </p>
                            <div class="inline-flex items-center gap-2">
                                <span class="relative flex h-2.5 w-2.5">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"
                                    ></span>
                                    <span
                                        class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"
                                    ></span>
                                </span>
                                <span class="text-sm font-bold text-slate-700"
                                    >Aktif / Hadir</span
                                >
                            </div>
                        </div>
                        <div
                            class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-lg font-black text-slate-400 border-2 border-white shadow-sm"
                        >
                            {{ auth.user.name.substring(0, 1).toUpperCase() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="showApprovalModal"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <div
                class="flex items-center justify-center min-h-screen px-4 text-center"
            >
                <div
                    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                    @click="showApprovalModal = false"
                ></div>
                <div
                    class="inline-block align-middle bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg w-full relative z-10"
                >
                    <div
                        class="bg-white px-6 py-5 border-b border-slate-50 flex justify-between items-center"
                    >
                        <h3
                            class="text-lg font-black text-slate-800 flex items-center gap-2"
                        >
                            <BellAlertIcon class="w-5 h-5 text-indigo-500" />
                            Permintaan Izin
                        </h3>
                        <button
                            @click="showApprovalModal = false"
                            class="bg-slate-50 rounded-full p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition"
                        >
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>
                    <div
                        class="bg-slate-50/50 max-h-[450px] overflow-y-auto p-2"
                    >
                        <ApprovalList :absensiGrouped="absensiGrouped" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
