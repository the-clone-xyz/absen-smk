<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import {
    BookOpenIcon,
    MagnifyingGlassIcon,
    PlusIcon,
    TrashIcon,
    XMarkIcon,
    DocumentTextIcon,
    ArrowDownTrayIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    ebooks: Object,
    filters: Object,
    canManage: Boolean,
});

const search = ref(props.filters.search || "");
const showUploadModal = ref(false);
const tempUploads = ref([]);
const fileInputKey = ref(0); // Kunci rahasia untuk mereset input file

const form = useForm({
    title: "",
    author: "",
    description: "",
    cover: null,
    file: null,
});

// --- PERBAIKAN 1: RESET DATA TOTAL SAAT BUKA/TUTUP ---
const openUploadModal = () => {
    form.reset();
    form.clearErrors();
    fileInputKey.value++; // Reset input file HTML
    showUploadModal.value = true;
};

const closeModal = () => {
    showUploadModal.value = false;
    setTimeout(() => {
        form.reset(); // Hapus sisa data saat animasi tutup selesai
        form.clearErrors();
    }, 300);
};

const handleFile = (e, type) => {
    if (e.target.files[0]) {
        form[type] = e.target.files[0];
    }
};

const submitUpload = () => {
    const tempId = Date.now();
    let coverPreview = null;

    if (form.cover) {
        coverPreview = URL.createObjectURL(form.cover);
    }

    tempUploads.value.unshift({
        id: tempId,
        title: form.title,
        author: form.author,
        coverPreview: coverPreview,
        progress: 0,
        status: "uploading",
        errorMessage: "",
    });

    closeModal(); // Tutup & Reset modal

    form.post(route("ebooks.store"), {
        preserveScroll: true,
        onProgress: (progress) => {
            const item = tempUploads.value.find((i) => i.id === tempId);
            if (item) item.progress = progress.percentage;
        },
        onSuccess: () => {
            const item = tempUploads.value.find((i) => i.id === tempId);
            if (item) {
                item.progress = 100;
                item.status = "success";
            }
            setTimeout(() => {
                tempUploads.value = tempUploads.value.filter(
                    (i) => i.id !== tempId
                );
            }, 1000);
        },
        onError: (errors) => {
            const item = tempUploads.value.find((i) => i.id === tempId);
            if (item) {
                item.status = "error";
                item.errorMessage = Object.values(errors)[0] || "Gagal upload.";
            }
        },
    });
};

// --- PERBAIKAN 2: LINK PAGINATION YANG AMAN ---
const paginationLinks = computed(() => {
    // Ambil links dari meta (Resource) atau root (Standard)
    const links = props.ebooks.meta?.links || props.ebooks.links || [];
    // Filter agar pagination terlihat rapi (opsional, membuang link text yang duplikat)
    return links;
});

let searchTimeout = null;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route("ebooks.index"),
            { search: val },
            { preserveState: true, replace: true }
        );
    }, 300);
});

const deleteBook = (id) => {
    if (confirm("Yakin ingin menghapus buku ini?")) {
        router.delete(route("ebooks.destroy", id));
    }
};

const removeTempUpload = (id) => {
    tempUploads.value = tempUploads.value.filter((i) => i.id !== id);
};

const isPdf = (filename) => filename && filename.toLowerCase().endsWith(".pdf");
</script>

