<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link, usePage } from "@inertiajs/vue3";
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

// Pantau pesan Sukses dari Controller
watch(
    () => page.props.flash.success,
    (msg) => {
        if (msg) {
            flashMessage.value = msg;
            flashType.value = "success";
            showFlash.value = true;
            setTimeout(() => (showFlash.value = false), 4000);
        }
    }
);

// Pantau pesan Error (jika ada error validasi global)
watch(
    () => page.props.errors,
    (errors) => {
        if (Object.keys(errors).length > 0) {
            flashMessage.value = "Gagal menyimpan. Periksa isian form.";
            flashType.value = "error";
            showFlash.value = true;
            setTimeout(() => (showFlash.value = false), 4000);
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
const form = useForm({
    schedule_id: props.schedule.id,
    topic: props.existingJournal?.topic || "",
    notes: props.existingJournal?.notes || "",
    module: null,
    attendances: props.students.reduce((acc, student) => {
        let status = "Alpha";
        if (student.attendance && student.attendance.status) {
            status = student.attendance.status; // Prioritas 1: Absen Harian
        } else if (props.existingJournal?.attendances) {
            const record = props.existingJournal.attendances.find(
                (a) => a.student_id === student.id
            );
            if (record) status = record.status; // Prioritas 2: History Jurnal
        }
        acc[student.id] = status;
        return acc;
    }, {}),
});

// Update status dari Child Component (AttendanceList)
const updateAttendanceStatus = ({ studentId, status }) => {
    form.attendances[studentId] = status;
};

// Handle File Error dari Child (JournalForm)
const hasFileError = ref(false);
const handleFileError = (isError) => {
    hasFileError.value = isError;
};

// --- TOMBOL SIMPAN ---
const submitAll = () => {
    form.post(route("teacher.journal.store"), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            // Sukses ditangani oleh watch(flash.success)
        },
        onError: () => {
            // Error ditangani oleh watch(errors)
        },
    });
};

const isFormInvalid = computed(() => {
    return !form.topic || !form.notes || hasFileError.value || form.processing;
});

// --- REALTIME POLLING ---
let pollingInterval = null;
const startPolling = () => {
    pollingInterval = setInterval(() => {
        if (!isEditing.value) {
            fetch(route("teacher.classroom.data", props.schedule.id))
                .then((res) => res.json())
                .then((data) => {
                    data.students.forEach((student) => {
                        // Update jadi Hadir jika siswa scan QR & status di layar guru masih Alpha
                        if (
                            student.status === "Hadir" &&
                            form.attendances[student.id] === "Alpha"
                        ) {
                            form.attendances[student.id] = "Hadir";
                        }
                    });
                })
                .catch((err) => console.error("Polling error:", err));
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
            <div class="flex justify-between items-center">
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
                            {{ schedule.class.name }} •
                            <ClockIcon class="w-3 h-3" />
                            {{ schedule.start_time.substring(0, 5) }}
                        </p>
                    </div>
                </div>
                <button
                    @click="showQRModal = true"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2 text-sm font-bold shadow animate-pulse hover:animate-none"
                >
                    <QrCodeIcon class="w-5 h-5" /> Buka QR Kelas
                </button>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 h-[calc(100vh-140px)]">
            <div
                class="max-w-[1600px] mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6 h-full"
            >
                <div class="lg:col-span-3 h-full overflow-hidden">
                    <JournalForm
                        :form="form"
                        :existing-file="existingJournal?.module_path"
                        @file-error="handleFileError"
                    />
                </div>

                <div
                    class="lg:col-span-5 h-full overflow-hidden flex flex-col gap-4"
                >
                    <AttendanceList
                        :students="students"
                        :attendances="form.attendances"
                        :summary="summary"
                        @update-status="updateAttendanceStatus"
                    />

                    <button
                        @click="submitAll"
                        :disabled="isFormInvalid"
                        class="py-3 rounded-xl font-bold shadow-md transition flex items-center justify-center gap-2 text-sm w-full active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
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
                        <CheckCircleIcon v-else class="w-5 h-5" />
                        {{ form.processing ? "Menyimpan..." : "Simpan" }}
                    </button>
                </div>

                <div class="lg:col-span-4 h-full overflow-hidden">
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
            :class-name="schedule.class.name"
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
