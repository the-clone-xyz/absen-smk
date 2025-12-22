<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link, usePage, router } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted, computed, watch } from "vue";
import {
    ArrowLeftIcon,
    QrCodeIcon,
    ClockIcon,
    CheckCircleIcon,
    XMarkIcon,
    ExclamationTriangleIcon,
} from "@heroicons/vue/24/solid";

// Import Partials
import JournalForm from "./Partials/JournalForm.vue";
import AttendanceList from "./Partials/AttendanceList.vue";
import TaskList from "./Partials/TaskList.vue";
import QrModal from "./Partials/QrModal.vue";
import GradingModal from "./Partials/GradingModal.vue";
import TaskModal from "./Partials/TaskModal.vue";

const props = defineProps({
    schedule: Object,
    students: Array,
    existingJournal: Object,
    tasks: Array,
});

// --- FLASH MESSAGE LOGIC ---
const page = usePage();
const showFlash = ref(false);
const flashMessage = ref("");
const flashType = ref("success"); // success / error

// Helper untuk menampilkan flash
const triggerFlash = (msg, type = "success") => {
    flashMessage.value = msg;
    flashType.value = type;
    showFlash.value = true;
    setTimeout(() => (showFlash.value = false), 4000);
};

// Pantau pesan Sukses dari Controller
watch(
    () => page.props.flash.success,
    (msg) => {
        if (msg) triggerFlash(msg, "success");
    }
);

// Pantau pesan Error Global
watch(
    () => page.props.errors,
    (errors) => {
        if (Object.keys(errors).length > 0) {
            triggerFlash("Gagal menyimpan. Periksa isian form.", "error");
        }
    }
);

const isEditing = computed(() => !!props.existingJournal);
const showQRModal = ref(false);
const showGradingModal = ref(false);
const showTaskModal = ref(false);
const selectedTaskToEdit = ref(null);
const selectedSubmission = ref(null);

// --- FORM UTAMA ---
// Inisialisasi Absensi: Prioritas Absen Harian -> History Jurnal -> Default Alpha
const initialAttendances = props.students.reduce((acc, student) => {
    let status = "Alpha";
    if (student.attendance?.status) {
        status = student.attendance.status;
    } else if (props.existingJournal?.attendances) {
        const record = props.existingJournal.attendances.find(
            (a) => a.student_id === student.id
        );
        if (record) status = record.status;
    }
    acc[student.id] = status;
    return acc;
}, {});

const form = useForm({
    schedule_id: props.schedule.id,
    topic: props.existingJournal?.topic || "",
    notes: props.existingJournal?.notes || "",
    module: null,
    attendances: initialAttendances,
});

// Update status dari Child Component
const updateAttendanceStatus = ({ studentId, status }) => {
    form.attendances[studentId] = status;
};

// Handle File Error
const hasFileError = ref(false);
const handleFileError = (isError) => {
    hasFileError.value = isError;
};

// --- TOMBOL SIMPAN ---
const submitAll = () => {
    form.post(route("teacher.journal.store"), {
        preserveScroll: true,
        forceFormData: true, // Wajib true karena ada upload file (module)
        onSuccess: () => {
            form.reset("module"); // Reset input file setelah sukses
        },
    });
};

const isFormInvalid = computed(() => {
    return !form.topic || !form.notes || hasFileError.value || form.processing;
});

// Prevent Navigation if Form is Dirty (Opsional, UX yang baik)
// onUnmounted(() => {
//     if (form.isDirty && !form.wasSuccessful) {
//         if (!confirm('Perubahan belum disimpan. Yakin ingin keluar?')) return false;
//     }
// });

// --- REALTIME POLLING ---
let pollingInterval = null;
const isFetching = ref(false); // Flag untuk mencegah request menumpuk

const startPolling = () => {
    pollingInterval = setInterval(async () => {
        // Jangan poll jika sedang edit jurnal lama, tab tidak aktif, atau request sebelumnya belum selesai
        if (!isEditing.value && !document.hidden && !isFetching.value) {
            isFetching.value = true;
            try {
                const res = await fetch(
                    route("teacher.classroom.data", props.schedule.id)
                );
                if (!res.ok) throw new Error("Network response was not ok");

                const data = await res.json();

                data.students.forEach((student) => {
                    // Update jadi Hadir HANYA jika siswa scan QR (status db Hadir)
                    // DAN status di layar guru masih "Alpha".
                    // Jika guru sudah manual set "Izin/Sakit", jangan di-override.
                    if (
                        student.status === "Hadir" &&
                        form.attendances[student.id] === "Alpha"
                    ) {
                        form.attendances[student.id] = "Hadir";
                        // Opsional: Berikan visual feedback kecil (toast/highlight) disini
                    }
                });
            } catch (err) {
                console.error("Polling error:", err);
            } finally {
                isFetching.value = false;
            }
        }
    }, 4000);
};

// --- TASK HELPERS ---
const openCreateTask = () => {
    selectedTaskToEdit.value = null;
    showTaskModal.value = true;
};
const openEditTask = (task) => {
    selectedTaskToEdit.value = task;
    showTaskModal.value = true;
};
const openGrading = (data) => {
    selectedSubmission.value = data;
    showGradingModal.value = true;
};

