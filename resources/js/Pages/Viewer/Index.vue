<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import {
    ArrowLeftIcon,
    ArrowDownTrayIcon,
    DocumentTextIcon,
    TableCellsIcon,
    PhotoIcon,
    BookOpenIcon,
    ExclamationTriangleIcon,
    PresentationChartLineIcon, // <--- IKON BARU UNTUK PPT
} from "@heroicons/vue/24/solid";
import { computed, ref, onMounted } from "vue";
import mammoth from "mammoth";
import * as XLSX from "xlsx";

const props = defineProps({
    fileUrl: String,
    fileName: String,
    extension: String,
    downloadUrl: String,
});

const contentHtml = ref("");
const isLoading = ref(false);
const errorMsg = ref(null);

// Kategori File
const isImage = computed(() =>
    ["jpg", "jpeg", "png", "webp", "gif"].includes(props.extension)
);
const isPdf = computed(() => ["pdf"].includes(props.extension));
const isWord = computed(() => ["docx"].includes(props.extension));
const isExcel = computed(() =>
    ["xlsx", "xls", "csv"].includes(props.extension)
);
// Deteksi PPT
const isPpt = computed(() => ["ppt", "pptx"].includes(props.extension));

const renderDocument = async () => {
    // PPT, Gambar, PDF tidak perlu dirender oleh JS Library khusus
    if (isImage.value || isPdf.value || isPpt.value) return;

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
                        <PresentationChartLineIcon
                            v-else-if="isPpt"
                            class="w-6 h-6"
                        />
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

            <div v-else-if="isPpt" class="text-center mt-10 md:mt-20">
                <div
                    class="bg-white p-10 rounded-3xl shadow-xl max-w-md mx-auto border border-gray-100"
                >
                    <div
                        class="bg-orange-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 text-orange-500"
                    >
                        <PresentationChartLineIcon class="w-12 h-12" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">
                        Mode Presentasi
                    </h3>
                    <p class="text-gray-500 mb-8 text-sm leading-relaxed">
                        File presentasi <b>({{ extension }})</b> paling baik
                        dilihat menggunakan aplikasi PowerPoint. Silakan
                        download untuk membukanya.
                    </p>
                    <a
                        :href="downloadUrl"
                        class="w-full flex justify-center items-center gap-2 bg-orange-600 text-white py-3.5 rounded-xl font-bold hover:bg-orange-700 transition shadow-lg shadow-orange-200"
                    >
                        <ArrowDownTrayIcon class="w-5 h-5" />
                        Download Presentasi
                    </a>
                    <p class="mt-4 text-[10px] text-gray-400">
                        Tips: Minta Guru upload versi PDF agar bisa dibaca
                        langsung.
                    </p>
                </div>
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
/* CSS tetap sama */
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
