<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    ArrowLeftIcon,
    MapPinIcon,
    PhotoIcon,
    CalendarDaysIcon,
    ClockIcon,
    FaceSmileIcon,
    DocumentTextIcon,
} from "@heroicons/vue/24/solid";

defineProps({
    absensi: Array,
});
</script>

<template>
    <Head title="Riwayat Absensi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('dashboard')"
                    class="p-2 rounded-full hover:bg-gray-200 transition text-gray-600"
                >
                    <ArrowLeftIcon class="w-5 h-5" />
                </Link>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">
                    Riwayat Kehadiran
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden"
                >
                    <div
                        class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex justify-between items-center"
                    >
                        <h3
                            class="font-bold text-gray-700 flex items-center gap-2"
                        >
                            <DocumentTextIcon class="w-5 h-5 text-green-600" />
                            Data Presensi Anda
                        </h3>
                        <span
                            class="text-xs text-gray-500 bg-white px-2 py-1 rounded border"
                            >Total: {{ absensi.length }} Data</span
                        >
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-white">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-[20%]"
                                    >
                                        Waktu
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-[15%]"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-[65%]"
                                    >
                                        Detail & Bukti
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr
                                    v-for="item in absensi"
                                    :key="item.id"
                                    class="hover:bg-green-50/40 transition duration-150"
                                >
                                    <td
                                        class="px-6 py-4 whitespace-nowrap align-top"
                                    >
                                        <div class="flex flex-col">
                                            <span
                                                class="text-sm font-bold text-gray-800 flex items-center gap-2"
                                            >
                                                {{ item.date }}
                                            </span>
                                            <span
                                                class="text-xs text-gray-500 flex items-center gap-1 mt-1 bg-gray-100 w-fit px-2 py-0.5 rounded"
                                            >
                                                <ClockIcon class="w-3 h-3" />
                                                {{ item.time_in }}
                                            </span>
                                        </div>
                                    </td>

                                    <td
                                        class="px-6 py-4 whitespace-nowrap align-top text-center"
                                    >
                                        <span
                                            v-if="item.status === 'Hadir'"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200 shadow-sm"
                                        >
                                            Hadir
                                        </span>
                                        <span
                                            v-else-if="item.status === 'Sakit'"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200 shadow-sm"
                                        >
                                            Sakit
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700 border border-yellow-200 shadow-sm"
                                        >
                                            Izin
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 align-top">
                                        <div class="flex gap-4">
                                            <div class="flex-shrink-0">
                                                <a
                                                    v-if="item.photo_path"
                                                    :href="
                                                        '/storage/' +
                                                        item.photo_path
                                                    "
                                                    target="_blank"
                                                    class="group relative block"
                                                >
                                                    <img
                                                        :src="
                                                            '/storage/' +
                                                            item.photo_path
                                                        "
                                                        class="h-14 w-14 rounded-lg object-cover border border-gray-200 shadow-sm group-hover:ring-2 ring-green-500 transition"
                                                        alt="Bukti"
                                                    />
                                                    <div
                                                        class="absolute inset-0 bg-black/10 group-hover:bg-transparent rounded-lg transition"
                                                    ></div>
                                                </a>
                                                <div
                                                    v-else
                                                    class="h-14 w-14 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-200"
                                                >
                                                    <PhotoIcon
                                                        class="w-6 h-6"
                                                    />
                                                </div>
                                            </div>

                                            <div
                                                class="flex flex-col justify-center"
                                            >
                                                <p
                                                    class="text-sm text-gray-800 font-medium break-words line-clamp-2"
                                                >
                                                    {{
                                                        item.description || "-"
                                                    }}
                                                </p>

                                                <a
                                                    v-if="item.latitude != 0"
                                                    :href="
                                                        'https://www.google.com/maps?q=' +
                                                        item.latitude +
                                                        ',' +
                                                        item.longitude
                                                    "
                                                    target="_blank"
                                                    class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-medium mt-1 hover:underline w-fit"
                                                >
                                                    <MapPinIcon
                                                        class="w-3 h-3"
                                                    />
                                                    Lihat Lokasi
                                                </a>
                                                <span
                                                    v-else
                                                    class="text-xs text-gray-400 mt-1 italic"
                                                >
                                                    Tanpa lokasi (Surat/QR)
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="absensi.length === 0">
                                    <td
                                        colspan="3"
                                        class="px-6 py-12 text-center"
                                    >
                                        <div
                                            class="flex flex-col items-center justify-center text-gray-400"
                                        >
                                            <div
                                                class="bg-gray-50 p-4 rounded-full mb-3"
                                            >
                                                <FaceSmileIcon
                                                    class="w-10 h-10 text-gray-300"
                                                />
                                            </div>
                                            <p
                                                class="text-base font-medium text-gray-600"
                                            >
                                                Belum ada data.
                                            </p>
                                            <p class="text-sm mt-1">
                                                Anda belum pernah melakukan
                                                absensi.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
