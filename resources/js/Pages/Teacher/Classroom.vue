<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link, router } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted, computed } from "vue";
import { ArrowLeftIcon, QrCodeIcon, ClockIcon } from "@heroicons/vue/24/solid";

// Import Komponen Pecahan
import JournalForm from "./Partials/JournalForm.vue";
import AttendanceList from "./Partials/AttendanceList.vue";
import QrModal from "./Partials/QrModal.vue";

const props = defineProps({
    schedule: Object,
    students: Array,
    existingJournal: Object,
});

const isEditing = computed(() => !!props.existingJournal);
const showQRModal = ref(false);
let pollingInterval = null;

// Form Utama (Data dikirim ke child component via props)
const form = useForm({
    schedule_id: props.schedule.id,
    topic: props.existingJournal?.topic || "",
    notes: props.existingJournal?.notes || "",
    module: null,
    attendances: props.students.reduce((acc, student) => {
        let status = "Alpha";
        if (props.existingJournal?.attendances) {
            const record = props.existingJournal.attendances.find(
                (a) => a.student_id === student.id
            );
            if (record) status = record.status;
        }
        acc[student.id] = status;
        return acc;
    }, {}),
});

// Hitung Rekap untuk dikirim ke JournalForm
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
    return { h, s, i, a };
});

const startPolling = () => {
    pollingInterval = setInterval(() => {
        // Hanya polling jika belum mode Edit
        if (!isEditing.value) {
            // Panggil API getClassData
            fetch(route("teacher.classroom.data", props.schedule.id))
                .then((res) => res.json())
                .then((data) => {
                    // Loop data siswa terbaru dari server
                    data.students.forEach((student) => {
                        // Jika status di server BUKAN Alpha (artinya sudah absen), update tampilan
                        if (student.status && student.status !== "Alpha") {
                            form.attendances[student.id] = student.status;
                        }
                    });
                })
                .catch((err) => console.error("Polling error:", err));
        }
    }, 3000); // Cek setiap 3 detik
};

onMounted(() => {
    startPolling();
});
onUnmounted(() => {
    clearInterval(pollingInterval);
});
</script>

<template>
    <Head title="Mengajar Kelas" />

    <AuthenticatedLayout>
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
                            <span
                                v-if="isEditing"
                                class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded text-[10px] font-bold border border-yellow-200"
                                >MODE EDIT</span
                            >
                        </p>
                    </div>
                </div>

                <button
                    @click="showQRModal = true"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2 text-sm font-bold shadow transition animate-pulse hover:animate-none"
                >
                    <QrCodeIcon class="w-5 h-5" /> QR Kelas
                </button>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div
                class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8"
            >
                <div class="lg:col-span-1">
                    <JournalForm
                        :form="form"
                        :is-editing="isEditing"
                        :existing-journal="existingJournal"
                        :summary="summary"
                    />
                </div>

                <div class="lg:col-span-2">
                    <AttendanceList :students="students" :form="form" />
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
    </AuthenticatedLayout>
</template>
