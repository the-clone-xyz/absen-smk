<script setup>
import { useForm } from "@inertiajs/vue3";
import { XMarkIcon, ExclamationTriangleIcon } from "@heroicons/vue/24/solid";
import { watch } from "vue";

const props = defineProps({
    task: Object,
    schedule: Object,
    show: Boolean,
});

const emit = defineEmits(["close"]);

const form = useForm({
    title: "",
    description: "",
    deadline: "",
    file: null, // Sesuai dengan Controller ('file')
    kelas_id: "",
    subject_id: "",
});

// Helper: Format tanggal DB (UTC) ke Input HTML (Lokal)
const formatToLocalInput = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    const offset = date.getTimezoneOffset() * 60000;
    return new Date(date.getTime() - offset).toISOString().slice(0, 16);
};

// Watcher: Reset & Isi Form
watch(
    () => props.show,
    (isOpen) => {
        if (isOpen) {
            form.clearErrors(); // Hapus error lama

            if (props.task) {
                // --- MODE EDIT ---
                form.title = props.task.title;
                form.description = props.task.description;
                form.deadline = formatToLocalInput(props.task.deadline);
                // Ambil ID Kelas & Mapel dari data tugas
                form.kelas_id =
                    props.task.kelas_id || props.task.class_id || "";
                form.subject_id = props.task.subject_id || "";

                form.file = null; // Reset input file saat edit
            } else {
                // --- MODE BARU ---
                form.reset();
                form.file = null;

                // Auto-fill dari Jadwal (Schedule)
                if (props.schedule) {
                    // Logika Robust: Cek berbagai kemungkinan nama field ID Kelas
                    form.kelas_id =
                        props.schedule.class_id ||
                        props.schedule.kelas_id ||
                        props.schedule.class?.id ||
                        "";

                    form.subject_id =
                        props.schedule.subject_id ||
                        props.schedule.subject?.id ||
                        "";
                }
            }
        }
    }
);

const submit = () => {
    if (props.task) {
        // --- UPDATE ---
        form.post(route("tasks.update", props.task.id), {
            forceFormData: true, // WAJIB: Agar file terkirim
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                emit("close");
            },
        });
    } else {
        // --- CREATE ---
        form.post(route("tasks.store"), {
            forceFormData: true, // WAJIB: Agar file terkirim
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                emit("close");
            },
        });
    }
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 transition-opacity"
    >
        <div
            class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden animate-fade-in-up"
        >
            <div
                class="bg-purple-50 px-6 py-4 border-b border-purple-100 flex justify-between items-center"
            >
                <h3 class="font-bold text-gray-800 text-lg">
                    {{ task ? "Edit Tugas" : "Buat Tugas Baru" }}
                </h3>
                <button
                    @click="$emit('close')"
                    class="text-gray-400 hover:text-red-500 transition"
                >
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div
                    v-if="Object.keys(form.errors).length > 0"
                    class="bg-red-50 p-3 rounded-lg border border-red-200 mb-2 flex items-start gap-2"
                >
                    <ExclamationTriangleIcon
                        class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"
                    />
                    <div>
                        <p class="text-red-800 font-bold text-xs mb-1">
                            Gagal Menyimpan:
                        </p>
                        <ul class="list-disc list-inside text-red-600 text-xs">
                            <li v-for="(error, key) in form.errors" :key="key">
                                {{ error }}
                            </li>
                        </ul>
                    </div>
                </div>

                <div>
                    <label
                        class="block text-xs font-bold text-gray-600 uppercase mb-1"
                        >Judul Tugas</label
                    >
                    <input
                        v-model="form.title"
                        type="text"
                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Contoh: Latihan Soal Bab 1"
                    />
                </div>

                <div>
                    <label
                        class="block text-xs font-bold text-gray-600 uppercase mb-1"
                        >Soal / Instruksi</label
                    >
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Tulis detail tugas disini..."
                    ></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-xs font-bold text-gray-600 uppercase mb-1"
                            >Deadline</label
                        >
                        <input
                            v-model="form.deadline"
                            type="datetime-local"
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-purple-500"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-gray-600 uppercase mb-1"
                        >
                            Lampiran
                            <span
                                v-if="task"
                                class="text-gray-400 font-normal lowercase"
                                >(opsional saat edit)</span
                            >
                        </label>
                        <input
                            @input="form.file = $event.target.files[0]"
                            type="file"
                            class="w-full text-xs text-gray-500 border border-gray-300 rounded-lg p-1.5 bg-gray-50 file:mr-4 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200"
                        />
                        <div
                            v-if="form.errors.file"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ form.errors.file }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-2 border-t">
                <button
                    @click="$emit('close')"
                    type="button"
                    class="px-4 py-2 text-gray-600 font-bold hover:bg-gray-200 rounded-lg text-sm transition"
                >
                    Batal
                </button>
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 bg-purple-600 text-white font-bold rounded-lg hover:bg-purple-700 text-sm transition flex items-center gap-2 disabled:opacity-50"
                >
                    <span
                        v-if="form.processing"
                        class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"
                    ></span>
                    {{
                        form.processing
                            ? "Menyimpan..."
                            : task
                            ? "Update Tugas"
                            : "Simpan Tugas"
                    }}
                </button>
            </div>
        </div>
    </div>
</template>
