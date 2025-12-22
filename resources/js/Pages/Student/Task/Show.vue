<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import {
    ArrowLeftIcon,
    CalendarIcon,
    DocumentTextIcon,
    CloudArrowUpIcon,
    PaperClipIcon,
    CheckCircleIcon,
    StarIcon,
} from "@heroicons/vue/24/solid";
import { ref, computed } from "vue";

const props = defineProps({
    task: Object,
    submission: Object,
});

const formatDate = (date) =>
    new Date(date).toLocaleString("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        hour: "2-digit",
        minute: "2-digit",
    });

const form = useForm({
    notes: props.submission?.notes || "",
    file: null,
});

const fileError = ref(null);
const isEditing = ref(false);

const handleFile = (e) => {
    const file = e.target.files[0];
    fileError.value = null;
    form.file = null;

    if (file) {
        if (file.size > 10 * 1024 * 1024) {
            fileError.value = "Ukuran file maksimal 10MB.";
            e.target.value = "";
        } else {
            form.file = file;
        }
    }
};

const submitTask = () => {
    // Pastikan route ini benar di web.php
    form.post(route("student.tasks.submit", props.task.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
            form.reset("file");
        },
    });
};

const goBack = () => window.history.back();

const isSubmitted = computed(() => !!props.submission);
const isGraded = computed(
    () =>
        props.submission?.score !== null &&
        props.submission?.score !== undefined
);
</script>

