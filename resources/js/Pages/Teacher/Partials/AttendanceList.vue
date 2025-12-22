<script setup>
import {
    UserGroupIcon,
    CheckIcon,
    XMarkIcon,
    PlusCircleIcon,
    InformationCircleIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    students: Array,
    attendances: Object, // Ubah dari 'form' jadi object data biasa
    summary: Object,
});

// Definisikan Event
const emit = defineEmits(["update-status"]);

const setStatus = (studentId, status) => {
    // Kirim sinyal ke Parent (Classroom.vue)
    emit("update-status", { studentId, status });
};
</script>

<template>
    <div
        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-full"
    >
        <div
            class="px-5 py-4 border-b border-gray-200 bg-gray-50 sticky top-0 z-10"
        >
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    <UserGroupIcon class="w-5 h-5 text-blue-600" /> Presensi
                    Siswa
                </h3>
                <span
                    class="text-xs font-bold text-gray-500 bg-white px-2 py-1 rounded border shadow-sm"
                    >{{ students.length }} Siswa</span
                >
            </div>

            <div class="grid grid-cols-4 gap-2">
                <div
                    class="bg-green-100 border border-green-200 rounded p-1.5 text-center transition-all duration-300"
                    :class="{ 'scale-105 shadow-sm': summary.h > 0 }"
                >
                    <span
                        class="block text-[10px] font-bold text-green-700 uppercase"
                        >Hadir</span
                    >
                    <span
                        class="text-xl font-extrabold text-green-800 leading-none"
                        >{{ summary.h }}</span
                    >
                </div>
                <div
                    class="bg-blue-100 border border-blue-200 rounded p-1.5 text-center transition-all duration-300"
                    :class="{ 'scale-105 shadow-sm': summary.s > 0 }"
                >
                    <span
                        class="block text-[10px] font-bold text-blue-700 uppercase"
                        >Sakit</span
                    >
                    <span
                        class="text-xl font-extrabold text-blue-800 leading-none"
                        >{{ summary.s }}</span
                    >
                </div>
                <div
                    class="bg-yellow-100 border border-yellow-200 rounded p-1.5 text-center transition-all duration-300"
                    :class="{ 'scale-105 shadow-sm': summary.i > 0 }"
                >
                    <span
                        class="block text-[10px] font-bold text-yellow-700 uppercase"
                        >Izin</span
                    >
                    <span
                        class="text-xl font-extrabold text-yellow-800 leading-none"
                        >{{ summary.i }}</span
                    >
                </div>
                <div
                    class="bg-red-100 border border-red-200 rounded p-1.5 text-center transition-all duration-300"
                    :class="{ 'scale-105 shadow-sm': summary.a > 0 }"
                >
                    <span
                        class="block text-[10px] font-bold text-red-700 uppercase"
                        >Alpha</span
                    >
                    <span
                        class="text-xl font-extrabold text-red-800 leading-none"
                        >{{ summary.a }}</span
                    >
                </div>
            </div>
        </div>

        <div
            class="divide-y divide-gray-100 overflow-y-auto flex-grow custom-scrollbar"
        >
            <div
                v-for="student in students"
                :key="student.id"
                class="px-5 py-3 flex items-center justify-between hover:bg-blue-50/30 transition group"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500 border border-gray-200 shadow-sm"
                    >
                        {{ student.user.name.substring(0, 2).toUpperCase() }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800">
                            {{ student.user.name }}
                        </p>
                        <p
                            class="text-[10px] text-gray-400 font-mono flex items-center gap-1"
                        >
                            {{ student.nis }}
                            <span
                                v-if="attendances[student.id] === 'Hadir'"
                                class="text-green-600 font-bold ml-2"
                                >• Hadir</span
                            >
                            <span
                                v-if="attendances[student.id] === 'Sakit'"
                                class="text-blue-600 font-bold ml-2"
                                >• Sakit</span
                            >
                            <span
                                v-if="attendances[student.id] === 'Izin'"
                                class="text-yellow-600 font-bold ml-2"
                                >• Izin</span
                            >
                            <span
                                v-if="
                                    ['Alpha', 'Bolos'].includes(
                                        attendances[student.id]
                                    )
                                "
                                class="text-red-600 font-bold ml-2"
                                >• Alpha</span
                            >
                        </p>
                    </div>
                </div>

                <div class="flex gap-1.5">
                    <button
                        @click="setStatus(student.id, 'Hadir')"
                        type="button"
                        class="w-8 h-8 rounded-lg border transition flex items-center justify-center transform active:scale-95"
                        :class="
                            attendances[student.id] === 'Hadir'
                                ? 'bg-green-500 text-white border-green-600 shadow-md ring-2 ring-green-200'
                                : 'bg-white text-gray-300 hover:border-green-400 hover:text-green-500 hover:shadow-sm'
                        "
                    >
                        <CheckIcon class="w-5 h-5" />
                    </button>

                    <button
                        @click="setStatus(student.id, 'Sakit')"
                        type="button"
                        class="w-8 h-8 rounded-lg border transition flex items-center justify-center transform active:scale-95"
                        :class="
                            attendances[student.id] === 'Sakit'
                                ? 'bg-blue-500 text-white border-blue-600 shadow-md ring-2 ring-blue-200'
                                : 'bg-white text-gray-300 hover:border-blue-400 hover:text-blue-500 hover:shadow-sm'
                        "
                    >
                        <PlusCircleIcon class="w-5 h-5" />
                    </button>

                    <button
                        @click="setStatus(student.id, 'Izin')"
                        type="button"
                        class="w-8 h-8 rounded-lg border transition flex items-center justify-center transform active:scale-95"
                        :class="
                            attendances[student.id] === 'Izin'
                                ? 'bg-yellow-500 text-white border-yellow-600 shadow-md ring-2 ring-yellow-200'
                                : 'bg-white text-gray-300 hover:border-yellow-400 hover:text-yellow-500 hover:shadow-sm'
                        "
                    >
                        <InformationCircleIcon class="w-5 h-5" />
                    </button>

                    <button
                        @click="setStatus(student.id, 'Alpha')"
                        type="button"
                        class="w-8 h-8 rounded-lg border transition flex items-center justify-center transform active:scale-95"
                        :class="
                            ['Alpha', 'Bolos'].includes(attendances[student.id])
                                ? 'bg-red-500 text-white border-red-600 shadow-md ring-2 ring-red-200'
                                : 'bg-white text-gray-300 hover:border-red-400 hover:text-red-500 hover:shadow-sm'
                        "
                    >
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
