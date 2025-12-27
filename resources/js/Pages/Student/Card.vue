<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { toPng } from "html-to-image";
import { ArrowDownTrayIcon, PrinterIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    cardData: Object,
    template: Object,
});

const frontRef = ref(null);
const backRef = ref(null);
const isGeneratingFront = ref(false);
const isGeneratingBack = ref(false);

// --- LOGIC RENDER ---
const renderElements = (side) => {
    const elements = props.template?.elements
        ? JSON.parse(props.template.elements)
        : [];
    return elements
        .filter((el) => (el.side || "front") === side)
        .map((el) => {
            let content = el.content;
            let src = el.src;

            if (el.type === "dynamic_text" && el.data_key) {
                const keys = {
                    nama: props.cardData.nama,
                    nisn: props.cardData.nisn,
                    kelas: props.cardData.kelas,
                    ttl: props.cardData.ttl,
                    alamat: props.cardData.alamat,
                    kepsek: props.cardData.kepsek,
                    nip_kepsek: props.cardData.nip_kepsek,
                    tanggal_cetak: props.cardData.tanggal_cetak,
                };
                if (content.includes("{")) {
                    content = content.replace(/{(.*?)}/g, (match, key) => {
                        const cleanKey = key
                            .replace(" SISWA", "")
                            .toLowerCase();
                        const map = {
                            nama: "nama",
                            nisn: "nisn",
                            kelas: "kelas",
                            ttl: "ttl",
                            nip: "nip_kepsek",
                            kepsek: "kepsek",
                            "tgl cetak": "tanggal_cetak",
                        };
                        return keys[map[cleanKey] || el.data_key] || match;
                    });
                } else {
                    content = keys[el.data_key] || content;
                }
            }

            if (el.type === "photo")
                src =
                    props.cardData.foto ||
                    `https://ui-avatars.com/api/?name=${props.cardData.nama}&background=random`;
            if (
                el.type === "qr" ||
                (el.type === "image" && el.data_key === "qr_code")
            ) {
                src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(
                    JSON.stringify({ nis: props.cardData.nis })
                )}`;
            }
            return { ...el, content, src };
        });
};

const renderedFront = computed(() => renderElements("front"));
const renderedBack = computed(() => renderElements("back"));

// --- FIX DOWNLOAD FUNCTION ---
// Kita ubah parameter ke-3 menjadi tipe string ('front'/'back') untuk menentukan state mana yang diubah
const downloadSingleSide = async (refEl, fileNameSuffix, sideType) => {
    if (!refEl) return;

    // Set loading state
    if (sideType === "front") isGeneratingFront.value = true;
    else isGeneratingBack.value = true;

    try {
        await new Promise((resolve) => setTimeout(resolve, 200));
        // Skip fonts agar tidak error CORS/SecurityError
        const dataUrl = await toPng(refEl, {
            quality: 1.0,
            pixelRatio: 3,
            cacheBust: true,
            skipFonts: true,
        });

        const link = document.createElement("a");
        link.download = `Kartu_${fileNameSuffix}_${props.cardData.nama}.png`;
        link.href = dataUrl;
        link.click();
    } catch (error) {
        alert("Gagal mengunduh. Coba lagi.");
        console.error(error);
    } finally {
        // Reset loading state
        if (sideType === "front") isGeneratingFront.value = false;
        else isGeneratingBack.value = false;
    }
};
</script>

<template>
    <Head title="Kartu Pelajar" />
    <AuthenticatedLayout>
        <div
            class="min-h-screen bg-slate-50 flex flex-col items-center py-12 px-4 sm:px-6 lg:px-8"
        >
            <div class="text-center max-w-2xl mb-12">
                <h2
                    class="text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl"
                >
                    Kartu Pelajar Digital
                </h2>
                <p class="mt-4 text-lg text-slate-600">
                    Unduh kartu pelajar resmi Anda. Gunakan versi digital ini
                    untuk keperluan administrasi sekolah.
                </p>
            </div>

            <div
                class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-8 items-start justify-items-center"
            >
                <div class="flex flex-col items-center w-full group">
                    <div class="mb-4 flex items-center gap-2">
                        <span
                            class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold uppercase rounded-full tracking-wider"
                            >Sisi Depan</span
                        >
                    </div>

                    <div
                        class="card-scaler-wrapper shadow-2xl rounded-xl ring-1 ring-black/5 transition-transform duration-300 group-hover:scale-[1.02]"
                    >
                        <div class="card-scaler">
                            <div ref="frontRef" class="card-canvas">
                                <img
                                    v-if="template?.background_path"
                                    :src="`/${template.background_path}`"
                                    class="bg-img"
                                />
                                <div
                                    v-else
                                    class="bg-placeholder bg-gradient-to-br from-blue-600 to-indigo-700"
                                ></div>

                                <div
                                    v-for="el in renderedFront"
                                    :key="el.id"
                                    class="absolute z-10 whitespace-pre"
                                    :style="{
                                        left: el.x + 'px',
                                        top: el.y + 'px',
                                        width: el.width
                                            ? el.width + 'px'
                                            : 'auto',
                                        height: el.height
                                            ? el.height + 'px'
                                            : 'auto',
                                    }"
                                >
                                    <p
                                        v-if="el.type.includes('text')"
                                        :style="{
                                            fontSize: el.fontSize + 'px',
                                            fontWeight: el.fontWeight,
                                            fontFamily: el.fontFamily,
                                            color: el.color,
                                            textAlign: el.align,
                                            lineHeight: 1.2,
                                            opacity: el.opacity,
                                            letterSpacing:
                                                (el.letterSpacing || 0) + 'px',
                                        }"
                                    >
                                        {{ el.content }}
                                    </p>
                                    <img
                                        v-if="['photo', 'qr'].includes(el.type)"
                                        :src="el.src"
                                        class="w-full h-full object-cover"
                                        :style="{ opacity: el.opacity }"
                                        crossorigin="anonymous"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <button
                        @click="downloadSingleSide(frontRef, 'Depan', 'front')"
                        :disabled="isGeneratingFront"
                        class="mt-8 inline-flex items-center justify-center px-8 py-3 border border-transparent text-sm font-medium rounded-full text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg transition-all disabled:opacity-70 disabled:cursor-wait w-full sm:w-auto"
                    >
                        <svg
                            v-if="isGeneratingFront"
                            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
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
                        <ArrowDownTrayIcon v-else class="-ml-1 mr-2 h-5 w-5" />
                        {{
                            isGeneratingFront
                                ? "Memproses..."
                                : "Unduh Sisi Depan"
                        }}
                    </button>
                </div>

                <div class="flex flex-col items-center w-full group">
                    <div class="mb-4 flex items-center gap-2">
                        <span
                            class="px-3 py-1 bg-slate-100 text-slate-800 text-xs font-bold uppercase rounded-full tracking-wider"
                            >Sisi Belakang</span
                        >
                    </div>

                    <div
                        class="card-scaler-wrapper shadow-2xl rounded-xl ring-1 ring-black/5 transition-transform duration-300 group-hover:scale-[1.02]"
                    >
                        <div class="card-scaler">
                            <div ref="backRef" class="card-canvas">
                                <img
                                    v-if="template?.background_back_path"
                                    :src="`/${template.background_back_path}`"
                                    class="bg-img"
                                />
                                <div
                                    v-else
                                    class="bg-placeholder bg-gradient-to-br from-slate-700 to-slate-900"
                                ></div>

                                <div
                                    v-for="el in renderedBack"
                                    :key="el.id"
                                    class="absolute z-10 whitespace-pre"
                                    :style="{
                                        left: el.x + 'px',
                                        top: el.y + 'px',
                                        width: el.width
                                            ? el.width + 'px'
                                            : 'auto',
                                        height: el.height
                                            ? el.height + 'px'
                                            : 'auto',
                                    }"
                                >
                                    <p
                                        v-if="el.type.includes('text')"
                                        :style="{
                                            fontSize: el.fontSize + 'px',
                                            fontWeight: el.fontWeight,
                                            fontFamily: el.fontFamily,
                                            color: el.color,
                                            textAlign: el.align,
                                            lineHeight: 1.2,
                                            opacity: el.opacity,
                                            letterSpacing:
                                                (el.letterSpacing || 0) + 'px',
                                        }"
                                    >
                                        {{ el.content }}
                                    </p>
                                    <img
                                        v-if="['qr'].includes(el.type)"
                                        :src="el.src"
                                        class="w-full h-full object-cover"
                                        :style="{ opacity: el.opacity }"
                                        crossorigin="anonymous"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <button
                        @click="downloadSingleSide(backRef, 'Belakang', 'back')"
                        :disabled="isGeneratingBack"
                        class="mt-8 inline-flex items-center justify-center px-8 py-3 border border-transparent text-sm font-medium rounded-full text-white bg-slate-800 hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 shadow-lg transition-all disabled:opacity-70 disabled:cursor-wait w-full sm:w-auto"
                    >
                        <svg
                            v-if="isGeneratingBack"
                            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
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
                        <ArrowDownTrayIcon v-else class="-ml-1 mr-2 h-5 w-5" />
                        {{
                            isGeneratingBack
                                ? "Memproses..."
                                : "Unduh Sisi Belakang"
                        }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* TEKNIK RESPONSIVE SCALING */
/* Membungkus kartu agar ukurannya mengecil di HP tapi koordinat tetap akurat */
.card-scaler-wrapper {
    position: relative;
    width: 323px;
    height: 204px;
    /* Default scale untuk desktop agar terlihat besar & jelas */
    transform: scale(1.4);
    transform-origin: top center;
    margin-bottom: 50px; /* Kompensasi ruang kosong akibat scale */
}

/* Responsif untuk Tablet */
@media (max-width: 1024px) {
    .card-scaler-wrapper {
        transform: scale(1.2);
        margin-bottom: 40px;
    }
}

/* Responsif untuk HP */
@media (max-width: 640px) {
    .card-scaler-wrapper {
        transform: scale(0.9); /* Mengecil agar muat di layar potrait */
        margin-bottom: 0;
    }
}

/* Untuk HP yang sangat kecil (iPhone SE dsb) */
@media (max-width: 380px) {
    .card-scaler-wrapper {
        transform: scale(0.8);
    }
}

.card-canvas {
    width: 323px; /* Ukuran ASLI kartu ID-1 (Jangan diubah agar hasil cetak presisi) */
    height: 204px;
    background: white;
    position: relative;
    overflow: hidden;
    font-family: Arial, Helvetica, sans-serif;
}

.bg-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    inset: 0;
    z-index: 0;
}
.bg-placeholder {
    width: 100%;
    height: 100%;
    position: absolute;
    inset: 0;
    z-index: 0;
}
</style>
