<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import {
    ArrowLeftIcon,
    CalendarIcon,
    DocumentTextIcon,
    CheckCircleIcon,
    XCircleIcon,
    PencilSquareIcon,
} from "@heroicons/vue/24/solid";
const goBack = () => {
    window.history.back();
};
import GradingModal from "@/Pages/Teacher/Partials/GradingModal.vue";

const props = defineProps({
    task: Object,
    studentData: Array, // Data gabungan siswa + submission
});

const showGradingModal = ref(false);
const selectedSubmission = ref(null);

// Helper Buka Modal Nilai
const openGrading = (student) => {
    // Kita format datanya agar sesuai dengan GradingModal
    // Jika siswa belum kumpul, kita buat submission dummy agar tetap bisa dinilai (opsional)
    // Atau hanya izinkan nilai jika submission ada.
    if (!student.submission) {
        alert("Siswa ini belum mengumpulkan tugas.");
        return;
    }

    selectedSubmission.value = {
        task: props.task,
        submission: {
            ...student.submission,
            student: { name: student.name }, // GradingModal butuh nama siswa
        },
    };
    showGradingModal.value = true;
};

// Format Tanggal
const formatDate = (d) =>
    new Date(d).toLocaleString("id-ID", {
        dateStyle: "medium",
        timeStyle: "short",
    });
</script>

<template>
    <Head :title="`Detail Tugas - ${task.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <button
                    @click="goBack"
                    class="p-2 rounded-full hover:bg-gray-200 transition text-gray-500"
                >
                    <ArrowLeftIcon class="w-5 h-5" />
                </button>
                <div>
                    <h2 class="font-bold text-xl text-gray-800 leading-tight">
                        Detail Penilaian
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ task.kelas.name }} • {{ task.subject.name }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
            >
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 mb-2">
                            {{ task.title }}
                        </h1>
                        <div
                            class="flex items-center gap-4 text-sm text-gray-500"
                        >
                            <span
                                class="flex items-center gap-1 bg-red-50 text-red-600 px-3 py-1 rounded-full font-medium"
                            >
                                <CalendarIcon class="w-4 h-4" /> Deadline:
                                {{ formatDate(task.deadline) }}
                            </span>
                            <a
                                v-if="task.file_path"
                                :href="`/storage/${task.file_path}`"
                                target="_blank"
                                class="flex items-center gap-1 text-blue-600 hover:underline font-bold"
                            >
                                <DocumentTextIcon class="w-4 h-4" /> Lihat
                                Lampiran Soal
                            </a>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-gray-50 p-4 rounded-lg text-gray-700 text-sm whitespace-pre-line leading-relaxed border border-gray-100"
                >
                    {{ task.description }}
                </div>
            </div>

            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
            >
                <div
                    class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center"
                >
                    <h3 class="font-bold text-gray-700">Rekap Pengumpulan</h3>
                    <span
                        class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full"
                    >
                        Total Siswa: {{ studentData.length }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="bg-gray-50 text-gray-500 uppercase text-xs"
                        >
                            <tr>
                                <th class="px-6 py-3 font-bold">Nama Siswa</th>
                                <th class="px-6 py-3 font-bold">Status</th>
                                <th class="px-6 py-3 font-bold">
                                    Waktu Kumpul
                                </th>
                                <th class="px-6 py-3 font-bold text-center">
                                    Nilai
                                </th>
                                <th class="px-6 py-3 font-bold text-center">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="data in studentData"
                                :key="data.id"
                                class="hover:bg-gray-50/50 transition"
                            >
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-800">
                                        {{ data.name }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ data.nis }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        v-if="data.submission"
                                        class="px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-700 flex items-center gap-1 w-fit"
                                    >
                                        <CheckCircleIcon class="w-3 h-3" />
                                        Dikumpulkan
                                    </span>
                                    <span
                                        v-else
                                        class="px-2 py-1 rounded text-xs font-bold bg-red-100 text-red-700 flex items-center gap-1 w-fit"
                                    >
                                        <XCircleIcon class="w-3 h-3" /> Belum
                                        Ada
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500">
                                    {{
                                        data.submission
                                            ? formatDate(
                                                  data.submission.created_at
                                              )
                                            : "-"
                                    }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        v-if="data.submission?.score"
                                        class="text-lg font-bold text-green-600"
                                    >
                                        {{ data.submission.score }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button
                                        v-if="data.submission"
                                        @click="openGrading(data)"
                                        class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-indigo-700 transition flex items-center gap-1 mx-auto shadow"
                                    >
                                        <PencilSquareIcon class="w-3 h-3" />
                                        Nilai
                                    </button>
                                    <span
                                        v-else
                                        class="text-xs text-gray-400 italic"
                                        >Menunggu</span
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <GradingModal
            v-if="showGradingModal"
            :data="selectedSubmission"
            @close="showGradingModal = false"
        />
    </AuthenticatedLayout>
</template>