// Hitung rekap kehadiran real-time
const summary = computed(() => {
    let h = 0,
        s = 0,
        i = 0,
        a = 0;
    Object.values(form.attendances).forEach((status) => {
        if (status === "Hadir") h++;
        else if (status === "Sakit") s++;
        else if (status === "Izin") i++;
        else a++;
    });
    return { h, s, i, a, total: props.students.length };
});

onMounted(() => startPolling());
onUnmounted(() => {
    if (pollingInterval) clearInterval(pollingInterval);
});
</script>

<template>
    <Head :title="`Mengajar - ${schedule.subject.name}`" />

    <AuthenticatedLayout>
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showFlash"
                class="fixed top-24 right-6 z-50 px-6 py-4 rounded-xl shadow-2xl flex items-center gap-4 border pointer-events-auto min-w-[300px]"
                :class="
                    flashType === 'success'
                        ? 'bg-green-600 text-white border-green-500'
                        : 'bg-red-600 text-white border-red-500'
                "
            >
                <div class="bg-white/20 p-2 rounded-full">
                    <CheckCircleIcon
                        v-if="flashType === 'success'"
                        class="w-6 h-6"
                    />
                    <ExclamationTriangleIcon v-else class="w-6 h-6" />
                </div>
                <div class="flex-grow">
                    <h4 class="font-bold text-sm uppercase tracking-wide">
                        {{ flashType === "success" ? "Berhasil" : "Perhatian" }}
                    </h4>
                    <p class="text-xs opacity-90">{{ flashMessage }}</p>
                </div>
                <button
                    @click="showFlash = false"
                    class="text-white/60 hover:text-white transition"
                >
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>
        </transition>

        <template #header>
            <div
                class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
            >
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('teacher.dashboard')"
                        class="p-2 rounded-full hover:bg-gray-200 transition text-gray-500"
                    >
                        <ArrowLeftIcon class="w-5 h-5" />
                    </Link>
                    <div>
                        <h2
                            class="font-bold text-xl text-blue-900 leading-tight"
                        >
                            {{ schedule.subject.name }}
                        </h2>
                        <p
                            class="text-sm text-gray-500 flex items-center gap-1"
                        >
                            {{
                                schedule.kelas?.name ??
                                schedule.class?.name ??
                                "Kelas"
                            }}
                            •
                            <ClockIcon class="w-3 h-3" />
                            {{ schedule.start_time.substring(0, 5) }}
                        </p>
                    </div>
                </div>
                <button
                    @click="showQRModal = true"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2 text-sm font-bold shadow animate-pulse hover:animate-none w-full sm:w-auto justify-center"
                >
                    <QrCodeIcon class="w-5 h-5" /> Buka QR Kelas
                </button>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 min-h-[calc(100vh-140px)]">
            <div
                class="max-w-[1600px] mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6 h-full"
            >
                <div
                    class="lg:col-span-3 h-full lg:overflow-y-auto custom-scrollbar"
                >
                    <JournalForm
                        :form="form"
                        :existing-file="existingJournal?.module_path"
                        @file-error="handleFileError"
                    />
                </div>

                <div class="lg:col-span-5 h-full flex flex-col gap-4">
                    <div class="flex-grow lg:overflow-y-auto custom-scrollbar">
                        <AttendanceList
                            :students="students"
                            :attendances="form.attendances"
                            :summary="summary"
                            @update-status="updateAttendanceStatus"
                        />
                    </div>

                    <button
                        @click="submitAll"
                        :disabled="isFormInvalid"
                        class="py-3 rounded-xl font-bold shadow-md transition flex items-center justify-center gap-2 text-sm w-full active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed mb-4 lg:mb-0"
                        :class="
                            isFormInvalid
                                ? 'bg-gray-400 text-gray-200 cursor-not-allowed'
                                : 'bg-green-600 text-white hover:bg-green-700'
                        "
                    >
                        <span
                            v-if="form.processing"
                            class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"
                        ></span>
                        <template v-else>
                            <CheckCircleIcon class="w-5 h-5" />
                            {{
                                form.processing
                                    ? "Menyimpan..."
                                    : "Simpan Jurnal & Absensi"
                            }}
                        </template>
                    </button>
                </div>

                <div
                    class="lg:col-span-4 h-full lg:overflow-y-auto custom-scrollbar"
                >
                    <TaskList
                        :tasks="tasks"
                        @create="openCreateTask"
                        @edit="openEditTask"
                        @grade="openGrading"
                    />
                </div>
            </div>
        </div>

        <QrModal
            v-if="showQRModal"
            :schedule-id="schedule.id"
            :subject-name="schedule.subject.name"
            :class-name="schedule.kelas?.name ?? schedule.class?.name"
            @close="showQRModal = false"
        />

        <TaskModal
            :show="showTaskModal"
            :task="selectedTaskToEdit"
            :schedule="schedule"
            @close="showTaskModal = false"
        />

        <GradingModal
            v-if="showGradingModal"
            :data="selectedSubmission"
            @close="showGradingModal = false"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
/* Opsional: Style untuk scrollbar agar rapi di desktop */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
