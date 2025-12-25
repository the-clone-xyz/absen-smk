<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import {
    ArrowLeftIcon,
    UserGroupIcon,
    BookOpenIcon,
    ClipboardDocumentListIcon,
    ClockIcon,
    EllipsisHorizontalIcon,
    PlusIcon,
    DocumentTextIcon,
    VideoCameraIcon,
    ArrowDownTrayIcon,
    CheckCircleIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    classroom: Object,
    students: Array,
    assignments: Array,
    materials: Array,
});

// State untuk Tab Aktif
const activeTab = ref("overview"); // overview, students, assignments, materials

const tabs = [
    { id: "overview", label: "Ringkasan" },
    { id: "students", label: "Daftar Siswa" },
    { id: "assignments", label: "Tugas" },
    { id: "materials", label: "Materi & Modul" },
];
</script>

<template>
    <Head :title="classroom.name" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8FAFC] pb-20">
            <div class="bg-white border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4">
                    <Link
                        :href="route('dashboard')"
                        class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition mb-6 font-medium"
                    >
                        <ArrowLeftIcon class="w-4 h-4" /> Kembali ke Dashboard
                    </Link>

                    <div
                        class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6"
                    >
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span
                                    class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold uppercase tracking-wider border border-indigo-100"
                                >
                                    {{ classroom.subject }}
                                </span>
                            </div>
                            <h1
                                class="text-3xl md:text-4xl font-black text-slate-800 tracking-tight mb-2"
                            >
                                {{ classroom.name }}
                            </h1>
                            <p class="text-slate-500">
                                {{ classroom.description }}
                            </p>
                        </div>

                        <div class="flex gap-4">
                            <div
                                class="bg-slate-50 px-5 py-3 rounded-2xl border border-slate-100 text-center"
                            >
                                <p
                                    class="text-xs text-slate-400 font-bold uppercase"
                                >
                                    Siswa
                                </p>
                                <p class="text-xl font-black text-slate-700">
                                    {{ students.length }}
                                </p>
                            </div>
                            <div
                                class="bg-indigo-50 px-5 py-3 rounded-2xl border border-indigo-100 text-center"
                            >
                                <p
                                    class="text-xs text-indigo-400 font-bold uppercase"
                                >
                                    Sesi Ke
                                </p>
                                <p class="text-xl font-black text-indigo-700">
                                    {{ classroom.session
                                    }}<span class="text-xs text-indigo-300"
                                        >/{{ classroom.total_sessions }}</span
                                    >
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-6 mt-10 border-b border-slate-100 overflow-x-auto"
                    >
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            class="pb-3 text-sm font-bold transition-all relative whitespace-nowrap"
                            :class="
                                activeTab === tab.id
                                    ? 'text-indigo-600'
                                    : 'text-slate-400 hover:text-slate-600'
                            "
                        >
                            {{ tab.label }}
                            <span
                                v-if="activeTab === tab.id"
                                class="absolute bottom-0 left-0 w-full h-1 bg-indigo-600 rounded-t-full"
                            ></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div v-if="activeTab === 'overview'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div
                            class="col-span-2 bg-gradient-to-br from-indigo-600 to-violet-700 rounded-3xl p-8 text-white relative overflow-hidden shadow-xl"
                        >
                            <div class="relative z-10">
                                <h3 class="text-2xl font-bold mb-2">
                                    Progress Pembelajaran
                                </h3>
                                <p class="text-indigo-100 mb-6 max-w-md">
                                    Anda telah menyelesaikan
                                    {{ classroom.session }} dari
                                    {{ classroom.total_sessions }} pertemuan
                                    yang direncanakan. Pertahankan semangat!
                                </p>

                                <div
                                    class="bg-white/20 h-3 rounded-full w-full backdrop-blur-sm overflow-hidden"
                                >
                                    <div
                                        class="bg-white h-full rounded-full"
                                        :style="`width: ${
                                            (classroom.session /
                                                classroom.total_sessions) *
                                            100
                                        }%`"
                                    ></div>
                                </div>
                                <div
                                    class="flex justify-between mt-2 text-xs font-bold text-indigo-200 uppercase tracking-wide"
                                >
                                    <span>Mulai</span>
                                    <span
                                        >{{
                                            Math.round(
                                                (classroom.session /
                                                    classroom.total_sessions) *
                                                    100
                                            )
                                        }}% Selesai</span
                                    >
                                </div>
                            </div>
                            <ClockIcon
                                class="absolute right-[-20px] bottom-[-20px] w-64 h-64 text-white opacity-10 rotate-12"
                            />
                        </div>

                        <div
                            class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm flex flex-col justify-center items-center text-center"
                        >
                            <div
                                class="w-16 h-16 bg-green-50 text-green-600 rounded-full flex items-center justify-center mb-4"
                            >
                                <ClipboardDocumentListIcon class="w-8 h-8" />
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg">
                                Buat Tugas Baru
                            </h4>
                            <p class="text-sm text-slate-500 mb-4">
                                Berikan evaluasi untuk pertemuan ini.
                            </p>
                            <button
                                class="w-full py-2.5 bg-green-600 text-white rounded-xl font-bold text-sm hover:bg-green-700 transition"
                            >
                                + Tambah Tugas
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'students'">
                    <div
                        class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden"
                    >
                        <div
                            class="px-6 py-5 border-b border-slate-50 flex justify-between items-center"
                        >
                            <h3 class="font-bold text-lg text-slate-800">
                                Daftar Siswa ({{ students.length }})
                            </h3>
                            <button
                                class="text-sm text-indigo-600 font-bold hover:underline"
                            >
                                Export Data
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table
                                class="w-full text-left text-sm text-slate-600"
                            >
                                <thead
                                    class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold text-xs"
                                >
                                    <tr>
                                        <th class="px-6 py-4">Nama Siswa</th>
                                        <th class="px-6 py-4">NIS</th>
                                        <th class="px-6 py-4">
                                            Status Hari Ini
                                        </th>
                                        <th class="px-6 py-4 text-right">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr
                                        v-for="student in students"
                                        :key="student.id"
                                        class="hover:bg-slate-50/50 transition"
                                    >
                                        <td
                                            class="px-6 py-4 font-medium text-slate-800 flex items-center gap-3"
                                        >
                                            <div
                                                class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs"
                                            >
                                                {{
                                                    student.name
                                                        .substring(0, 2)
                                                        .toUpperCase()
                                                }}
                                            </div>
                                            {{ student.name }}
                                        </td>
                                        <td class="px-6 py-4 font-mono">
                                            {{ student.nis }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-2.5 py-1 rounded-full text-xs font-bold"
                                                :class="
                                                    student.status === 'Hadir'
                                                        ? 'bg-green-100 text-green-700'
                                                        : 'bg-red-100 text-red-700'
                                                "
                                            >
                                                {{ student.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button
                                                class="text-slate-400 hover:text-indigo-600"
                                            >
                                                <EllipsisHorizontalIcon
                                                    class="w-5 h-5"
                                                />
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'assignments'">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-lg text-slate-800">
                            Daftar Tugas
                        </h3>
                        <button
                            class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-indigo-700 transition"
                        >
                            <PlusIcon class="w-4 h-4" /> Buat Tugas
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="task in assignments"
                            :key="task.id"
                            class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition group"
                        >
                            <div class="flex justify-between items-start mb-3">
                                <div
                                    class="bg-orange-50 p-2.5 rounded-xl text-orange-600 group-hover:bg-orange-600 group-hover:text-white transition"
                                >
                                    <ClipboardDocumentListIcon
                                        class="w-6 h-6"
                                    />
                                </div>
                                <span
                                    class="text-[10px] font-bold px-2 py-1 rounded-full uppercase"
                                    :class="
                                        task.status === 'active'
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-slate-100 text-slate-500'
                                    "
                                >
                                    {{
                                        task.status === "active"
                                            ? "Sedang Berjalan"
                                            : "Selesai"
                                    }}
                                </span>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg mb-1">
                                {{ task.title }}
                            </h4>
                            <p
                                class="text-xs text-slate-500 mb-4 flex items-center gap-1"
                            >
                                <ClockIcon class="w-3 h-3" /> Tenggat:
                                {{ task.deadline }}
                            </p>

                            <div
                                class="w-full bg-slate-100 rounded-full h-2 mb-2 overflow-hidden"
                            >
                                <div
                                    class="bg-orange-500 h-full rounded-full"
                                    :style="`width: ${
                                        (task.submitted / task.total) * 100
                                    }%`"
                                ></div>
                            </div>
                            <div
                                class="flex justify-between text-xs font-bold text-slate-500"
                            >
                                <span
                                    >{{ task.submitted }} /
                                    {{ task.total }} Mengumpulkan</span
                                >
                                <span
                                    >{{
                                        Math.round(
                                            (task.submitted / task.total) * 100
                                        )
                                    }}%</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'materials'">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-lg text-slate-800">
                            Bahan Ajar & Modul
                        </h3>
                        <button
                            class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-indigo-700 transition"
                        >
                            <PlusIcon class="w-4 h-4" /> Upload Materi
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="file in materials"
                            :key="file.id"
                            class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between hover:border-indigo-200 transition"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-xl flex items-center justify-center"
                                    :class="
                                        file.type === 'pdf'
                                            ? 'bg-red-50 text-red-600'
                                            : 'bg-blue-50 text-blue-600'
                                    "
                                >
                                    <DocumentTextIcon
                                        v-if="file.type === 'pdf'"
                                        class="w-6 h-6"
                                    />
                                    <VideoCameraIcon v-else class="w-6 h-6" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800">
                                        {{ file.title }}
                                    </h4>
                                    <p class="text-xs text-slate-500">
                                        {{ file.size }} • {{ file.date }}
                                    </p>
                                </div>
                            </div>
                            <button
                                class="p-2 rounded-full hover:bg-slate-100 text-slate-400 hover:text-indigo-600 transition"
                            >
                                <ArrowDownTrayIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
