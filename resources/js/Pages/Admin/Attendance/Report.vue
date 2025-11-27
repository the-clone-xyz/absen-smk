<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import {
    PrinterIcon,
    FunnelIcon,
    DocumentTextIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    attendances: Array,
    classes: Array,
    filters: Object,
});

const form = useForm({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    class_id: props.filters.class_id || "",
});

// Fungsi Filter Tampilan
const filterData = () => {
    form.get(route("admin.attendance.report"), {
        preserveState: true,
        replace: true,
    });
};

// Fungsi Cetak (Membuka Tab Baru ke Blade View)
const printData = () => {
    // Kita bangun URL manual dengan parameter query
    const url =
        route("admin.attendance.report") +
        `?print=true&start_date=${form.start_date}&end_date=${form.end_date}&class_id=${form.class_id}`;
    window.open(url, "_blank");
};
</script>

<template>
    <Head title="Laporan Absensi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-red-800 flex items-center gap-2">
                <DocumentTextIcon class="w-6 h-6" /> Laporan Absensi
            </h2>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div
                    class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-6"
                >
                    <div
                        class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end"
                    >
                        <div>
                            <label
                                class="block text-sm font-bold text-gray-700 mb-1"
                                >Dari Tanggal</label
                            >
                            <input
                                v-model="form.start_date"
                                type="date"
                                class="w-full rounded-lg border-gray-300 focus:ring-red-500"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-bold text-gray-700 mb-1"
                                >Sampai Tanggal</label
                            >
                            <input
                                v-model="form.end_date"
                                type="date"
                                class="w-full rounded-lg border-gray-300 focus:ring-red-500"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-bold text-gray-700 mb-1"
                                >Kelas (Opsional)</label
                            >
                            <select
                                v-model="form.class_id"
                                class="w-full rounded-lg border-gray-300 focus:ring-red-500"
                            >
                                <option value="">-- Semua Kelas --</option>
                                <option
                                    v-for="k in classes"
                                    :key="k.id"
                                    :value="k.id"
                                >
                                    {{ k.name }}
                                </option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button
                                @click="filterData"
                                class="flex-1 bg-gray-800 text-white px-4 py-2.5 rounded-lg font-bold hover:bg-gray-700 flex justify-center items-center gap-2"
                            >
                                <FunnelIcon class="w-5 h-5" /> Filter
                            </button>
                            <button
                                @click="printData"
                                class="flex-1 bg-red-600 text-white px-4 py-2.5 rounded-lg font-bold hover:bg-red-700 flex justify-center items-center gap-2"
                            >
                                <PrinterIcon class="w-5 h-5" /> Cetak
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200"
                >
                    <div
                        class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between"
                    >
                        <span class="font-bold text-gray-700"
                            >Hasil Pencarian</span
                        >
                        <span class="text-xs bg-white border px-2 py-1 rounded"
                            >Total Data: {{ attendances.length }}</span
                        >
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                    >
                                        Tanggal
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                    >
                                        Nama Siswa
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                    >
                                        Kelas
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                    >
                                        Ket
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="item in attendances"
                                    :key="item.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ item.date }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm font-bold text-gray-800"
                                    >
                                        {{ item.user.name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{
                                            item.user.student?.kelas?.name ||
                                            "-"
                                        }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="px-2 py-1 text-xs font-bold rounded-full"
                                            :class="{
                                                'bg-green-100 text-green-800':
                                                    item.status === 'Hadir',
                                                'bg-blue-100 text-blue-800':
                                                    item.status === 'Sakit',
                                                'bg-yellow-100 text-yellow-800':
                                                    item.status === 'Izin',
                                            }"
                                        >
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs"
                                    >
                                        {{ item.description }}
                                    </td>
                                </tr>
                                <tr v-if="attendances.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-6 py-10 text-center text-gray-400"
                                    >
                                        Tidak ada data absensi pada periode ini.
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
