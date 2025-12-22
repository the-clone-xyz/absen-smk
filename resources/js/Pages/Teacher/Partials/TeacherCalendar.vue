<script setup>
import { ref, computed } from "vue";
import {
    ChevronLeftIcon,
    ChevronRightIcon,
    XMarkIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    jadwalKalender: Object,
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
const dayNames = [
    "Minggu",
    "Senin",
    "Selasa",
    "Rabu",
    "Kamis",
    "Jumat",
    "Sabtu",
];

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
        const dayName = dayNames[dateObj.getDay()];
        const hasSchedule =
            props.jadwalKalender && props.jadwalKalender[dayName]
                ? true
                : false;
        days.push({
            date: i,
            dayName: dayName,
            hasSchedule: hasSchedule,
            isToday:
                i === new Date().getDate() &&
                currentMonth.value === new Date().getMonth(),
        });
    }
    return days;
});

const selectDate = (day) => {
    if (!day || !day.hasSchedule) {
        selectedDate.value = null;
        return;
    }
    selectedDate.value = day.date;
    selectedSchedule.value = props.jadwalKalender[day.dayName] || [];
};
</script>

<template>
    <div
        class="lg:col-span-4 bg-white rounded-3xl shadow-sm border border-gray-200 p-4 flex flex-col h-[500px]"
    >
        <div class="flex justify-between items-center mb-4 px-2">
            <span class="font-bold text-gray-800"
                >{{ monthNames[currentMonth] }} {{ currentYear }}</span
            >
            <div class="flex gap-1">
                <button class="p-1 hover:bg-gray-100 rounded text-gray-600">
                    <ChevronLeftIcon class="w-4 h-4" />
                </button>
                <button class="p-1 hover:bg-gray-100 rounded text-gray-600">
                    <ChevronRightIcon class="w-4 h-4" />
                </button>
            </div>
        </div>

        <div
            class="grid grid-cols-7 text-center text-xs text-gray-400 font-bold mb-2"
        >
            <span v-for="d in ['M', 'S', 'S', 'R', 'K', 'J', 'S']" :key="d">{{
                d
            }}</span>
        </div>

        <div
            class="grid grid-cols-7 gap-1 text-center text-sm flex-grow content-start overflow-y-auto custom-scrollbar"
        >
            <div
                v-for="(day, i) in calendarDays"
                :key="i"
                @click="selectDate(day)"
                class="h-9 flex flex-col items-center justify-center rounded-lg relative transition cursor-pointer"
                :class="[
                    !day
                        ? ''
                        : day.isToday
                        ? 'bg-green-600 text-white font-bold shadow-md'
                        : day.hasSchedule
                        ? 'bg-green-50 text-green-800 hover:bg-green-100 font-medium'
                        : 'hover:bg-gray-50 text-gray-400',
                    selectedDate === (day ? day.date : -1)
                        ? 'ring-2 ring-offset-1 ring-green-500'
                        : '',
                ]"
            >
                {{ day ? day.date : "" }}
                <span
                    v-if="day && day.hasSchedule && !day.isToday"
                    class="w-1 h-1 bg-green-500 rounded-full absolute bottom-1"
                ></span>
            </div>
        </div>

        <div
            v-if="selectedDate"
            class="mt-4 bg-gray-50 border border-gray-200 rounded-xl p-3 flex-shrink-0 max-h-[150px] overflow-y-auto custom-scrollbar"
        >
            <div
                class="flex justify-between items-center mb-2 border-b border-gray-200 pb-1"
            >
                <h5 class="font-bold text-xs text-gray-700">
                    Jadwal Tgl {{ selectedDate }}
                </h5>
                <button
                    @click="selectedDate = null"
                    class="text-gray-400 hover:text-red-500"
                >
                    <XMarkIcon class="w-4 h-4" />
                </button>
            </div>
            <div v-if="selectedSchedule.length > 0">
                <div
                    v-for="s in selectedSchedule"
                    :key="s.id"
                    class="mb-2 text-xs"
                >
                    <p class="font-bold text-gray-800">{{ s.subject.name }}</p>
                    <p class="text-gray-500">
                        {{ s.class.name }} • {{ s.start_time.substring(0, 5) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
