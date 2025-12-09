<script setup>
import { Head } from "@inertiajs/vue3";
import {
    ArrowLeftIcon,
    ArrowDownTrayIcon,
    DocumentTextIcon,
    TableCellsIcon,
    PhotoIcon,
    BookOpenIcon,
    ExclamationTriangleIcon,
} from "@heroicons/vue/24/solid";
import { computed, ref, onMounted } from "vue";
import mammoth from "mammoth"; // npm install mammoth
import * as XLSX from "xlsx"; // npm install xlsx

const props = defineProps({
    fileUrl: String,
    fileName: String,
    extension: String,
    downloadUrl: String,
});

// State untuk Dokumen Office
const contentHtml = ref("");
const isLoading = ref(false);
const errorMsg = ref(null);

// Deteksi Tipe File
const isImage = computed(() =>
    ["jpg", "jpeg", "png", "webp", "gif"].includes(props.extension)
);
const isPdf = computed(() => ["pdf"].includes(props.extension));
const isWord = computed(() => ["docx"].includes(props.extension));
const isExcel = computed(() =>
    ["xlsx", "xls", "csv"].includes(props.extension)
);

// --- ENGINE RENDERER (OFFLINE) ---
const renderDocument = async () => {
    if (isImage.value || isPdf.value) return; // Browser native handle

    isLoading.value = true;
    try {
        const response = await fetch(props.fileUrl);
        const arrayBuffer = await response.arrayBuffer();

        if (isWord.value) {
            const result = await mammoth.convertToHtml({
                arrayBuffer: arrayBuffer,
            });
            contentHtml.value = result.value;
        } else if (isExcel.value) {
            const workbook = XLSX.read(arrayBuffer, { type: "array" });
            const sheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[sheetName];
            contentHtml.value = XLSX.utils.sheet_to_html(worksheet, {
                id: "excel-render",
                class: "w-full border-collapse text-sm",
            });
        }
    } catch (err) {
        console.error(err);
        errorMsg.value = "Gagal merender dokumen. Silakan download.";
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => renderDocument());

// Fungsi Back (Bisa kembali ke halaman sebelumnya, entah itu Guru/Siswa/Perpustakaan)
const goBack = () => window.history.back();
</script>

<template>
    <Head :title="fileName" />

    <div class="min-h-screen bg-gray-100 flex flex-col font-sans">
        <header
            class="bg-white border-b border-gray-200 px-4 py-3 shadow-sm sticky top-0 z-50 flex items-center justify-between"
        >
            <div class="flex items-center gap-4">
                <button
                    @click="goBack"
                    class="p-2 rounded-full hover:bg-gray-100 text-gray-600 transition"
                >
                    <ArrowLeftIcon class="w-5 h-5" />
                </button>

                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded bg-indigo-600 text-white flex items-center justify-center shadow-md"
                    >
                        <PhotoIcon v-if="isImage" class="w-6 h-6" />
                        <BookOpenIcon v-else-if="isPdf" class="w-6 h-6" />
                        <DocumentTextIcon v-else-if="isWord" class="w-6 h-6" />
                        <TableCellsIcon v-else-if="isExcel" class="w-6 h-6" />
                        <DocumentTextIcon v-else class="w-6 h-6" />
                    </div>
                    <div class="overflow-hidden">
                        <h1
                            class="font-bold text-gray-800 text-sm md:text-base truncate max-w-[200px] md:max-w-md"
                        >
                            {{ fileName }}
                        </h1>
                        <span
                            class="text-[10px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded uppercase font-bold tracking-wider"
                            >{{ extension }}</span
                        >
                    </div>
                </div>
            </div>

            <a
                :href="downloadUrl"
                class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 hover:bg-black transition shadow"
            >
                <ArrowDownTrayIcon class="w-4 h-4" />
                <span class="hidden sm:inline">Download</span>
            </a>
        </header>

        <main
            class="flex-grow p-4 md:p-8 flex justify-center overflow-auto relative"
        >
            <div
                v-if="isImage"
                class="w-full h-full flex items-center justify-center"
            >
                <img
                    :src="fileUrl"
                    class="max-w-full max-h-[85vh] rounded shadow-lg object-contain"
                />
            </div>

            <iframe
                v-else-if="isPdf"
                :src="fileUrl"
                class="w-full h-[85vh] rounded-lg shadow-md border bg-white"
            ></iframe>

            <div
                v-else-if="isWord || isExcel"
                class="w-full max-w-4xl bg-white shadow-xl rounded-xl min-h-[500px] p-8 md:p-12 doc-canvas"
            >
                <div
                    v-if="isLoading"
                    class="flex flex-col items-center justify-center h-64 text-gray-400"
                >
                    <div
                        class="animate-spin rounded-full h-12 w-12 border-b-4 border-indigo-600 mb-4"
                    ></div>
                    <p>Membuka Dokumen...</p>
                </div>
                <div v-else-if="errorMsg" class="text-center py-20">
                    <ExclamationTriangleIcon
                        class="w-16 h-16 text-red-300 mx-auto mb-4"
                    />
                    <p class="text-gray-500">{{ errorMsg }}</p>
                </div>
                <div v-else v-html="contentHtml" class="prose max-w-none"></div>
            </div>

            <div v-else class="text-center mt-20">
                <div class="bg-white p-8 rounded-2xl shadow-lg inline-block">
                    <p class="text-gray-600 mb-4">
                        Format file tidak dapat dipreview.
                    </p>
                    <a
                        :href="downloadUrl"
                        class="text-indigo-600 font-bold hover:underline"
                        >Klik untuk Download</a
                    >
                </div>
            </div>
        </main>
    </div>
</template>

<style>
/* Style Global untuk Dokumen Rendered */
.doc-canvas table {
    border-collapse: collapse;
    width: 100%;
    margin-top: 1rem;
}
.doc-canvas th,
.doc-canvas td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
.doc-canvas th {
    background-color: #f3f4f6;
    font-weight: bold;
}
.doc-canvas h1,
.doc-canvas h2,
.doc-canvas h3 {
    color: #111827;
    margin-top: 1.5em;
    font-weight: bold;
}
.doc-canvas p {
    line-height: 1.6;
    color: #374151;
    margin-bottom: 1em;
}
</style>
