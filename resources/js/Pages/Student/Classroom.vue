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
} from "@heroicons/vue/24/solid";

const props = defineProps({
    schedule: Object,
    journal: Object,
    tasks: Array,
    student: Object,
});

// Format Tanggal
const formatDate = (date) => {
    return new Date(date).toLocaleDateString("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
    });
};

const formatTime = (time) => time.substring(0, 5);
</script>

<template>
    <Head :title="schedule.subject.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('student.dashboard')"
                    class="p-2 rounded-full hover:bg-gray-200 transition text-gray-500"
                >
                    <ArrowLeftIcon class="w-5 h-5" />
                </Link>
                <div>
                    <h2 class="font-bold text-xl text-gray-800 leading-tight">
                        {{ schedule.subject.name }}
                    </h2>
                    <p class="text-sm text-gray-500 flex items-center gap-2">
                        <span>{{ schedule.teacher.user.name }}</span> •
                        <span
                            class="flex items-center gap-1 bg-gray-100 px-2 py-0.5 rounded text-xs font-bold"
                        >
                            <ClockIcon class="w-3 h-3" />
                            {{ formatTime(schedule.start_time) }} -
                            {{ formatTime(schedule.end_time) }}
                        </span>
                    </p>
                </div>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="px-6 py-4 border-b border-gray-100 bg-blue-50 flex items-center gap-2"
                        >
                            <BookOpenIcon class="w-5 h-5 text-blue-600" />
                            <h3 class="font-bold text-gray-800">
                                Materi Hari Ini
                            </h3>
                        </div>

                        <div class="p-6">
                            <div v-if="journal">
                                <h4
                                    class="text-lg font-bold text-gray-800 mb-2"
                                >
                                    {{ journal.topic }}
                                </h4>
                                <div
                                    class="prose text-gray-600 text-sm whitespace-pre-line mb-6"
                                >
                                    {{ journal.notes }}
                                </div>

                                <div
                                    v-if="journal.module_path"
                                    class="bg-gray-50 border border-gray-200 rounded-lg p-4 flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="bg-white p-2 rounded shadow-sm"
                                        >
                                            <DocumentTextIcon
                                                class="w-6 h-6 text-blue-500"
                                            />
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-bold text-gray-700"
                                            >
                                                Lampiran Materi
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                Klik tombol untuk membaca
                                            </p>
                                        </div>
                                    </div>
                                    <Link
                                        :href="
                                            route('viewer.show', {
                                                path: journal.module_path,
                                            })
                                        "
                                        class="bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-blue-700 transition shadow"
                                    >
                                        Baca Materi
                                    </Link>
                                </div>
                            </div>

                            <div v-else class="text-center py-10 text-gray-400">
                                <div
                                    class="bg-gray-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3"
                                >
                                    <BookOpenIcon class="w-8 h-8 opacity-50" />
                                </div>
                                <p class="text-sm">
                                    Guru belum mengisi jurnal materi hari ini.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden h-full"
                    >
                        <div
                            class="px-5 py-4 border-b border-gray-100 bg-purple-50 flex items-center gap-2"
                        >
                            <ClipboardDocumentListIcon
                                class="w-5 h-5 text-purple-600"
                            />
                            <h3 class="font-bold text-gray-800">Tugas Kelas</h3>
                        </div>

                        <div class="p-2 space-y-2">
                            <div
                                v-if="tasks.length === 0"
                                class="text-center py-8 text-gray-400"
                            >
                                <p class="text-sm italic">
                                    Tidak ada tugas aktif.
                                </p>
                            </div>

                            <Link
                                v-for="task in tasks"
                                :key="task.id"
                                :href="route('student.task.show', task.id)"
                                class="block bg-white border border-gray-200 rounded-lg p-4 hover:border-purple-400 hover:shadow-md transition group relative overflow-hidden"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex-grow pr-2">
                                        <h4
                                            class="font-bold text-gray-800 text-sm mb-1 group-hover:text-purple-700"
                                        >
                                            {{ task.title }}
                                        </h4>
                                        <p
                                            class="text-[10px] text-gray-400 line-clamp-1 mb-2"
                                        >
                                            {{ task.description }}
                                        </p>

                                        <div
                                            v-if="task.my_submission"
                                            class="inline-flex items-center gap-1 bg-green-100 text-green-700 px-2 py-1 rounded text-[10px] font-bold"
                                        >
                                            <CheckCircleIcon class="w-3 h-3" />
                                            Sudah Dikumpul
                                        </div>
                                        <div
                                            v-else
                                            class="inline-flex items-center gap-1 bg-red-100 text-red-700 px-2 py-1 rounded text-[10px] font-bold"
                                        >
                                            <XCircleIcon class="w-3 h-3" />
                                            Belum Dikumpul
                                        </div>
                                    </div>
                                    <ChevronRightIcon
                                        class="w-4 h-4 text-gray-300 group-hover:text-purple-500"
                                    />
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
