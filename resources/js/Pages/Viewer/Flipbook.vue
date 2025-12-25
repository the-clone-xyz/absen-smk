<script setup>
import { onMounted, ref, onBeforeUnmount } from "vue";
import { Head } from "@inertiajs/vue3";
import { PageFlip } from "page-flip";
import * as pdfjsLib from "pdfjs-dist";
import {
    ArrowLeftIcon,
    BookOpenIcon,
    ExclamationCircleIcon,
    SpeakerWaveIcon,
    SpeakerXMarkIcon,
    ArrowsPointingOutIcon,
    ArrowsPointingInIcon,
} from "@heroicons/vue/24/solid";

// Worker Config
pdfjsLib.GlobalWorkerOptions.workerSrc = `https://unpkg.com/pdfjs-dist@${pdfjsLib.version}/build/pdf.worker.min.mjs`;

const props = defineProps({
    fileUrl: String,
    fileName: String,
});

const bookContainer = ref(null);
const isLoading = ref(true);
const loadingProgress = ref(0);
const errorMsg = ref(null);
const totalPages = ref(0);
const currentPage = ref(0);
const isMuted = ref(false);
const isFullscreen = ref(false);
const isHeaderVisible = ref(false); // State untuk Auto-Hide Header

let pageFlipInstance = null;
let pagesDomCache = [];

// --- AUDIO ---
let flipSound = null;
const initAudio = () => {
    if (!flipSound) {
        flipSound = new Audio("/audio/page-flip.mp3");
        flipSound.volume = 0.5;
        flipSound.preload = "auto";
        flipSound.load();
    }
};
const playFlipSound = () => {
    if (isMuted.value || !flipSound) return;
    flipSound.currentTime = 0;
    const playPromise = flipSound.play();
    if (playPromise !== undefined) playPromise.catch(() => {});
};
const toggleMute = () => (isMuted.value = !isMuted.value);

// --- NAVIGATION ---
const goBack = () => window.history.back();

const toggleFullScreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        if (document.exitFullscreen) document.exitFullscreen();
    }
};

const handleFullscreenChange = () => {
    isFullscreen.value = !!document.fullscreenElement;
    setTimeout(() => {
        if (pagesDomCache.length > 0) setupFlipbook();
    }, 500);
};

// --- CORE LOGIC ---
const renderPdf = async () => {
    try {
        isLoading.value = true;
        errorMsg.value = null;
        initAudio();

        const loadingTask = pdfjsLib.getDocument(props.fileUrl);
        loadingTask.onProgress = (p) => {
            if (p.total > 0)
                loadingProgress.value = Math.round((p.loaded / p.total) * 100);
        };
        const pdf = await loadingTask.promise;
        totalPages.value = pdf.numPages;

        // Render Scale Super Tinggi (4.0)
        const renderScale = 4.0;

        const pagesToLoad = Math.min(pdf.numPages, 50);
        pagesDomCache = [];

        for (let pageNum = 1; pageNum <= pagesToLoad; pageNum++) {
            const page = await pdf.getPage(pageNum);
            const viewport = page.getViewport({ scale: renderScale });

            const canvas = document.createElement("canvas");
            const context = canvas.getContext("2d", { alpha: false });
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            canvas.style.width = "100%";
            canvas.style.height = "100%";
            canvas.style.objectFit = "contain";

            await page.render({ canvasContext: context, viewport: viewport })
                .promise;

            const pageDiv = document.createElement("div");
            if (pageNum === 1 || pageNum === pdf.numPages) {
                pageDiv.dataset.isCover = "true";
            }

            pageDiv.appendChild(canvas);
            pageDiv.dataset.aspectRatio = viewport.width / viewport.height;
            pagesDomCache.push(pageDiv);
        }

        setupFlipbook();
        isLoading.value = false;
    } catch (error) {
        console.error("PDF Error:", error);
        errorMsg.value = "Gagal memuat dokumen PDF.";
        isLoading.value = false;
    }
};

