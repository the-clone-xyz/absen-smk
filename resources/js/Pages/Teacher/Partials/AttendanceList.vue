<script setup>
import { UserGroupIcon } from "@heroicons/vue/24/solid";

const props = defineProps({
    students: Array,
    form: Object, // Kita butuh akses ke form.attendances
});

const setStatus = (studentId, status) => {
    props.form.attendances[studentId] = status;
};
</script>

<template>
    <div
        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
    >
        <div
            class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center sticky top-0 z-10"
        >
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <UserGroupIcon class="w-5 h-5 text-blue-600" /> Absensi Siswa
            </h3>
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <span
                    class="animate-spin w-2 h-2 bg-green-500 rounded-full"
                ></span>
                Live Sync
                <span
                    class="bg-white border px-2 py-1 rounded font-bold text-gray-700"
                    >{{ students.length }} Siswa</span
                >
            </div>
        </div>

        <div class="divide-y divide-gray-100 max-h-[600px] overflow-y-auto">
            <div
                v-for="student in students"
                :key="student.id"
                class="px-6 py-3 flex items-center justify-between hover:bg-blue-50 transition group"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-sm font-bold text-gray-500 border border-gray-200"
                    >
                        {{ student.user.name.substring(0, 2).toUpperCase() }}
                    </div>
                    <div>
                        <p
                            class="text-sm font-bold text-gray-800 group-hover:text-blue-700"
                        >
                            {{ student.user.name }}
                        </p>
                        <p class="text-xs text-gray-500 font-mono">
                            {{ student.nis }}
                        </p>
                    </div>
                </div>

                <div class="flex gap-1">
                    <button
                        @click="setStatus(student.id, 'Hadir')"
                        class="w-8 h-8 rounded-full text-xs font-bold border transition flex items-center justify-center"
                        :class="
                            form.attendances[student.id] === 'Hadir'
                                ? 'bg-green-600 text-white border-green-600 shadow-md scale-110'
                                : 'bg-white text-gray-400 border-gray-200 hover:border-green-400 hover:text-green-600'
                        "
                    >
                        H
                    </button>
                    <button
                        @click="setStatus(student.id, 'Sakit')"
                        class="w-8 h-8 rounded-full text-xs font-bold border transition flex items-center justify-center"
                        :class="
                            form.attendances[student.id] === 'Sakit'
                                ? 'bg-blue-600 text-white border-blue-600 shadow-md scale-110'
                                : 'bg-white text-gray-400 border-gray-200 hover:border-blue-400 hover:text-blue-600'
                        "
                    >
                        S
                    </button>
                    <button
                        @click="setStatus(student.id, 'Izin')"
                        class="w-8 h-8 rounded-full text-xs font-bold border transition flex items-center justify-center"
                        :class="
                            form.attendances[student.id] === 'Izin'
                                ? 'bg-yellow-500 text-white border-yellow-500 shadow-md scale-110'
                                : 'bg-white text-gray-400 border-gray-200 hover:border-yellow-400 hover:text-yellow-500'
                        "
                    >
                        I
                    </button>
                    <button
                        @click="setStatus(student.id, 'Alpha')"
                        class="w-8 h-8 rounded-full text-xs font-bold border transition flex items-center justify-center"
                        :class="
                            form.attendances[student.id] === 'Alpha' ||
                            form.attendances[student.id] === 'Bolos'
                                ? 'bg-red-600 text-white border-red-600 shadow-md scale-110'
                                : 'bg-white text-gray-400 border-gray-200 hover:border-red-400 hover:text-red-600'
                        "
                    >
                        A
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
