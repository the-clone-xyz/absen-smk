<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import {
    BookOpenIcon,
    TrophyIcon,
    CheckCircleIcon,
    LockClosedIcon,
    PlusIcon,
    TrashIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    classroom: Object,
    students: Array,
});

const globalCurriculum = ref({ tps: [""], material_scope: "" });
const studentAssessments = ref({});

// --- 1. WATCHER & INITIALIZATION ---
watch(
    () => props.students,
    (students) => {
        if (!students) return;

        // Init Checkbox Siswa
        students.forEach((student) => {
            if (!studentAssessments.value[student.id]) {
                try {
                    studentAssessments.value[student.id] = student.grade
                        ?.tp_assessment
                        ? JSON.parse(student.grade.tp_assessment)
                        : [];
                } catch (e) {
                    studentAssessments.value[student.id] = [];
                }
            }
        });

        // Init Data TP dari Siswa Pertama
        if (
            students.length > 0 &&
            globalCurriculum.value.tps.length === 1 &&
            globalCurriculum.value.tps[0] === ""
        ) {
            const firstStudent = students[0];
            try {
                if (firstStudent.grade?.learning_goals) {
                    let parsed = JSON.parse(firstStudent.grade.learning_goals);
                    if (!Array.isArray(parsed))
                        parsed = [firstStudent.grade.learning_goals];
                    globalCurriculum.value.tps = parsed;
                }
            } catch (e) {
                if (firstStudent.grade?.learning_goals)
                    globalCurriculum.value.tps = [
                        firstStudent.grade.learning_goals,
                    ];
            }
            if (
                !globalCurriculum.value.material_scope &&
                firstStudent.grade?.material_scope
            ) {
                globalCurriculum.value.material_scope =
                    firstStudent.grade.material_scope;
            }
        }
    },
    { immediate: true, deep: true }
);

// --- 2. HELPERS ---
const addTp = () => globalCurriculum.value.tps.push("");
const removeTp = (index) => {
    if (globalCurriculum.value.tps.length > 1)
        globalCurriculum.value.tps.splice(index, 1);
};

const isCurriculumFilled = computed(
    () =>
        globalCurriculum.value.tps.some((tp) => tp.trim().length > 0) &&
        globalCurriculum.value.material_scope.trim().length > 0
);

const formGrades = useForm({
    class_id: props.classroom.class_id,
    subject_id: props.classroom.subject_id,
    kkm: props.classroom.kkm,
    grades: [],
    master_tps: [],
});

const getPredikat = (score) => {
    const kkm = Number(props.classroom.kkm) || 75;
    const val = Number(score) || 0;
    const interval = (100 - kkm) / 3;
    if (val < kkm) return "D";
    if (val >= 100 - interval) return "A";
    if (val >= 100 - interval * 2) return "B";
    return "C";
};

const getPredikatColor = (score) => {
    const p = getPredikat(score);
    if (p === "A")
        return "bg-emerald-100 text-emerald-700 border-emerald-300 ring-1 ring-emerald-300";
    if (p === "B")
        return "bg-blue-100 text-blue-700 border-blue-300 ring-1 ring-blue-300";
    if (p === "C")
        return "bg-yellow-100 text-yellow-700 border-yellow-300 ring-1 ring-yellow-300";
    return "bg-rose-100 text-rose-700 border-rose-300 ring-1 ring-rose-300";
};

// --- 3. GENERATOR DESKRIPSI ---
const generateDescriptionParts = (studentId) => {
    const checks = studentAssessments.value[studentId];
    if (!checks) return { tercapai: "", belum: "" };

    const tps = globalCurriculum.value.tps;
    const achieved = [];
    const needed = [];

    tps.forEach((tp, idx) => {
        if (!tp || tp.trim() === "") return;
        if (!!checks[idx]) achieved.push(tp);
        else needed.push(tp);
    });

    const formatList = (list) => {
        if (list.length === 0) return "";
        if (list.length === 1) return list[0];
        const items = [...list];
        const last = items.pop();
        return items.join(", ") + " dan " + last;
    };

    return {
        tercapai:
            achieved.length > 0
                ? `Ananda menunjukkan pemahaman dalam ${formatList(achieved)}.`
                : "",
        belum:
            needed.length > 0
                ? `Ananda membutuhkan bimbingan dalam ${formatList(needed)}.`
                : "",
    };
};

