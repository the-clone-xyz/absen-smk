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

// Props dari Controller
const props = defineProps({
    classes: Array,
});

// State Modal
const showModal = ref(false);
const isEditing = ref(false);
const modalTitle = ref("");

// Form Inertia
const form = useForm({
    id: null,
    name: "",
    level: "X",
});

const levelOptions = ["X", "XI", "XII"];

// --- ACTION HANDLERS ---

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    isEditing.value = false;
    modalTitle.value = "Tambah Kelas Baru";
    showModal.value = true;
};

const openEditModal = (item) => {
    form.clearErrors();
    form.id = item.id;
    form.name = item.name;
    form.level = item.level;
    isEditing.value = true;
    modalTitle.value = `Edit Kelas: ${item.name}`;
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

const deleteClass = (item) => {
    if (
        confirm(
            `Yakin hapus kelas ${item.name}? Data siswa di dalamnya akan kehilangan kelas.`
        )
    ) {
        router.delete(route("admin.classes.destroy", item.id));
    }
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};
</script>

<template>
    <Head title="Manajemen Kelas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-bold text-xl text-red-800 leading-tight flex items-center gap-2"
                >
                    <AcademicCapIcon class="w-6 h-6" /> Manajemen Kelas
                </h2>
                <button
                    @click="openCreateModal"
                    class="bg-red-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-red-700 text-sm font-bold shadow"
                >
                    <PlusIcon class="w-5 h-5" /> Tambah Kelas
                </button>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div
                    class="bg-white shadow-md rounded-xl overflow-hidden border border-gray-200"
                >
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-12"
                                >
                                    No
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                >
                                    Nama Kelas
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                >
                                    Tingkat
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase w-32"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="classes.length === 0">
                                <td
                                    colspan="4"
                                    class="px-6 py-8 text-center text-gray-400 text-sm"
                                >
                                    Belum ada data kelas. Silakan tambah baru.
                                </td>
                            </tr>
                            <tr
                                v-for="(item, index) in classes"
                                :key="item.id"
                                class="hover:bg-red-50/30 transition"
                            >
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ index + 1 }}
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-800">
                                    {{ item.name }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-1 rounded-full"
                                    >
                                        {{ item.level }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 text-center flex justify-center gap-3"
                                >
                                    <button
                                        @click="openEditModal(item)"
                                        class="text-blue-600 hover:text-blue-800"
                                        title="Edit"
                                    >
                                        <PencilSquareIcon class="w-5 h-5" />
                                    </button>
                                    <button
                                        @click="deleteClass(item)"
                                        class="text-red-600 hover:text-red-800"
                                        title="Hapus"
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
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
            @click="closeModal"
        >
            <div
                class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden"
                @click.stop
            >
                <div
                    class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50"
                >
                    <h3 class="font-bold text-gray-800">{{ modalTitle }}</h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-red-500"
                    >
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Nama Kelas</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"
                            placeholder="Contoh: XII RPL 1"
                            required
                        />
                        <p
                            v-if="form.errors.name"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Tingkat</label
                        >
                        <select
                            v-model="form.level"
                            class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"
                        >
                            <option
                                v-for="opt in levelOptions"
                                :key="opt"
                                :value="opt"
                            >
                                {{ opt }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.level"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ form.errors.level }}
                        </p>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium transition"
                            :disabled="form.processing"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 text-sm disabled:opacity-70 disabled:cursor-not-allowed transition-all shadow-md"
                        >
                            <svg
                                v-if="form.processing"
                                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
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

                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>{{
                                isEditing ? "Update Kelas" : "Simpan Kelas"
                            }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
