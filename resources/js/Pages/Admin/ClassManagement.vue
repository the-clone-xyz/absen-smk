<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import {
    AcademicCapIcon,
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    XMarkIcon,
} from "@heroicons/vue/24/solid";
import { ref } from "vue";

const props = defineProps({
    classes: Array,
});

// State untuk Modal
const showModal = ref(false);
const isEditing = ref(false);
const modalTitle = ref("");

// Form Data (untuk Tambah/Edit)
const form = useForm({
    id: null,
    name: "",
    level: "X",
});

// Opsi Level Kelas
const levelOptions = ["X", "XI", "XII"];

// ---------------------------
// FUNGSI UTAMA
// ---------------------------

const openCreateModal = () => {
    form.reset();
    isEditing.value = false;
    modalTitle.value = "Tambah Kelas Baru";
    showModal.value = true;
};

const openEditModal = (kelas) => {
    form.id = kelas.id;
    form.name = kelas.name;
    form.level = kelas.level;
    isEditing.value = true;
    modalTitle.value = `Edit Kelas: ${kelas.name}`;
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.patch(route("admin.classes.update", form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("admin.classes.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteKelas = (kelas) => {
    if (
        confirm(
            `Apakah Anda yakin ingin menghapus Kelas "${kelas.name}"? Semua siswa di kelas ini akan kehilangan penugasan kelasnya.`
        )
    ) {
        router.delete(route("admin.classes.destroy", kelas.id));
    }
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    // Gunakan router.reload untuk refresh data dan flash message
    router.reload({ only: ["classes"] });
};
</script>

<template>
    <Head title="Manajemen Kelas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-bold text-xl text-red-800 leading-tight flex items-center gap-3"
                >
                    <AcademicCapIcon class="w-6 h-6 text-red-600" />
                    Manajemen Kelas
                </h2>
                <button
                    @click="openCreateModal"
                    class="px-4 py-2 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition duration-150 flex items-center gap-1"
                >
                    <PlusIcon class="w-5 h-5" /> Tambah Kelas
                </button>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div
                    class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-12"
                                    >
                                        #
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Nama Kelas
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Level
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-if="classes.length === 0">
                                    <td
                                        colspan="4"
                                        class="text-center py-6 text-gray-500"
                                    >
                                        Belum ada data kelas.
                                    </td>
                                </tr>
                                <tr
                                    v-for="(kelas, index) in classes"
                                    :key="kelas.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ index + 1 }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                    >
                                        {{ kelas.name }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                    >
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
                                        >
                                            {{ kelas.level }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                    >
                                        <div class="flex gap-2">
                                            <button
                                                @click="openEditModal(kelas)"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                <PencilSquareIcon
                                                    class="w-5 h-5"
                                                />
                                            </button>
                                            <button
                                                @click="deleteKelas(kelas)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                <TrashIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="showModal"
            class="fixed inset-0 z-[60] overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
            >
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true"
                    @click="closeModal"
                ></div>

                <span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen"
                    aria-hidden="true"
                    >&#8203;</span
                >

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div
                            class="flex justify-between items-center pb-3 border-b"
                        >
                            <h3
                                class="text-lg leading-6 font-bold text-gray-900"
                                id="modal-title"
                            >
                                {{ modalTitle }}
                            </h3>
                            <button
                                @click="closeModal"
                                class="text-gray-400 hover:text-gray-600"
                            >
                                <XMarkIcon class="w-6 h-6" />
                            </button>
                        </div>

                        <form @submit.prevent="submitForm" class="mt-4">
                            <div class="space-y-4">
                                <div>
                                    <label
                                        for="name"
                                        class="block text-sm font-medium text-gray-700"
                                        >Nama Kelas (Contoh: XI RPL 2)</label
                                    >
                                    <input
                                        type="text"
                                        id="name"
                                        v-model="form.name"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    />
                                    <div
                                        v-if="form.errors.name"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="level"
                                        class="block text-sm font-medium text-gray-700"
                                        >Level Kelas</label
                                    >
                                    <select
                                        id="level"
                                        v-model="form.level"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    >
                                        <option
                                            v-for="level in levelOptions"
                                            :key="level"
                                            :value="level"
                                        >
                                            {{ level }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.level"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ form.errors.level }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="mr-3 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm"
                                >
                                    Batal
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm disabled:opacity-50"
                                >
                                    <span v-if="form.processing"
                                        >Menyimpan...</span
                                    >
                                    <span v-else>{{
                                        isEditing ? "Update" : "Simpan"
                                    }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
