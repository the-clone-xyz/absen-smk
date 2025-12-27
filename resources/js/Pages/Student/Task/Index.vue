<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    ArrowLeftIcon,
    BookOpenIcon,
    ClockIcon,
    ClipboardDocumentListIcon,
    DocumentTextIcon,
    CheckCircleIcon,
    XCircleIcon,
    ChevronRightIcon,
    UserIcon,
    CloudArrowDownIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    schedule: Object,
    journal: Object,
    tasks: Array,
    student: Object,
});

const formatTime = (time) => {
    if (!time) return "00:00";
    return time.substring(0, 5);
};
</script>

<template>
    <Head :title="schedule?.subject?.name || 'Detail Pelajaran'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('student.dashboard')"
                    class="p-2 rounded-full hover:bg-gray-200 transition text-gray-500 bg-white shadow-sm border border-gray-100"
                >
                    <ArrowLeftIcon class="w-5 h-5" />
                </Link>
                <div class="min-w-0">
                    <h2
                        class="font-bold text-xl text-gray-800 leading-tight truncate"
                    >
                        {{ schedule?.subject?.name }}
                    </h2>
                    <div
                        class="flex flex-wrap items-center gap-2 mt-1 text-sm text-gray-500"
                    >
                        <div class="flex items-center gap-1 min-w-0">
                            <UserIcon class="w-3 h-3 shrink-0" />
                            <span class="truncate">{{
                                schedule?.teacher?.user?.name
                            }}</span>
                        </div>
                        <span class="text-gray-300 hidden sm:inline">•</span>
                        <div
                            class="flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-xs font-bold border border-blue-100 shrink-0"
                        >
                            <ClockIcon class="w-3 h-3" />
                            {{ formatTime(schedule?.start_time) }} -
                            {{ formatTime(schedule?.end_time) }}
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
                    >
                        <div
                            class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-white flex items-center gap-3"
                        >
                            <div
                                class="p-2 bg-blue-100 rounded-lg text-blue-600"
                            >
                                <BookOpenIcon class="w-5 h-5" />
                            </div>
                            <h3 class="font-bold text-slate-800 text-lg">
                                Materi Hari Ini
                            </h3>
                        </div>

                        <div class="p-5 sm:p-6">
                            <div v-if="journal" class="space-y-6">
                                <div>
                                    <h4
                                        class="text-xl font-bold text-slate-800 mb-2"
                                    >
                                        {{ journal.topic }}
                                    </h4>
                                    <div
                                        class="prose prose-sm text-slate-600 leading-relaxed whitespace-pre-line bg-slate-50 p-4 rounded-xl border border-slate-100 max-w-none"
                                    >
                                        {{
                                            journal.notes ||
                                            "Tidak ada catatan tambahan."
                                        }}
                                    </div>
                                </div>

                                <div v-if="journal.module_path">
                                    <h5
                                        class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3"
                                    >
                                        Lampiran Dokumen
                                    </h5>

                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white border border-slate-200 rounded-xl hover:border-blue-300 hover:shadow-md transition group gap-4"
                                    >
                                        <div
                                            class="flex items-center gap-3 min-w-0"
                                        >
                                            <div
                                                class="p-3 bg-red-50 text-red-500 rounded-lg shrink-0"
                                            >
                                                <DocumentTextIcon
                                                    class="w-8 h-8"
                                                />
                                            </div>
                                            <div class="min-w-0">
                                                <p
                                                    class="font-bold text-slate-700 group-hover:text-blue-600 transition truncate text-sm sm:text-base"
                                                >
                                                    Modul Pembelajaran
                                                </p>
                                                <p
                                                    class="text-xs text-slate-400 truncate"
                                                >
                                                    Klik tombol untuk mengunduh
                                                    file
                                                </p>
                                            </div>
                                        </div>

                                        <a
                                            :href="
                                                route('viewer.download', {
                                                    path: journal.module_path,
                                                })
                                            "
                                            class="w-full sm:w-auto flex items-center justify-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-lg text-sm font-bold hover:bg-blue-700 transition shadow-sm active:scale-95 shrink-0"
                                        >
                                            <CloudArrowDownIcon
                                                class="w-4 h-4"
                                            />
                                            Download
                                        </a>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="text-sm text-slate-400 italic bg-slate-50 p-3 rounded-lg border border-dashed border-slate-200 text-center"
                                >
                                    Tidak ada file lampiran untuk materi ini.
                                </div>
                            </div>

                            <div
                                v-else
                                class="flex flex-col items-center justify-center py-12 text-center"
                            >
                                <div
                                    class="bg-slate-50 w-20 h-20 rounded-full flex items-center justify-center mb-4"
                                >
                                    <BookOpenIcon
                                        class="w-10 h-10 text-slate-300"
                                    />
                                </div>
                                <h4 class="text-slate-600 font-bold">
                                    Belum Ada Materi
                                </h4>
                                <p
                                    class="text-slate-400 text-sm max-w-xs mx-auto mt-1"
                                >
                                    Guru belum menginput jurnal atau materi
                                    untuk pertemuan hari ini.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden h-full flex flex-col"
                    >
                        <div
                            class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-purple-50 to-white flex items-center gap-3"
                        >
                            <div
                                class="p-2 bg-purple-100 rounded-lg text-purple-600"
                            >
                                <ClipboardDocumentListIcon class="w-5 h-5" />
                            </div>
                            <h3 class="font-bold text-slate-800 text-lg">
                                Tugas Kelas
                            </h3>
                        </div>

                        <div class="p-4 space-y-3 flex-grow bg-slate-50/50">
                            <div
                                v-if="!tasks || tasks.length === 0"
                                class="flex flex-col items-center justify-center py-10 text-center h-full"
                            >
                                <ClipboardDocumentListIcon
                                    class="w-12 h-12 text-slate-200 mb-2"
                                />
                                <p class="text-slate-400 text-sm italic">
                                    Tidak ada tugas aktif saat ini.
                                </p>
                            </div>

                            <Link
                                v-for="task in tasks"
                                :key="task.id"
                                :href="route('student.tasks.show', task.id)"
                                class="block bg-white border border-slate-200 rounded-xl p-4 hover:border-purple-400 hover:shadow-md transition-all group relative overflow-hidden"
                            >
                                <div
                                    class="absolute top-0 right-0 w-16 h-16 bg-purple-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-150"
                                ></div>

                                <div
                                    class="relative z-10 flex justify-between items-start"
                                >
                                    <div class="flex-grow pr-4 min-w-0">
                                        <h4
                                            class="font-bold text-slate-800 text-sm mb-1 group-hover:text-purple-700 transition-colors line-clamp-1"
                                        >
                                            {{ task.title }}
                                        </h4>
                                        <p
                                            class="text-xs text-slate-500 line-clamp-2 mb-3 leading-relaxed"
                                        >
                                            {{
                                                task.description ||
                                                "Tidak ada deskripsi singkat."
                                            }}
                                        </p>

                                        <div
                                            v-if="task.my_submission"
                                            class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 px-2.5 py-1 rounded-md text-[10px] font-bold border border-green-100"
                                        >
                                            <CheckCircleIcon
                                                class="w-3.5 h-3.5"
                                            />
                                            Selesai
                                        </div>
                                        <div
                                            v-else
                                            class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 px-2.5 py-1 rounded-md text-[10px] font-bold border border-red-100"
                                        >
                                            <XCircleIcon class="w-3.5 h-3.5" />
                                            Belum
                                        </div>
                                    </div>

                                    <div class="self-center shrink-0">
                                        <ChevronRightIcon
                                            class="w-5 h-5 text-slate-300 group-hover:text-purple-500 transition-colors"
                                        />
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
