<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import {
    ArrowLeftIcon,
    AcademicCapIcon,
    BookOpenIcon,
} from "@heroicons/vue/24/solid";

// Import Partials
import ClassroomOverview from "./Partials/ClassroomOverview.vue";
import ClassroomStudents from "./Partials/ClassroomStudents.vue";
import ClassroomAssignments from "./Partials/ClassroomAssignments.vue";
import ClassroomMaterials from "./Partials/ClassroomMaterials.vue";
import ClassroomGrades from "./Partials/ClassroomGrades.vue";

const props = defineProps({
    classroom: { type: Object, default: () => ({}) },
    students: { type: Array, default: () => [] },
    assignments: { type: Array, default: () => [] },
    materials: { type: Array, default: () => [] },
});

const tabs = [
    { id: "overview", label: "Ringkasan" },
    { id: "students", label: "Data Siswa" },
    { id: "assignments", label: "Tugas" },
    { id: "materials", label: "Materi" },
    { id: "grades", label: "Nilai Rapot" },
];

// --- LOGIKA TAB PERSISTENT (Anti Reset) ---
// 1. Cek URL saat halaman dimuat (misal: .../kelas/1?tab=assignments)
const params = new URLSearchParams(window.location.search);
const currentTabParam = params.get("tab");

// 2. Set Active Tab: Jika ada di URL pakai itu, jika tidak default 'overview'
const activeTab = ref(
    tabs.find((t) => t.id === currentTabParam) ? currentTabParam : "overview"
);

// 3. Fungsi Ganti Tab + Update URL tanpa Reload
const selectTab = (tabId) => {
    activeTab.value = tabId;

    // Update URL agar saat di-refresh atau back tetap di tab ini
    const url = new URL(window.location);
    url.searchParams.set("tab", tabId);
    window.history.replaceState({}, "", url);
};
</script>

<template>
    <Head :title="`Kelas ${classroom?.name}`" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8FAFC] pb-20" v-if="classroom">
            <div
                class="bg-white border-b border-gray-200 sticky top-0 z-20 shadow-sm"
            >
                <div
                    class="max-w-[95rem] mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-0"
                >
                    <div
                        class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-6"
                    >
                        <div class="flex items-center gap-5">
                            <div
                                class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200"
                            >
                                <AcademicCapIcon class="w-8 h-8" />
                            </div>
                            <div>
                                <div class="flex items-center gap-3">
                                    <h1
                                        class="text-3xl font-black text-slate-800 tracking-tight leading-none"
                                    >
                                        {{ classroom.name }}
                                    </h1>
                                    <span
                                        class="px-2.5 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-bold uppercase border border-indigo-100 tracking-wide"
                                        >KKM: {{ classroom.kkm }}</span
                                    >
                                </div>
                                <p
                                    class="text-slate-500 font-medium flex items-center gap-2 text-sm mt-1"
                                >
                                    <BookOpenIcon
                                        class="w-4 h-4 text-indigo-500"
                                    />
                                    {{ classroom.subject }}
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <Link
                                :href="route('teacher.dashboard')"
                                class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold hover:bg-slate-50 transition"
                            >
                                <ArrowLeftIcon class="w-4 h-4 inline mr-1" />
                                Kembali
                            </Link>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-8 overflow-x-auto scrollbar-hide"
                    >
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="selectTab(tab.id)"
                            class="pb-4 text-sm font-bold transition-all relative whitespace-nowrap px-2"
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

            <div class="max-w-[95rem] mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <ClassroomOverview
                    v-if="activeTab === 'overview'"
                    :classroom="classroom"
                />

                <ClassroomStudents
                    v-if="activeTab === 'students'"
                    :students="students"
                />

                <ClassroomAssignments
                    v-if="activeTab === 'assignments'"
                    :assignments="assignments"
                />

                <ClassroomMaterials
                    v-if="activeTab === 'materials'"
                    :materials="materials"
                />

                <ClassroomGrades
                    v-if="activeTab === 'grades'"
                    :classroom="classroom"
                    :students="students"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
