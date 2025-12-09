<script setup>
import { Link } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted } from "vue";
import {
    ClockIcon,
    UserGroupIcon,
    ChevronRightIcon,
    CheckCircleIcon,
    BookOpenIcon,
    ExclamationCircleIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    jadwal: Array,
});

// State untuk waktu sekarang (Realtime)
const now = ref(new Date());
let timerInterval = null;

// Fungsi update waktu setiap detik
onMounted(() => {
    timerInterval = setInterval(() => {
        now.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

// Logika Hitung Mundur & Cek Urgensi
const getScheduleStatus = (startTimeStr) => {
    if (!startTimeStr) return { urgent: false, text: "" };

    const [hours, minutes] = startTimeStr.split(":");
    const scheduleDate = new Date();
    scheduleDate.setHours(hours, minutes, 0);

    const diffMs = scheduleDate - now.value;
    const diffMinutes = Math.floor(diffMs / 60000);
    const diffSeconds = Math.floor((diffMs % 60000) / 1000);

    // Jika waktu kurang dari 5 menit DAN belum lewat (masih positif)
    if (diffMinutes >= 0 && diffMinutes < 5) {
        return {
            urgent: true, // Trigger warna merah & kedip
            text: `Mulai dalam ${diffMinutes}m ${diffSeconds}s`,
        };
    }

    return { urgent: false, text: "" };
};
</script>

<template>
    <div
        class="lg:col-span-4 bg-white rounded-3xl shadow-sm border border-gray-200 p-6 flex flex-col h-[500px]"
    >
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-gray-800 flex items-center gap-2 text-lg">
                <ClockIcon class="w-6 h-6 text-green-600" /> Jadwal Hari Ini
            </h3>
            <span
                class="text-xs bg-green-50 text-green-700 px-2 py-1 rounded font-bold border border-green-100"
            >
                {{
                    new Date().toLocaleDateString("id-ID", { weekday: "long" })
                }}
            </span>
        </div>

        <div class="overflow-y-auto flex-grow pr-2 custom-scrollbar">
            <div v-if="jadwal && jadwal.length > 0">
                <div
                    v-for="item in jadwal"
                    :key="item.id"
                    class="relative pl-6 py-2 group"
                >
                    <div
                        class="absolute left-2 top-0 bottom-0 w-0.5 bg-gray-100 group-hover:bg-green-300 transition"
                    ></div>

                    <div
                        class="absolute left-[5px] top-1/2 -mt-1.5 w-3 h-3 rounded-full border-2 border-white shadow-sm z-10"
                        :class="[
                            item.is_done
                                ? 'bg-gray-400'
                                : getScheduleStatus(item.start_time).urgent
                                ? 'bg-red-600 animate-ping'
                                : 'bg-green-500',
                        ]"
                    ></div>
                    <div
                        class="absolute left-[5px] top-1/2 -mt-1.5 w-3 h-3 rounded-full border-2 border-white shadow-sm z-10"
                        :class="
                            item.is_done
                                ? 'bg-gray-400'
                                : getScheduleStatus(item.start_time).urgent
                                ? 'bg-red-600'
                                : 'bg-green-500'
                        "
                    ></div>

                    <Link
                        :href="route('teacher.classroom.show', item.id)"
                        class="block"
                    >
                        <div
                            class="p-4 rounded-xl border transition relative"
                            :class="[
                                // Style jika selesai: Abu-abu tapi tetap hover effect
                                item.is_done
                                    ? 'bg-gray-50 border-gray-200 hover:border-blue-300 hover:bg-white'
                                    : // Style jika URGENT (< 5 menit): Merah & Berkedip
                                    getScheduleStatus(item.start_time).urgent
                                    ? 'bg-red-50 border-red-300 ring-2 ring-red-200 animate-pulse'
                                    : // Style Normal
                                      'bg-white border-gray-100 shadow-sm hover:border-green-300 hover:shadow-md',
                            ]"
                        >
                            <div
                                v-if="
                                    !item.is_done &&
                                    getScheduleStatus(item.start_time).urgent
                                "
                                class="absolute -top-2 -right-2 bg-red-600 text-white text-[10px] px-2 py-1 rounded-full font-bold shadow-sm flex items-center gap-1 z-20"
                            >
                                <ClockIcon class="w-3 h-3 animate-spin" />
                                {{ getScheduleStatus(item.start_time).text }}
                            </div>

                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-bold text-gray-800">
                                    {{ item.subject.name }}
                                </h4>
                                <span
                                    class="text-xs font-mono px-2 py-1 rounded"
                                    :class="
                                        item.is_done
                                            ? 'bg-gray-200 text-gray-500'
                                            : 'bg-green-100 text-green-700 font-bold'
                                    "
                                >
                                    {{ item.start_time.substring(0, 5) }}
                                </span>
                            </div>

                            <div
                                class="flex items-center gap-2 text-xs text-gray-500 mb-3"
                            >
                                <UserGroupIcon class="w-4 h-4" />
                                <span>{{ item.class.name }}</span>
                            </div>

                            <div
                                class="text-right border-t pt-2 border-gray-100/50"
                            >
                                <span
                                    v-if="!item.is_done"
                                    class="text-xs text-green-600 font-bold flex items-center justify-end gap-1"
                                >
                                    Masuk Kelas
                                    <ChevronRightIcon class="w-3 h-3" />
                                </span>
                                <span
                                    v-else
                                    class="text-xs text-blue-500 font-bold flex items-center justify-end gap-1"
                                >
                                    <CheckCircleIcon class="w-4 h-4" /> Selesai
                                    (Klik untuk Edit)
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
            <div
                v-else
                class="h-full flex flex-col items-center justify-center text-gray-400 text-sm italic"
            >
                <BookOpenIcon class="w-12 h-12 mb-2 opacity-20" />
                Tidak ada jadwal mengajar hari ini.
            </div>
        </div>
    </div>
</template>
