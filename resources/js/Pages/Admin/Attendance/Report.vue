<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import {
    PrinterIcon,
    FunnelIcon,
    DocumentTextIcon,
    UserGroupIcon,
    AcademicCapIcon,
} from "@heroicons/vue/24/solid";
import { computed } from "vue";

const props = defineProps({
    attendances: Array,
    classes: Array,
    filters: Object,
});

const form = useForm({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    class_id: props.filters.class_id || "",
    role: props.filters.role || "student", // Simpan role di form
});

// Judul Dinamis
const pageTitle = computed(() => {
    return props.filters.role === "teacher"
        ? "Laporan Absensi Guru"
        : "Laporan Absensi Siswa";
});

const filterData = () => {
    form.get(route("admin.attendance.report"), {
        preserveState: true,
        replace: true,
    });
};

const printData = () => {
    const url =
        route("admin.attendance.report") +
        `?print=true&role=${form.role}&start_date=${form.start_date}&end_date=${form.end_date}&class_id=${form.class_id}`;
    window.open(url, "_blank");
};
</script>

<template>
    <Head :title="pageTitle" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-xl text-red-800 flex items-center gap-2">
                <DocumentTextIcon class="w-6 h-6" /> {{ pageTitle }}
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
                                class="block text-xs font-bold text-gray-700 mb-1"
                                >Dari Tanggal</label
                            >
                            <input
                                v-model="form.start_date"
                                type="date"
                                class="w-full rounded-lg border-gray-300 focus:ring-red-500 text-sm"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-xs font-bold text-gray-700 mb-1"
                                >Sampai Tanggal</label
                            >
                            <input
                                v-model="form.end_date"
                                type="date"
                                class="w-full rounded-lg border-gray-300 focus:ring-red-500 text-sm"
                            />
                        </div>

                        <div v-if="form.role === 'student'">
                            <label
                                class="block text-xs font-bold text-gray-700 mb-1"
                                >Kelas (Opsional)</label
                            >
                            <select
                                v-model="form.class_id"
                                class="w-full rounded-lg border-gray-300 focus:ring-red-500 text-sm"
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
                        <div v-else></div>

                        <div class="flex gap-2">
                            <button
                                @click="filterData"
                                class="flex-1 bg-gray-800 text-white px-4 py-2.5 rounded-lg font-bold hover:bg-gray-700 flex justify-center items-center gap-2 text-sm transition"
                            >
                                <FunnelIcon class="w-4 h-4" /> Filter
                            </button>
                            <button
                                @click="printData"
                                class="flex-1 bg-red-600 text-white px-4 py-2.5 rounded-lg font-bold hover:bg-red-700 flex justify-center items-center gap-2 text-sm transition"
                            >
                                <PrinterIcon class="w-4 h-4" /> Cetak
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200"
                >
                    <div
                        class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center"
                    >
                        <span
                            class="font-bold text-gray-700 flex items-center gap-2"
                        >
                            <component
                                :is="
                                    form.role === 'teacher'
                                        ? AcademicCapIcon
                                        : UserGroupIcon
                                "
                                class="w-5 h-5 text-gray-500"
                            />
                            Data
                            {{ form.role === "teacher" ? "Guru" : "Siswa" }}
                        </span>
                        <span
                            class="text-xs bg-white border px-3 py-1 rounded-full font-mono"
                            >Total: {{ attendances.length }}</span
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
                                        Nama Lengkap
                                    </th>

                                    <th
                                        v-if="form.role === 'student'"
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                    >
                                        Kelas
                                    </th>
                                    <th
                                        v-else
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                    >
                                        NIP
                                    </th>

                                    <th
                                        class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                    >
                                        Keterangan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="item in attendances"
                                    :key="item.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td
                                        class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap"
                                    >
                                        {{ item.date }}
                                        <span class="text-xs text-gray-400 ml-1"
                                            >({{ item.time_in }})</span
                                        >
                                    </td>
                                    <td class="px-6 py-4">
                                        <div
                                            class="text-sm font-bold text-gray-800"
                                        >
                                            {{ item.user.name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ item.user.email }}
                                        </div>
                                    </td>

                                    <td
                                        v-if="form.role === 'student'"
                                        class="px-6 py-4 text-sm text-gray-600"
                                    >
                                        <span
                                            class="bg-gray-100 px-2 py-1 rounded text-xs border"
                                            >{{
                                                item.user.student?.kelas
                                                    ?.name || "-"
                                            }}</span
                                        >
                                    </td>
                                    <td
                                        v-else
                                        class="px-6 py-4 text-sm text-gray-600 font-mono"
                                    >
                                        {{ item.user.teacher?.nip || "-" }}
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="px-3 py-1 text-xs font-bold rounded-full border"
                                            :class="{
                                                'bg-green-100 text-green-700 border-green-200':
                                                    item.status === 'Hadir',
                                                'bg-blue-100 text-blue-700 border-blue-200':
                                                    item.status === 'Sakit',
                                                'bg-yellow-100 text-yellow-700 border-yellow-200':
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
                                        :colspan="
                                            form.role === 'student' ? 5 : 5
                                        "
                                        class="px-6 py-12 text-center text-gray-400"
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
