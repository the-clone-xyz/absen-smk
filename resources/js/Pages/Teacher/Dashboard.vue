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
    SparklesIcon,
    QrCodeIcon,
    CameraIcon,
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
                        class="font-black text-3xl text-slate-800 tracking-tight"
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
                        class="relative p-3 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all group"
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
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
                <div
                    class="bg-white p-6 rounded-[2rem] shadow-[0_2px_20px_rgb(0,0,0,0.04)] border border-slate-50 flex items-center gap-4 hover:-translate-y-1 transition-all duration-300 group"
                >
                    <div
                        class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform"
                    >
                        <UserGroupIcon class="w-7 h-7" />
                    </div>
                    <div>
                        <p
                            class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                        >
                            Total Siswa
                        </p>
                        <p class="text-3xl font-black text-slate-800">
                            {{ statistik?.total_siswa || "-" }}
                        </p>
                    </div>
                </div>

                <div
                    class="bg-white p-6 rounded-[2rem] shadow-[0_2px_20px_rgb(0,0,0,0.04)] border border-slate-50 flex items-center gap-4 hover:-translate-y-1 transition-all duration-300 group"
                >
                    <div
                        class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform"
                    >
                        <CheckBadgeIcon class="w-7 h-7" />
                    </div>
                    <div>
                        <p
                            class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                        >
                            Hadir
                        </p>
                        <p class="text-3xl font-black text-slate-800">
                            {{ statistik?.hadir || "0" }}
                        </p>
                    </div>
                </div>

                <div
                    class="bg-white p-6 rounded-[2rem] shadow-[0_2px_20px_rgb(0,0,0,0.04)] border border-slate-50 flex items-center gap-4 hover:-translate-y-1 transition-all duration-300 group"
                >
                    <div
                        class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform"
                    >
                        <ChartBarIcon class="w-7 h-7" />
                    </div>
                    <div>
                        <p
                            class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                        >
                            Izin / Sakit
                        </p>
                        <p class="text-3xl font-black text-slate-800">
                            {{
                                (statistik?.izin || 0) + (statistik?.sakit || 0)
                            }}
                        </p>
                    </div>
                </div>

                <div
                    class="bg-white p-6 rounded-[2rem] shadow-[0_2px_20px_rgb(0,0,0,0.04)] border border-slate-50 flex items-center gap-4 hover:-translate-y-1 transition-all duration-300 group"
                >
                    <div
                        class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform"
                    >
                        <XCircleIcon class="w-7 h-7" />
                    </div>
                    <div>
                        <p
                            class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1"
                        >
                            Alpha
                        </p>
                        <p class="text-3xl font-black text-slate-800">
                            {{ statistik?.alpha || "0" }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <div class="flex flex-col h-full">
                    <TeacherSchedule
                        :jadwal="jadwal"
                        class="h-full shadow-[0_4px_20px_rgb(0,0,0,0.03)]"
                    />
                </div>

                <div class="flex flex-col h-full">
                    <div
                        class="h-full bg-white rounded-[2rem] p-1 shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-slate-50"
                    >
                        <TeacherCalendar :jadwalKalender="jadwalKalender" />
                    </div>
                </div>

                <div class="flex flex-col gap-6">
                    <div
                        class="relative overflow-hidden bg-white rounded-[2rem] p-6 shadow-xl shadow-indigo-100 border border-white group"
                    >
                        <div
                            class="absolute top-0 right-0 -mr-8 -mt-8 w-40 h-40 bg-indigo-50 rounded-full blur-3xl opacity-60"
                        ></div>
                        <div
                            class="absolute bottom-0 left-0 -ml-8 -mb-8 w-32 h-32 bg-purple-50 rounded-full blur-3xl opacity-60"
                        ></div>

                        <svg
                            class="absolute top-4 right-4 text-indigo-100 w-16 h-16 opacity-50"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                clip-rule="evenodd"
                            />
                        </svg>

                        <div
                            class="relative z-10 flex flex-col items-center text-center"
                        >
                            <div
                                class="mb-4 inline-flex p-4 rounded-2xl bg-gradient-to-br from-indigo-50 to-white shadow-sm border border-indigo-50"
                            >
                                <QrCodeIcon class="w-10 h-10 text-indigo-600" />
                            </div>

                            <h3
                                class="text-xl font-black text-slate-800 tracking-tight mb-1"
                            >
                                Presensi Guru
                            </h3>
                            <p
                                class="text-xs text-slate-500 mb-6 max-w-[200px] leading-relaxed"
                            >
                                Catat kehadiran Anda dengan mudah melalui scan
                                kode QR.
                            </p>

                            <Link
                                :href="route('attendance.index')"
                                class="w-full py-3.5 px-6 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:shadow-indigo-300 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2"
                            >
                                <CameraIcon class="w-5 h-5" />
                                Buka Scanner
                            </Link>
                        </div>
                    </div>

                    <div
                        class="bg-white rounded-[2rem] p-6 shadow-[0_4px_20px_rgb(0,0,0,0.03)] border border-slate-50 flex flex-col items-center text-center relative overflow-hidden"
                    >
                        <div
                            class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-400"
                        ></div>

                        <div
                            class="w-16 h-16 rounded-full bg-slate-50 border-4 border-white shadow-sm mb-3 flex items-center justify-center text-xl font-black text-slate-300"
                        >
                            {{ auth.user.name.substring(0, 1).toUpperCase() }}
                        </div>

                        <p
                            class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1"
                        >
                            Status Hari Ini
                        </p>

                        <div
                            class="inline-flex items-center gap-2 px-4 py-1.5 bg-emerald-50 text-emerald-700 rounded-full border border-emerald-100 mt-1"
                        >
                            <span class="relative flex h-2.5 w-2.5">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"
                                ></span>
                                <span
                                    class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"
                                ></span>
                            </span>
                            <span class="text-xs font-extrabold"
                                >Aktif / Hadir</span
                            >
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
                    class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm transition-opacity"
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
