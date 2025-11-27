<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import {
    Cog6ToothIcon,
    MapPinIcon,
    ClockIcon,
    BuildingLibraryIcon,
} from "@heroicons/vue/24/solid";
import { onMounted, ref, watch } from "vue";

// Import Leaflet (Peta)
import "leaflet/dist/leaflet.css";
import L from "leaflet";

const props = defineProps({
    setting: Object,
});

const form = useForm({
    school_name: props.setting?.school_name || "",
    academic_year: props.setting?.academic_year || "",
    latitude: props.setting?.latitude || -6.2,
    longitude: props.setting?.longitude || 106.816666,
    radius_limit: props.setting?.radius_limit || 50,
    clock_in_time: props.setting?.clock_in_time || "07:00",
    clock_out_time: props.setting?.clock_out_time || "15:00",
});

// --- LOGIKA PETA LEAFLET ---
let map = null;
let marker = null;
let circle = null;

onMounted(() => {
    initMap();
});

const initMap = () => {
    // 1. Inisialisasi Peta
    map = L.map("map").setView([form.latitude, form.longitude], 18); // Zoom level 18 biar dekat

    // 2. Pasang Tile (Gambar Peta dari OpenStreetMap)
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap contributors",
    }).addTo(map);

    // 3. Perbaiki Icon Marker (Bug bawaan Leaflet di Vite)
    const iconUrl =
        "https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png";
    const shadowUrl =
        "https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png";
    const customIcon = L.icon({
        iconUrl: iconUrl,
        shadowUrl: shadowUrl,
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
    });

    // 4. Tambahkan Marker (Bisa Digeser)
    marker = L.marker([form.latitude, form.longitude], {
        icon: customIcon,
        draggable: true,
    }).addTo(map);

    // 5. Tambahkan Lingkaran Radius
    circle = L.circle([form.latitude, form.longitude], {
        color: "red",
        fillColor: "#f03",
        fillOpacity: 0.2,
        radius: form.radius_limit,
    }).addTo(map);

    // 6. Event Listener: Saat Marker Digeser -> Update Form Lat/Long
    marker.on("dragend", function (e) {
        const position = marker.getLatLng();
        form.latitude = position.lat;
        form.longitude = position.lng;

        // Pindahkan juga lingkarannya
        circle.setLatLng(position);
    });

    // 7. Event Listener: Klik Peta -> Pindahkan Marker
    map.on("click", function (e) {
        const { lat, lng } = e.latlng;
        updateMarkerPosition(lat, lng);
    });
};

// Fungsi Update Posisi Marker & Form
const updateMarkerPosition = (lat, lng) => {
    form.latitude = lat;
    form.longitude = lng;
    marker.setLatLng([lat, lng]);
    circle.setLatLng([lat, lng]);
    map.panTo([lat, lng]);
};

// Watcher: Jika input Radius berubah, update lingkaran di peta
watch(
    () => form.radius_limit,
    (newRadius) => {
        if (circle) circle.setRadius(newRadius);
    }
);

// Watcher: Jika input Lat/Long diketik manual, update peta
watch(
    () => [form.latitude, form.longitude],
    ([newLat, newLng]) => {
        if (marker && circle) {
            marker.setLatLng([newLat, newLng]);
            circle.setLatLng([newLat, newLng]);
            map.panTo([newLat, newLng]);
        }
    }
);

const submit = () => {
    form.patch(route("admin.settings.update"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Pengaturan Sistem" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-bold text-xl text-red-800 leading-tight flex items-center gap-2"
            >
                <Cog6ToothIcon class="w-6 h-6" /> Pengaturan Sistem Sekolah
            </h2>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto">
                <form @submit.prevent="submit">
                    <div
                        class="bg-white shadow-md rounded-xl overflow-hidden border border-gray-200 mb-6"
                    >
                        <div
                            class="px-6 py-4 bg-gray-50 border-b border-gray-200 font-bold text-gray-700 flex items-center gap-2"
                        >
                            <BuildingLibraryIcon class="w-5 h-5 text-red-600" />
                            Identitas Sekolah
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-1"
                                    >Nama Sekolah</label
                                >
                                <input
                                    v-model="form.school_name"
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 focus:ring-red-500"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-1"
                                    >Tahun Ajaran</label
                                >
                                <input
                                    v-model="form.academic_year"
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 focus:ring-red-500"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white shadow-md rounded-xl overflow-hidden border border-gray-200 mb-6"
                    >
                        <div
                            class="px-6 py-4 bg-gray-50 border-b border-gray-200 font-bold text-gray-700 flex items-center gap-2"
                        >
                            <MapPinIcon class="w-5 h-5 text-red-600" /> Lokasi
                            Absensi (Geofencing)
                        </div>

                        <div class="p-6">
                            <div
                                class="mb-6 rounded-lg overflow-hidden border-2 border-gray-300 shadow-inner"
                            >
                                <div
                                    id="map"
                                    style="
                                        height: 400px;
                                        width: 100%;
                                        z-index: 1;
                                    "
                                ></div>
                                <p
                                    class="text-xs text-center bg-gray-100 py-1 text-gray-500"
                                >
                                    Geser 📍 Pin Biru untuk menentukan titik
                                    pusat sekolah. Area merah adalah jangkauan
                                    radius.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-700 mb-1"
                                        >Latitude</label
                                    >
                                    <input
                                        v-model="form.latitude"
                                        type="text"
                                        class="w-full rounded-lg border-gray-300 bg-gray-50 cursor-not-allowed"
                                        readonly
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-700 mb-1"
                                        >Longitude</label
                                    >
                                    <input
                                        v-model="form.longitude"
                                        type="text"
                                        class="w-full rounded-lg border-gray-300 bg-gray-50 cursor-not-allowed"
                                        readonly
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-700 mb-1"
                                        >Radius (Meter)</label
                                    >
                                    <input
                                        v-model="form.radius_limit"
                                        type="number"
                                        class="w-full rounded-lg border-gray-300 focus:ring-red-500"
                                    />
                                    <p class="text-xs text-red-500 mt-1">
                                        *Area merah di peta akan berubah sesuai
                                        radius.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white shadow-md rounded-xl overflow-hidden border border-gray-200 mb-6"
                    >
                        <div
                            class="px-6 py-4 bg-gray-50 border-b border-gray-200 font-bold text-gray-700 flex items-center gap-2"
                        >
                            <ClockIcon class="w-5 h-5 text-red-600" /> Aturan
                            Waktu
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-1"
                                    >Jam Masuk (Batas Terlambat)</label
                                >
                                <input
                                    v-model="form.clock_in_time"
                                    type="time"
                                    class="w-full rounded-lg border-gray-300 focus:ring-red-500"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-1"
                                    >Jam Pulang</label
                                >
                                <input
                                    v-model="form.clock_out_time"
                                    type="time"
                                    class="w-full rounded-lg border-gray-300 focus:ring-red-500"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pb-10">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-red-600 text-white px-10 py-3 rounded-full font-bold hover:bg-red-700 shadow-lg transition transform hover:scale-105 disabled:opacity-50"
                        >
                            <span v-if="form.processing">Menyimpan...</span>
                            <span v-else>Simpan Pengaturan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Fix z-index issue with Leaflet */
.leaflet-pane {
    z-index: 10 !important;
}
.leaflet-top,
.leaflet-bottom {
    z-index: 20 !important;
}
</style>
