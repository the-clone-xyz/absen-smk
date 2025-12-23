<script setup>
import { ref, computed } from "vue";
import {
    ChevronLeftIcon,
    ChevronRightIcon,
    CalendarIcon,
    ClockIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    jadwalKalender: { type: Object, default: () => ({}) },
});
const currentDate = new Date();
const currentMonth = ref(currentDate.getMonth());
const currentYear = ref(currentDate.getFullYear());
const selectedDate = ref(null);
const selectedSchedule = ref([]);
const monthNames = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
];
const dayNames = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];
const prevMonth = () => {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
    selectedDate.value = null;
};
const nextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
    selectedDate.value = null;
};
const calendarDays = computed(() => {
    const days = [];
    const firstDay = new Date(
        currentYear.value,
        currentMonth.value,
        1
    ).getDay();
    const daysInMonth = new Date(
        currentYear.value,
        currentMonth.value + 1,
        0
    ).getDate();
    for (let i = 0; i < firstDay; i++) days.push(null);
    for (let i = 1; i <= daysInMonth; i++) {
        const dateObj = new Date(currentYear.value, currentMonth.value, i);
        const dayName = [
            "Minggu",
            "Senin",
            "Selasa",
            "Rabu",
            "Kamis",
            "Jumat",
            "Sabtu",
        ][dateObj.getDay()];
        const hasSchedule =
            props.jadwalKalender && props.jadwalKalender[dayName]
                ? true
                : false;
        const isToday =
            i === new Date().getDate() &&
            currentMonth.value === new Date().getMonth();
        days.push({
            date: i,
            dayName: dayName,
            hasSchedule: hasSchedule,
            isToday: isToday,
        });
    }
    return days;
});
const selectDate = (day) => {
    if (!day) return;
    selectedDate.value = day.date;
    selectedSchedule.value = props.jadwalKalender[day.dayName] || [];
};
</script>

<template>
    <div class="h-full flex flex-col p-6 bg-white rounded-[2rem] relative">
        <div class="flex justify-between items-end mb-8">
            <div>
                <p
                    class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-1"
                >
                    {{ currentYear }}
                </p>
                <h2
                    class="text-2xl font-black text-slate-800 tracking-tight leading-none"
                >
                    {{ monthNames[currentMonth] }}
                </h2>
            </div>
            <div class="flex gap-1 bg-slate-50 p-1 rounded-xl">
                <button
                    @click="prevMonth"
                    class="p-2 rounded-lg text-slate-400 hover:bg-white hover:text-indigo-600 hover:shadow-sm transition"
                >
                    <ChevronLeftIcon class="w-4 h-4" />
                </button>
                <button
                    @click="nextMonth"
                    class="p-2 rounded-lg text-slate-400 hover:bg-white hover:text-indigo-600 hover:shadow-sm transition"
                >
                    <ChevronRightIcon class="w-4 h-4" />
                </button>
            </div>
        </div>

        <div class="flex-grow flex flex-col">
            <div class="grid grid-cols-7 mb-4">
                <div
                    v-for="d in dayNames"
                    :key="d"
                    class="text-center text-[10px] font-extrabold text-slate-300 uppercase"
                >
                    {{ d }}
                </div>
            </div>
            <div class="grid grid-cols-7 gap-2 content-start">
                <div
                    v-for="(day, i) in calendarDays"
                    :key="i"
                    class="flex justify-center aspect-square"
                >
                    <button
                        v-if="day"
                        @click="selectDate(day)"
                        class="w-full h-full rounded-2xl flex flex-col items-center justify-center relative transition-all duration-300 group"
                        :class="[
                            day.isToday
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 scale-105'
                                : selectedDate === day.date
                                ? 'bg-slate-800 text-white shadow-md'
                                : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-600',
                            !day.hasSchedule && !day.isToday
                                ? 'opacity-40'
                                : '',
                        ]"
                    >
                        <span class="text-sm font-bold">{{ day.date }}</span>
                        <span
                            v-if="day.hasSchedule"
                            class="absolute bottom-1.5 w-1 h-1 rounded-full transition-colors"
                            :class="
                                day.isToday || selectedDate === day.date
                                    ? 'bg-white'
                                    : 'bg-indigo-400'
                            "
                        ></span>
                    </button>
                </div>
            </div>
        </div>

        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-10 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-10 opacity-0"
        >
            <div
                v-if="selectedDate"
                class="absolute inset-4 bg-white/95 backdrop-blur-md z-20 flex flex-col rounded-3xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)] border border-white p-6"
            >
                <div class="flex justify-between items-center mb-4">
                    <h5 class="font-bold text-slate-800 text-sm">
                        Jadwal Tanggal {{ selectedDate }}
                    </h5>
                    <button
                        @click="selectedDate = null"
                        class="bg-slate-50 p-2 rounded-full text-slate-400 hover:text-rose-500 transition"
                    >
                        <XMarkIcon class="w-4 h-4" />
                    </button>
                </div>
                <div
                    class="overflow-y-auto space-y-3 pr-1 custom-scrollbar flex-grow"
                >
                    <div v-if="selectedSchedule.length > 0">
                        <div
                            v-for="s in selectedSchedule"
                            :key="s.id"
                            class="flex gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100"
                        >
                            <div class="w-1 rounded-full bg-indigo-500"></div>
                            <div>
                                <p class="font-bold text-xs text-slate-800">
                                    {{ s.subject?.name }}
                                </p>
                                <p class="text-[10px] text-slate-500 mt-0.5">
                                    {{ s.start_time?.substring(0, 5) }} •
                                    {{ s.class?.name ?? s.kelas?.name }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="h-full flex flex-col items-center justify-center text-center opacity-40"
                    >
                        <p class="text-xs text-slate-400">Kosong</p>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
