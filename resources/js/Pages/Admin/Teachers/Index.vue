<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    UserGroupIcon,
    PlusIcon,
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon,
    AcademicCapIcon,
    XMarkIcon,
    KeyIcon,
} from "@heroicons/vue/24/solid";
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    teachers: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const showEditModal = ref(false);
const editingTeacher = ref(null);

// Form Edit
const form = useForm({
    id: "",
    name: "",
    nip: "",
    phone: "",
    address: "",
    password_option: "", // '', 'manual', 'random'
    new_password: "",
});

// Search
watch(search, (value) => {
    router.get(
        route("admin.teachers.index"),
        { search: value },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        }
    );
});

// Buka Modal Edit
const openEditModal = (teacher) => {
    editingTeacher.value = teacher;
    form.id = teacher.id;
    form.name = teacher.user.name;
    form.nip = teacher.nip;
    form.phone = teacher.phone;
    form.address = teacher.address;
    form.password_option = ""; // Reset pilihan password
    form.new_password = "";
    showEditModal.value = true;
};

// Submit Edit
const submitEdit = () => {
    form.patch(route("admin.teachers.update", form.id), {
        onSuccess: () => {
            showEditModal.value = false;
            form.reset();
        },
    });
};

const deleteTeacher = (item) => {
    if (confirm(`Yakin hapus Guru ${item.user.name}?`)) {
        router.delete(route("admin.teachers.destroy", item.id));
    }
};
</script>

<template>
    <Head title="Manajemen Guru" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-bold text-xl text-red-800 leading-tight flex items-center gap-2"
                >
                    <AcademicCapIcon class="w-6 h-6" /> Data Guru
                </h2>
                <Link
                    :href="route('admin.teachers.create')"
                    class="bg-red-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-red-700 text-sm font-bold shadow transition active:scale-95"
                >
                    <PlusIcon class="w-5 h-5" /> Tambah Guru
                </Link>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-end mb-4">
                    <div class="relative w-full max-w-md">
                        <div
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                        >
                            <MagnifyingGlassIcon
                                class="h-5 w-5 text-gray-400"
                            />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 sm:text-sm"
                            placeholder="Cari Nama / NIP..."
                        />
                    </div>
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
                                    Nama Guru
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                >
                                    NIP
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                >
                                    Kontak
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr
                                v-for="item in teachers.data"
                                :key="item.id"
                                class="hover:bg-red-50/30"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <img
                                            class="h-10 w-10 rounded-full object-cover border border-gray-200"
                                            :src="
                                                item.user.avatar
                                                    ? '/storage/' +
                                                      item.user.avatar
                                                    : `https://ui-avatars.com/api/?name=${item.user.name}&background=random`
                                            "
                                        />
                                        <div class="ml-4">
                                            <div
                                                class="text-sm font-bold text-gray-900"
                                            >
                                                {{ item.user.name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ item.user.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 font-mono text-sm text-gray-700"
                                >
                                    {{ item.nip }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ item.phone }} <br />
                                    <span class="text-xs text-gray-400">{{
                                        item.address || "-"
                                    }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button
                                            @click="openEditModal(item)"
                                            class="text-blue-600 hover:text-blue-800 bg-blue-50 p-2 rounded-lg transition"
                                        >
                                            <PencilSquareIcon class="w-5 h-5" />
                                        </button>
                                        <button
                                            @click="deleteTeacher(item)"
                                            class="text-red-600 hover:text-red-800 bg-red-50 p-2 rounded-lg transition"
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

        <div
            v-if="showEditModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
        >
            <div
                class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto"
            >
                <div
                    class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 sticky top-0"
                >
                    <h3 class="font-bold text-gray-800 text-lg">
                        Edit Data Guru
                    </h3>
                    <button
                        @click="showEditModal = false"
                        class="text-gray-400 hover:text-red-500"
                    >
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                    <div>
                        <label
                            class="block text-sm font-bold text-gray-700 mb-1"
                            >Nama Lengkap</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:ring-red-500"
                        />
                        <p
                            v-if="form.errors.name"
                            class="text-xs text-red-500 mt-1"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-bold text-gray-700 mb-1"
                                >NIP</label
                            >
                            <input
                                v-model="form.nip"
                                type="text"
                                class="w-full rounded-lg border-gray-300 focus:ring-red-500"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-bold text-gray-700 mb-1"
                                >No HP</label
                            >
                            <input
                                v-model="form.phone"
                                type="text"
                                class="w-full rounded-lg border-gray-300 focus:ring-red-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-bold text-gray-700 mb-1"
                            >Alamat</label
                        >
                        <textarea
                            v-model="form.address"
                            rows="2"
                            class="w-full rounded-lg border-gray-300 focus:ring-red-500"
                        ></textarea>
                    </div>

                    <div class="border-t border-gray-100 pt-4 mt-2">
                        <label
                            class="block text-sm font-bold text-gray-800 mb-2 flex items-center gap-2"
                        >
                            <KeyIcon class="w-4 h-4 text-yellow-600" /> Reset
                            Password (Opsional)
                        </label>

                        <div class="flex gap-4 mb-3">
                            <label
                                class="flex items-center gap-2 cursor-pointer"
                            >
                                <input
                                    type="radio"
                                    v-model="form.password_option"
                                    value=""
                                    class="text-red-600 focus:ring-red-500"
                                />
                                <span class="text-sm text-gray-600"
                                    >Tidak Ganti</span
                                >
                            </label>
                            <label
                                class="flex items-center gap-2 cursor-pointer"
                            >
                                <input
                                    type="radio"
                                    v-model="form.password_option"
                                    value="random"
                                    class="text-red-600 focus:ring-red-500"
                                />
                                <span class="text-sm text-gray-600"
                                    >Default (guru123)</span
                                >
                            </label>
                            <label
                                class="flex items-center gap-2 cursor-pointer"
                            >
                                <input
                                    type="radio"
                                    v-model="form.password_option"
                                    value="manual"
                                    class="text-red-600 focus:ring-red-500"
                                />
                                <span class="text-sm text-gray-600"
                                    >Input Manual</span
                                >
                            </label>
                        </div>

                        <div v-if="form.password_option === 'manual'">
                            <input
                                v-model="form.new_password"
                                type="text"
                                class="w-full rounded-lg border-gray-300 focus:ring-red-500"
                                placeholder="Masukkan password baru..."
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t">
                        <button
                            type="button"
                            @click="showEditModal = false"
                            class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-bold disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? "Menyimpan..."
                                    : "Simpan Perubahan"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
