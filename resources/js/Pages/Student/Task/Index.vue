<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import {
    ClipboardDocumentListIcon,
    CheckCircleIcon,
    ClockIcon,
    PaperClipIcon,
    ArrowUpTrayIcon,
    DocumentTextIcon,
    XMarkIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    tasks: Array, // Data tugas dari TaskController
});

const showModal = ref(false);
const selectedTask = ref(null);

const form = useForm({
    notes: "",
    file: null,
});

// Buka Modal untuk Submit
const openSubmitModal = (task) => {
    selectedTask.value = task;
    form.reset();
    showModal.value = true;
};

// Kirim Jawaban
const submitAssignment = () => {
    if (!selectedTask.value) return;

    form.post(route("tasks.submit", selectedTask.value.id), {
        forceFormData: true, // Wajib untuk upload file
        onSuccess: () => {
            showModal.value = false;
            selectedTask.value = null;
            form.reset();
        },
    });
};

// Helper untuk format tanggal
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("id-ID", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <Head title="Tugas & PR" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-extrabold text-2xl text-gray-800 leading-tight">
                    Tugas & PR
                </h2>
                <div
                    class="bg-white px-3 py-1 rounded-full border text-xs font-bold text-gray-600 shadow-sm"
                >
                    {{ tasks.length }} Tugas Aktif
                </div>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto">
                <div
                    v-if="tasks.length === 0"
                    class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl shadow-sm border border-gray-200 text-center"
                >
                    <div class="bg-green-50 p-4 rounded-full mb-4">
                        <CheckCircleIcon class="w-16 h-16 text-green-500" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">
                        Hore! Tidak ada tugas.
                    </h3>
                    <p class="text-gray-500 text-sm mt-1">
                        Kamu sudah menyelesaikan semua pekerjaan rumah.
                    </p>
                </div>

                <div v-else class="grid gap-6">
                    <div
                        v-for="task in tasks"
                        :key="task.id"
                        class="bg-white rounded-2xl shadow-sm border overflow-hidden transition hover:shadow-md relative"
                        :class="
                            task.is_submitted
                                ? 'border-green-200'
                                : 'border-red-200'
                        "
                    >
                        <div
                            class="absolute top-0 right-0 px-4 py-1.5 rounded-bl-xl text-[10px] font-bold uppercase tracking-wider"
                            :class="
                                task.is_submitted
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700'
                            "
                        >
                            {{
                                task.is_submitted
                                    ? "Selesai Dikumpul"
                                    : "Belum Dikerjakan"
                            }}
                        </div>

                        <div class="p-6 flex flex-col md:flex-row gap-6">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span
                                        class="px-2.5 py-0.5 rounded-md bg-gray-100 text-gray-600 text-xs font-bold uppercase"
                                    >
                                        {{ task.subject.name }}
                                    </span>
                                    <span
                                        class="text-xs text-gray-400 flex items-center gap-1"
                                    >
                                        <ClockIcon class="w-3 h-3" /> Deadline:
                                        {{ formatDate(task.deadline) }}
                                    </span>
                                </div>

                                <h3
                                    class="text-lg font-bold text-gray-800 mb-1"
                                >
                                    {{ task.title }}
                                </h3>
                                <p class="text-xs text-gray-500 mb-4">
                                    Guru: {{ task.teacher.name }}
                                </p>

                                <div
                                    class="bg-gray-50 p-4 rounded-xl text-sm text-gray-700 border border-gray-100 whitespace-pre-wrap"
                                >
                                    {{ task.description }}
                                </div>

                                <div v-if="task.attachment" class="mt-4">
                                    <a
                                        :href="`/storage/${task.attachment}`"
                                        target="_blank"
                                        class="inline-flex items-center gap-2 text-sm text-blue-600 font-bold hover:underline bg-blue-50 px-3 py-2 rounded-lg border border-blue-100 transition"
                                    >
                                        <PaperClipIcon class="w-4 h-4" />
                                        Download Lampiran Soal
                                    </a>
                                </div>
                            </div>

                            <div
                                class="md:w-1/3 flex flex-col justify-center border-t md:border-t-0 md:border-l border-gray-100 md:pl-6 pt-4 md:pt-0"
                            >
                                <div
                                    v-if="task.is_submitted"
                                    class="text-center"
                                >
                                    <div v-if="task.my_score !== null">
                                        <p
                                            class="text-xs text-gray-500 uppercase font-bold mb-1"
                                        >
                                            Nilai Kamu
                                        </p>
                                        <div
                                            class="text-5xl font-extrabold text-green-600"
                                        >
                                            {{ task.my_score }}
                                        </div>
                                        <p
                                            class="text-[10px] text-gray-400 mt-2"
                                        >
                                            Dinilai oleh Guru
                                        </p>
                                    </div>
                                    <div
                                        v-else
                                        class="bg-yellow-50 p-4 rounded-xl border border-yellow-100"
                                    >
                                        <div class="flex justify-center mb-2">
                                            <div
                                                class="animate-pulse bg-yellow-400 w-3 h-3 rounded-full mx-1"
                                            ></div>
                                            <div
                                                class="animate-pulse bg-yellow-400 w-3 h-3 rounded-full mx-1 delay-75"
                                            ></div>
                                            <div
                                                class="animate-pulse bg-yellow-400 w-3 h-3 rounded-full mx-1 delay-150"
                                            ></div>
                                        </div>
                                        <p
                                            class="text-sm font-bold text-yellow-700"
                                        >
                                            Menunggu Penilaian
                                        </p>
                                        <p class="text-xs text-yellow-600 mt-1">
                                            Tugas sudah dikirim.
                                        </p>
                                    </div>
                                </div>

                                <div v-else>
                                    <button
                                        @click="openSubmitModal(task)"
                                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-200 transition transform active:scale-95 flex items-center justify-center gap-2"
                                    >
                                        <ArrowUpTrayIcon class="w-5 h-5" />
                                        Kumpulkan Tugas
                                    </button>
                                    <p
                                        class="text-xs text-center text-red-500 mt-3 font-medium"
                                    >
                                        Segera kerjakan sebelum deadline!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 transition-opacity"
            @click="showModal = false"
        >
            <div
                class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all scale-100"
                @click.stop
            >
                <div
                    class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center"
                >
                    <h3 class="font-bold text-gray-800">Kirim Jawaban</h3>
                    <button
                        @click="showModal = false"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <p
                            class="text-xs font-bold text-gray-500 uppercase mb-1"
                        >
                            Judul Tugas
                        </p>
                        <p class="text-sm font-medium text-gray-800">
                            {{ selectedTask?.title }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-bold text-gray-700 mb-2"
                            >Upload File (Foto/PDF)</label
                        >
                        <div
                            class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:bg-blue-50 hover:border-blue-300 transition cursor-pointer relative group"
                        >
                            <input
                                @input="form.file = $event.target.files[0]"
                                type="file"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            />

                            <div
                                v-if="form.file"
                                class="flex flex-col items-center"
                            >
                                <DocumentTextIcon
                                    class="w-10 h-10 text-blue-600 mb-2"
                                />
                                <p
                                    class="text-sm font-bold text-blue-600 truncate max-w-[200px]"
                                >
                                    {{ form.file.name }}
                                </p>
                                <p class="text-xs text-green-600">
                                    Siap diupload
                                </p>
                            </div>
                            <div v-else class="flex flex-col items-center">
                                <ArrowUpTrayIcon
                                    class="w-10 h-10 text-gray-400 group-hover:text-blue-500 mb-2 transition"
                                />
                                <p class="text-sm text-gray-600 font-medium">
                                    Klik untuk pilih file
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    Maksimal 5MB
                                </p>
                            </div>
                        </div>
                        <p
                            v-if="form.errors.file"
                            class="text-red-500 text-xs mt-2"
                        >
                            {{ form.errors.file }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-bold text-gray-700 mb-2"
                            >Catatan (Opsional)</label
                        >
                        <textarea
                            v-model="form.notes"
                            rows="3"
                            class="w-full rounded-xl border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Tambahkan pesan untuk guru..."
                        ></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3">
                    <button
                        @click="showModal = false"
                        class="px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-200 rounded-lg transition"
                    >
                        Batal
                    </button>
                    <button
                        @click="submitAssignment"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 shadow-md disabled:opacity-50 flex items-center gap-2 transition"
                    >
                        <span
                            v-if="form.processing"
                            class="animate-spin border-2 border-white border-t-transparent rounded-full w-4 h-4"
                        ></span>
                        {{ form.processing ? "Mengirim..." : "Kirim Sekarang" }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