const submitGrades = () => {
    if (!isCurriculumFilled.value)
        return alert("Mohon isi Tujuan Pembelajaran dan Lingkup Materi!");

    const cleanTps = globalCurriculum.value.tps.filter((t) => t.trim() !== "");
    formGrades.master_tps = cleanTps;

    formGrades.grades = props.students.map((student) => {
        const checks = studentAssessments.value[student.id] || [];
        const descParts = generateDescriptionParts(student.id);
        const finalDesc = [descParts.tercapai, descParts.belum]
            .filter(Boolean)
            .join("\n\n");

        return {
            student_id: student.id,
            final_score: student.final_score || 0,
            learning_goals: JSON.stringify(cleanTps),
            material_scope: globalCurriculum.value.material_scope,
            tp_assessment: checks,
            description: finalDesc,
        };
    });

    formGrades.post(route("teacher.grades.store"), { preserveScroll: true });
};
</script>

<template>
    <div class="space-y-8 pb-20">
        <div
            class="bg-white rounded-[1.5rem] border border-slate-200 shadow-sm overflow-hidden"
        >
            <div
                class="bg-slate-50 px-8 py-5 border-b border-slate-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="p-2 bg-white rounded-lg text-indigo-600 shadow-sm border border-indigo-100"
                    >
                        <BookOpenIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-lg">
                            Informasi Kurikulum (TP & Materi)
                        </h3>
                        <p class="text-sm text-slate-500">
                            Wajib diisi agar sistem bisa
                            <strong>meng-generate deskripsi rapor</strong>
                            secara otomatis.
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-8 grid grid-cols-1 lg:grid-cols-2 gap-10">
                <div class="space-y-4">
                    <div class="flex justify-between items-center mb-2">
                        <label
                            class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1"
                        >
                            <CheckCircleIcon class="w-4 h-4 text-emerald-500" />
                            Tujuan Pembelajaran (TP)
                        </label>
                        <button
                            @click="addTp"
                            type="button"
                            class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg font-bold hover:bg-indigo-700 transition flex items-center gap-1 shadow-sm shadow-indigo-200"
                        >
                            <PlusIcon class="w-4 h-4" /> Tambah TP
                        </button>
                    </div>

                    <div
                        class="space-y-3 max-h-[250px] overflow-y-auto pr-2 custom-scrollbar"
                    >
                        <div
                            v-for="(tp, index) in globalCurriculum.tps"
                            :key="index"
                            class="group relative flex gap-3 items-start"
                        >
                            <span
                                class="mt-3 text-xs font-mono text-slate-400 font-bold select-none min-w-[2.5rem]"
                                >TP-{{ index + 1 }}</span
                            >
                            <textarea
                                v-model="globalCurriculum.tps[index]"
                                rows="2"
                                class="flex-1 text-sm text-slate-700 bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all p-3 resize-none shadow-sm focus:bg-white"
                                :placeholder="`Contoh: Memahami konsep algoritma...`"
                            ></textarea>
                            <button
                                @click="removeTp(index)"
                                v-if="globalCurriculum.tps.length > 1"
                                class="mt-3 text-slate-300 hover:text-rose-500 transition"
                                title="Hapus baris ini"
                            >
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <label
                        class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1 mb-2"
                    >
                        <BookOpenIcon class="w-4 h-4 text-amber-500" /> Lingkup
                        Materi
                    </label>
                    <textarea
                        v-model="globalCurriculum.material_scope"
                        placeholder="Tuliskan materi inti yang dipelajari semester ini..."
                        class="w-full text-sm text-slate-700 bg-slate-50 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all p-4 resize-none shadow-sm focus:bg-white h-[250px]"
                    ></textarea>
                </div>
            </div>
        </div>

        <div
            class="bg-white rounded-[1.5rem] border border-slate-200 shadow-sm overflow-hidden flex flex-col relative"
            :class="{
                'opacity-100': isCurriculumFilled,
                'opacity-75 grayscale-[0.5]': !isCurriculumFilled,
            }"
        >
            <div
                v-if="!isCurriculumFilled"
                class="absolute inset-0 z-20 bg-slate-50/80 backdrop-blur-[3px] flex flex-col items-center justify-center gap-4"
            >
                <div
                    class="bg-white p-6 rounded-full shadow-2xl border border-indigo-100 animate-bounce"
                >
                    <LockClosedIcon class="w-12 h-12 text-indigo-400" />
                </div>
                <div
                    class="bg-white px-6 py-4 rounded-2xl shadow-xl border border-indigo-50 text-center max-w-md"
                >
                    <h4 class="font-bold text-slate-800 text-lg mb-1">
                        Akses Terkunci
                    </h4>
                    <p class="text-sm text-slate-500">
                        Silakan lengkapi <strong>Tujuan Pembelajaran</strong> &
                        <strong>Lingkup Materi</strong> di atas terlebih dahulu.
                    </p>
                </div>
            </div>

            <div
                class="p-6 border-b border-slate-200 flex flex-col md:flex-row justify-between items-center bg-slate-50 gap-4"
            >
                <div>
                    <h3
                        class="text-lg font-bold text-slate-800 flex items-center gap-2"
                    >
                        <TrophyIcon class="w-6 h-6 text-amber-500" />
                        Matriks Asesmen & Nilai Akhir
                    </h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Geser tabel ke samping untuk melihat deskripsi otomatis.
                    </p>
                </div>

                <button
                    @click="submitGrades"
                    :disabled="!isCurriculumFilled || formGrades.processing"
                    class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold text-sm shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:shadow-indigo-300 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transform active:scale-95"
                >
                    <span
                        v-if="formGrades.processing"
                        class="flex items-center gap-2"
                    >
                        <svg
                            class="animate-spin h-4 w-4 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        Menyimpan...
                    </span>
                    <span v-else class="flex items-center gap-2">
                        <CheckCircleIcon class="w-5 h-5" /> SIMPAN DATA RAPOT
                    </span>
                </button>
            </div>

            <div class="overflow-x-auto w-full relative">
                <table class="w-full text-left border-collapse min-w-[1200px]">
                    <thead>
                        <tr
                            class="bg-slate-100 text-slate-600 uppercase font-bold text-[11px] tracking-wider"
                        >
                            <th
                                rowspan="2"
                                class="px-4 py-4 w-14 text-center align-middle sticky left-0 z-100 bg-slate-100 border-r border-b border-slate-300"
                            >
                                No
                            </th>

                            <th
                                rowspan="2"
                                class="px-4 py-4 w-64 align-middle sticky left-14 z-100 bg-slate-30 border-r-2 border-b border-slate-300"
                            >
                                Nama Siswa
                            </th>

                            <th
                                :colspan="globalCurriculum.tps.length"
                                class="px-4 py-2 text-center bg-indigo-50 text-indigo-700 border-b border-indigo-100 border-r border-slate-300"
                            >
                                Capaian TP (Centang Jika Tercapai)
                            </th>
                            <th
                                rowspan="2"
                                class="px-4 py-4 w-32 text-center align-middle border-r border-b border-slate-300 bg-white"
                            >
                                Nilai Akhir
                            </th>
                            <th
                                rowspan="2"
                                class="px-4 py-4 w-24 text-center align-middle border-r border-b border-slate-300 bg-white"
                            >
                                Predikat
                            </th>
                            <th
                                rowspan="2"
                                class="px-6 py-4 w-[450px] align-middle bg-slate-50 border-b border-slate-300"
                            >
                                Deskripsi Otomatis (Preview)
                            </th>
                        </tr>

                        <tr
                            class="bg-slate-100 text-slate-600 uppercase font-bold text-[10px] tracking-wider"
                        >
                            <th
                                v-for="(tp, idx) in globalCurriculum.tps"
                                :key="idx"
                                class="px-2 py-3 text-center w-24 border-r border-b border-slate-300 last:border-r-0 hover:bg-white cursor-help transition bg-indigo-50/50"
                                :title="tp"
                            >
                                <div
                                    class="text-indigo-600 mb-1 font-extrabold"
                                >
                                    TP-{{ idx + 1 }}
                                </div>
                                <div
                                    class="text-[9px] font-normal normal-case leading-tight text-slate-500 line-clamp-2 min-h-[2.5em] opacity-80"
                                >
                                    {{ tp || "(Kosong)" }}
                                </div>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        <tr
                            v-for="(student, index) in props.students"
                            :key="student.id"
                            class="hover:bg-indigo-50/20 transition group bg-white"
                        >
                            <td
                                class="px-4 py-4 text-center font-bold text-slate-400 sticky left-0 z-10 bg-slate-50 border-r border-b border-slate-200"
                            >
                                {{ index + 1 }}
                            </td>

                            <td
                                class="px-4 py-4 sticky left-14 z-10 bg-white border-r-2 border-b border-slate-200"
                            >
                                <div
                                    class="font-bold text-slate-800 text-sm capitalize truncate max-w-[14rem]"
                                >
                                    {{ student.name.toLowerCase() }}
                                </div>
                                <div
                                    class="text-[11px] text-slate-400 font-mono mt-1 flex items-center gap-1"
                                >
                                    <span
                                        class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200"
                                    >
                                        {{ student.nis }}
                                    </span>
                                </div>
                            </td>

                            <td
                                v-for="(tp, idx) in globalCurriculum.tps"
                                :key="idx"
                                class="px-2 py-4 text-center border-r border-b border-slate-200 last:border-r-0 bg-indigo-50/5"
                            >
                                <div
                                    class="flex items-center justify-center h-full"
                                >
                                    <div
                                        v-if="studentAssessments[student.id]"
                                        class="relative"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="
                                                studentAssessments[student.id][
                                                    idx
                                                ]
                                            "
                                            :disabled="!isCurriculumFilled"
                                            class="w-6 h-6 rounded-md border-2 border-slate-300 text-indigo-600 focus:ring-offset-0 focus:ring-indigo-500 cursor-pointer disabled:bg-slate-100 disabled:border-slate-200 transition-all hover:border-indigo-400 checked:border-indigo-600 shadow-sm"
                                        />
                                    </div>
                                    <div
                                        v-else
                                        class="w-5 h-5 rounded-full border-2 border-slate-200 border-t-indigo-500 animate-spin"
                                    ></div>
                                </div>
                            </td>

                            <td
                                class="px-4 py-4 text-center border-r border-b border-slate-200 bg-white"
                            >
                                <input
                                    type="number"
                                    v-model="student.final_score"
                                    :disabled="!isCurriculumFilled"
                                    class="w-20 text-center font-black text-xl text-indigo-700 bg-white border-2 border-indigo-100 rounded-xl focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-2 shadow-sm"
                                    min="0"
                                    max="100"
                                    placeholder="0"
                                />
                            </td>

                            <td
                                class="px-4 py-4 text-center border-r border-b border-slate-200 bg-white"
                            >
                                <div
                                    class="w-10 h-10 rounded-full flex items-center justify-center font-black text-base mx-auto transition-all border"
                                    :class="
                                        getPredikatColor(student.final_score)
                                    "
                                >
                                    {{ getPredikat(student.final_score) }}
                                </div>
                            </td>

                            <td
                                class="px-6 py-4 text-xs bg-slate-50 border-b border-slate-200 align-top min-w-[400px]"
                            >
                                <div
                                    v-if="
                                        isCurriculumFilled &&
                                        studentAssessments[student.id]
                                    "
                                    class="flex flex-col h-full gap-0 border border-slate-200 rounded-lg bg-white shadow-sm overflow-hidden"
                                >
                                    <div
                                        class="p-3 border-b border-slate-100 bg-emerald-50/30 min-h-[60px] relative"
                                    >
                                        <div
                                            class="absolute top-0 left-0 w-1 h-full bg-emerald-400"
                                        ></div>
                                        <div
                                            v-if="
                                                generateDescriptionParts(
                                                    student.id
                                                ).tercapai
                                            "
                                        >
                                            <p
                                                class="text-emerald-800 leading-relaxed text-justify font-medium"
                                            >
                                                {{
                                                    generateDescriptionParts(
                                                        student.id
                                                    ).tercapai
                                                }}
                                            </p>
                                        </div>
                                        <div
                                            v-else
                                            class="text-slate-300 italic text-[10px] pt-1"
                                        >
                                            Belum ada TP yang tercapai.
                                        </div>
                                    </div>

                                    <div
                                        class="p-3 bg-rose-50/30 min-h-[60px] relative"
                                    >
                                        <div
                                            class="absolute top-0 left-0 w-1 h-full bg-rose-400"
                                        ></div>
                                        <div
                                            v-if="
                                                generateDescriptionParts(
                                                    student.id
                                                ).belum
                                            "
                                        >
                                            <p
                                                class="text-rose-700 leading-relaxed text-justify font-medium"
                                            >
                                                {{
                                                    generateDescriptionParts(
                                                        student.id
                                                    ).belum
                                                }}
                                            </p>
                                        </div>
                                        <div
                                            v-else
                                            class="text-slate-300 italic text-[10px] pt-1"
                                        >
                                            Tidak ada catatan bimbingan.
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="h-full flex items-center justify-center text-slate-400 italic bg-slate-50 border border-dashed border-slate-300 rounded-lg p-4"
                                >
                                    Menunggu input nilai...
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="bg-slate-50 border-t border-slate-200 p-4 text-xs text-slate-500 flex justify-between items-center"
            >
                <div class="flex gap-4">
                    <span class="flex items-center gap-1"
                        ><div
                            class="w-3 h-3 bg-emerald-100 border border-emerald-300 rounded"
                        ></div>
                        Tercapai</span
                    >
                    <span class="flex items-center gap-1"
                        ><div
                            class="w-3 h-3 bg-rose-100 border border-rose-300 rounded"
                        ></div>
                        Perlu Bimbingan</span
                    >
                </div>
                <div>*Data disimpan otomatis saat tombol Simpan ditekan.</div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