<template>
    <Head :title="`Tugas - ${task.title}`" />

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
                        Pengumpulan Tugas
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ task.subject?.name || "Mapel" }} •
                        {{ task.teacher?.user?.name || "Guru Tidak Dikenal" }}
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 relative overflow-hidden"
                    >
                        <div
                            class="absolute top-0 right-0 bg-red-100 text-red-600 px-4 py-1.5 rounded-bl-xl text-xs font-bold flex items-center gap-1"
                        >
                            <CalendarIcon class="w-3 h-3" /> Deadline:
                            {{ formatDate(task.deadline) }}
                        </div>

                        <h1 class="text-2xl font-bold text-gray-800 mt-2 mb-4">
                            {{ task.title }}
                        </h1>

                        <div
                            class="bg-gray-50 p-5 rounded-xl text-gray-700 text-sm whitespace-pre-line leading-relaxed border border-gray-100"
                        >
                            {{ task.description }}
                        </div>

                        <div v-if="task.file_path" class="mt-6">
                            <p
                                class="text-xs font-bold text-gray-400 uppercase mb-2"
                            >
                                Lampiran Soal
                            </p>
                            <div
                                class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:border-indigo-300 transition group bg-white"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="bg-indigo-50 p-2 rounded text-indigo-600"
                                    >
                                        <DocumentTextIcon class="w-5 h-5" />
                                    </div>
                                    <span
                                        class="text-sm font-medium text-gray-700"
                                        >File Pendukung Tugas</span
                                    >
                                </div>
                                <a
                                    :href="`/storage/${task.file_path}`"
                                    target="_blank"
                                    class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded font-bold hover:bg-indigo-700 transition"
                                >
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div
                        v-if="isGraded"
                        class="bg-white rounded-2xl shadow-sm border border-green-200 overflow-hidden text-center"
                    >
                        <div class="bg-green-50 p-6 border-b border-green-100">
                            <div
                                class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3 text-green-600"
                            >
                                <StarIcon class="w-8 h-8" />
                            </div>
                            <h3 class="text-green-800 font-bold">
                                Tugas Selesai!
                            </h3>
                            <p class="text-green-600 text-xs mt-1">
                                Guru sudah memberikan nilai.
                            </p>
                        </div>
                        <div class="p-8">
                            <span
                                class="text-sm text-gray-400 font-bold uppercase tracking-widest"
                                >Nilai Kamu</span
                            >
                            <div class="text-6xl font-black text-gray-800 mt-2">
                                {{ submission.score }}
                            </div>
                        </div>
                    </div>

                    <div
                        v-else-if="isSubmitted && !isEditing"
                        class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6"
                    >
                        <div
                            class="flex items-center gap-2 text-green-600 font-bold mb-4 bg-green-50 p-3 rounded-lg"
                        >
                            <CheckCircleIcon class="w-5 h-5" /> Status:
                            Dikumpulkan
                        </div>
                        <div class="space-y-4">
                            <div class="text-sm">
                                <p class="text-xs text-gray-400 font-bold mb-1">
                                    File Jawaban:
                                </p>
                                <div
                                    v-if="submission.file_path"
                                    class="flex items-center gap-2"
                                >
                                    <DocumentTextIcon
                                        class="w-4 h-4 text-gray-500"
                                    />
                                    <a
                                        :href="`/storage/${submission.file_path}`"
                                        target="_blank"
                                        class="text-blue-600 underline font-medium truncate"
                                        >Lihat File Saya</a
                                    >
                                </div>
                                <p v-else class="text-gray-500 italic">
                                    - Tidak ada file -
                                </p>
                            </div>
                            <div class="text-sm">
                                <p class="text-xs text-gray-400 font-bold mb-1">
                                    Catatan Kamu:
                                </p>
                                <p
                                    class="bg-gray-50 p-3 rounded border border-gray-100 text-gray-600"
                                >
                                    {{ submission.notes || "-" }}
                                </p>
                            </div>
                            <button
                                @click="isEditing = true"
                                class="w-full mt-4 bg-white border border-gray-300 text-gray-700 py-2 rounded-lg text-sm font-bold hover:bg-gray-50 transition"
                            >
                                Edit Jawaban / Upload Ulang
                            </button>
                        </div>
                    </div>

                    <div
                        v-else
                        class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6"
                    >
                        <h3
                            class="font-bold text-gray-800 mb-4 flex items-center gap-2"
                        >
                            <CloudArrowUpIcon class="w-5 h-5 text-indigo-600" />
                            {{ isEditing ? "Edit Jawaban" : "Upload Jawaban" }}
                        </h3>
                        <form @submit.prevent="submitTask" class="space-y-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase mb-1"
                                    >Catatan Tambahan</label
                                >
                                <textarea
                                    v-model="form.notes"
                                    rows="3"
                                    class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500"
                                ></textarea>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase mb-1"
                                    >File Tugas</label
                                >
                                <div
                                    class="border-2 border-dashed rounded-xl p-6 text-center hover:bg-gray-50 transition cursor-pointer relative"
                                    :class="
                                        fileError
                                            ? 'border-red-300 bg-red-50'
                                            : 'border-gray-300'
                                    "
                                >
                                    <input
                                        type="file"
                                        @change="handleFile"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    />
                                    <div
                                        v-if="form.file"
                                        class="flex flex-col items-center text-indigo-600"
                                    >
                                        <PaperClipIcon class="w-8 h-8 mb-1" />
                                        <span
                                            class="text-xs font-bold truncate w-full px-2"
                                            >{{ form.file.name }}</span
                                        >
                                        <span
                                            class="text-[10px] text-gray-400 mt-1"
                                            >Klik untuk ganti</span
                                        >
                                    </div>
                                    <div v-else class="text-gray-400">
                                        <CloudArrowUpIcon
                                            class="w-8 h-8 mx-auto mb-1"
                                        />
                                        <p class="text-xs font-bold">
                                            Upload File
                                        </p>
                                        <p class="text-[10px]">
                                            PDF, DOCX, IMG (Max 10MB)
                                        </p>
                                    </div>
                                </div>
                                <p
                                    v-if="fileError"
                                    class="text-xs text-red-500 mt-1 font-bold"
                                >
                                    {{ fileError }}
                                </p>
                            </div>
                            <div class="flex gap-2 pt-2">
                                <button
                                    v-if="isEditing"
                                    @click="isEditing = false"
                                    type="button"
                                    class="flex-1 bg-gray-100 text-gray-600 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-200 transition"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex-1 bg-indigo-600 text-white py-2.5 rounded-lg text-sm font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 disabled:opacity-50 flex justify-center items-center gap-2"
                                >
                                    <span
                                        v-if="form.processing"
                                        class="animate-spin w-3 h-3 border-2 border-white border-t-transparent rounded-full"
                                    ></span>
                                    {{
                                        isEditing
                                            ? "Simpan Perubahan"
                                            : "Kirim Tugas"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
