<script setup>
import { Link } from "@inertiajs/vue3";
import {
    ArrowRightIcon,
    ClipboardDocumentListIcon,
    ClockIcon,
    UserGroupIcon,
} from "@heroicons/vue/24/solid";

defineProps({ assignments: Array });

// Helper untuk menghitung persentase
const getPercentage = (submitted, total) => {
    if (!total || total === 0) return 0;
    return Math.round((submitted / total) * 100);
};

// Helper warna bar berdasarkan progress
const getProgressColor = (percent) => {
    if (percent === 100) return "bg-emerald-500";
    if (percent >= 50) return "bg-indigo-500";
    return "bg-amber-500";
};
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Link
            v-for="task in assignments"
            :key="task.id"
            :href="route('teacher.tasks.show', task.id)"
            class="bg-white rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group relative overflow-hidden flex flex-col h-full"
        >
            <div
                class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-indigo-50/50 rounded-full blur-2xl group-hover:bg-indigo-100/60 transition-all"
            ></div>
            <div
                class="absolute bottom-0 left-0 -mb-4 -ml-4 w-24 h-24 bg-orange-50/50 rounded-full blur-2xl group-hover:bg-orange-100/60 transition-all"
            ></div>

            <svg
                class="absolute right-0 top-0 w-32 h-32 text-slate-50 transform translate-x-8 -translate-y-8 group-hover:text-indigo-50/80 transition-colors duration-500"
                fill="currentColor"
                viewBox="0 0 200 200"
            >
                <path
                    d="M45,-76.3C58.9,-69.3,71.4,-59.1,81.4,-46.8C91.4,-34.4,98.9,-20,98.1,-5.9C97.3,8.1,88.2,21.9,78.2,34.6C68.2,47.3,57.3,59,44.7,67.6C32.1,76.2,17.8,81.7,2.8,76.8C-12.2,71.9,-27.9,56.6,-41.6,44.2C-55.3,31.8,-66.9,22.3,-73.9,9.5C-80.9,-3.3,-83.3,-19.4,-77.1,-33.3C-70.9,-47.2,-56.1,-58.9,-41.3,-65.5C-26.5,-72.1,-11.7,-73.6,1.8,-76.7C15.3,-79.8,31.1,-83.3,45,-76.3Z"
                    transform="translate(100 100)"
                />
            </svg>

            <div class="relative z-10 p-7 flex flex-col h-full">
                <div class="flex justify-between items-start mb-4">
                    <div
                        class="p-3.5 bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl text-orange-600 shadow-sm border border-orange-50 group-hover:scale-110 transition-transform duration-300"
                    >
                        <ClipboardDocumentListIcon class="w-7 h-7" />
                    </div>
                    <span
                        class="px-3 py-1 rounded-full bg-slate-50 border border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider group-hover:bg-white group-hover:shadow-sm transition-all"
                    >
                        {{ task.status }}
                    </span>
                </div>

                <div class="mb-6 flex-grow">
                    <h4
                        class="font-bold text-slate-800 text-lg mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors leading-snug"
                    >
                        {{ task.title }}
                    </h4>
                    <div
                        class="flex items-center gap-1.5 text-xs font-medium text-slate-400 bg-slate-50 w-fit px-2 py-1 rounded-lg border border-slate-100"
                    >
                        <ClockIcon class="w-3.5 h-3.5" />
                        Deadline: {{ task.deadline }}
                    </div>
                </div>

                <div class="mt-auto">
                    <div class="flex justify-between items-end mb-2">
                        <div class="flex flex-col">
                            <span
                                class="text-[10px] uppercase font-bold text-slate-400 tracking-wider"
                                >Dikumpulkan</span
                            >
                            <div
                                class="flex items-center gap-1 text-sm font-bold text-slate-700"
                            >
                                <UserGroupIcon class="w-4 h-4 text-slate-400" />
                                <span class="text-indigo-600">{{
                                    task.submitted
                                }}</span>
                                <span class="text-slate-300">/</span>
                                <span>{{ task.total }} Siswa</span>
                            </div>
                        </div>
                        <span
                            class="text-xs font-black text-slate-300 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-100"
                        >
                            {{ getPercentage(task.submitted, task.total) }}%
                        </span>
                    </div>

                    <div
                        class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden border border-slate-100"
                    >
                        <div
                            class="h-full rounded-full transition-all duration-1000 ease-out relative overflow-hidden"
                            :class="
                                getProgressColor(
                                    getPercentage(task.submitted, task.total)
                                )
                            "
                            :style="{
                                width:
                                    getPercentage(task.submitted, task.total) +
                                    '%',
                            }"
                        >
                            <div
                                class="absolute inset-0 bg-white/20 w-full h-full animate-pulse"
                            ></div>
                        </div>
                    </div>
                </div>

                <div
                    class="absolute top-7 right-7 opacity-0 -translate-x-4 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300 ease-out"
                >
                    <ArrowRightIcon class="w-5 h-5 text-indigo-400" />
                </div>
            </div>
        </Link>

        <div
            v-if="assignments.length === 0"
            class="col-span-full py-16 text-center text-slate-400 bg-white rounded-[2rem] border-2 border-dashed border-slate-200 flex flex-col items-center justify-center gap-2"
        >
            <ClipboardDocumentListIcon class="w-12 h-12 text-slate-200" />
            <p class="font-medium">Belum ada tugas yang dibuat.</p>
        </div>
    </div>
</template>
