<template>
    <Head title="Panel Guru" />

    <AuthenticatedLayout>
        <!-- HEADER -->
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-bold text-2xl text-gray-800">Panel Guru</h2>

                <button
                    @click="showQRModal = true"
                    class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl shadow-md flex items-center gap-2 font-semibold transition-all"
                >
                    <QrCodeIcon class="w-5 h-5" /> QR Absen
                </button>
            </div>
        </template>

        <!-- CONTENT -->
        <div class="py-10 px-4 lg:px-10">
            <div class="max-w-7xl mx-auto">
                <!-- STAT CARDS -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <div
                        class="bg-white/70 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl transition"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="p-3 bg-blue-100 text-blue-600 rounded-xl"
                            >
                                <UserGroupIcon class="w-7 h-7" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    Total Masuk
                                </p>
                                <p class="text-3xl font-bold text-gray-900">
                                    {{ statistik.total }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white/70 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl transition"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="p-3 bg-green-100 text-green-600 rounded-xl"
                            >
                                <CheckBadgeIcon class="w-7 h-7" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    Hadir
                                </p>
                                <p class="text-3xl font-bold text-gray-900">
                                    {{ statistik.hadir }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white/70 backdrop-blur-lg p-6 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl transition"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="p-3 bg-yellow-100 text-yellow-600 rounded-xl"
                            >
                                <ExclamationCircleIcon class="w-7 h-7" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">
                                    Butuh Approval
                                </p>
                                <p class="text-3xl font-bold text-gray-900">
                                    {{ statistik.pending }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLE -->
                <div
                    class="bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100"
                >
                    <div
                        class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center"
                    >
                        <h3 class="font-semibold text-gray-700 text-lg">
                            Data Absensi Hari Ini
                        </h3>
                        <span class="text-xs text-gray-500">{{
                            tanggalHariIni
                        }}</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-white">
                                <tr class="border-b">
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase"
                                    >
                                        Siswa
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase"
                                    >
                                        Detail
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase"
                                    >
                                        Aksi Guru
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="item in absensi"
                                    :key="item.id"
                                    class="hover:bg-gray-50 transition border-b"
                                >
                                    <!-- Nama -->
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-gray-800">
                                            {{ item.user.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ item.user.email }}
                                        </p>
                                        <p
                                            class="text-[10px] text-gray-400 font-mono mt-1"
                                        >
                                            {{ item.time_in }}
                                        </p>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold"
                                            :class="{
                                                'bg-green-100 text-green-700':
                                                    item.status === 'Hadir',
                                                'bg-blue-100 text-blue-700':
                                                    item.status === 'Sakit',
                                                'bg-yellow-100 text-yellow-700':
                                                    item.status === 'Izin',
                                            }"
                                        >
                                            {{ item.status }}
                                        </span>

                                        <div
                                            v-if="item.status !== 'Hadir'"
                                            class="mt-1"
                                        >
                                            <span
                                                v-if="
                                                    item.approval_status ===
                                                    'pending'
                                                "
                                                class="text-[10px] font-bold text-orange-600 bg-orange-100 px-2 py-0.5 rounded"
                                                >Menunggu</span
                                            >
                                            <span
                                                v-else-if="
                                                    item.approval_status ===
                                                    'approved'
                                                "
                                                class="text-[10px] font-bold text-green-600 bg-green-100 px-2 py-0.5 rounded"
                                                >Disetujui</span
                                            >
                                            <span
                                                v-else
                                                class="text-[10px] font-bold text-red-600 bg-red-100 px-2 py-0.5 rounded"
                                                >Ditolak</span
                                            >
                                        </div>
                                    </td>

                                    <!-- Detail -->
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-700">
                                            {{ item.description }}
                                        </p>
                                        <a
                                            v-if="item.photo_path"
                                            :href="`/storage/${item.photo_path}`"
                                            target="_blank"
                                            class="text-blue-600 text-xs underline mt-2 block"
                                            >📷 Lihat Bukti</a
                                        >
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-6 py-4 text-center">
                                        <div
                                            v-if="
                                                item.approval_status ===
                                                    'pending' &&
                                                item.status !== 'Hadir'
                                            "
                                            class="flex justify-center gap-3"
                                        >
                                            <button
                                                @click="
                                                    updateStatus(
                                                        item.id,
                                                        'approved'
                                                    )
                                                "
                                                class="p-2 bg-green-100 text-green-700 rounded-full hover:bg-green-200 transition"
                                            >
                                                <CheckCircleIcon
                                                    class="w-5 h-5"
                                                />
                                            </button>

                                            <button
                                                @click="
                                                    updateStatus(
                                                        item.id,
                                                        'rejected'
                                                    )
                                                "
                                                class="p-2 bg-red-100 text-red-700 rounded-full hover:bg-red-200 transition"
                                            >
                                                <XCircleIcon class="w-5 h-5" />
                                            </button>
                                        </div>

                                        <span
                                            v-else
                                            class="text-xs text-gray-400 italic"
                                            >{{
                                                item.status === "Hadir"
                                                    ? "Otomatis"
                                                    : "Selesai"
                                            }}</span
                                        >
                                    </td>
                                </tr>

                                <tr v-if="absensi.length === 0">
                                    <td
                                        colspan="4"
                                        class="text-center py-10 text-gray-500 text-sm"
                                    >
                                        Belum ada data absensi hari ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR MODAL -->
        <div
            v-if="showQRModal"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            @click="showQRModal = false"
        >
            <div
                class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md text-center animate-fade-in"
                @click.stop
            >
                <h3 class="text-2xl font-bold text-gray-800 mb-2">
                    Scan QR Absen
                </h3>

                <p class="text-red-600 font-semibold mb-4">
                    Kode berubah setiap 15 detik — jangan screenshot!
                </p>

                <div
                    class="bg-gray-100 p-4 rounded-xl border border-gray-200 inline-block shadow-inner my-4"
                >
                    <QrcodeVue :value="currentQrToken" :size="250" level="H" />
                </div>

                <p
                    class="font-mono text-md font-bold text-green-700 bg-green-50 border border-green-200 p-3 rounded-xl break-all"
                >
                    {{ currentQrToken }}
                </p>

                <button
                    @click="showQRModal = false"
                    class="mt-6 text-gray-500 hover:text-gray-900 underline text-sm"
                >
                    Tutup
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import QrcodeVue from "qrcode.vue";

import {
    UserGroupIcon,
    CheckBadgeIcon,
    QrCodeIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationCircleIcon,
} from "@heroicons/vue/24/solid";

export default {
    name: "TeacherDashboard",
    components: {
        AuthenticatedLayout,
        Head,
        QrcodeVue,
        UserGroupIcon,
        CheckBadgeIcon,
        QrCodeIcon,
        CheckCircleIcon,
        XCircleIcon,
        ExclamationCircleIcon,
    },
    props: {
        absensi: {
            type: Array,
            default: () => [],
        },
        statistik: {
            type: Object,
            default: () => ({ total: 0, hadir: 0, pending: 0 }),
        },
        qrToken: {
            type: String,
            default: "",
        },
    },
    data() {
        return {
            showQRModal: false,
            currentQrToken: this.qrToken || "",
            intervalId: null,
        };
    },
    computed: {
        tanggalHariIni() {
            return new Date().toLocaleDateString("id-ID", {
                weekday: "long",
                year: "numeric",
                month: "long",
                day: "numeric",
            });
        },
    },
    methods: {
        updateStatus(id, status) {
            if (
                confirm(
                    `Apakah Anda yakin ingin mengubah status menjadi ${status}?`
                )
            ) {
                router.patch(route("teacher.attendance.approve", id), {
                    status: status,
                });
            }
        },
        fetchNewToken() {
            // Memanggil route backend yang mengembalikan JSON { token: '...' }
            fetch(route("teacher.qr.token"))
                .then((response) => {
                    if (response.status !== 200) {
                        console.error(
                            "FETCH FAILED: Status code",
                            response.status
                        );
                        return null;
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data && data.token) {
                        this.currentQrToken = data.token;
                        // optional: console.log("TOKEN UPDATED:", data.token);
                    }
                })
                .catch((err) => {
                    console.error("ERROR FETCHING TOKEN:", err);
                });
        },
    },
    mounted() {
        // ambil token pertama kali dan set interval
        this.fetchNewToken();
        this.intervalId = setInterval(this.fetchNewToken, 15000);
    },
    beforeUnmount() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }
    },
};
</script>

<style scoped>
/* opsional: animasi kecil untuk modal */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in {
    animation: fadeIn 220ms ease;
}
</style>
