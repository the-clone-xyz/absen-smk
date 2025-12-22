<script setup>
import { Link } from "@inertiajs/vue3"; // Import Link Inertia
import {
    ClipboardDocumentListIcon,
    PlusIcon,
    ClockIcon,
    UserGroupIcon,
    DocumentTextIcon,
    ChevronRightIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({ tasks: Array });
const emit = defineEmits(["create"]);

const formatDate = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleDateString("id-ID", {
        day: "numeric",
        month: "short",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <div
        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-full"
    >
        <div
            class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-white flex justify-between items-center"
        >
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <ClipboardDocumentListIcon class="w-5 h-5 text-purple-600" />
                Daftar Tugas
            </h3>
            <button
                @click="$emit('create')"
                class="bg-purple-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-purple-700 flex items-center gap-1 shadow-sm transition hover:shadow-md transform active:scale-95"
            >
                <PlusIcon class="w-3 h-3" /> Tugas Baru
            </button>
        </div>

        <div
            class="p-4 space-y-3 overflow-y-auto flex-grow custom-scrollbar bg-gray-50/30"
        >
            <div
                v-if="tasks.length === 0"
                class="flex flex-col items-center justify-center h-40 text-gray-400 border-2 border-dashed border-gray-200 rounded-xl"
            >
                <ClipboardDocumentListIcon class="w-10 h-10 opacity-20 mb-2" />
                <p class="text-sm font-medium">Belum ada tugas aktif</p>
            </div>

            <Link
                v-for="task in tasks"
                :key="task.id"
                :href="route('teacher.tasks.show', task.id)"
                class="group block bg-white p-4 rounded-xl border border-gray-200 hover:border-purple-300 hover:shadow-md transition relative overflow-hidden"
            >
                <div
                    class="absolute left-0 top-0 bottom-0 w-1 bg-purple-500 opacity-0 group-hover:opacity-100 transition"
                ></div>

                <div class="flex justify-between items-start">
                    <div class="flex-grow pr-4">
                        <div class="flex items-center gap-2 mb-1">
                            <h4
                                class="font-bold text-gray-800 text-sm group-hover:text-purple-700 transition"
                            >
                                {{ task.title }}
                            </h4>
                            <span
                                v-if="task.file_path"
                                class="bg-gray-100 text-gray-500 p-0.5 rounded"
                                title="Ada Lampiran"
                            >
                                <DocumentTextIcon class="w-3 h-3" />
                            </span>
                        </div>

                        <p
                            class="text-xs text-gray-500 line-clamp-2 mb-3 leading-relaxed"
                        >
                            {{ task.description }}
                        </p>

                        <div class="flex items-center gap-3 flex-wrap">
                            <div
                                class="flex items-center gap-1 bg-red-50 text-red-600 px-2 py-1 rounded-md border border-red-100"
                            >
                                <ClockIcon class="w-3 h-3" />
                                <span class="text-[10px] font-bold">{{
                                    formatDate(task.deadline)
                                }}</span>
                            </div>

                            <div
                                class="flex items-center gap-1 bg-blue-50 text-blue-600 px-2 py-1 rounded-md border border-blue-100"
                            >
                                <UserGroupIcon class="w-3 h-3" />
                                <span class="text-[10px] font-bold"
                                    >{{
                                        task.submissions.length
                                    }}
                                    Mengumpulkan</span
                                >
                            </div>
                        </div>
                    </div>

                    <div
                        class="self-center text-gray-300 group-hover:text-purple-500 transition"
                    >
                        <ChevronRightIcon class="w-5 h-5" />
                    </div>
                </div>
            </Link>
        </div>
    </div>
</template>
