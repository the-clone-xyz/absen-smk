<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { MapPinIcon, CameraIcon } from "@heroicons/vue/24/solid";

// Import Partials
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
                        class="font-extrabold text-2xl text-gray-800 leading-tight"
                    >
                        Panel Guru
                    </h2>
                    <p class="text-sm text-gray-500">
                        Selamat datang, {{ auth.user.name }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
                    <TeacherSchedule :jadwal="jadwal" />

                    <div class="lg:col-span-4 flex flex-col gap-6 h-[500px]">
                        <div
                            class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden flex-shrink-0"
                        >
                            <div
                                class="absolute right-0 top-0 opacity-10 transform translate-x-4 -translate-y-4"
                            >
                                <MapPinIcon class="w-32 h-32" />
                            </div>
                            <h3 class="font-bold text-lg mb-1 relative z-10">
                                Kehadiran Anda
                            </h3>
                            <p class="text-blue-100 text-xs mb-4 relative z-10">
                                Lakukan absensi harian Anda di sini.
                            </p>

                            <Link
                                :href="route('attendance.index')"
                                class="relative z-10 bg-white text-blue-700 px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-blue-50 inline-flex items-center gap-2"
                            >
                                <CameraIcon class="w-4 h-4" /> Scan Absen Masuk
                            </Link>
                        </div>

                        <ApprovalList :absensiGrouped="absensiGrouped" />
                    </div>

                    <TeacherCalendar :jadwalKalender="jadwalKalender" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
