<script setup>
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import {
    XMarkIcon,
    ArrowDownTrayIcon,
    DocumentTextIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    data: Object, // Berisi { task, submission }
});

const emit = defineEmits(["close"]);

const form = useForm({
    score: props.data?.submission?.score || "",
});

const submitGrade = () => {
    form.post(route("tasks.grade", props.data.submission.id), {
        preserveScroll: true,
        onSuccess: () => emit("close"),
    });
};

// Helper Preview
const fileUrl = computed(() =>
    props.data?.submission?.file_path
        ? `/storage/${props.data.submission.file_path}`
        : null
);
const fileExt = computed(() =>
    fileUrl.value ? fileUrl.value.split(".").pop().toLowerCase() : ""
);
const isImage = computed(() =>
    ["jpg", "jpeg", "png", "webp"].includes(fileExt.value)
);
const isPdf = computed(() => ["pdf"].includes(fileExt.value));
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm p-4"
        @click="$emit('close')"
    >
        <div
            class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[85vh] flex flex-col md:flex-row overflow-hidden"
            @click.stop
        >
            <div
                class="md:w-3/4 bg-gray-100 flex items-center justify-center relative border-r border-gray-200"
            >
                <div
                    v-if="!fileUrl"
                    class="text-gray-400 flex flex-col items-center"
                >
                    <DocumentTextIcon class="w-16 h-16 opacity-30" />
                    <p>Siswa tidak melampirkan file.</p>
                </div>

                <div
                    v-else-if="isImage"
                    class="w-full h-full overflow-auto flex items-center justify-center p-4"
                >
                    <img
                        :src="fileUrl"
                        class="max-w-full max-h-full shadow-lg rounded"
                    />
                </div>

                <iframe
                    v-else-if="isPdf"
                    :src="fileUrl"
                    class="w-full h-full"
                ></iframe>

                <div v-else class="text-center p-10">
                    <p class="text-gray-500 mb-4">
                        Preview tidak tersedia untuk format .{{ fileExt }}
                    </p>
                    <a
                        :href="fileUrl"
                        download
                        class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold flex items-center gap-2 mx-auto w-fit hover:bg-blue-700"
                    >
                        <ArrowDownTrayIcon class="w-5 h-5" /> Download File
                    </a>
                </div>
            </div>

            <div class="md:w-1/4 flex flex-col bg-white">
                <div
                    class="p-5 border-b border-gray-100 flex justify-between items-start bg-gray-50"
                >
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">
                            {{ data.submission.student.name }}
                        </h3>
                        <p class="text-xs text-gray-500 truncate max-w-[200px]">
                            {{ data.task.title }}
                        </p>
                    </div>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-red-500"
                    >
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <div class="p-6 flex-grow overflow-y-auto">
                    <div class="mb-6">
                        <label
                            class="block text-xs font-bold text-gray-500 uppercase mb-2"
                            >Catatan Siswa</label
                        >
                        <div
                            class="bg-blue-50 p-3 rounded-lg text-sm text-gray-700 italic border border-blue-100 min-h-[60px]"
                        >
                            "{{ data.submission.notes || "-" }}"
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-bold text-gray-800 mb-2"
                            >Input Nilai (0-100)</label
                        >
                        <div class="relative">
                            <input
                                type="number"
                                v-model="form.score"
                                min="0"
                                max="100"
                                class="w-full text-center text-4xl font-extrabold border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:ring-purple-500 py-4 text-purple-700 placeholder-gray-200"
                                placeholder="0"
                            />
                        </div>
                        <p
                            v-if="form.errors.score"
                            class="text-red-500 text-xs mt-1 text-center"
                        >
                            {{ form.errors.score }}
                        </p>
                    </div>
                </div>

                <div class="p-5 border-t border-gray-100 bg-gray-50">
                    <button
                        @click="submitGrade"
                        :disabled="form.processing"
                        class="w-full bg-purple-600 text-white py-3 rounded-xl font-bold hover:bg-purple-700 transition disabled:opacity-50 flex justify-center items-center gap-2 shadow-lg transform active:scale-95"
                    >
                        <span
                            v-if="form.processing"
                            class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"
                        ></span>
                        Simpan Nilai
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