const setupFlipbook = () => {
    if (pageFlipInstance) {
        try {
            pageFlipInstance.destroy();
        } catch (e) {}
        pageFlipInstance = null;
    }

    if (bookContainer.value) bookContainer.value.innerHTML = "";
    if (!bookContainer.value || pagesDomCache.length === 0) return;

    pagesDomCache.forEach((p) => {
        let cleanClass = "page bg-white";
        if (p.dataset.isCover === "true") cleanClass += " hard-cover";
        p.className = cleanClass;
    });

    const winWidth = window.innerWidth;
    const aspectRatio = parseFloat(pagesDomCache[0].dataset.aspectRatio) || 0.7;

    const isSinglePageMode = isFullscreen.value || winWidth < 768;

    // --- LOGIKA FULL WIDTH ---
    // Gunakan 98% lebar layar (hampir mentok)
    const maxAvailableWidth = winWidth * 0.98;

    let bookWidth, bookHeight;

    if (isSinglePageMode) {
        // Mode 1 Halaman (Fullscreen): Lebar maksimal
        bookWidth = maxAvailableWidth;
        bookHeight = bookWidth / aspectRatio;
    } else {
        // Mode 2 Halaman (Desktop):
        // Paksa lebar maksimal dibagi 2 agar buku SANGAT BESAR
        const targetWidthPerPage = maxAvailableWidth / 2;
        bookWidth = targetWidthPerPage;
        bookHeight = bookWidth / aspectRatio;
    }

    try {
        pageFlipInstance = new PageFlip(bookContainer.value, {
            width: bookWidth,
            height: bookHeight,
            size: "fixed", // Wajib fixed agar rasio tidak gepeng
            minWidth: 100,
            maxWidth: 10000,
            minHeight: 100,
            maxHeight: 10000,
            showCover: !isSinglePageMode,
            usePortrait: isSinglePageMode,
            startPage: currentPage.value,
            flippingTime: 600,
            mobileScrollSupport: false,
            maxShadowOpacity: 0.5,
            autoSize: true,
            startZIndex: 5,
        });

        pageFlipInstance.loadFromHTML(pagesDomCache);

        pageFlipInstance.on("flip", (e) => {
            currentPage.value = e.data;
            playFlipSound();
        });
    } catch (err) {
        console.error("Flipbook Init Error:", err);
    }
};

onMounted(() => {
    document.addEventListener("fullscreenchange", handleFullscreenChange);
    setTimeout(() => renderPdf(), 300);
});

onBeforeUnmount(() => {
    document.removeEventListener("fullscreenchange", handleFullscreenChange);
    if (pageFlipInstance) pageFlipInstance.destroy();
    flipSound = null;
});
</script>