<template>
    <Head title="Katalog Buku" />

    <AuthenticatedLayout>
        <div class="fixed inset-0 z-0 pointer-events-none bg-[#f8f9fa]">
            <div
                class="absolute top-0 right-0 w-[500px] h-[500px] bg-blue-100/40 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"
            ></div>
            <div
                class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-emerald-100/40 rounded-full blur-3xl translate-y-1/3 -translate-x-1/3"
            ></div>
        </div>

        <div class="relative z-10 min-h-screen pb-10">
            <div
                class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div
                        class="flex flex-col md:flex-row items-center justify-between gap-4"
                    >
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <div
                                class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white shadow-blue-200 shadow-lg"
                            >
                                <BookOpenIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <h1
                                    class="text-xl font-bold text-gray-800 leading-tight"
                                >
                                    Perpustakaan Digital
                                </h1>
                                <p class="text-xs text-gray-500 font-medium">
                                    Katalog Buku Sekolah
                                </p>
                            </div>
                        </div>

                        <div class="w-full md:max-w-xl relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
                            >
                                <MagnifyingGlassIcon
                                    class="h-5 w-5 text-gray-400"
                                />
                            </div>
                            <input
                                v-model="search"
                                type="text"
                                class="w-full pl-11 pr-4 py-2.5 bg-gray-100 border-none rounded-full text-sm font-medium text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all shadow-inner"
                                placeholder="Cari judul buku, penulis..."
                            />
                        </div>

                        <button
                            v-if="canManage"
                            @click="openUploadModal"
                            class="hidden md:flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-full text-sm font-bold transition-all shadow-md hover:shadow-lg active:scale-95"
                        >
                            <PlusIcon class="w-5 h-5" />
                            <span>Upload Buku</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex justify-between items-center mb-6">
                    <h2
                        class="text-lg font-bold text-gray-800 border-l-4 border-blue-500 pl-3"
                    >
                        Katalog Terbaru
                    </h2>
                    <div class="text-sm text-gray-500">
                        Total
                        <span class="font-bold text-gray-800">{{
                            ebooks.total || ebooks.data.length
                        }}</span>
                        buku
                    </div>
                </div>

                <div
                    v-if="ebooks.data.length > 0 || tempUploads.length > 0"
                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6"
                >
                    <div
                        v-for="upload in tempUploads"
                        :key="upload.id"
                        class="relative group bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all"
                    >
                        <div class="aspect-[3/4] bg-gray-100 relative">
                            <img
                                v-if="upload.coverPreview"
                                :src="upload.coverPreview"
                                class="w-full h-full object-cover opacity-50 blur-sm"
                            />
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center"
                            >
                                <DocumentTextIcon
                                    class="w-12 h-12 text-gray-300 animate-pulse"
                                />
                            </div>

                            <div
                                class="absolute inset-0 flex flex-col items-center justify-center p-3 bg-black/10"
                            >
                                <div
                                    v-if="upload.status === 'uploading'"
                                    class="w-full text-center bg-white/90 p-3 rounded-xl shadow-lg backdrop-blur-sm"
                                >
                                    <div
                                        class="flex items-center justify-center gap-2 mb-2 text-blue-600 font-bold text-xs"
                                    >
                                        <ArrowPathIcon
                                            class="w-4 h-4 animate-spin"
                                        />
                                        Mengunggah
                                    </div>
                                    <div
                                        class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden"
                                    >
                                        <div
                                            class="bg-blue-500 h-1.5 rounded-full transition-all duration-300"
                                            :style="{
                                                width: upload.progress + '%',
                                            }"
                                        ></div>
                                    </div>
                                </div>
                                <div
                                    v-else-if="upload.status === 'success'"
                                    class="bg-green-500 text-white p-2 rounded-full shadow-lg animate-bounce"
                                >
                                    <CheckCircleIcon class="w-8 h-8" />
                                </div>
                                <div
                                    v-else-if="upload.status === 'error'"
                                    class="text-center bg-white p-2 rounded-lg shadow-lg"
                                >
                                    <ExclamationCircleIcon
                                        class="w-8 h-8 text-red-500 mx-auto"
                                    />
                                    <p
                                        class="text-[10px] font-bold text-red-500 mt-1 leading-tight"
                                    >
                                        {{ upload.errorMessage }}
                                    </p>
                                    <button
                                        @click="removeTempUpload(upload.id)"
                                        class="text-[10px] underline mt-1"
                                    >
                                        Tutup
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="p-3">
                            <h3
                                class="font-bold text-gray-800 text-sm mb-1 truncate"
                            >
                                {{ upload.title }}
                            </h3>
                            <div
                                class="h-3 bg-gray-100 rounded w-1/2 animate-pulse"
                            ></div>
                        </div>
                    </div>

                    <div
                        v-for="book in ebooks.data"
                        :key="book.id"
                        class="group bg-white rounded-xl shadow-sm hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-gray-100 overflow-hidden transition-all duration-300 flex flex-col h-full transform hover:-translate-y-1"
                    >
                        <div
                            class="relative aspect-[3/4] bg-gray-100 overflow-hidden"
                        >
                            <img
                                v-if="book.cover"
                                :src="'/storage/' + book.cover"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                            <div
                                v-else
                                class="w-full h-full flex flex-col items-center justify-center text-gray-300 bg-gray-50"
                            >
                                <DocumentTextIcon class="w-12 h-12 mb-2" />
                                <span
                                    class="text-[10px] font-bold uppercase tracking-wider"
                                    >No Cover</span
                                >
                            </div>

                            <div
                                class="absolute top-2 left-2 flex flex-col gap-1"
                            >
                                <span
                                    v-if="isPdf(book.file_path)"
                                    class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow-sm"
                                    >PDF</span
                                >
                                <span
                                    v-else
                                    class="bg-blue-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow-sm"
                                    >DOC</span
                                >
                            </div>

                            <div
                                class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-center gap-2 p-4 backdrop-blur-[1px]"
                            >
                                <Link
                                    :href="route('ebooks.read', book.id)"
                                    class="bg-white hover:bg-gray-100 text-gray-900 w-full py-2 rounded-lg font-bold text-xs flex items-center justify-center gap-1 shadow-lg transition-transform hover:scale-105"
                                >
                                    <DocumentTextIcon class="w-3 h-3" /> Baca
                                    Scroll
                                </Link>
                                <Link
                                    v-if="isPdf(book.file_path)"
                                    :href="route('ebooks.readFlip', book.id)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white w-full py-2 rounded-lg font-bold text-xs flex items-center justify-center gap-1 shadow-lg transition-transform hover:scale-105"
                                >
                                    <BookOpenIcon class="w-3 h-3" /> Flipbook
                                </Link>
                            </div>
                        </div>

                        <div class="p-3.5 flex-grow flex flex-col">
                            <h3
                                class="font-bold text-gray-800 text-sm leading-snug line-clamp-2 mb-1 group-hover:text-blue-600 transition-colors"
                                :title="book.title"
                            >
                                {{ book.title }}
                            </h3>
                            <p class="text-xs text-gray-500 line-clamp-1 mb-3">
                                {{ book.author }}
                            </p>

                            <div
                                v-if="canManage"
                                class="mt-auto pt-2 border-t border-dashed border-gray-200 flex justify-end"
                            >
                                <button
                                    @click="deleteBook(book.id)"
                                    class="text-[10px] font-bold text-gray-400 hover:text-red-500 flex items-center gap-1 transition-colors px-2 py-1 rounded hover:bg-red-50"
                                >
                                    <TrashIcon class="w-3 h-3" /> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center py-24 text-center"
                >
                    <div class="bg-blue-50 p-6 rounded-full mb-4">
                        <BookOpenIcon class="w-16 h-16 text-blue-200" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">
                        Belum ada buku
                    </h3>
                    <button
                        v-if="canManage"
                        @click="openUploadModal"
                        class="mt-4 bg-blue-600 text-white px-6 py-2.5 rounded-full text-sm font-bold shadow-md hover:shadow-lg transition-all hover:-translate-y-0.5"
                    >
                        Upload Buku Sekarang
                    </button>
                </div>

                <div
                    v-if="paginationLinks.length > 3"
                    class="mt-12 flex justify-center"
                >
                    <div
                        class="inline-flex items-center gap-1 bg-white p-2 rounded-full shadow-sm border border-gray-200"
                    >
                        <template
                            v-for="(link, key) in paginationLinks"
                            :key="key"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold transition-all"
                                :class="[
                                    link.active
                                        ? 'bg-blue-600 text-white shadow-md transform scale-110'
                                        : 'text-gray-600 hover:bg-gray-100 hover:text-blue-600',
                                ]"
                            >
                                <span v-if="link.label.includes('Previous')"
                                    ><ChevronLeftIcon class="w-4 h-4"
                                /></span>
                                <span v-else-if="link.label.includes('Next')"
                                    ><ChevronRightIcon class="w-4 h-4"
                                /></span>
                                <span v-else v-html="link.label"></span>
                            </Link>

                            <div
                                v-else
                                class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold text-gray-300 cursor-not-allowed"
                            >
                                <span v-if="link.label.includes('Previous')"
                                    ><ChevronLeftIcon class="w-4 h-4"
                                /></span>
                                <span v-else-if="link.label.includes('Next')"
                                    ><ChevronRightIcon class="w-4 h-4"
                                /></span>
                                <span v-else v-html="link.label"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <button
            v-if="canManage"
            @click="openUploadModal"
            class="md:hidden fixed bottom-6 right-6 w-14 h-14 bg-blue-600 text-white rounded-full shadow-xl shadow-blue-600/30 flex items-center justify-center z-40 active:scale-90 transition-transform"
        >
            <PlusIcon class="w-7 h-7" />
        </button>

        <div
            v-if="showUploadModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0"
            >
                <div
                    class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                    @click="closeModal"
                ></div>

                <div
                    class="relative inline-block bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full"
                >
                    <div
                        class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center"
                    >
                        <h3 class="text-lg font-bold text-gray-800">
                            Upload Buku Baru
                        </h3>
                        <button
                            @click="closeModal"
                            class="bg-gray-100 p-1.5 rounded-full text-gray-500 hover:bg-red-50 hover:text-red-500 transition-colors"
                        >
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitUpload" class="p-6 space-y-5">
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase mb-1"
                                    >Judul Buku</label
                                >
                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="w-full rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-sm"
                                    required
                                    placeholder="Contoh: Ilmu Pengetahuan Alam"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase mb-1"
                                    >Penulis</label
                                >
                                <input
                                    v-model="form.author"
                                    type="text"
                                    class="w-full rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-sm"
                                    required
                                    placeholder="Nama Penulis"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase mb-1"
                                    >Deskripsi (Opsional)</label
                                >
                                <textarea
                                    v-model="form.description"
                                    rows="2"
                                    class="w-full rounded-lg border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 text-sm"
                                ></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative group">
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase mb-1"
                                    >Cover</label
                                >
                                <div
                                    class="relative h-24 border-2 border-dashed border-gray-300 rounded-xl flex flex-col items-center justify-center hover:border-blue-500 hover:bg-blue-50 transition-colors cursor-pointer bg-gray-50 overflow-hidden"
                                >
                                    <input
                                        :key="fileInputKey"
                                        @change="handleFile($event, 'cover')"
                                        type="file"
                                        accept="image/*"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                    />
                                    <div
                                        v-if="form.cover"
                                        class="absolute inset-0 flex items-center justify-center bg-blue-100"
                                    >
                                        <span
                                            class="text-[10px] font-bold text-blue-600 px-2 text-center truncate w-full"
                                            >{{ form.cover.name }}</span
                                        >
                                    </div>
                                    <div v-else class="text-center">
                                        <ArrowDownTrayIcon
                                            class="w-5 h-5 text-gray-400 mx-auto mb-1 group-hover:text-blue-500"
                                        />
                                        <span
                                            class="text-[10px] text-gray-400 font-bold uppercase"
                                            >Pilih Gambar</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="relative group">
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase mb-1"
                                    >File PDF</label
                                >
                                <div
                                    class="relative h-24 border-2 border-dashed border-blue-300 bg-blue-50/50 rounded-xl flex flex-col items-center justify-center hover:border-blue-600 hover:bg-blue-100 transition-colors cursor-pointer overflow-hidden"
                                >
                                    <input
                                        :key="fileInputKey"
                                        @change="handleFile($event, 'file')"
                                        type="file"
                                        accept="application/pdf"
                                        required
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                    />
                                    <div
                                        v-if="form.file"
                                        class="absolute inset-0 flex items-center justify-center bg-blue-100"
                                    >
                                        <span
                                            class="text-[10px] font-bold text-blue-700 px-2 text-center truncate w-full"
                                            >{{ form.file.name }}</span
                                        >
                                    </div>
                                    <div v-else class="text-center">
                                        <DocumentTextIcon
                                            class="w-5 h-5 text-blue-400 mx-auto mb-1 group-hover:text-blue-600"
                                        />
                                        <span
                                            class="text-[10px] text-blue-500 font-bold uppercase"
                                            >Pilih PDF</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end gap-3">
                            <button
                                type="button"
                                @click="closeModal"
                                class="px-4 py-2 rounded-lg font-bold text-gray-500 hover:bg-gray-100 text-xs transition"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 shadow-md text-xs flex items-center gap-2"
                            >
                                <span v-if="form.processing"
                                    >Mengunggah...</span
                                >
                                <span v-else>Simpan Buku</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
