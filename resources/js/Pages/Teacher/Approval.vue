<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import {
    CheckCircleIcon,
    XCircleIcon,
    ExclamationCircleIcon,
    ArrowLeftIcon,
    ClipboardDocumentListIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    pendingRequests: Array,
});

const updateStatus = (id, status) => {
    if (
        confirm(
            `Apakah Anda yakin ingin ${
                status === "approved" ? "menyetujui" : "menolak"
            } pengajuan ini?`
        )
    ) {
        router.patch(route("teacher.attendance.approve", id), {
            status: status,
        });
    }
};
</script>

<template>
    <Head title="Approval Izin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('teacher.dashboard')"
                        class="p-2 rounded-full hover:bg-gray-200 transition text-gray-500"
                    >
                        <ArrowLeftIcon class="w-5 h-5" />
                    </Link>
                    <h2 class="font-bold text-xl text-green-800 leading-tight">
                        Daftar Persetujuan Izin
                    </h2>
                </div>
                <span
                    class="text-sm font-bold text-orange-600 bg-orange-100 px-3 py-1 rounded-full border border-orange-200"
                >
                    {{ pendingRequests.length }} Pengajuan Menunggu
                </span>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div
                    class="bg-white shadow-md rounded-xl overflow-hidden border border-gray-200"
                >
                    <div
                        v-if="pendingRequests.length > 0"
                        class="overflow-x-auto"
                    >
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-orange-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Siswa
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Jenis Izin
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Keterangan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Bukti
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr
                                    v-for="item in pendingRequests"
                                    :key="item.id"
                                    class="hover:bg-red-50/20"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-bold text-gray-900"
                                        >
                                            {{ item.user.name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ item.user.email }}
                                        </div>
                                    </td>

                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center"
                                    >
                                        <span
                                            class="px-3 py-1 text-xs font-bold rounded-full border"
                                            :class="{
                                                'bg-blue-100 text-blue-800 border-blue-200':
                                                    item.status === 'Sakit',
                                                'bg-yellow-100 text-yellow-800 border-yellow-200':
                                                    item.status === 'Izin',
                                            }"
                                        >
                                            {{ item.status }}
                                        </span>
                                    </td>

                                    <td
                                        class="px-6 py-4 text-sm text-gray-700 max-w-sm"
                                    >
                                        <p class="truncate max-w-xs">
                                            {{ item.description }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            Diajukan: {{ item.date }}
                                        </p>
                                    </td>

                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center"
                                    >
                                        <a
                                            :href="
                                                '/storage/' + item.photo_path
                                            "
                                            target="_blank"
                                            class="text-blue-600 hover:underline text-xs block"
                                        >
                                            📷 Lihat Surat
                                        </a>
                                    </td>

                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center"
                                    >
                                        <div class="flex justify-center gap-2">
                                            <button
                                                @click="
                                                    updateStatus(
                                                        item.id,
                                                        'approved'
                                                    )
                                                "
                                                class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition"
                                                title="Setujui"
                                            >
                                                <CheckCircleIcon
                                                    class="w-6 h-6"
                                                />
                                            </button>
                                            <button
                                                @click="
                                                    updateStatus(
                                                        item.id,
                                                        'rejected'
                                                    )
                                                "
                                                class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition"
                                                title="Tolak"
                                            >
                                                <XCircleIcon class="w-6 h-6" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="text-center py-10 bg-white">
                        <ClipboardDocumentListIcon
                            class="w-12 h-12 mx-auto text-green-300 mb-3"
                        />
                        <p class="text-lg font-bold text-gray-600">
                            Tidak ada pengajuan izin yang menunggu.
                        </p>
                        <p class="text-sm text-gray-400">
                            Semua absensi hari ini sudah terekap.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
