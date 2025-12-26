<script setup>
import { ref, computed } from "vue";
import {
    ChevronLeftIcon,
    ChevronRightIcon,
    PhoneIcon,
    UserIcon,
    IdentificationIcon,
    CalendarDaysIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({ students: Array });

// Pagination Logic
const itemsPerPage = 10;
const currentPage = ref(1);
const totalPages = computed(() =>
    Math.ceil((props.students?.length || 0) / itemsPerPage)
);
const paginatedStudents = computed(() => {
    if (!props.students) return [];
    const start = (currentPage.value - 1) * itemsPerPage;
    return props.students.slice(start, start + itemsPerPage);
});
const nextPage = () =>
    currentPage.value < totalPages.value && currentPage.value++;
const prevPage = () => currentPage.value > 1 && currentPage.value--;
</script>

<template>
    <div class="space-y-6">
        <div
            v-if="!students || students.length === 0"
            class="bg-white rounded-[2rem] border-2 border-dashed border-slate-200 p-16 text-center"
        >
            <div
                class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 animate-pulse"
            >
                <UserIcon class="w-10 h-10 text-slate-300" />
            </div>
            <h3 class="text-lg font-bold text-slate-700">
                Belum ada data siswa
            </h3>
            <p class="text-slate-400 text-sm mt-1">
                Data siswa yang terdaftar di kelas ini akan muncul di sini.
            </p>
        </div>

        <div v-else>
            <div class="grid grid-cols-1 gap-5 sm:hidden">
                <div
                    v-for="student in paginatedStudents"
                    :key="student.id"
                    class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex flex-col gap-5 relative overflow-hidden group"
                >
                    <div
                        class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-bl-full -mr-4 -mt-4 opacity-50 transition-transform group-hover:scale-110"
                    ></div>

                    <div class="flex items-center gap-4 relative z-10">
                        <img
                            class="h-16 w-16 rounded-full border-4 border-slate-50 shadow-md object-cover bg-white"
                            :src="
                                student.avatar
                                    ? '/storage/' + student.avatar
                                    : `https://ui-avatars.com/api/?name=${student.name}&background=dde7f7&color=2563eb`
                            "
                        />
                        <div>
                            <h3
                                class="font-bold text-lg capitalize text-slate-800 leading-tight line-clamp-1"
                            >
                                {{ student.name.toLowerCase() }}
                            </h3>
                            <div
                                class="flex items-center gap-1.5 mt-1 text-slate-500 bg-slate-50 w-fit px-2 py-0.5 rounded-md border border-slate-100"
                            >
                                <IdentificationIcon
                                    class="w-3 h-3 text-slate-400"
                                />
                                <p
                                    class="text-xs font-mono tracking-wide font-bold"
                                >
                                    {{ student.nis }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-4 border-t border-slate-50 pt-4"
                    >
                        <div>
                            <span
                                class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1 flex items-center gap-1"
                            >
                                <CalendarDaysIcon class="w-3 h-3" /> TTL
                            </span>
                            <p
                                class="text-sm text-slate-600 font-medium capitalize"
                            >
                                {{ student.pob }}, {{ student.dob }}
                            </p>
                        </div>
                        <div class="flex items-end justify-end">
                            <a
                                v-if="student.phone"
                                :href="'https://wa.me/' + student.phone"
                                target="_blank"
                                class="bg-emerald-50 text-emerald-600 px-4 py-2 rounded-xl text-xs font-bold border border-emerald-100 flex items-center gap-2 hover:bg-emerald-600 hover:text-white transition-all shadow-sm"
                            >
                                <PhoneIcon class="w-4 h-4" /> WhatsApp
                            </a>
                            <span
                                v-else
                                class="text-xs text-slate-400 italic px-3 py-2 bg-slate-50 rounded-lg"
                                >No Phone</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="hidden sm:block bg-white rounded-[2rem] border border-slate-100 shadow-sm overflow-hidden"
            >
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 border-b border-slate-100">
                        <tr>
                            <th
                                class="px-8 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider w-20 text-center"
                            >
                                No
                            </th>
                            <th
                                class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider"
                            >
                                Siswa
                            </th>
                            <th
                                class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider"
                            >
                                Nomor Induk
                            </th>
                            <th
                                class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider"
                            >
                                Tempat, Tanggal Lahir
                            </th>
                            <th
                                class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center"
                            >
                                Kontak
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr
                            v-for="(student, idx) in paginatedStudents"
                            :key="student.id"
                            class="hover:bg-indigo-50/30 transition-colors duration-200 group"
                        >
                            <td
                                class="px-8 py-4 text-center font-bold text-slate-400 group-hover:text-indigo-500 transition-colors"
                            >
                                {{ (currentPage - 1) * itemsPerPage + idx + 1 }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="relative group-hover:scale-105 transition-transform"
                                    >
                                        <img
                                            class="h-12 w-12 rounded-full border-2 border-white shadow-sm object-cover bg-slate-100"
                                            :src="
                                                student.avatar
                                                    ? '/storage/' +
                                                      student.avatar
                                                    : `https://ui-avatars.com/api/?name=${student.name}&background=dde7f7&color=2563eb`
                                            "
                                        />
                                    </div>
                                    <div>
                                        <div
                                            class="font-bold text-slate-700 capitalize text-sm group-hover:text-indigo-700 transition-colors"
                                        >
                                            {{ student.name.toLowerCase() }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2.5 py-1 rounded-md bg-slate-50 border border-slate-100 text-xs font-mono text-slate-500 font-bold shadow-sm"
                                >
                                    {{ student.nis }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 text-sm text-slate-600 capitalize"
                            >
                                <div class="flex flex-col">
                                    <span class="font-medium">{{
                                        student.pob
                                    }}</span>
                                    <span
                                        class="text-xs text-slate-400 font-mono"
                                        >{{ student.dob }}</span
                                    >
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a
                                    v-if="student.phone"
                                    :href="'https://wa.me/' + student.phone"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-50 text-emerald-600 text-xs font-bold border border-emerald-100 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 transition-all transform hover:scale-105 shadow-sm"
                                >
                                    <PhoneIcon class="w-3.5 h-3.5" /> WhatsApp
                                </a>
                                <span
                                    v-else
                                    class="text-xs text-slate-400 italic bg-slate-50 px-4 py-2 rounded-full border border-slate-100"
                                    >Belum ada nomor</span
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div
                    class="p-5 border-t border-slate-50 flex items-center justify-between bg-white"
                    v-if="totalPages > 1"
                >
                    <span class="text-xs text-slate-400 font-medium">
                        Menampilkan halaman
                        <span class="text-indigo-600 font-bold">{{
                            currentPage
                        }}</span>
                        dari {{ totalPages }}
                    </span>
                    <div class="flex gap-2">
                        <button
                            @click="prevPage"
                            :disabled="currentPage === 1"
                            class="p-2 border border-slate-200 rounded-xl hover:bg-slate-50 hover:border-slate-300 disabled:opacity-40 disabled:cursor-not-allowed transition-all text-slate-500 shadow-sm"
                        >
                            <ChevronLeftIcon class="w-4 h-4" />
                        </button>
                        <button
                            @click="nextPage"
                            :disabled="currentPage === totalPages"
                            class="p-2 border border-slate-200 rounded-xl hover:bg-slate-50 hover:border-slate-300 disabled:opacity-40 disabled:cursor-not-allowed transition-all text-slate-500 shadow-sm"
                        >
                            <ChevronRightIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
