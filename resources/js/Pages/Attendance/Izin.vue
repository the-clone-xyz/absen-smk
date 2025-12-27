<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Swal from "sweetalert2";
import {
    DocumentTextIcon,
    PhotoIcon,
    PaperAirplaneIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
    XMarkIcon,
} from "@heroicons/vue/24/solid";

const photoPreview = ref(null);
const fileInput = ref(null);

const form = useForm({
    type: "permit", // <--- PERBAIKAN UTAMA DI SINI (Wajib ada)
    status: "Sakit",
    description: "",
    photo: null,
});

// LOGIKA DINAMIS
const dynamicPlaceholder = computed(() => {
    return form.status === "Sakit"
        ? "Jelaskan sakit apa dan kondisi saat ini.\nContoh: Demam tinggi 39°C disertai pusing sejak kemarin malam."
        : "Jelaskan keperluan izin secara detail.\nContoh: Menghadiri pernikahan kakak kandung di luar kota.";
});

const uploadLabel = computed(() => {
    return form.status === "Sakit"
        ? "Upload Surat Dokter / Foto Obat"
        : "Upload Surat Orang Tua / Undangan";
});

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validasi Ukuran (Max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({
                icon: "error",
                title: "File Terlalu Besar",
                text: "Maksimal ukuran foto adalah 5MB!",
                confirmButtonColor: "#166534",
            });
            event.target.value = "";
            return;
        }

        form.photo = file;
        photoPreview.value = URL.createObjectURL(file);
    }
};

const removePhoto = () => {
    photoPreview.value = null;
    form.photo = null;
    if (fileInput.value) fileInput.value.value = "";
};

const selectStatus = (value) => {
    form.status = value;
};

