<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { ref, reactive, computed, onMounted, onUnmounted } from "vue";
import {
    CheckIcon,
    PhotoIcon,
    TrashIcon,
    SwatchIcon,
    ArrowLeftIcon,
    QrCodeIcon,
    Bars3BottomLeftIcon,
    Bars3Icon,
    Bars3BottomRightIcon,
    MagnifyingGlassMinusIcon,
    MagnifyingGlassPlusIcon,
    LockClosedIcon,
    LockOpenIcon,
    DocumentDuplicateIcon,
    ChevronUpIcon,
    ChevronDownIcon,
} from "@heroicons/vue/24/outline";
import Swal from "sweetalert2";

const props = defineProps({ template: Object });

// --- CONFIG ---
const fontOptions = [
    { name: "Arial (Sans)", value: "Arial, Helvetica, sans-serif" },
    {
        name: "Times New Roman (Serif)",
        value: '"Times New Roman", Times, serif',
    },
    { name: "Courier New (Mono)", value: '"Courier New", Courier, monospace' },
    { name: "Verdana", value: "Verdana, Geneva, sans-serif" },
    { name: "Georgia", value: "Georgia, serif" },
    { name: "Trebuchet MS", value: '"Trebuchet MS", sans-serif' },
    { name: "Impact", value: "Impact, Charcoal, sans-serif" },
    { name: "Comic Sans", value: '"Comic Sans MS", cursive, sans-serif' },
];

const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener("mouseenter", Swal.stopTimer);
        toast.addEventListener("mouseleave", Swal.resumeTimer);
    },
});

// --- STATE ---
const side = ref("front");
const design = reactive({
    width: 323,
    height: 204,
    scale: 2.0,
    backgrounds: {
        front: props.template?.background_path
            ? `/${props.template.background_path}`
            : null,
        back: props.template?.background_back_path
            ? `/${props.template.background_back_path}`
            : null,
    },
    frontFile: null,
    backFile: null,
    elements: props.template?.elements
        ? JSON.parse(props.template.elements)
        : [],
});

const activeElementId = ref(null);
const isSaving = ref(false);

const currentElements = computed(() =>
    design.elements.filter((el) => (el.side || "front") === side.value)
);
const activeElement = computed(() =>
    design.elements.find((el) => el.id === activeElementId.value)
);

// --- ACTIONS ---
const addElement = (type, dataKey = null, defaultText = "Teks Baru") => {
    const newId = Date.now();
    design.elements.push({
        id: newId,
        side: side.value,
        type: type,
        data_key: dataKey,
        content: defaultText,
        x: 50,
        y: 50,
        width: type === "photo" || type === "qr" ? 50 : null,
        height: type === "photo" ? 70 : type === "qr" ? 50 : null,
        // Style Defaults
        fontSize: 10,
        fontWeight: "bold",
        fontFamily: "Arial, Helvetica, sans-serif",
        color: "#000000",
        align: "left",
        opacity: 1,
        letterSpacing: 0,
        locked: false,
        zIndex: design.elements.length + 1,
    });
    activeElementId.value = newId;
    Toast.fire({ icon: "success", title: "Elemen ditambahkan" });
};

const duplicateElement = () => {
    if (!activeElement.value) return;
    const clone = JSON.parse(JSON.stringify(activeElement.value));
    clone.id = Date.now();
    clone.x += 10;
    clone.y += 10;
    design.elements.push(clone);
    activeElementId.value = clone.id;
    Toast.fire({ icon: "info", title: "Diduplikat" });
};

const removeElement = () => {
    if (!activeElement.value) return;
    Swal.fire({
        title: "Hapus?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        confirmButtonText: "Ya",
        cancelButtonText: "Batal",
    }).then((result) => {
        if (result.isConfirmed) {
            design.elements = design.elements.filter(
                (el) => el.id !== activeElementId.value
            );
            activeElementId.value = null;
            Toast.fire({ icon: "success", title: "Terhapus" });
        }
    });
};

const handleBackgroundUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        if (side.value === "front") design.frontFile = file;
        else design.backFile = file;
        design.backgrounds[side.value] = URL.createObjectURL(file);
        Toast.fire({ icon: "success", title: "Background diperbarui" });
    }
};

const saveTemplate = () => {
    isSaving.value = true;
    const form = useForm({
        background: design.frontFile,
        background_back: design.backFile,
        elements: JSON.stringify(design.elements),
    });
    form.post(route("admin.card.store"), {
        preserveScroll: true,
        onFinish: () => {
            isSaving.value = false;
            Toast.fire({ icon: "success", title: "Desain Tersimpan" });
        },
    });
};

// --- DRAG & DROP ---
let isDragging = false;
let startX = 0,
    startY = 0,
    initialElX = 0,
    initialElY = 0;

const startDrag = (e, element) => {
    if (element.locked) return; // Jangan drag jika terkunci
    e.preventDefault();
    e.stopPropagation();
    activeElementId.value = element.id;
    isDragging = true;
    startX = e.clientX;
    startY = e.clientY;
    initialElX = element.x;
    initialElY = element.y;
    window.addEventListener("mousemove", onMouseMove);
    window.addEventListener("mouseup", onMouseUp);
};

const onMouseMove = (e) => {
    if (!isDragging) return;
    const el = design.elements.find((el) => el.id === activeElementId.value);
    if (el && !el.locked) {
        el.x = Math.round(initialElX + (e.clientX - startX) / design.scale);
        el.y = Math.round(initialElY + (e.clientY - startY) / design.scale);
    }
};

const onMouseUp = () => {
    isDragging = false;
    window.removeEventListener("mousemove", onMouseMove);
    window.removeEventListener("mouseup", onMouseUp);
};

// --- KEYBOARD SHORTCUTS (Arrows & Delete) ---
const handleKeydown = (e) => {
    if (!activeElementId.value) return;
    const el = design.elements.find((el) => el.id === activeElementId.value);
    if (!el) return;

    // Abaikan jika sedang mengetik di input
    if (["INPUT", "TEXTAREA", "SELECT"].includes(e.target.tagName)) return;

    // Move with arrows (Fine Tuning)
    if (e.key.startsWith("Arrow")) {
        e.preventDefault();
        if (el.locked) return;
        const step = e.shiftKey ? 10 : 1; // Shift+Arrow = 10px, Arrow = 1px
        if (e.key === "ArrowUp") el.y -= step;
        if (e.key === "ArrowDown") el.y += step;
        if (e.key === "ArrowLeft") el.x -= step;
        if (e.key === "ArrowRight") el.x += step;
    }

    // Delete
    if (e.key === "Delete" || e.key === "Backspace") {
        if (!el.locked) removeElement();
    }
};

onMounted(() => window.addEventListener("keydown", handleKeydown));
onUnmounted(() => window.removeEventListener("keydown", handleKeydown));
</script>

