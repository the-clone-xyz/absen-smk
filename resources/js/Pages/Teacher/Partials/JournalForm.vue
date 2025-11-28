<script setup>
import {
    BookOpenIcon,
    DocumentArrowUpIcon,
    CheckCircleIcon,
    PencilSquareIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    form: Object, // Terima object form dari parent
    isEditing: Boolean,
    existingJournal: Object,
    summary: Object, // Terima data rekap H/S/I/A
});

const submit = () => {
    props.form.post(route("teacher.journal.store"), { preserveScroll: true });
};
</script>

<template>
    <div
        class="bg-white p-6 rounded-xl shadow-sm border border-blue-100 sticky top-24"
    >
        <h3
            class="font-bold text-gray-800 mb-4 flex items-center gap-2 pb-3 border-b"
        >
            <BookOpenIcon class="w-5 h-5 text-blue-600" /> Jurnal Mengajar
        </h3>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label
                    class="block text-xs font-bold text-gray-500 uppercase mb-1"
                    >Materi / Topik</label
                >
                <input
                    v-model="form.topic"
                    type="text"
                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 transition"
                    placeholder="Contoh: Aljabar Linear"
                    required
                />
            </div>

            <div class="mb-4">
                <label
                    class="block text-xs font-bold text-gray-500 uppercase mb-1"
                    >Catatan Kelas</label
                >
                <textarea
                    v-model="form.notes"
                    rows="3"
                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 transition"
                    placeholder="Catatan kejadian..."
                ></textarea>
            </div>

            <div class="mb-6">
                <label
                    class="block text-xs font-bold text-gray-500 uppercase mb-1"
                    >Upload Modul (Opsional)</label
                >
                <div
                    class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:bg-gray-50 transition relative"
                >
                    <input
                        type="file"
                        @input="form.module = $event.target.files[0]"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png"
                    />
                    <div
                        v-if="form.module"
                        class="text-sm text-green-600 font-bold truncate"
                    >
                        📂 {{ form.module.name }}
                    </div>
                    <div
                        v-else
                        class="text-gray-400 flex flex-col items-center"
                    >
                        <DocumentArrowUpIcon class="w-6 h-6 mb-1" />
                        <span class="text-xs">Klik untuk upload PDF/Doc</span>
                    </div>
                </div>
                <div v-if="existingJournal?.module_path" class="mt-2 text-xs">
                    <a
                        :href="'/storage/' + existingJournal.module_path"
                        target="_blank"
                        class="text-blue-600 hover:underline flex items-center gap-1"
                    >
                        📄 Lihat Modul Saat Ini
                    </a>
                </div>
            </div>

            <div
                class="grid grid-cols-4 gap-2 mb-6 text-center text-xs font-bold"
            >
                <div
                    class="bg-green-100 text-green-700 p-2 rounded border border-green-200"
                >
                    H: {{ summary.h }}
                </div>
                <div
                    class="bg-blue-100 text-blue-700 p-2 rounded border border-blue-200"
                >
                    S: {{ summary.s }}
                </div>
                <div
                    class="bg-yellow-100 text-yellow-700 p-2 rounded border border-yellow-200"
                >
                    I: {{ summary.i }}
                </div>
                <div
                    class="bg-red-100 text-red-700 p-2 rounded border border-red-200"
                >
                    A: {{ summary.a }}
                </div>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full text-white py-3 rounded-xl font-bold shadow-lg transition disabled:opacity-50 flex justify-center items-center gap-2"
                :class="
                    isEditing
                        ? 'bg-yellow-500 hover:bg-yellow-600'
                        : 'bg-green-600 hover:bg-green-700'
                "
            >
                <component
                    :is="isEditing ? PencilSquareIcon : CheckCircleIcon"
                    class="w-5 h-5"
                />
                {{ isEditing ? "Update Jurnal" : "Simpan & Selesai" }}
            </button>
        </form>
    </div>
</template>