<template>
    <Head :title="'Membaca: ' + fileName" />

    <div
        class="fixed inset-0 w-full h-full bg-[#1a1a1a] font-sans text-gray-100 overflow-hidden"
    >
        <div
            v-if="!isFullscreen"
            class="absolute inset-0 bg-[#0a0a0a] z-0"
        ></div>
        <div
            v-if="!isFullscreen"
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[80vw] h-[80vh] bg-indigo-500/5 blur-[150px] rounded-full z-0"
        ></div>

        <div
            class="absolute top-0 left-0 w-full p-4 flex justify-between items-center z-[60] transition-all duration-500 ease-in-out bg-gradient-to-b from-black/90 to-transparent pb-16 opacity-0 hover:opacity-100"
        >
            <button
                @click="goBack"
                class="pointer-events-auto flex items-center gap-2 px-4 py-2 bg-black/60 hover:bg-black/80 rounded-full backdrop-blur-md border border-white/10 transition-all shadow-lg hover:scale-105"
            >
                <ArrowLeftIcon class="w-5 h-5 text-gray-200" />
                <span class="font-medium text-sm hidden md:inline"
                    >Kembali</span
                >
            </button>

            <div
                class="flex flex-col items-center pointer-events-auto bg-black/40 px-6 py-1 rounded-full backdrop-blur-md border border-white/5"
            >
                <h1
                    class="font-bold text-sm tracking-wide text-white/90 max-w-[200px] truncate"
                >
                    {{ fileName }}
                </h1>
                <span class="text-[10px] text-indigo-300 font-mono">
                    {{ isFullscreen ? "Mode Presentasi" : "Mode Buku" }} • Hal
                    {{ currentPage + 1 }} / {{ totalPages }}
                </span>
            </div>

            <div class="pointer-events-auto flex items-center gap-2">
                <button
                    @click="toggleMute"
                    class="p-2.5 bg-black/60 hover:bg-black/80 rounded-full backdrop-blur-md border border-white/10 transition hover:scale-110 shadow-lg"
                    :title="isMuted ? 'Unmute' : 'Mute'"
                >
                    <SpeakerXMarkIcon
                        v-if="isMuted"
                        class="w-5 h-5 text-red-400"
                    />
                    <SpeakerWaveIcon v-else class="w-5 h-5 text-green-400" />
                </button>
                <button
                    @click="toggleFullScreen"
                    class="p-2.5 bg-indigo-600 hover:bg-indigo-500 rounded-full backdrop-blur-md border border-white/10 transition hover:scale-110 shadow-lg text-white"
                    title="Fullscreen"
                >
                    <ArrowsPointingInIcon v-if="isFullscreen" class="w-5 h-5" />
                    <ArrowsPointingOutIcon v-else class="w-5 h-5" />
                </button>
            </div>
        </div>

        <div class="absolute top-0 left-0 w-full h-4 z-[59]"></div>

        <div
            class="absolute inset-0 w-full h-full overflow-y-auto overflow-x-hidden flex items-center justify-center z-10 pt-4 pb-10"
        >
            <div
                v-if="isLoading"
                class="absolute inset-0 flex flex-col items-center justify-center z-50 bg-[#1a1a1a]"
            >
                <div class="relative w-16 h-16 mb-4">
                    <div
                        class="absolute inset-0 border-4 border-indigo-600/30 rounded-full"
                    ></div>
                    <div
                        class="absolute inset-0 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"
                    ></div>
                    <BookOpenIcon
                        class="absolute inset-0 m-auto w-6 h-6 text-indigo-400 animate-pulse"
                    />
                </div>
                <p class="text-sm text-indigo-200 font-mono">
                    Memuat... {{ loadingProgress }}%
                </p>
            </div>

            <div
                v-if="errorMsg"
                class="absolute inset-0 flex items-center justify-center z-50 bg-[#1a1a1a]"
            >
                <div
                    class="bg-red-900/80 p-6 rounded-2xl backdrop-blur-xl text-center max-w-sm border border-red-500/50"
                >
                    <ExclamationCircleIcon
                        class="w-12 h-12 text-red-300 mx-auto mb-3"
                    />
                    <p class="text-red-100 text-sm mb-4">{{ errorMsg }}</p>
                    <button
                        @click="window.location.reload()"
                        class="bg-white text-red-900 px-6 py-2 rounded-full font-bold hover:bg-gray-200 transition"
                    >
                        Muat Ulang
                    </button>
                </div>
            </div>

            <div
                ref="bookContainer"
                :class="[
                    'transition-transform duration-300 origin-top',
                    isFullscreen ? 'shadow-none' : 'flip-book-container',
                ]"
            ></div>
        </div>
    </div>
</template>

<style>
.flip-book-container {
    /* Shadow */
    box-shadow: 0 20px 60px -10px rgba(0, 0, 0, 0.8),
        0 0 20px rgba(0, 0, 0, 0.5);
}

.page {
    background-color: #ffffff;
    overflow: hidden;
    position: relative;
}

.hard-cover {
    background-color: #f3f4f6;
    border: 1px solid #d1d5db;
}

/* Custom Scrollbar agar tidak mengganggu pemandangan */
::-webkit-scrollbar {
    width: 10px;
}
::-webkit-scrollbar-track {
    background: #0a0a0a;
}
::-webkit-scrollbar-thumb {
    background: #333;
    border-radius: 5px;
    border: 2px solid #0a0a0a;
}
::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
