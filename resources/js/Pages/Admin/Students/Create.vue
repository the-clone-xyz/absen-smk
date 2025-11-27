<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import {
    ArrowLeftIcon,
    UserCircleIcon,
    CameraIcon,
} from "@heroicons/vue/24/solid";
import { ref } from "vue";

const props = defineProps({
    classes: Array,
});

const photoPreview = ref(null);

const form = useForm({
    name: "",
    nis: "",
    nisn: "",
    class_id: "",
    pob: "",
    dob: "",
    phone: "",
    address: "",
    photo: null, // Untuk file gambar
});

// Handle Preview Foto
const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.photo = file;
        photoPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route("admin.students.store"));
};
</script>

<template>
    <Head title="Tambah Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.students.index')"
                    class="text-gray-500 hover:text-gray-700 transition"
                >
                    <ArrowLeftIcon class="w-6 h-6" />
                </Link>
                <h2 class="font-bold text-xl text-gray-800">
                    Input Data Siswa
                </h2>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <form
                    @submit.prevent="submit"
                    class="grid grid-cols-1 md:grid-cols-3 gap-6"
                >
                    <div class="space-y-6">
                        <div
                            class="bg-white p-6 rounded-xl shadow border border-gray-100 flex flex-col items-center text-center"
                        >
                            <div class="relative group cursor-pointer">
                                <div
                                    class="w-40 h-40 rounded-full overflow-hidden border-4 border-gray-200 shadow-inner bg-gray-50"
                                >
                                    <img
                                        v-if="photoPreview"
                                        :src="photoPreview"
                                        class="w-full h-full object-cover"
                                    />
                                    <UserCircleIcon
                                        v-else
                                        class="w-full h-full text-gray-300"
                                    />
                                </div>
                                <label
                                    class="absolute inset-0 bg-black/50 rounded-full flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition text-white font-bold cursor-pointer"
                                >
                                    <CameraIcon class="w-8 h-8 mb-1" />
                                    Upload Foto
                                    <input
                                        type="file"
                                        class="hidden"
                                        accept="image/*"
                                        @change="handleFileUpload"
                                    />
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-3">
                                Format JPG/PNG. Max 5MB.
                            </p>
                            <p
                                class="text-red-500 text-xs mt-1"
                                v-if="form.errors.photo"
                            >
                                {{ form.errors.photo }}
                            </p>
                        </div>

                        <div
                            class="bg-white p-6 rounded-xl shadow border border-gray-100 space-y-4"
                        >
                            <h3 class="font-bold text-gray-800 border-b pb-2">
                                Data Akademik
                            </h3>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >NIS (Wajib)</label
                                >
                                <input
                                    v-model="form.nis"
                                    type="number"
                                    class="w-full rounded-lg border-gray-300 focus:ring-blue-500"
                                    placeholder="2023xxx"
                                    required
                                />
                                <div
                                    v-if="form.errors.nis"
                                    class="text-red-500 text-xs mt-1"
                                >
                                    {{ form.errors.nis }}
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >NISN (Opsional)</label
                                >
                                <input
                                    v-model="form.nisn"
                                    type="number"
                                    class="w-full rounded-lg border-gray-300"
                                    placeholder="0012xxx"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Kelas</label
                                >
                                <select
                                    v-model="form.class_id"
                                    class="w-full rounded-lg border-gray-300"
                                    required
                                >
                                    <option value="" disabled>
                                        -- Pilih Kelas --
                                    </option>
                                    <option
                                        v-for="kelas in classes"
                                        :key="kelas.id"
                                        :value="kelas.id"
                                    >
                                        {{ kelas.name }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.class_id"
                                    class="text-red-500 text-xs mt-1"
                                >
                                    {{ form.errors.class_id }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <div
                            class="bg-white p-6 rounded-xl shadow border border-gray-100 h-full"
                        >
                            <h3
                                class="font-bold text-gray-800 border-b pb-2 mb-4"
                            >
                                Biodata Lengkap
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Nama Lengkap</label
                                    >
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500"
                                        placeholder="Nama Sesuai Ijazah"
                                        required
                                    />
                                    <div
                                        v-if="form.errors.name"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                                >
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                            >Tempat Lahir</label
                                        >
                                        <input
                                            v-model="form.pob"
                                            type="text"
                                            class="w-full rounded-lg border-gray-300"
                                            placeholder="Contoh: Jakarta"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                            >Tanggal Lahir</label
                                        >
                                        <input
                                            v-model="form.dob"
                                            type="date"
                                            class="w-full rounded-lg border-gray-300"
                                            required
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Nomor WhatsApp (Aktif)</label
                                    >
                                    <input
                                        v-model="form.phone"
                                        type="text"
                                        class="w-full rounded-lg border-gray-300"
                                        placeholder="0812xxxx"
                                    />
                                </div>

                                <div
                                    class="bg-blue-50 p-4 rounded-lg text-sm text-blue-800 mt-6"
                                >
                                    <p
                                        class="font-bold flex items-center gap-2"
                                    >
                                        <svg
                                            class="w-5 h-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                        Informasi Akun Otomatis
                                    </p>
                                    <ul class="list-disc ml-6 mt-2 space-y-1">
                                        <li>
                                            Username / Email:
                                            <span class="font-mono font-bold">{{
                                                form.nis
                                                    ? form.nis + "@smk.sch.id"
                                                    : "..."
                                            }}</span>
                                        </li>
                                        <li>
                                            Password Default:
                                            <span class="font-mono font-bold">{{
                                                form.nis || "..."
                                            }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end border-t pt-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-blue-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-700 transition shadow-lg disabled:opacity-50 flex items-center gap-2"
                                >
                                    <span v-if="form.processing"
                                        >Menyimpan...</span
                                    >
                                    <span v-else>Simpan Data Siswa</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
