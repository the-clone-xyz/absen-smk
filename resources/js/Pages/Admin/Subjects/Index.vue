<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import {
    BookOpenIcon,
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    XMarkIcon,
    HashtagIcon,
} from "@heroicons/vue/24/solid";
import { ref } from "vue";

// Props dari Controller
const props = defineProps({
    subjects: Array,
});

// State Modal
const showModal = ref(false);
const isEditing = ref(false);
const modalTitle = ref("");

// Form Inertia
const form = useForm({
    id: null,
    name: "",
    code: "",
});

// --- ACTION HANDLERS ---

const openCreateModal = () => {
    form.reset();
    form.clearErrors();
    isEditing.value = false;
    modalTitle.value = "Tambah Mata Pelajaran";
    showModal.value = true;
};

const openEditModal = (item) => {
    form.clearErrors();
    form.id = item.id;
    form.name = item.name;
    form.code = item.code;
    isEditing.value = true;
    modalTitle.value = `Edit Mapel: ${item.name}`;
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.patch(route("admin.subjects.update", form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("admin.subjects.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteSubject = (item) => {
    if (
        confirm(
            `Yakin hapus mata pelajaran "${item.name}"? Jadwal yang menggunakan mapel ini juga akan terhapus.`
        )
    ) {
        router.delete(route("admin.subjects.destroy", item.id));
    }
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};
</script>

<template>
    <Head title="Manajemen Mata Pelajaran" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-bold text-xl text-red-800 leading-tight flex items-center gap-2"
                >
                    <BookOpenIcon class="w-6 h-6" /> Data Mata Pelajaran
                </h2>
                <button
                    @click="openCreateModal"
                    class="bg-red-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-red-700 text-sm font-bold shadow transition active:scale-95"
                >
                    <PlusIcon class="w-5 h-5" /> Tambah Mapel
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
                                    Kode
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                >
                                    Nama Mata Pelajaran
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase w-32"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="subjects.length === 0">
                                <td
                                    colspan="4"
                                    class="px-6 py-8 text-center text-gray-400 text-sm"
                                >
                                    Belum ada mata pelajaran. Silakan tambah
                                    baru.
                                </td>
                            </tr>
                            <tr
                                v-for="(item, index) in subjects"
                                :key="item.id"
                                class="hover:bg-red-50/30 transition"
                            >
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ index + 1 }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        v-if="item.code"
                                        class="bg-gray-100 text-gray-600 text-xs font-mono font-bold px-2 py-1 rounded border border-gray-300"
                                    >
                                        {{ item.code }}
                                    </span>
                                    <span
                                        v-else
                                        class="text-gray-400 text-xs italic"
                                        >-</span
                                    >
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-800">
                                    {{ item.name }}
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
                                        @click="deleteSubject(item)"
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
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
            @click="closeModal"
        >
            <div
                class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden"
                @click.stop
            >
                <div
                    class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50"
                >
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <BookOpenIcon class="w-5 h-5 text-red-600" />
                        {{ modalTitle }}
                    </h3>
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
                            >Nama Mata Pelajaran</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"
                            placeholder="Contoh: Matematika Wajib"
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
                            >Kode Mapel (Opsional)</label
                        >
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <HashtagIcon class="h-4 w-4 text-gray-400" />
                            </div>
                            <input
                                v-model="form.code"
                                type="text"
                                class="pl-10 w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500 uppercase"
                                placeholder="MTK-01"
                            />
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            *Kode unik untuk identifikasi (Contoh: MTK-X)
                        </p>
                        <p
                            v-if="form.errors.code"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ form.errors.code }}
                        </p>
                    </div>

                    <div
                        class="flex justify-end gap-2 pt-4 border-t border-gray-100 mt-4"
                    >
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
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 text-sm disabled:opacity-70 disabled:cursor-not-allowed transition-all shadow-md min-w-[120px] justify-center"
                        >
                            <div
                                v-if="form.processing"
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
                                <span>Proses...</span>
                            </div>
                            <span v-else>
                                {{ isEditing ? "Update Data" : "Simpan Data" }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
