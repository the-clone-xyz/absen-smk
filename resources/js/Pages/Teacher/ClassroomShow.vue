<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import {
    ArrowLeftIcon,
    UserGroupIcon,
    BookOpenIcon,
    ClipboardDocumentListIcon,
    AcademicCapIcon,
    InformationCircleIcon,
    ChartBarIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    classroom: Object, // Data Utama Kelas (Nama, Mapel, Deskripsi, dll)
    students: Array, // Daftar Seluruh Anggota Kelas
    assignments: Array, // Rekap Seluruh Tugas yang pernah diberikan
    materials: Array, // Rekap Seluruh Modul/Materi yang tersedia
});

const activeTab = ref("overview");

const tabs = [
    { id: "overview", label: "Ringkasan Kelas" },
    { id: "students", label: "Anggota Kelas" },
    { id: "assignments", label: "Rekap Tugas" },
    { id: "materials", label: "Bank Materi" },
];
</script>

<template>
    <Head :title="`Detail Kelas ${classroom.name}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8FAFC] pb-20">
            <div class="bg-white border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-4">
                    <Link
                        :href="route('dashboard')"
                        class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-indigo-600 transition mb-6 font-medium"
                    >
                        <ArrowLeftIcon class="w-4 h-4" /> Kembali ke Panel Utama
                    </Link>

                    <div
                        class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6"
                    >
                        <div class="flex items-center gap-5">
                            <div
                                class="w-16 h-16 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200"
                            >
                                <AcademicCapIcon class="w-10 h-10" />
                            </div>
                            <div>
                                <h1
                                    class="text-3xl font-black text-slate-800 tracking-tight leading-none mb-2"
                                >
                                    {{ classroom.name }}
                                </h1>
                                <p
                                    class="text-slate-500 font-medium flex items-center gap-2"
                                >
                                    <BookOpenIcon
                                        class="w-4 h-4 text-indigo-500"
                                    />
                                    {{ classroom.subject }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div
                                class="bg-slate-50 px-6 py-3 rounded-2xl border border-slate-100 text-center"
                            >
                                <p
                                    class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1"
                                >
                                    Total Siswa
                                </p>
                                <p class="text-2xl font-black text-slate-700">
                                    {{ students.length }}
                                </p>
                            </div>
                            <div
                                class="bg-indigo-50 px-6 py-3 rounded-2xl border border-indigo-100 text-center"
                            >
                                <p
                                    class="text-[10px] text-indigo-400 font-bold uppercase tracking-widest mb-1"
                                >
                                    Total Sesi
                                </p>
                                <p class="text-2xl font-black text-indigo-700">
                                    {{ classroom.session_total }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-8 mt-10 border-b border-slate-100 overflow-x-auto scrollbar-hide"
                    >
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            class="pb-4 text-sm font-bold transition-all relative whitespace-nowrap"
                            :class="
                                activeTab === tab.id
                                    ? 'text-indigo-600'
                                    : 'text-slate-400 hover:text-slate-500'
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
                            class="col-span-2 bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm relative overflow-hidden"
                        >
                            <div class="relative z-10">
                                <div class="flex items-center gap-3 mb-6">
                                    <div
                                        class="p-2 bg-indigo-50 rounded-lg text-indigo-600"
                                    >
                                        <ChartBarIcon class="w-5 h-5" />
                                    </div>
                                    <h3
                                        class="text-lg font-bold text-slate-800"
                                    >
                                        Progress Kurikulum Smt. Ganjil
                                    </h3>
                                </div>
                                <div
                                    class="flex items-end justify-between mb-4"
                                >
                                    <div>
                                        <span
                                            class="text-4xl font-black text-slate-800"
                                            >{{
                                                classroom.session_completed
                                            }}</span
                                        >
                                        <span
                                            class="text-slate-400 font-bold ml-2 text-lg"
                                            >/
                                            {{ classroom.session_total }} Sesi
                                            Selesai</span
                                        >
                                    </div>
                                    <span
                                        class="text-indigo-600 font-black text-xl"
                                        >{{
                                            classroom.progress_percentage
                                        }}%</span
                                    >
                                </div>
                                <div
                                    class="w-full bg-slate-100 rounded-full h-4 overflow-hidden"
                                >
                                    <div
                                        class="bg-indigo-600 h-full rounded-full transition-all duration-1000"
                                        :style="{
                                            width:
                                                classroom.progress_percentage +
                                                '%',
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-indigo-600 rounded-[2.5rem] p-8 text-white shadow-xl shadow-indigo-100"
                        >
                            <h4
                                class="font-bold text-lg mb-4 flex items-center gap-2"
                            >
                                <InformationCircleIcon
                                    class="w-5 h-5 text-indigo-200"
                                />
                                Informasi Kelas
                            </h4>
                            <p
                                class="text-indigo-100 text-sm leading-relaxed font-medium"
                            >
                                {{ classroom.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="activeTab === 'students'"
                    class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden"
                >
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr
                                    class="bg-slate-50/50 text-slate-400 uppercase font-black text-[10px] tracking-[0.1em] border-b border-slate-100"
                                >
                                    <th class="px-6 py-5 w-16 text-center">
                                        No
                                    </th>
                                    <th class="px-4 py-5">Nama Lengkap</th>
                                    <th class="px-4 py-5">NIS / NISN</th>
                                    <th class="px-4 py-5">Tempat, Tgl Lahir</th>
                                    <th class="px-6 py-5 text-center">
                                        No. WA
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr
                                    v-for="(student, index) in students"
                                    :key="student.id"
                                    class="hover:bg-slate-50/50 transition group"
                                >
                                    <td
                                        class="px-6 py-5 text-center text-slate-400 font-bold"
                                    >
                                        {{ index + 1 }}
                                    </td>
                                    <td class="px-4 py-5">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-xs uppercase border border-indigo-100 shrink-0"
                                            >
                                                {{
                                                    student.name?.substring(
                                                        0,
                                                        2
                                                    )
                                                }}
                                            </div>
                                            <span
                                                class="font-bold text-slate-700 uppercase tracking-tight text-[11px]"
                                                >{{ student.name }}</span
                                            >
                                        </div>
                                    </td>
                                    <td class="px-4 py-5">
                                        <div class="flex flex-col">
                                            <span
                                                class="text-xs font-mono font-bold text-slate-600"
                                                >{{ student.nis || "-" }}</span
                                            >
                                            <span
                                                class="text-[10px] text-slate-400 italic"
                                                >NISN:
                                                {{ student.nisn || "-" }}</span
                                            >
                                        </div>
                                    </td>
                                    <td
                                        class="px-4 py-5 text-slate-600 text-xs font-medium"
                                    >
                                        {{ student.pob || "-" }},
                                        {{ student.dob || "-" }}
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <a
                                            v-if="student.phone"
                                            :href="
                                                'https://wa.me/' + student.phone
                                            "
                                            target="_blank"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-wider hover:bg-emerald-600 hover:text-white transition-all shadow-sm shadow-emerald-100"
                                        >
                                            <svg
                                                class="w-3.5 h-3.5"
                                                fill="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"
                                                />
                                            </svg>
                                            Kirim Pesan
                                        </a>
                                        <span
                                            v-else
                                            class="text-slate-300 text-[10px] font-bold italic"
                                            >Tidak Ada No.</span
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div
                    v-if="activeTab === 'assignments'"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <div
                        v-for="task in assignments"
                        :key="task.id"
                        class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-md transition"
                    >
                        <div
                            class="p-3 bg-orange-50 rounded-2xl text-orange-600 w-fit mb-4"
                        >
                            <ClipboardDocumentListIcon class="w-6 h-6" />
                        </div>
                        <h4
                            class="font-bold text-slate-800 mb-1 line-clamp-1 uppercase text-sm tracking-tight"
                        >
                            {{ task.title }}
                        </h4>
                        <p
                            class="text-xs text-slate-400 font-medium mb-4 italic"
                        >
                            Selesai pada: {{ task.deadline }}
                        </p>
                        <div
                            class="flex items-center justify-between text-[10px] font-black text-slate-500 uppercase tracking-widest pt-4 border-t border-slate-50"
                        >
                            <span>Tugas Ke-{{ task.id }}</span>
                            <span class="text-indigo-600"
                                >{{ task.submitted }} /
                                {{ task.total }} Siswa</span
                            >
                        </div>
                    </div>
                </div>

                <div
                    v-if="activeTab === 'materials'"
                    class="space-y-4 max-w-4xl"
                >
                    <div
                        v-for="file in materials"
                        :key="file.id"
                        class="bg-white p-5 rounded-2xl border border-slate-100 flex items-center justify-between hover:border-indigo-200 transition"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-xl flex items-center justify-center bg-blue-50 text-blue-600"
                            >
                                <BookOpenIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h4
                                    class="font-bold text-slate-800 text-sm uppercase"
                                >
                                    {{ file.title }}
                                </h4>
                                <p
                                    class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5"
                                >
                                    Materi Sesi {{ file.date }}
                                </p>
                            </div>
                        </div>
                        <a
                            :href="file.file_url"
                            download
                            class="p-2 bg-slate-50 text-slate-400 rounded-lg hover:text-indigo-600 hover:bg-indigo-50 transition"
                        >
                            <ArrowDownTrayIcon class="w-5 h-5" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