<template>
    <Head title="Desainer Kartu Pro" />
    <AuthenticatedLayout>
        <div
            class="flex flex-col h-[calc(100vh-65px)] bg-gray-100 overflow-hidden"
        >
            <div
                class="bg-white border-b h-14 shrink-0 flex items-center justify-between px-6 shadow-sm z-30"
            >
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.dashboard')"
                        class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-xs font-bold uppercase tracking-wide"
                    >
                        <ArrowLeftIcon class="w-4 h-4" /> Dashboard
                    </Link>
                    <div class="h-6 w-px bg-gray-300"></div>
                    <div class="flex bg-slate-100 p-1 rounded-md">
                        <button
                            @click="
                                side = 'front';
                                activeElementId = null;
                            "
                            :class="[
                                'px-4 py-1 text-[10px] font-bold rounded transition uppercase',
                                side === 'front'
                                    ? 'bg-white text-blue-600 shadow-sm'
                                    : 'text-slate-500',
                            ]"
                        >
                            Depan
                        </button>
                        <button
                            @click="
                                side = 'back';
                                activeElementId = null;
                            "
                            :class="[
                                'px-4 py-1 text-[10px] font-bold rounded transition uppercase',
                                side === 'back'
                                    ? 'bg-white text-blue-600 shadow-sm'
                                    : 'text-slate-500',
                            ]"
                        >
                            Belakang
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div
                        class="text-[10px] text-slate-400 mr-2 hidden md:block"
                    >
                        Tip: Gunakan panah keyboard untuk geser presisi
                    </div>
                    <button
                        @click="saveTemplate"
                        :disabled="isSaving"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-bold text-xs flex items-center gap-2 shadow-lg transition transform active:scale-95"
                    >
                        <CheckIcon v-if="!isSaving" class="w-4 h-4" />
                        <span
                            v-else
                            class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"
                        ></span>
                        {{ isSaving ? "Menyimpan..." : "SIMPAN DESAIN" }}
                    </button>
                </div>
            </div>

            <div class="flex flex-1 overflow-hidden relative">
                <div class="w-64 bg-white border-r flex flex-col z-20 shrink-0">
                    <div class="p-4 bg-slate-50 border-b">
                        <h3
                            class="text-[10px] font-bold text-slate-500 uppercase tracking-widest"
                        >
                            Library Elemen
                        </h3>
                    </div>
                    <div
                        class="p-4 space-y-3 overflow-y-auto flex-1 custom-scrollbar"
                    >
                        <div class="space-y-2">
                            <p class="section-title text-blue-600">
                                Identitas Siswa
                            </p>
                            <button
                                @click="
                                    addElement('dynamic_text', 'nama', '{NAMA}')
                                "
                                class="btn-tool"
                            >
                                Nama Lengkap
                            </button>
                            <button
                                @click="
                                    addElement('dynamic_text', 'nisn', '{NISN}')
                                "
                                class="btn-tool"
                            >
                                NISN
                            </button>
                            <button
                                @click="
                                    addElement(
                                        'dynamic_text',
                                        'kelas',
                                        '{KELAS}'
                                    )
                                "
                                class="btn-tool"
                            >
                                Kelas
                            </button>
                            <button
                                @click="
                                    addElement('dynamic_text', 'ttl', '{TTL}')
                                "
                                class="btn-tool"
                            >
                                TTL
                            </button>
                            <button
                                @click="
                                    addElement(
                                        'dynamic_text',
                                        'alamat',
                                        '{ALAMAT}'
                                    )
                                "
                                class="btn-tool"
                            >
                                Alamat
                            </button>
                        </div>
                        <div class="space-y-2 mt-4">
                            <p class="section-title text-purple-600">Media</p>
                            <button
                                @click="addElement('photo', 'foto', '')"
                                class="btn-tool"
                            >
                                <PhotoIcon
                                    class="w-4 h-4 mr-2 text-purple-500"
                                />
                                Foto Siswa
                            </button>
                            <button
                                @click="addElement('qr', 'qr_code', '')"
                                class="btn-tool"
                            >
                                <QrCodeIcon
                                    class="w-4 h-4 mr-2 text-slate-700"
                                />
                                QR Code
                            </button>
                        </div>
                        <div class="space-y-2 mt-4">
                            <p class="section-title text-orange-600">
                                Data Sekolah
                            </p>
                            <button
                                @click="
                                    addElement(
                                        'dynamic_text',
                                        'kepsek',
                                        '{KEPSEK}'
                                    )
                                "
                                class="btn-tool"
                            >
                                Kepala Sekolah
                            </button>
                            <button
                                @click="
                                    addElement(
                                        'dynamic_text',
                                        'nip_kepsek',
                                        'NIP. {NIP}'
                                    )
                                "
                                class="btn-tool"
                            >
                                NIP
                            </button>
                            <button
                                @click="
                                    addElement(
                                        'dynamic_text',
                                        'tanggal_cetak',
                                        '{TGL CETAK}'
                                    )
                                "
                                class="btn-tool"
                            >
                                Tanggal Cetak
                            </button>
                        </div>
                        <div class="space-y-2 mt-4">
                            <p class="section-title text-gray-500">Lainnya</p>
                            <button
                                @click="
                                    addElement(
                                        'static_text',
                                        null,
                                        'Teks Label'
                                    )
                                "
                                class="btn-tool border-dashed"
                            >
                                Teks Manual
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    class="flex-1 bg-slate-200 relative overflow-auto flex flex-col items-center justify-center p-10"
                >
                    <div
                        :style="{
                            width: design.width * design.scale + 'px',
                            height: design.height * design.scale + 'px',
                        }"
                        class="relative bg-white shadow-2xl rounded-sm overflow-hidden select-none ring-1 ring-slate-300 transition-transform duration-100 ease-linear shrink-0 origin-center"
                    >
                        <div
                            class="absolute inset-0 w-full h-full z-0 pointer-events-none bg-white"
                        >
                            <img
                                v-if="design.backgrounds[side]"
                                :src="design.backgrounds[side]"
                                class="w-full h-full object-cover"
                            />
                            <div
                                v-else
                                class="flex flex-col items-center justify-center h-full text-slate-300 gap-2 opacity-50"
                            >
                                <PhotoIcon class="w-16 h-16" />
                                <span
                                    class="text-xs font-bold uppercase tracking-widest"
                                    >Background {{ side }}</span
                                >
                            </div>
                        </div>

                        <div
                            class="origin-top-left"
                            :style="{
                                transform: `scale(${design.scale})`,
                                width: design.width + 'px',
                                height: design.height + 'px',
                            }"
                        >
                            <div
                                v-for="el in currentElements"
                                :key="el.id"
                                @mousedown="startDrag($event, el)"
                                :class="[
                                    'absolute cursor-move hover:outline hover:outline-1 hover:outline-blue-400',
                                    activeElementId === el.id
                                        ? 'outline outline-2 outline-blue-600 z-50'
                                        : '',
                                ]"
                                :style="{
                                    left: el.x + 'px',
                                    top: el.y + 'px',
                                    width: el.width ? el.width + 'px' : 'auto',
                                    height: el.height
                                        ? el.height + 'px'
                                        : 'auto',
                                    zIndex: el.zIndex || 1,
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
                                        opacity: el.opacity || 1,
                                        letterSpacing:
                                            (el.letterSpacing || 0) + 'px',
                                        whiteSpace: 'pre',
                                        lineHeight: 1.2,
                                    }"
                                >
                                    {{ el.content }}
                                </p>

                                <div
                                    v-if="el.type === 'photo'"
                                    class="w-full h-full bg-purple-100 border border-purple-300 flex items-center justify-center text-[8px] font-bold text-purple-800"
                                    :style="{ opacity: el.opacity || 1 }"
                                >
                                    FOTO
                                </div>
                                <div
                                    v-if="el.type === 'qr'"
                                    class="w-full h-full bg-white border border-slate-800 flex items-center justify-center"
                                    :style="{ opacity: el.opacity || 1 }"
                                >
                                    <QrCodeIcon
                                        class="w-2/3 h-2/3 text-slate-800"
                                    />
                                </div>

                                <div
                                    v-if="el.locked"
                                    class="absolute -top-3 -right-3 bg-red-100 text-red-600 rounded-full p-0.5 shadow-sm"
                                >
                                    <LockClosedIcon class="w-3 h-3" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="absolute bottom-8 flex items-center gap-3 bg-white/90 backdrop-blur px-4 py-2 rounded-full shadow-xl border border-gray-200 z-40"
                    >
                        <MagnifyingGlassMinusIcon
                            @click="
                                design.scale = Math.max(0.5, design.scale - 0.1)
                            "
                            class="w-4 h-4 cursor-pointer hover:text-blue-600"
                        />
                        <input
                            type="range"
                            v-model.number="design.scale"
                            min="0.5"
                            max="4"
                            step="0.1"
                            class="w-24 h-1.5 bg-gray-300 rounded-lg appearance-none cursor-pointer accent-blue-600"
                        />
                        <MagnifyingGlassPlusIcon
                            @click="
                                design.scale = Math.min(4, design.scale + 0.1)
                            "
                            class="w-4 h-4 cursor-pointer hover:text-blue-600"
                        />
                        <span
                            class="text-[10px] font-bold text-slate-600 min-w-[30px] text-right"
                            >{{ Math.round(design.scale * 100) }}%</span
                        >
                    </div>
                </div>

                <div
                    class="w-72 bg-white border-l flex flex-col z-20 shrink-0 h-full"
                >
                    <div
                        class="p-4 bg-slate-50 border-b flex justify-between items-center"
                    >
                        <h3
                            class="text-[10px] font-bold text-slate-500 uppercase tracking-widest"
                        >
                            Properti
                        </h3>
                        <div v-if="activeElement" class="flex gap-2">
                            <button
                                @click="
                                    activeElement.locked = !activeElement.locked
                                "
                                :class="
                                    activeElement.locked
                                        ? 'text-red-500'
                                        : 'text-slate-400 hover:text-slate-600'
                                "
                                title="Kunci Posisi"
                            >
                                <component
                                    :is="
                                        activeElement.locked
                                            ? LockClosedIcon
                                            : LockOpenIcon
                                    "
                                    class="w-4 h-4"
                                />
                            </button>
                            <button
                                @click="duplicateElement"
                                class="text-blue-500 hover:text-blue-700"
                                title="Duplikat"
                            >
                                <DocumentDuplicateIcon class="w-4 h-4" />
                            </button>
                            <button
                                @click="removeElement"
                                class="text-red-500 hover:text-red-700"
                                title="Hapus"
                            >
                                <TrashIcon class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="!activeElement"
                        class="p-8 flex flex-col items-center text-center text-slate-300 mt-10"
                    >
                        <SwatchIcon class="w-16 h-16 mb-4 opacity-30" />
                        <p class="text-xs font-medium text-slate-400">
                            Pilih elemen untuk mengedit
                        </p>

                        <div
                            class="w-full border-t border-gray-100 mt-8 pt-6 text-left"
                        >
                            <label class="label-control mb-2 text-slate-500"
                                >Background {{ side }}</label
                            >
                            <input
                                type="file"
                                @change="handleBackgroundUpload"
                                class="file-input"
                                accept="image/*"
                            />
                        </div>
                    </div>

                    <div
                        v-else
                        class="p-5 space-y-5 overflow-y-auto flex-1 custom-scrollbar"
                    >
                        <div class="flex items-center gap-2 mb-2">
                            <span class="badge uppercase">{{
                                activeElement.type.replace("_", " ")
                            }}</span>
                            <span
                                v-if="activeElement.locked"
                                class="text-[9px] text-red-500 font-bold flex items-center gap-1 bg-red-50 px-2 py-0.5 rounded"
                                ><LockClosedIcon class="w-3 h-3" />
                                Terkunci</span
                            >
                        </div>

                        <div
                            v-if="activeElement.type.includes('text')"
                            class="space-y-4"
                        >
                            <div>
                                <label class="label-control">Isi Konten</label>
                                <textarea
                                    v-model="activeElement.content"
                                    rows="2"
                                    class="input-control font-sans"
                                ></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="label-control"
                                        >Font Family</label
                                    >
                                    <select
                                        v-model="activeElement.fontFamily"
                                        class="input-control"
                                    >
                                        <option
                                            v-for="font in fontOptions"
                                            :key="font.value"
                                            :value="font.value"
                                        >
                                            {{ font.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="label-control">Style</label>
                                    <select
                                        v-model="activeElement.fontWeight"
                                        class="input-control"
                                    >
                                        <option value="normal">Normal</option>
                                        <option value="bold">Bold</option>
                                        <option value="800">Extra Bold</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="label-control"
                                        >Ukuran ({{
                                            activeElement.fontSize
                                        }}px)</label
                                    >
                                    <input
                                        type="range"
                                        v-model.number="activeElement.fontSize"
                                        min="6"
                                        max="72"
                                        class="w-full accent-blue-600"
                                    />
                                </div>
                                <div>
                                    <label class="label-control">Warna</label>
                                    <div
                                        class="flex items-center border rounded px-2 py-1 bg-white"
                                    >
                                        <input
                                            type="color"
                                            v-model="activeElement.color"
                                            class="w-6 h-6 border-none bg-transparent cursor-pointer"
                                        />
                                        <span
                                            class="text-[10px] ml-2 text-slate-500"
                                            >{{ activeElement.color }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="label-control"
                                    >Perataan Teks</label
                                >
                                <div
                                    class="flex bg-slate-100 rounded p-1 gap-1"
                                >
                                    <button
                                        @click="activeElement.align = 'left'"
                                        :class="[
                                            'align-btn',
                                            activeElement.align === 'left'
                                                ? 'bg-white shadow text-blue-600'
                                                : 'text-slate-400',
                                        ]"
                                    >
                                        <Bars3BottomLeftIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="activeElement.align = 'center'"
                                        :class="[
                                            'align-btn',
                                            activeElement.align === 'center'
                                                ? 'bg-white shadow text-blue-600'
                                                : 'text-slate-400',
                                        ]"
                                    >
                                        <Bars3Icon class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="activeElement.align = 'right'"
                                        :class="[
                                            'align-btn',
                                            activeElement.align === 'right'
                                                ? 'bg-white shadow text-blue-600'
                                                : 'text-slate-400',
                                        ]"
                                    >
                                        <Bars3BottomRightIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="label-control"
                                    >Spasi Huruf ({{
                                        activeElement.letterSpacing || 0
                                    }}px)</label
                                >
                                <input
                                    type="range"
                                    v-model.number="activeElement.letterSpacing"
                                    min="-2"
                                    max="10"
                                    step="0.5"
                                    class="w-full accent-purple-500"
                                />
                            </div>
                        </div>

                        <div
                            v-if="
                                activeElement.type === 'photo' ||
                                activeElement.type === 'qr'
                            "
                            class="grid grid-cols-2 gap-3"
                        >
                            <div>
                                <label class="label-control">Lebar</label
                                ><input
                                    type="number"
                                    v-model.number="activeElement.width"
                                    class="input-control"
                                />
                            </div>
                            <div>
                                <label class="label-control">Tinggi</label
                                ><input
                                    type="number"
                                    v-model.number="activeElement.height"
                                    class="input-control"
                                />
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 space-y-3">
                            <div>
                                <label class="label-control"
                                    >Transparansi ({{
                                        (activeElement.opacity || 1) * 100
                                    }}%)</label
                                >
                                <input
                                    type="range"
                                    v-model.number="activeElement.opacity"
                                    min="0.1"
                                    max="1"
                                    step="0.1"
                                    class="w-full accent-green-500"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="label-control">Posisi X</label
                                    ><input
                                        type="number"
                                        v-model.number="activeElement.x"
                                        class="input-control"
                                    />
                                </div>
                                <div>
                                    <label class="label-control">Posisi Y</label
                                    ><input
                                        type="number"
                                        v-model.number="activeElement.y"
                                        class="input-control"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.btn-tool {
    @apply w-full text-left px-3 py-2 text-xs font-medium bg-white border border-slate-200 rounded hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 transition flex items-center mb-2 shadow-sm;
}
.label-control {
    @apply block text-[10px] font-bold text-slate-500 mb-1.5 uppercase tracking-wide;
}
.input-control {
    @apply w-full text-xs border-slate-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-slate-50 focus:bg-white;
}
.file-input {
    @apply block w-full text-[10px] text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer;
}
.badge {
    @apply text-[9px] font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded border border-blue-200;
}
.align-btn {
    @apply flex-1 flex justify-center py-1 rounded transition hover:bg-white/50;
}
.section-title {
    @apply text-[9px] font-extrabold uppercase tracking-widest mb-2;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 4px;
}
</style>
