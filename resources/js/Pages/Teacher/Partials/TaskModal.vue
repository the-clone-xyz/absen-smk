<script setup>
import { useForm } from "@inertiajs/vue3";
import { XMarkIcon } from "@heroicons/vue/24/solid";
import { watch } from "vue";

const props = defineProps({
    task: Object,
    schedule: Object, // Menerima data jadwal dari parent
    show: Boolean,
});

const emit = defineEmits(["close"]);

const form = useForm({
    title: "",
    description: "",
    deadline: "",
    file: null,
    kelas_id: "",
    subject_id: "",
});

// Watcher: Reset form setiap kali modal dibuka atau data berubah
watch(
    () => props.show,
    (isOpen) => {
        if (isOpen) {
            if (props.task) {
                // MODE EDIT
                form.title = props.task.title;
                form.description = props.task.description;
                form.deadline = new Date(props.task.deadline)
                    .toISOString()
                    .slice(0, 16);
                form.kelas_id = props.task.kelas_id;
                form.subject_id = props.task.subject_id;
            } else {
                // MODE BUAT BARU (AUTO FILL)
                form.reset();
                if (props.schedule) {
                    // INI PERBAIKANNYA: Ambil ID dari Jadwal yang sedang aktif
                    // Akses via relasi: schedule.class.id atau schedule.kelas_id (tergantung nama kolom DB)
                    form.kelas_id =
                        props.schedule.kelas_id || props.schedule.class.id;
                    form.subject_id =
                        props.schedule.subject_id || props.schedule.subject.id;
                }
            }
        }
    }
);

const submit = () => {
    if (props.task) {
        form.post(route("tasks.update", props.task.id), {
            onSuccess: () => emit("close"),
        });
    } else {
        form.post(route("tasks.store"), { onSuccess: () => emit("close") });
    }
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
    >
        <div
            class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden"
        >
            <div
                class="bg-purple-50 px-6 py-4 border-b border-purple-100 flex justify-between items-center"
            >
                <h3 class="font-bold text-gray-800">
                    {{ task ? "Edit Tugas" : "Buat Tugas Baru" }}
                </h3>
                <button
                    @click="$emit('close')"
                    class="text-gray-400 hover:text-red-500"
                >
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label
                        class="block text-xs font-bold text-gray-600 uppercase mb-1"
                        >Judul Tugas</label
                    >
                    <input
                        v-model="form.title"
                        type="text"
                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-purple-500"
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
                        class="w-full rounded-lg border-gray-300 text-sm focus:ring-purple-500"
                    ></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
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
                            >Lampiran</label
                        >
                        <input
                            @input="form.file = $event.target.files[0]"
                            type="file"
                            class="w-full text-xs text-gray-500 border rounded-lg p-2 bg-gray-50"
                        />
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-2">
                <button
                    @click="$emit('close')"
                    type="button"
                    class="px-4 py-2 text-gray-600 font-bold hover:bg-gray-200 rounded-lg text-sm"
                >
                    Batal
                </button>
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 bg-purple-600 text-white font-bold rounded-lg hover:bg-purple-700 text-sm"
                >
                    {{ form.processing ? "Menyimpan..." : "Simpan Tugas" }}
                </button>
            </div>
        </div>
    </div>
</template>
