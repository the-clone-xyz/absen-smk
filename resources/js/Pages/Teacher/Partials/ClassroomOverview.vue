<script setup>
import {
    ChartBarIcon,
    InformationCircleIcon,
    AcademicCapIcon,
    BookOpenIcon,
} from "@heroicons/vue/24/solid";

defineProps({ classroom: Object });
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
            class="col-span-2 bg-white rounded-[2rem] p-8 border border-slate-100 shadow-sm relative overflow-hidden group hover:shadow-lg transition-all duration-300"
        >
            <div
                class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-indigo-50 rounded-full blur-3xl opacity-50 group-hover:scale-110 transition-transform duration-700"
            ></div>

            <div class="relative z-10">
                <div class="flex items-center gap-4 mb-8">
                    <div
                        class="p-3 bg-indigo-50 rounded-2xl text-indigo-600 shadow-sm border border-indigo-100"
                    >
                        <ChartBarIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">
                            Statistik Pembelajaran
                        </h3>
                        <p class="text-xs text-slate-500 font-medium">
                            Monitoring progres penyelesaian sesi materi
                        </p>
                    </div>
                </div>

                <div class="flex items-end justify-between mb-3">
                    <div class="flex items-baseline gap-2">
                        <span
                            class="text-5xl font-black text-slate-800 tracking-tighter"
                        >
                            {{ classroom.session_completed }}
                        </span>
                        <span class="text-slate-400 text-lg font-bold">
                            / {{ classroom.session_total }} Sesi
                        </span>
                    </div>
                    <div class="text-right">
                        <span
                            class="text-3xl font-black text-indigo-600 block leading-none"
                        >
                            {{ classroom.progress_percentage }}%
                        </span>
                        <span
                            class="text-[10px] uppercase font-bold text-slate-400 tracking-widest mt-1 block"
                            >Completed</span
                        >
                    </div>
                </div>

                <div
                    class="w-full bg-slate-100 rounded-full h-4 overflow-hidden shadow-inner border border-slate-100"
                >
                    <div
                        class="bg-gradient-to-r from-indigo-500 to-violet-600 h-full rounded-full transition-all duration-1000 ease-out relative"
                        :style="{
                            width: (classroom.progress_percentage || 0) + '%',
                        }"
                    >
                        <div
                            class="absolute inset-0 bg-white/30 w-full h-full animate-[shimmer_2s_infinite] -skew-x-12"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="bg-indigo-600 rounded-[2rem] p-8 text-white shadow-xl shadow-indigo-200 relative overflow-hidden flex flex-col justify-between group hover:-translate-y-1 transition-transform duration-300"
        >
            <svg
                class="absolute top-0 right-0 w-64 h-64 text-white opacity-10 transform translate-x-20 -translate-y-20 group-hover:rotate-12 transition-transform duration-700 ease-in-out"
                fill="currentColor"
                viewBox="0 0 200 200"
            >
                <path
                    d="M42.7,-72.2C56.1,-65.8,68.4,-57.6,77.3,-46.8C86.2,-36,91.7,-22.6,90.4,-9.7C89.1,3.2,81,15.6,71.7,26.1C62.4,36.6,51.9,45.2,41.2,53.2C30.5,61.2,19.6,68.6,7.5,70.9C-4.6,73.2,-17.9,70.4,-29.9,64.2C-41.9,58,-52.6,48.4,-61.8,37C-71,25.6,-78.7,12.4,-78.3,0.3C-77.9,-11.9,-69.4,-22.9,-60.1,-33.4C-50.8,-43.9,-40.7,-53.9,-29.3,-61.5C-17.9,-69.1,-5.2,-74.3,6.6,-73.3C18.4,-72.3,36.8,-65.1,42.7,-72.2Z"
                    transform="translate(100 100)"
                />
            </svg>

            <div
                class="absolute bottom-0 left-0 w-40 h-40 bg-indigo-500 rounded-full blur-3xl opacity-60 -ml-12 -mb-12"
            ></div>

            <div class="relative z-10 space-y-6">
                <div class="flex items-start justify-between">
                    <div
                        class="p-3 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20"
                    >
                        <InformationCircleIcon class="w-6 h-6 text-white" />
                    </div>
                    <div
                        class="px-3 py-1.5 bg-white/10 backdrop-blur-md rounded-lg border border-white/20 text-xs font-bold tracking-wide shadow-sm"
                    >
                        KKM:
                        <span class="text-amber-300">{{ classroom.kkm }}</span>
                    </div>
                </div>

                <div>
                    <h4
                        class="text-3xl font-black mb-2 tracking-tight leading-none text-white drop-shadow-md"
                    >
                        {{ classroom.name }}
                    </h4>
                    <div
                        class="flex items-center gap-2 text-indigo-100 font-medium text-sm bg-indigo-800/30 w-fit px-3 py-1 rounded-full border border-indigo-500/30"
                    >
                        <BookOpenIcon class="w-4 h-4" />
                        {{ classroom.subject }}
                    </div>
                </div>

                <div class="pt-5 border-t border-white/10">
                    <p
                        class="text-indigo-50 text-sm leading-relaxed font-light opacity-90 line-clamp-3"
                    >
                        {{
                            classroom.description ||
                            "Tidak ada deskripsi tambahan untuk kelas ini."
                        }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes shimmer {
    0% {
        transform: translateX(-150%);
    }
    100% {
        transform: translateX(150%);
    }
}
</style>
