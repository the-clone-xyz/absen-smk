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

// Menambahkan props 'teachers' untuk menerima data guru dari controller
const props = defineProps({
    attendances: Array,
    classes: Array,
    teachers: Array, // <--- TAMBAHAN: Data Guru
    filters: Object,
});

const form = useForm({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    class_id: props.filters.class_id || "",
    teacher_id: props.filters.teacher_id || "", // <--- TAMBAHAN: Filter Guru
    role: props.filters.role || "student",
});

// Judul Dinamis
const pageTitle = computed(() => {
    return form.role === "teacher"
        ? "Laporan Absensi Guru"
        : "Laporan Absensi Siswa";
});

const filterData = () => {
    // Reset filter yang tidak relevan saat ganti role
    if (form.role === "student") form.teacher_id = "";
    if (form.role === "teacher") form.class_id = "";

    form.get(route("admin.attendance.report"), {
        preserveState: true,
        replace: true,
    });
};

const printData = () => {
    const url =
        route("admin.attendance.report") +
        `?print=true&role=${form.role}&start_date=${form.start_date}&end_date=${form.end_date}&class_id=${form.class_id}&teacher_id=${form.teacher_id}`;
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
                                >Tipe Laporan</label
                            >
                            <select
                                v-model="form.role"
                                @change="filterData"
                                class="w-full rounded-lg border-gray-300 focus:ring-red-500 text-sm"
                            >
                                <option value="student">Siswa</option>
                                <option value="teacher">Guru</option>
                            </select>
                        </div>

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

                        <div v-if="form.role === 'teacher'">
                            <label
                                class="block text-xs font-bold text-gray-700 mb-1"
                                >Nama Guru (Opsional)</label
                            >
                            <select
                                v-model="form.teacher_id"
                                class="w-full rounded-lg border-gray-300 focus:ring-red-500 text-sm"
                            >
                                <option value="">-- Semua Guru --</option>
                                <option
                                    v-for="t in teachers"
                                    :key="t.id"
                                    :value="t.id"
                                >
                                    {{ t.user.name }}
                                </option>
                            </select>
                        </div>

                        <div class="md:col-span-4 flex gap-2 mt-2">
                            <button
                                @click="filterData"
                                class="bg-gray-800 text-white px-6 py-2.5 rounded-lg font-bold hover:bg-gray-700 flex items-center gap-2 text-sm transition"
                            >
                                <FunnelIcon class="w-4 h-4" /> Tampilkan Data
                            </button>
                            <button
                                @click="printData"
                                class="bg-red-600 text-white px-6 py-2.5 rounded-lg font-bold hover:bg-red-700 flex items-center gap-2 text-sm transition"
                            >
                                <PrinterIcon class="w-4 h-4" /> Cetak PDF
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
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase whitespace-nowrap"
                                    >
                                        Tanggal
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase whitespace-nowrap"
                                    >
                                        Nama Lengkap
                                    </th>

                                    <th
                                        v-if="form.role === 'student'"
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase whitespace-nowrap"
                                    >
                                        Kelas
                                    </th>
                                    <th
                                        v-else
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase whitespace-nowrap"
                                    >
                                        NIP
                                    </th>

                                    <th
                                        class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase whitespace-nowrap"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase whitespace-nowrap"
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
                                        <span
                                            class="text-xs text-gray-400 ml-1 font-mono"
                                            >({{ item.time_in }})</span
                                        >
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
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
                                        class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap"
                                    >
                                        <span
                                            class="bg-gray-100 px-2 py-1 rounded text-xs border font-bold"
                                        >
                                            {{
                                                item.user.student?.kelas
                                                    ?.name || "-"
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        v-else
                                        class="px-6 py-4 text-sm text-gray-600 font-mono whitespace-nowrap"
                                    >
                                        {{ item.user.teacher?.nip || "-" }}
                                    </td>

                                    <td
                                        class="px-6 py-4 text-center whitespace-nowrap"
                                    >
                                        <span
                                            class="px-3 py-1 text-xs font-bold rounded-full border"
                                            :class="{
                                                'bg-green-100 text-green-700 border-green-200':
                                                    item.status === 'Hadir',
                                                'bg-blue-100 text-blue-700 border-blue-200':
                                                    item.status === 'Sakit',
                                                'bg-yellow-100 text-yellow-700 border-yellow-200':
                                                    item.status === 'Izin',
                                                'bg-red-100 text-red-700 border-red-200':
                                                    item.status === 'Alpha' ||
                                                    item.status === 'Telat',
                                            }"
                                        >
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate"
                                    >
                                        {{ item.description || "-" }}
                                    </td>
                                </tr>
                                <tr v-if="attendances.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-6 py-12 text-center text-gray-400 italic"
                                    >
                                        Tidak ada data absensi untuk periode
                                        ini.
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
