<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import {
    ArrowLeftIcon,
    CameraIcon,
    UserCircleIcon,
    InformationCircleIcon,
} from "@heroicons/vue/24/solid";
import { ref, computed } from "vue";

const photoPreview = ref(null);
const form = useForm({
    name: "",
    nip: "",
    phone: "",
    address: "",
    photo: null,
});

// LOGIKA TEXT LOGIN DINAMIS
const loginInfo = computed(() => {
    if (form.nip) {
        return { user: form.nip, pass: form.nip };
    } else if (form.phone) {
        return { user: form.phone, pass: form.phone };
    } else {
        return { user: "...", pass: "..." };
    }
});

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.photo = file;
        photoPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route("admin.teachers.store"));
};
</script>

<template>
    <Head title="Tambah Guru" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.teachers.index')"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <ArrowLeftIcon class="w-6 h-6" />
                </Link>
                <h2 class="font-bold text-xl text-gray-800">
                    Tambah Guru Baru
                </h2>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div
                class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow border border-gray-200"
            >
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="flex flex-col items-center">
                        <div
                            class="relative w-32 h-32 rounded-full overflow-hidden border-4 border-gray-100 shadow-inner bg-gray-50 group cursor-pointer"
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

                            <label
                                class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-white text-xs font-bold cursor-pointer"
                            >
                                <CameraIcon class="w-6 h-6 mb-1" /> Upload
                                <input
                                    type="file"
                                    class="hidden"
                                    accept="image/*"
                                    @change="handleFileUpload"
                                />
                            </label>
                        </div>
                        <p class="text-xs text-gray-400 mt-2">Foto Opsional</p>
                        <p
                            class="text-xs text-red-500 mt-1"
                            v-if="form.errors.photo"
                        >
                            {{ form.errors.photo }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-bold text-gray-700 mb-1"
                            >Nama Lengkap & Gelar</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border-gray-300 focus:ring-purple-500"
                            placeholder="Contoh: Budi Santoso, S.Pd"
                            required
                        />
                        <div
                            v-if="form.errors.name"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-sm font-bold text-gray-700 mb-1"
                            >
                                NIP
                                <span class="text-gray-400 font-normal"
                                    >(Opsional)</span
                                >
                            </label>
                            <input
                                v-model="form.nip"
                                type="number"
                                class="w-full rounded-lg border-gray-300 focus:ring-purple-500"
                                placeholder="Kosongkan jika tidak ada"
                            />
                            <div
                                v-if="form.errors.nip"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.nip }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-bold text-gray-700 mb-1"
                                >Nomor HP / WhatsApp</label
                            >
                            <input
                                v-model="form.phone"
                                type="text"
                                class="w-full rounded-lg border-gray-300 focus:ring-purple-500"
                                placeholder="0812xxxx"
                                required
                            />
                            <div
                                v-if="form.errors.phone"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.phone }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-bold text-gray-700 mb-1"
                            >Alamat Lengkap</label
                        >
                        <textarea
                            v-model="form.address"
                            rows="2"
                            class="w-full rounded-lg border-gray-300 focus:ring-purple-500"
                        ></textarea>
                    </div>

                    <div
                        class="bg-yellow-50 p-4 rounded-lg text-sm text-yellow-800 border border-yellow-200 flex gap-3 items-start"
                    >
                        <InformationCircleIcon
                            class="w-6 h-6 text-yellow-600 flex-shrink-0"
                        />
                        <div>
                            <p class="font-bold mb-1">Akun Login Guru:</p>
                            <p>
                                Username:
                                <b>{{ loginInfo.user }}@guru.smk.sch.id</b>
                            </p>
                            <p>
                                Password: <b>{{ loginInfo.pass }}</b>
                            </p>
                            <p class="text-xs text-yellow-700 mt-1 italic">
                                *Jika NIP kosong, Nomor HP akan digunakan
                                sebagai Username & Password.
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-100">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-purple-600 text-white px-8 py-2.5 rounded-lg font-bold hover:bg-purple-700 transition shadow-lg disabled:opacity-50 flex items-center gap-2"
                        >
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan Data Guru</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
