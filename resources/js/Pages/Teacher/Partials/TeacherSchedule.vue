<script setup>
import { Link } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted } from "vue";
import {
    UserGroupIcon,
    ChevronRightIcon,
    CheckCircleIcon,
    MapPinIcon,
    BookOpenIcon,
    ClockIcon,
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
    scheduleDate.setHours(hours, minutes, 0);
    const diffMs = scheduleDate - now.value;
    const diffMinutes = Math.floor(diffMs / 60000);
    if (diffMinutes >= 0 && diffMinutes < 15)
        return { urgent: true, text: `${diffMinutes} mnt` };
    return { urgent: false, text: "" };
};
</script>

<template>
    <div
        class="bg-white rounded-[2rem] p-8 h-full flex flex-col relative overflow-hidden border border-slate-50"
    >
        <div class="flex justify-between items-center mb-8">
            <div>
                <h3 class="font-black text-slate-800 text-xl tracking-tight">
                    Timeline
                </h3>
                <p class="text-xs text-slate-400 font-medium mt-1">
                    Agenda mengajar hari ini
                </p>
            </div>
            <span
                class="text-[10px] font-extrabold text-indigo-500 bg-indigo-50 px-3 py-1.5 rounded-full uppercase tracking-wider"
            >
                {{
                    new Date().toLocaleDateString("id-ID", { weekday: "long" })
                }}
            </span>
        </div>

        <div
            class="overflow-y-auto flex-grow custom-scrollbar pr-2 relative z-10"
        >
            <div v-if="jadwal && jadwal.length > 0" class="pl-2 pt-2">
                <div
                    v-for="(item, index) in jadwal"
                    :key="item.id"
                    class="relative pl-12 pb-10 last:pb-0 group"
                >
                    <div
                        class="absolute left-[19px] top-8 bottom-0 w-[2px] bg-slate-100 group-last:hidden"
                    ></div>

                    <span
                        class="absolute left-0 top-1 text-[10px] font-bold text-slate-400 w-10 text-right"
                    >
                        {{ item.start_time.substring(0, 5) }}
                    </span>

                    <div
                        class="absolute left-[14px] top-1.5 w-3 h-3 rounded-full border-2 border-white ring-4 ring-slate-50 z-10"
                        :class="[
                            item.is_done
                                ? 'bg-slate-300'
                                : getScheduleStatus(item.start_time).urgent
                                ? 'bg-rose-500 animate-ping'
                                : 'bg-indigo-500',
                        ]"
                    ></div>

                    <Link
                        :href="route('teacher.classroom.show', item.id)"
                        class="block"
                    >
                        <div
                            class="p-5 rounded-[1.5rem] border transition-all duration-300 relative bg-white hover:shadow-[0_10px_30px_-10px_rgba(79,70,229,0.15)] hover:border-indigo-100 hover:-translate-y-1 group"
                            :class="[
                                item.is_done
                                    ? 'border-slate-100 bg-slate-50/50 opacity-60'
                                    : 'border-slate-100 shadow-sm',
                            ]"
                        >
                            <div
                                v-if="
                                    !item.is_done &&
                                    getScheduleStatus(item.start_time).urgent
                                "
                                class="absolute -top-3 right-4 bg-rose-500 text-white text-[9px] font-bold px-3 py-1 rounded-full shadow-lg shadow-rose-200 animate-bounce"
                            >
                                Mulai
                                {{ getScheduleStatus(item.start_time).text }}
                                lagi!
                            </div>

                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4
                                        class="font-bold text-slate-800 text-lg group-hover:text-indigo-600 transition-colors"
                                    >
                                        {{ item.subject.name }}
                                    </h4>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span
                                            class="px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider"
                                            >{{ item.kelas?.name }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-between pt-3 border-t border-slate-50"
                            >
                                <span
                                    class="text-[10px] font-bold text-slate-400 flex items-center gap-1"
                                    ><MapPinIcon class="w-3 h-3" /> Ruang
                                    Kelas</span
                                >
                                <span
                                    v-if="!item.is_done"
                                    class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition-all flex items-center gap-1"
                                >
                                    Masuk Kelas
                                    <ChevronRightIcon class="w-3 h-3" />
                                </span>
                                <span
                                    v-else
                                    class="text-[10px] font-bold text-emerald-500 flex items-center gap-1 bg-emerald-50 px-3 py-1 rounded-lg"
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
                class="h-full flex flex-col items-center justify-center text-center opacity-40 mt-6"
            >
                <div class="bg-slate-50 p-4 rounded-full mb-3">
                    <BookOpenIcon class="w-8 h-8 text-slate-300" />
                </div>
                <p class="text-xs text-slate-400 font-medium">
                    Jadwal mengajar kosong.
                </p>
            </div>
        </div>
    </div>
</template>