const submitIzin = () => {
    // Validasi Foto Wajib
    if (!form.photo) {
        Swal.fire({
            icon: "warning",
            title: "Foto Belum Diupload",
            text: "Mohon sertakan bukti foto surat/obat untuk melanjutkan.",
            confirmButtonColor: "#166534",
        });
        return;
    }

    form.post(route("attendance.store"), {
        forceFormData: true,
        onStart: () => {
            // Loading handled by form.processing
        },
        onSuccess: () => {
            Swal.fire({
                icon: "success",
                title: "Berhasil Dikirim!",
                text: "Pengajuan izin Anda telah diterima dan menunggu persetujuan guru.",
                confirmButtonColor: "#166534",
            });
            form.reset();
            removePhoto();
        },
        onError: (errors) => {
            console.error("Error Log:", errors); // Untuk debugging

            // Pesan Error Spesifik jika type salah/missing
            let pesanError =
                "Terjadi kesalahan. Periksa koneksi atau inputan Anda.";
            if (errors.type)
                pesanError = "Sistem Error: Tipe absensi tidak dikenali.";
            if (errors.photo) pesanError = errors.photo;

            Swal.fire({
                icon: "error",
                title: "Gagal Mengirim",
                text: pesanError,
                confirmButtonColor: "#ef4444",
            });
        },
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
                    <div
                        class="bg-green-100 p-3 rounded-full text-green-600 shadow-sm"
                    >
                        <DocumentTextIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h3 class="font-bold text-green-900 text-lg">
                            Pengajuan Ketidakhadiran
                        </h3>
                        <p class="text-green-700 text-sm">
                            Isi data berikut dengan lengkap dan jujur.
                        </p>
                    </div>
                </div>

                <div class="p-6">
                    <form @submit.prevent="submitIzin" class="space-y-6">
                        <div>
                            <label
                                class="block text-sm font-bold text-gray-700 mb-3"
                                >Saya tidak masuk karena:</label
                            >
                            <div class="grid grid-cols-2 gap-4">
                                <div
                                    @click="selectStatus('Sakit')"
                                    class="cursor-pointer p-4 rounded-xl border-2 transition-all duration-200 flex flex-col items-center gap-2 relative hover:scale-[1.02]"
                                    :class="
                                        form.status === 'Sakit'
                                            ? 'border-green-500 bg-green-50 text-green-700 shadow-md'
                                            : 'border-gray-200 text-gray-400 hover:bg-gray-50'
                                    "
                                >
                                    <span
                                        class="text-4xl transition-all"
                                        :class="{
                                            'grayscale opacity-50':
                                                form.status !== 'Sakit',
                                        }"
                                        >🤒</span
                                    >
                                    <span class="font-bold">Sakit</span>
                                    <div
                                        v-if="form.status === 'Sakit'"
                                        class="absolute top-2 right-2 text-green-600 bg-white rounded-full"
                                    >
                                        <CheckCircleIcon class="w-6 h-6" />
                                    </div>
                                </div>

                                <div
                                    @click="selectStatus('Izin')"
                                    class="cursor-pointer p-4 rounded-xl border-2 transition-all duration-200 flex flex-col items-center gap-2 relative hover:scale-[1.02]"
                                    :class="
                                        form.status === 'Izin'
                                            ? 'border-yellow-500 bg-yellow-50 text-yellow-700 shadow-md'
                                            : 'border-gray-200 text-gray-400 hover:bg-gray-50'
                                    "
                                >
                                    <span
                                        class="text-4xl transition-all"
                                        :class="{
                                            'grayscale opacity-50':
                                                form.status !== 'Izin',
                                        }"
                                        >📝</span
                                    >
                                    <span class="font-bold">Izin / Acara</span>
                                    <div
                                        v-if="form.status === 'Izin'"
                                        class="absolute top-2 right-2 text-yellow-600 bg-white rounded-full"
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
                                rows="4"
                                class="w-full rounded-xl border-gray-300 focus:border-green-500 focus:ring-green-500 bg-gray-50 placeholder-gray-400 text-sm p-4 transition-all"
                                :placeholder="dynamicPlaceholder"
                                required
                            ></textarea>
                            <p
                                v-if="form.errors.description"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-bold text-gray-700 mb-2"
                            >
                                {{ uploadLabel }}
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative group">
                                <label
                                    v-if="!photoPreview"
                                    class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:bg-green-50 hover:border-green-400 transition cursor-pointer flex flex-col items-center justify-center w-full min-h-[200px] bg-gray-50"
                                >
                                    <div
                                        class="p-4 bg-white rounded-full shadow-sm mb-3 group-hover:scale-110 transition-transform"
                                    >
                                        <PhotoIcon
                                            class="w-8 h-8 text-green-600"
                                        />
                                    </div>
                                    <span
                                        class="text-sm font-bold text-gray-600 group-hover:text-green-700"
                                        >Klik untuk Upload Foto</span
                                    >
                                    <span class="text-xs text-gray-400 mt-1"
                                        >Format: JPG/PNG (Max 5MB)</span
                                    >

                                    <input
                                        ref="fileInput"
                                        type="file"
                                        @change="handleFileUpload"
                                        accept="image/*"
                                        class="hidden"
                                    />
                                </label>

                                <div
                                    v-else
                                    class="relative rounded-xl overflow-hidden border border-gray-200 shadow-md"
                                >
                                    <img
                                        :src="photoPreview"
                                        class="w-full h-64 object-cover bg-gray-100"
                                    />

                                    <div
                                        class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
                                    >
                                        <p class="text-white font-bold text-sm">
                                            Ganti Foto?
                                        </p>
                                    </div>

                                    <button
                                        @click="removePhoto"
                                        type="button"
                                        class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full shadow-lg hover:bg-red-600 transition transform hover:rotate-90 z-20"
                                        title="Hapus Foto"
                                    >
                                        <XMarkIcon class="w-5 h-5" />
                                    </button>
                                </div>
                                <p
                                    v-if="form.errors.photo"
                                    class="text-red-500 text-xs mt-1"
                                >
                                    {{ form.errors.photo }}
                                </p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full bg-green-700 text-white font-bold py-4 rounded-xl hover:bg-green-800 shadow-lg shadow-green-700/30 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2 group"
                            >
                                <span
                                    v-if="form.processing"
                                    class="flex items-center gap-2"
                                >
                                    <svg
                                        class="animate-spin h-5 w-5 text-white"
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
                                    Mengirim Data...
                                </span>
                                <span
                                    v-else
                                    class="flex items-center justify-center gap-2"
                                >
                                    <PaperAirplaneIcon
                                        class="w-5 h-5 -rotate-45 group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"
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
