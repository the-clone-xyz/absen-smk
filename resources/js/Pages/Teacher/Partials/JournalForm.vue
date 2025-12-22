<script setup>
import { Link } from "@inertiajs/vue3"; // Penting: Import Link
import {
    BookOpenIcon,
    PaperClipIcon,
    XMarkIcon,
    ExclamationCircleIcon,
    DocumentTextIcon,
} from "@heroicons/vue/24/solid";
import { ref, computed } from "vue";

const props = defineProps({
    form: Object,
    existingFile: String,
});

const emit = defineEmits(["file-error"]);
const fileError = ref(null);

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    props.form.module = null;
    fileError.value = null;
    emit("file-error", false);

    if (file) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            fileError.value = `Ukuran file terlalu besar! (Maks 10MB)`;
            event.target.value = "";
            emit("file-error", true);
        } else {
            props.form.module = file;
        }
    }
};

const removeFile = () => {
    props.form.module = null;
    fileError.value = null;
    emit("file-error", false);
};

const hasExistingFile = computed(
    () => !!props.existingFile && !props.form.module
);
</script>

<template>
    <div
        class="h-full flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
    >
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <BookOpenIcon class="w-5 h-5 text-indigo-600" /> Jurnal Materi
            </h3>
        </div>

        <div class="p-5 space-y-5 flex-grow overflow-y-auto">
            <div>
                <label
                    class="block text-xs font-bold text-gray-500 uppercase mb-1"
                >
                    Topik Pembelajaran <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.topic"
                    type="text"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    required
                />
            </div>

            <div>
                <label
                    class="block text-xs font-bold text-gray-500 uppercase mb-1"
                >
                    Tujuan Pembelajaran <span class="text-red-500">*</span>
                </label>
                <textarea
                    v-model="form.notes"
                    rows="8"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    required
                ></textarea>
            </div>

            <div>
                <label
                    class="block text-xs font-bold text-gray-500 uppercase mb-1"
                >
                    Upload Bahan Ajar (Opsional)
                </label>

                <div
                    v-if="hasExistingFile"
                    class="mb-2 bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-center justify-between transition hover:bg-blue-100"
                >
                    <div
                        class="flex items-center gap-2 text-blue-700 overflow-hidden"
                    >
                        <DocumentTextIcon class="w-5 h-5 flex-shrink-0" />
                        <div class="flex flex-col">
                            <span
                                class="text-[10px] text-blue-500 font-bold uppercase"
                                >File Tersimpan</span
                            >

                            <Link
                                :href="
                                    route('viewer.show', { path: existingFile })
                                "
                                class="text-xs font-bold underline truncate hover:text-blue-900 cursor-pointer"
                            >
                                Buka / Baca File
                            </Link>
                        </div>
                    </div>
                    <span class="text-[10px] text-gray-400 italic"
                        >Upload baru untuk mengganti</span
                    >
                </div>

                <div
                    class="border-2 border-dashed rounded-xl p-4 text-center transition relative group cursor-pointer"
                    :class="
                        fileError
                            ? 'border-red-300 bg-red-50'
                            : 'border-gray-300 hover:bg-indigo-50 hover:border-indigo-300'
                    "
                >
                    <input
                        @change="handleFileUpload"
                        type="file"
                        accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                        :disabled="form.processing"
                    />

                    <div
                        v-if="form.module"
                        class="flex items-center justify-center gap-2 text-indigo-600 relative z-20"
                    >
                        <PaperClipIcon class="w-5 h-5" />
                        <span
                            class="text-sm font-bold truncate max-w-[150px]"
                            >{{ form.module.name }}</span
                        >
                        <button
                            @click.prevent.stop="removeFile"
                            class="text-red-500 hover:bg-red-100 rounded-full p-1 transition"
                        >
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>

                    <div
                        v-else-if="fileError"
                        class="text-red-600 flex flex-col items-center justify-center relative z-20"
                    >
                        <ExclamationCircleIcon class="w-6 h-6 mb-1" />
                        <span class="text-xs font-bold">{{ fileError }}</span>
                    </div>

                    <div v-else class="relative z-0">
                        <p
                            class="text-sm text-gray-500 group-hover:text-indigo-600 font-medium"
                        >
                            {{
                                hasExistingFile
                                    ? "Klik untuk ganti file"
                                    : "Klik untuk upload file"
                            }}
                        </p>
                        <p class="text-[10px] text-gray-400">
                            PDF, PPT, DOCX (Max 10MB)
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
