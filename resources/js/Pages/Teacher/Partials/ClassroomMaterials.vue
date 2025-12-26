<script setup>
import { Link } from "@inertiajs/vue3";
import {
    DocumentTextIcon,
    ArrowDownTrayIcon,
    EyeIcon,
} from "@heroicons/vue/24/solid";

defineProps({ materials: Array });

// Helper untuk warna ikon berdasarkan tipe file
const getFileTheme = (type) => {
    const ext = type?.toLowerCase() || "";
    if (["pdf"].includes(ext)) {
        return {
            bg: "bg-rose-50",
            text: "text-rose-600",
            border: "border-rose-100",
            gradient: "from-rose-50 to-white",
        };
    }
    if (["doc", "docx"].includes(ext)) {
        return {
            bg: "bg-blue-50",
            text: "text-blue-600",
            border: "border-blue-100",
            gradient: "from-blue-50 to-white",
        };
    }
    if (["ppt", "pptx"].includes(ext)) {
        return {
            bg: "bg-orange-50",
            text: "text-orange-600",
            border: "border-orange-100",
            gradient: "from-orange-50 to-white",
        };
    }
    return {
        bg: "bg-slate-50",
        text: "text-slate-600",
        border: "border-slate-100",
        gradient: "from-slate-50 to-white",
    };
};
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Link
            v-for="file in materials"
            :key="file.id"
            :href="route('viewer.show', { path: file.file_path })"
            class="bg-white rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group relative overflow-hidden flex flex-col h-full"
        >
            <div
                class="absolute top-0 right-0 -mt-8 -mr-8 w-40 h-40 bg-slate-50 rounded-full blur-3xl group-hover:bg-indigo-50/50 transition-colors duration-500"
            ></div>

            <svg
                class="absolute right-0 top-0 w-24 h-24 text-slate-100 transform translate-x-4 -translate-y-4 group-hover:text-indigo-50 transition-colors duration-500"
                fill="currentColor"
                viewBox="0 0 100 100"
            >
                <path d="M0 0 L100 0 L100 100 Z" opacity="0.5" />
            </svg>

            <div class="relative z-10 p-7 flex flex-col h-full">
                <div class="flex justify-between items-start mb-5">
                    <div
                        class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm border bg-gradient-to-br transition-transform group-hover:scale-110 duration-300"
                        :class="[
                            getFileTheme(file.type).bg,
                            getFileTheme(file.type).text,
                            getFileTheme(file.type).border,
                            getFileTheme(file.type).gradient,
                        ]"
                    >
                        <DocumentTextIcon class="w-7 h-7" />
                    </div>
                    <span
                        class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border shadow-sm"
                        :class="[
                            getFileTheme(file.type).bg,
                            getFileTheme(file.type).text,
                            getFileTheme(file.type).border,
                        ]"
                    >
                        {{ file.type }}
                    </span>
                </div>

                <div class="flex-grow mb-6">
                    <h4
                        class="font-bold text-slate-800 text-lg mb-2 line-clamp-2 leading-snug group-hover:text-indigo-600 transition-colors"
                    >
                        {{ file.title }}
                    </h4>
                    <p class="text-xs text-slate-400 flex items-center gap-1">
                        Diunggah: {{ file.date }}
                    </p>
                </div>

                <div class="flex gap-3 mt-auto pt-5 border-t border-slate-50">
                    <button
                        class="flex-1 bg-slate-50 border border-slate-200 text-slate-600 py-2.5 rounded-xl text-xs font-bold hover:bg-white hover:shadow-md hover:border-slate-300 transition-all flex items-center justify-center gap-2"
                    >
                        <EyeIcon class="w-3.5 h-3.5" /> Lihat
                    </button>
                    <a
                        :href="
                            route('viewer.download', { path: file.file_path })
                        "
                        @click.stop
                        class="flex-1 bg-indigo-600 border border-indigo-600 text-white py-2.5 rounded-xl text-xs font-bold hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-200 transition-all flex items-center justify-center gap-2"
                    >
                        <ArrowDownTrayIcon class="w-3.5 h-3.5" /> Unduh
                    </a>
                </div>
            </div>
        </Link>

        <div
            v-if="materials.length === 0"
            class="col-span-full py-16 text-center text-slate-400 bg-white rounded-[2rem] border-2 border-dashed border-slate-200 flex flex-col items-center justify-center gap-3"
        >
            <div class="p-4 bg-slate-50 rounded-full">
                <DocumentTextIcon class="w-8 h-8 text-slate-300" />
            </div>
            <p class="font-medium">Belum ada materi pelajaran yang diunggah.</p>
        </div>
    </div>
</template>
