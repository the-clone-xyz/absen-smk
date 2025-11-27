<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { ref, computed } from "vue"; // Tambahkan 'computed'
import {
    DocumentTextIcon,
    PhotoIcon,
    PaperAirplaneIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
} from "@heroicons/vue/24/solid";

const photoPreview = ref(null);
const form = useForm({
    status: "Sakit", // Default
    description: "",
    photo: null,
});

// LOGIKA DINAMIS: Ubah contoh teks berdasarkan pilihan
const dynamicPlaceholder = computed(() => {
    if (form.status === "Sakit") {
        return "Jelaskan sakit apa dan kondisi saat ini.\nContoh: Demam tinggi 39°C disertai pusing sejak kemarin malam.";
    } else {
        return "Jelaskan keperluan izin secara detail.\nContoh: Menghadiri pernikahan kakak kandung di luar kota.";
    }
});

// LOGIKA DINAMIS: Ubah label upload
const uploadLabel = computed(() => {
    return form.status === "Sakit"
        ? "Upload Surat Dokter / Foto Obat"
        : "Upload Surat Orang Tua / Undangan";
});

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.photo = file;
        photoPreview.value = URL.createObjectURL(file);
    }
};

const selectStatus = (value) => {
    form.status = value;
    // Reset foto jika ganti status (opsional, biar bersih)
    // form.photo = null;
    // photoPreview.value = null;
};

const submitIzin = () => {
    form.post(route("attendance.store"), {
        forceFormData: true,
        onSuccess: () => {},
    });
};
</script>

<template>
    <Head title="Pengajuan Izin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('dashboard')"
                    class="text-gray-500 hover:text-green-700 transition"
                >
                    <ArrowLeftIcon class="w-6 h-6" />
                </Link>
                <h2 class="font-semibold text-xl text-green-800 leading-tight">
                    Formulir Izin
                </h2>
            </div>
        </template>

        <div class="py-6 px-4">
            <div
                class="max-w-xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden"
            >
                <div
                    class="bg-green-50 p-6 border-b border-green-100 flex items-center gap-4"
                >
                    <div class="bg-green-100 p-3 rounded-full text-green-600">
                        <DocumentTextIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h3 class="font-bold text-green-900 text-lg">
                            Pengajuan Ketidakhadiran
                        </h3>
                        <p class="text-green-700 text-sm">
                            Isi data berikut dengan lengkap.
                        </p>
                    </div>
                </div>

                <div class="p-6">
                    <form @submit.prevent="submitIzin" class="space-y-6">
                        <div>
                            <label
                                class="block text-sm font-bold text-gray-700 mb-2"
                                >Saya tidak masuk karena:</label
                            >
                            <div class="grid grid-cols-2 gap-4">
                                <div
                                    @click="selectStatus('Sakit')"
                                    class="cursor-pointer p-4 rounded-xl border-2 transition flex flex-col items-center gap-2 relative"
                                    :class="
                                        form.status === 'Sakit'
                                            ? 'border-green-500 bg-green-50 text-green-700 shadow-md'
                                            : 'border-gray-200 text-gray-400 hover:bg-gray-50'
                                    "
                                >
                                    <span
                                        class="text-3xl"
                                        :class="{
                                            grayscale: form.status !== 'Sakit',
                                        }"
                                        >🤒</span
                                    >
                                    <span class="font-bold">Sakit</span>
                                    <div
                                        v-if="form.status === 'Sakit'"
                                        class="absolute top-2 right-2 text-green-600"
                                    >
                                        <CheckCircleIcon class="w-6 h-6" />
                                    </div>
                                </div>

                                <div
                                    @click="selectStatus('Izin')"
                                    class="cursor-pointer p-4 rounded-xl border-2 transition flex flex-col items-center gap-2 relative"
                                    :class="
                                        form.status === 'Izin'
                                            ? 'border-yellow-500 bg-yellow-50 text-yellow-700 shadow-md'
                                            : 'border-gray-200 text-gray-400 hover:bg-gray-50'
                                    "
                                >
                                    <span
                                        class="text-3xl"
                                        :class="{
                                            grayscale: form.status !== 'Izin',
                                        }"
                                        >📝</span
                                    >
                                    <span class="font-bold">Izin / Acara</span>
                                    <div
                                        v-if="form.status === 'Izin'"
                                        class="absolute top-2 right-2 text-yellow-600"
                                    >
                                        <CheckCircleIcon class="w-6 h-6" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-bold text-gray-700 mb-2"
                                >Alasan Lengkap:</label
                            >
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full rounded-xl border-gray-300 focus:border-green-500 focus:ring-green-500 bg-gray-50 placeholder-gray-400 text-sm"
                                :placeholder="dynamicPlaceholder"
                                required
                            ></textarea>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-bold text-gray-700 mb-2"
                            >
                                {{ uploadLabel }}:
                            </label>

                            <div class="relative">
                                <label
                                    v-if="!photoPreview"
                                    class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:bg-gray-50 hover:border-green-400 transition cursor-pointer flex flex-col items-center justify-center w-full h-full group"
                                >
                                    <PhotoIcon
                                        class="w-10 h-10 text-gray-400 mb-2 group-hover:text-green-500 transition"
                                    />
                                    <span
                                        class="text-sm font-bold text-gray-600 group-hover:text-green-700"
                                        >Klik Disini Upload Foto</span
                                    >
                                    <span class="text-xs text-gray-400 mt-1"
                                        >Format: JPG/PNG (Max 5MB)</span
                                    >
                                    <input
                                        type="file"
                                        @change="handleFileUpload"
                                        accept="image/*"
                                        class="hidden"
                                    />
                                </label>

                                <div
                                    v-else
                                    class="relative rounded-xl overflow-hidden border border-gray-200 shadow-sm"
                                >
                                    <img
                                        :src="photoPreview"
                                        class="w-full h-48 object-cover bg-gray-100"
                                    />
                                    <button
                                        @click="
                                            photoPreview = null;
                                            form.photo = null;
                                        "
                                        type="button"
                                        class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full shadow-lg hover:bg-red-600 transition z-20"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p
                                class="text-red-500 text-xs mt-1"
                                v-if="form.errors.photo"
                            >
                                {{ form.errors.photo }}
                            </p>
                        </div>

                        <div class="pt-4">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full bg-green-700 text-white font-bold py-4 rounded-xl hover:bg-green-800 shadow-lg transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="form.processing"
                                    >Mengirim Data...</span
                                >
                                <span
                                    v-else
                                    class="flex items-center justify-center gap-2"
                                >
                                    <PaperAirplaneIcon
                                        class="w-5 h-5 -rotate-45"
                                    />
                                    Kirim Pengajuan
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
