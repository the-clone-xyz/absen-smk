<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    tasks: Array,
    kelas_list: Array,
    subjects: Array,
});

const showModal = ref(false);
const form = useForm({
    title: "",
    description: "",
    kelas_id: "",
    subject_id: "",
    deadline: "",
    file: null,
});

const submitTask = () => {
    form.post(route("tasks.store"), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Manajemen Tugas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800">Tugas Saya</h2>
                <button
                    @click="showModal = true"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-700"
                >
                    + Buat Tugas Baru
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div
                    v-for="task in tasks"
                    :key="task.id"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500"
                >
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">
                                {{ task.title }}
                            </h3>
                            <div class="text-sm text-gray-500 flex gap-3 mt-1">
                                <span
                                    class="bg-gray-100 px-2 py-0.5 rounded text-gray-600"
                                    >{{ task.subject.name }}</span
                                >
                                <span
                                    class="bg-gray-100 px-2 py-0.5 rounded text-gray-600"
                                    >{{ task.kelas.name }}</span
                                >
                            </div>
                            <p
                                class="mt-3 text-gray-700 text-sm whitespace-pre-wrap"
                            >
                                {{ task.description }}
                            </p>

                            <div v-if="task.attachment" class="mt-3">
                                <a
                                    :href="`/storage/${task.attachment}`"
                                    target="_blank"
                                    class="text-blue-600 text-sm underline flex items-center gap-1"
                                >
                                    📎 Download Lampiran Soal
                                </a>
                            </div>
                        </div>
                        <div class="text-right">
                            <div
                                class="text-xs text-red-500 font-bold uppercase"
                            >
                                Deadline
                            </div>
                            <div class="text-sm font-mono">
                                {{ new Date(task.deadline).toLocaleString() }}
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="tasks.length === 0"
                    class="text-center text-gray-400 py-10"
                >
                    Belum ada tugas yang dibuat.
                </div>
            </div>
        </div>

        <div
            v-if="showModal"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-bold mb-4">Buat Tugas Baru</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Judul Tugas</label
                        >
                        <input
                            v-model="form.title"
                            type="text"
                            class="w-full rounded-md border-gray-300 mt-1"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Mapel</label
                            >
                            <select
                                v-model="form.subject_id"
                                class="w-full rounded-md border-gray-300 mt-1"
                            >
                                <option
                                    v-for="s in subjects"
                                    :value="s.id"
                                    :key="s.id"
                                >
                                    {{ s.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Kelas</label
                            >
                            <select
                                v-model="form.kelas_id"
                                class="w-full rounded-md border-gray-300 mt-1"
                            >
                                <option
                                    v-for="k in kelas_list"
                                    :value="k.id"
                                    :key="k.id"
                                >
                                    {{ k.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Deskripsi / Soal</label
                        >
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-md border-gray-300 mt-1"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >Deadline Pengumpulan</label
                        >
                        <input
                            v-model="form.deadline"
                            type="datetime-local"
                            class="w-full rounded-md border-gray-300 mt-1"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            >File Lampiran (PDF/Gambar - Opsional)</label
                        >
                        <input
                            @input="form.file = $event.target.files[0]"
                            type="file"
                            class="w-full mt-1 text-sm border p-2 rounded"
                        />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        @click="showModal = false"
                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg"
                    >
                        Batal
                    </button>
                    <button
                        @click="submitTask"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        {{ form.processing ? "Menyimpan..." : "Simpan Tugas" }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
