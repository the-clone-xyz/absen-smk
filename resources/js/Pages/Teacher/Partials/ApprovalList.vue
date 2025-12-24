<script setup>
import { router } from "@inertiajs/vue3";
import {
    CheckCircleIcon,
    XCircleIcon,
    CheckBadgeIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    absensiGrouped: Object,
});

const updateStatus = (id, status) => {
    if (confirm(`Ubah status menjadi ${status}?`)) {
        router.patch(
            route("teacher.attendance.approve", id),
            { status: status },
            { preserveScroll: true }
        );
    }
};
</script>

<template>
    <div class="flex flex-col h-full bg-white min-h-[300px]">
        <div
            v-if="!absensiGrouped || Object.keys(absensiGrouped).length === 0"
            class="flex-grow flex flex-col items-center justify-center text-center p-8"
        >
            <div class="bg-green-50 p-4 rounded-full mb-3">
                <CheckBadgeIcon class="w-10 h-10 text-green-500" />
            </div>
            <h4 class="text-gray-900 font-bold text-sm">Semua Beres!</h4>
            <p class="text-xs text-gray-500 mt-1">
                Tidak ada permintaan izin yang menunggu.
            </p>
        </div>

        <div v-else class="flex-grow overflow-y-auto custom-scrollbar">
            <div v-for="(group, className) in absensiGrouped" :key="className">
                <div
                    class="bg-gray-100 px-6 py-2 text-[10px] font-bold text-gray-500 uppercase tracking-wider sticky top-0 z-10"
                >
                    {{ className }}
                </div>

                <div class="divide-y divide-gray-100">
                    <div
                        v-for="item in group"
                        :key="item.id"
                        class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold border border-indigo-100"
                            >
                                {{
                                    item.user.name.substring(0, 2).toUpperCase()
                                }}
                            </div>

                            <div>
                                <p class="text-sm font-bold text-gray-800">
                                    {{ item.user.name }}
                                </p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span
                                        class="text-[10px] px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-100 font-bold uppercase"
                                    >
                                        {{ item.status }}
                                    </span>
                                    <span class="text-[10px] text-gray-400"
                                        >Menunggu konfirmasi</span
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button
                                @click="updateStatus(item.id, 'approved')"
                                class="flex items-center gap-1 px-3 py-1.5 bg-green-50 text-green-700 rounded-lg border border-green-200 hover:bg-green-600 hover:text-white transition text-xs font-bold shadow-sm"
                            >
                                <CheckCircleIcon class="w-4 h-4" /> Terima
                            </button>
                            <button
                                @click="updateStatus(item.id, 'rejected')"
                                class="flex items-center gap-1 px-3 py-1.5 bg-red-50 text-red-700 rounded-lg border border-red-200 hover:bg-red-600 hover:text-white transition text-xs font-bold shadow-sm"
                            >
                                <XCircleIcon class="w-4 h-4" /> Tolak
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>
