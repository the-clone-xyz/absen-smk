<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import {
    CalendarDaysIcon,
    PlusIcon,
    TrashIcon,
    FunnelIcon,
} from "@heroicons/vue/24/solid";
import { ref, watch } from "vue";

const props = defineProps({
    schedules: Array,
    classes: Array, 
    subjects: Array,
    teachers: Array,
    filters: Object,
});

const showModal = ref(false);
const selectedClass = ref(props.filters.class_id || "");

const form = useForm({
    class_id: selectedClass.value || "",
    subject_id: "",
    teacher_id: "",
    day: "Senin",
    start_time: "",
    end_time: "",
});

const days = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];

watch(selectedClass, (value) => {
    router.get(
        route("admin.schedules.index"),
        { class_id: value },
        { preserveState: true, replace: true }
    );
});

const submit = () => {
    form.post(route("admin.schedules.store"), {
        onSuccess: () => {
            showModal.value = false;
            form.reset("subject_id", "teacher_id", "start_time", "end_time");
            form.class_id = selectedClass.value;
        },
    });
};

const deleteSchedule = (id) => {
    if (confirm("Hapus jadwal ini?"))
        router.delete(route("admin.schedules.destroy", id));
};
</script>

<template>
    <Head title="Manajemen Jadwal" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-bold text-xl text-red-800 flex items-center gap-2"
                >
                    <CalendarDaysIcon class="w-6 h-6" /> Manajemen Jadwal
                </h2>
                <button
                    @click="showModal = true"
                    class="bg-red-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-red-700 flex items-center gap-1"
                >
                    <PlusIcon class="w-5 h-5" /> Tambah Jadwal
                </button>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div
                    class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6 flex items-center gap-4"
                >
                    <FunnelIcon class="w-5 h-5 text-gray-500" />
                    <span class="text-sm font-bold text-gray-700"
                        >Filter Kelas:</span
                    >
                    <select
                        v-model="selectedClass"
                        class="border-gray-300 rounded-lg focus:ring-red-500 text-sm min-w-[200px]"
                    >
                        <option value="">-- Semua Kelas --</option>
                        <option v-for="k in classes" :key="k.id" :value="k.id">
                            {{ k.name }}
                        </option>
                    </select>
                </div>

                <div
                    class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200"
                >
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                >
                                    Hari / Jam
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                >
                                    Kelas
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                >
                                    Mata Pelajaran
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                >
                                    Guru Pengajar
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="schedules.length === 0">
                                <td
                                    colspan="5"
                                    class="px-6 py-8 text-center text-gray-500"
                                >
                                    Belum ada jadwal. Silakan pilih kelas atau
                                    tambah baru.
                                </td>
                            </tr>
                            <tr
                                v-for="item in schedules"
                                :key="item.id"
                                class="hover:bg-red-50/20"
                            >
                                <td class="px-6 py-4">
                                    <span
                                        class="font-bold text-gray-800 block"
                                        >{{ item.day }}</span
                                    >
                                    <span
                                        class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded"
                                        >{{ item.start_time }} -
                                        {{ item.end_time }}</span
                                    >
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-700">
                                    {{ item.kelas?.name }}
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900">
                                    {{ item.subject.name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ item.teacher?.user?.name }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button
                                        @click="deleteSchedule(item.id)"
                                        class="text-red-500 hover:text-red-700"
                                    >
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        >
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    Tambah Jadwal Pelajaran
                </h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-xs font-bold text-gray-700 mb-1"
                                >Kelas</label
                            >
                            <select
                                v-model="form.class_id"
                                class="w-full rounded-lg border-gray-300 text-sm"
                                required
                            >
                                <option
                                    v-for="k in classes"
                                    :key="k.id"
                                    :value="k.id"
                                >
                                    {{ k.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-xs font-bold text-gray-700 mb-1"
                                >Hari</label
                            >
                            <select
                                v-model="form.day"
                                class="w-full rounded-lg border-gray-300 text-sm"
                                required
                            >
                                <option
                                    v-for="day in days"
                                    :key="day"
                                    :value="day"
                                >
                                    {{ day }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-gray-700 mb-1"
                            >Mata Pelajaran</label
                        >
                        <select
                            v-model="form.subject_id"
                            class="w-full rounded-lg border-gray-300 text-sm"
                            required
                        >
                            <option
                                v-for="s in subjects"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.name }} ({{ s.code }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-gray-700 mb-1"
                            >Guru Pengajar</label
                        >
                        <select
                            v-model="form.teacher_id"
                            class="w-full rounded-lg border-gray-300 text-sm"
                            required
                        >
                            <option
                                v-for="t in teachers"
                                :key="t.id"
                                :value="t.id"
                            >
                                {{ t.user.name }}
                            </option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-xs font-bold text-gray-700 mb-1"
                                >Jam Mulai</label
                            >
                            <input
                                type="time"
                                v-model="form.start_time"
                                class="w-full rounded-lg border-gray-300 text-sm"
                                required
                            />
                        </div>
                        <div>
                            <label
                                class="block text-xs font-bold text-gray-700 mb-1"
                                >Jam Selesai</label
                            >
                            <input
                                type="time"
                                v-model="form.end_time"
                                class="w-full rounded-lg border-gray-300 text-sm"
                                required
                            />
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button
                            type="button"
                            @click="showModal = false"
                            class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-bold"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
