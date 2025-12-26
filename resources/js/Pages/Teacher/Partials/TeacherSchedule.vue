<script setup>
import { Link } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted } from "vue";
import {
    ChevronRightIcon,
    CheckCircleIcon,
    MapPinIcon,
    BookOpenIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({ jadwal: Array });
const now = ref(new Date());
let timerInterval = null;

onMounted(() => {
    timerInterval = setInterval(() => {
        now.value = new Date();
    }, 1000);
});
onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

const getScheduleStatus = (startTimeStr) => {
    if (!startTimeStr) return { urgent: false, text: "" };
    const [hours, minutes] = startTimeStr.split(":");
    const scheduleDate = new Date();
    // Pastikan detik di-reset agar kalkulasi akurat
    scheduleDate.setHours(hours, minutes, 0);

    const diffMs = scheduleDate - now.value;
    const diffMinutes = Math.floor(diffMs / 60000);
    if (diffMinutes >= 0 && diffMinutes < 30)
        return { urgent: true, text: `${diffMinutes} mnt` };
    return { urgent: false, text: "" };
};
</script>

<template>
    <div class="h-full flex flex-col relative bg-white">
        <div class="flex justify-between items-center mb-6 px-1">
            <div>
                <h3 class="font-black text-slate-800 text-lg tracking-tight">
                    Timeline
                </h3>
                <p class="text-xs text-slate-400 font-medium">
                    Agenda hari ini
                </p>
            </div>
            <span
                class="text-[10px] font-extrabold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100 uppercase"
            >
                {{
                    new Date().toLocaleDateString("id-ID", { weekday: "long" })
                }}
            </span>
        </div>

        <div class="flex-grow overflow-y-auto custom-scrollbar pr-2 -mr-2 pb-2">
            <div v-if="jadwal && jadwal.length > 0" class="pl-2 pt-2 relative">
                <div
                    class="absolute left-[27px] top-4 bottom-4 w-0.5 bg-slate-100 z-0"
                ></div>

                <div
                    v-for="(item, index) in jadwal"
                    :key="item.id"
                    class="relative pl-14 pb-8 last:pb-0 group z-10"
                >
                    <span
                        class="absolute left-0 top-3 text-[10px] font-bold text-slate-400 w-10 text-right font-mono"
                    >
                        {{ item.start_time.substring(0, 5) }}
                    </span>

                    <div
                        class="absolute left-[22px] top-3.5 w-3 h-3 rounded-full border-2 border-white ring-4 transition-all duration-500 z-20"
                        :class="[
                            item.is_done
                                ? 'bg-slate-300 ring-slate-50'
                                : getScheduleStatus(item.start_time).urgent
                                ? 'bg-rose-500 ring-rose-100 animate-pulse scale-110'
                                : 'bg-indigo-500 ring-indigo-50',
                        ]"
                    ></div>

                    <Link
                        :href="route('teacher.classroom.session', item.id)"
                        class="block"
                    >
                        <div
                            class="p-4 rounded-2xl border transition-all duration-300 relative bg-white hover:shadow-lg group-hover:-translate-y-1 hover:border-indigo-200"
                            :class="
                                item.is_done
                                    ? 'border-slate-100 bg-slate-50/50 opacity-60'
                                    : 'border-slate-100 shadow-sm'
                            "
                        >
                            <div class="flex flex-col gap-1">
                                <h4
                                    class="font-bold text-slate-800 text-sm group-hover:text-indigo-600 transition-colors line-clamp-1"
                                >
                                    {{ item.subject.name }}
                                </h4>
                                <span
                                    class="w-fit px-2 py-0.5 rounded bg-slate-100 text-[9px] font-bold text-slate-500 uppercase border border-slate-200 tracking-wider"
                                >
                                    {{ item.kelas?.name }}
                                </span>
                            </div>

                            <div
                                class="flex items-center justify-between mt-3 pt-3 border-t border-slate-50 border-dashed"
                            >
                                <span
                                    class="text-[10px] font-bold text-slate-400 flex items-center gap-1"
                                >
                                    <MapPinIcon
                                        class="w-3 h-3 text-slate-300"
                                    />
                                    Ruang Kelas
                                </span>

                                <span
                                    v-if="!item.is_done"
                                    class="text-[10px] font-bold text-indigo-600 flex items-center gap-1 bg-indigo-50 px-2 py-1 rounded-md group-hover:bg-indigo-600 group-hover:text-white transition-all"
                                >
                                    Masuk Kelas
                                    <ChevronRightIcon class="w-3 h-3" />
                                </span>
                                <span
                                    v-else
                                    class="text-[10px] font-bold text-emerald-600 flex items-center gap-1 bg-emerald-50 px-2 py-1 rounded-md"
                                >
                                    <CheckCircleIcon class="w-3 h-3" /> Selesai
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
            <div
                v-else
                class="h-full flex flex-col items-center justify-center text-center opacity-60 py-10"
            >
                <BookOpenIcon class="w-8 h-8 text-slate-300 mb-2" />
                <p class="text-xs text-slate-500 font-medium tracking-tight">
                    Tidak ada jadwal hari ini.
                </p>
            </div>
        </div>
    </div>
</template>
