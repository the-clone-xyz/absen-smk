<script setup>
import { ref } from "vue";
import {
    ClipboardDocumentListIcon,
    ChevronDownIcon,
    PlusIcon,
    ClockIcon,
    UserGroupIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({ tasks: Array });
const emit = defineEmits(["create", "edit", "grade"]); // Kita siapkan emit untuk tahap selanjutnya

// Helper Format Tanggal
const formatDate = (dateString) => {
    if (!dateString) return "-";
    return new Date(dateString).toLocaleDateString("id-ID", {
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
            class="px-5 py-4 border-b border-gray-200 bg-purple-50 flex justify-between items-center sticky top-0 z-10"
        >
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <ClipboardDocumentListIcon class="w-5 h-5 text-purple-600" />
                Tugas Kelas
            </h3>
            <button
                @click="$emit('create')"
                class="bg-purple-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-purple-700 flex items-center gap-1 shadow-sm transition"
            >
                <PlusIcon class="w-3 h-3" /> Buat Tugas
            </button>
        </div>

        <div
            class="divide-y divide-gray-100 overflow-y-auto flex-grow custom-scrollbar bg-gray-50/30"
        >
            <div
                v-if="tasks.length === 0"
                class="p-10 text-center flex flex-col items-center justify-center text-gray-400"
            >
                <div class="bg-gray-100 p-4 rounded-full mb-3">
                    <ClipboardDocumentListIcon class="w-8 h-8 opacity-30" />
                </div>
                <p class="text-sm font-medium">Belum ada tugas.</p>
            </div>

            <div
                v-for="task in tasks"
                :key="task.id"
                class="bg-white p-4 hover:bg-purple-50 transition border-b border-gray-100 cursor-pointer"
            >
                <div class="flex justify-between items-start">
                    <div class="flex-grow">
                        <h4 class="font-bold text-gray-800 text-sm mb-1">
                            {{ task.title }}
                        </h4>
                        <p class="text-xs text-gray-500 line-clamp-2 mb-2">
                            {{ task.description }}
                        </p>

                        <div class="flex items-center gap-2">
                            <span
                                class="text-[10px] bg-red-50 text-red-600 px-2 py-0.5 rounded border border-red-100 flex items-center gap-1 font-medium"
                            >
                                <ClockIcon class="w-3 h-3" />
                                {{ formatDate(task.deadline) }}
                            </span>
                            <span
                                class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded border border-blue-100 flex items-center gap-1 font-medium"
                            >
                                <UserGroupIcon class="w-3 h-3" />
                                {{ task.submissions.length }} Dikumpul
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
