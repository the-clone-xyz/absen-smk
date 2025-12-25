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

        // Cek apakah ada jadwal di hari tersebut (berdasarkan nama hari)
        const hasSchedule =
            props.jadwalKalender && props.jadwalKalender[dayName]
                ? true
                : false;

        const isToday =
            i === new Date().getDate() &&
            currentMonth.value === new Date().getMonth() &&
            currentYear.value === new Date().getFullYear();

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
    // Ambil jadwal berdasarkan nama hari (misal: "Senin")
    selectedSchedule.value = props.jadwalKalender[day.dayName] || [];
};
</script>

<template>
    <div
        class="h-full flex flex-col relative bg-white rounded-[2rem] overflow-hidden"
    >
        <div class="flex justify-between items-center p-6 pb-2">
            <div>
                <p
                    class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-1"
                >
                    {{ currentYear }}
                </p>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">
                    {{ monthNames[currentMonth] }}
                </h2>
            </div>
            <div
                class="flex gap-1 bg-slate-50 p-1 rounded-xl border border-slate-100"
            >
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

        <div class="flex-grow flex flex-col p-4 pt-0">
            <div class="grid grid-cols-7 mb-2">
                <div
                    v-for="d in dayNames"
                    :key="d"
                    class="text-center text-[10px] font-extrabold text-slate-300 uppercase py-2"
                >
                    {{ d }}
                </div>
            </div>

            <div class="grid grid-cols-7 gap-2 content-start flex-grow">
                <div
                    v-for="(day, i) in calendarDays"
                    :key="i"
                    class="aspect-square flex justify-center items-center"
                >
                    <button
                        v-if="day"
                        @click="selectDate(day)"
                        class="w-8 h-8 md:w-9 md:h-9 rounded-full flex flex-col items-center justify-center relative transition-all duration-300 group hover:bg-indigo-50"
                        :class="[
                            day.isToday
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700'
                                : selectedDate === day.date
                                ? 'bg-slate-800 text-white shadow-md'
                                : 'text-slate-600',
                            !day.hasSchedule &&
                            !day.isToday &&
                            selectedDate !== day.date
                                ? 'opacity-60'
                                : '',
                        ]"
                    >
                        <span class="text-xs font-bold">{{ day.date }}</span>

                        <span
                            v-if="day.hasSchedule"
                            class="absolute bottom-1 w-1 h-1 rounded-full"
                            :class="
                                day.isToday || selectedDate === day.date
                                    ? 'bg-white'
                                    : 'bg-indigo-500'
                            "
                        ></span>
                    </button>
                </div>
            </div>
        </div>

        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-full opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-10 opacity-0"
        >
            <div
                v-if="selectedDate"
                class="absolute inset-0 bg-white/95 backdrop-blur-md z-20 flex flex-col p-6 animate-fade-in"
            >
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <p
                            class="text-xs text-slate-400 font-bold uppercase tracking-wider"
                        >
                            Jadwal Tanggal
                        </p>
                        <h5 class="font-black text-slate-800 text-xl">
                            {{ selectedDate }} {{ monthNames[currentMonth] }}
                        </h5>
                    </div>
                    <button
                        @click="selectedDate = null"
                        class="bg-slate-100 p-2 rounded-full text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition"
                    >
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <div
                    class="overflow-y-auto custom-scrollbar flex-grow-mr-2 py-1 pr-2 space-y-3"
                >
                    <div v-if="selectedSchedule.length > 0">
                        <div
                            v-for="s in selectedSchedule"
                            :key="s.id"
                            class="flex my-2 gap-4 p-4 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all"
                        >
                            <div
                                class="w-1.5 rounded-full bg-indigo-500 self-stretch"
                            ></div>
                            <div class="flex-grow">
                                <h4
                                    class="font-bold text-slate-800 text-sm mb-1"
                                >
                                    {{ s.subject?.name }}
                                </h4>
                                <div
                                    class="flex items-center gap-2 text-xs text-slate-500"
                                >
                                    <ClockIcon class="w-3.5 h-3.5" />
                                    {{ s.start_time?.substring(0, 5) }} -
                                    {{ s.end_time?.substring(0, 5) }}
                                </div>
                                <div
                                    class="mt-2 inline-flex items-center px-2 py-1 rounded bg-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wide"
                                >
                                    {{ s.class?.name ?? s.kelas?.name }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="h-40 flex flex-col items-center justify-center text-center"
                    >
                        <div
                            class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mb-3 text-slate-300"
                        >
                            <CalendarIcon class="w-6 h-6" />
                        </div>
                        <p class="text-sm font-bold text-slate-400">
                            Tidak ada jadwal
                        </p>
                        <p class="text-xs text-slate-300">
                            Nikmati waktu luang Anda!
                        </p>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>
