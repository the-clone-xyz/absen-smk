<script setup>
import { router } from "@inertiajs/vue3";
import {
    ExclamationCircleIcon,
    CheckBadgeIcon,
    CheckCircleIcon,
    XCircleIcon,
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
    <div
        class="bg-white rounded-3xl shadow-sm border border-gray-200 flex-grow flex flex-col overflow-hidden"
    >
        <div
            class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center"
        >
            <h3 class="font-bold text-gray-700 flex items-center gap-2">
                <ExclamationCircleIcon class="w-5 h-5 text-yellow-500" /> Izin
                Tertunda
            </h3>
            <span
                class="text-xs font-bold bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full"
            >
                {{
                    absensiGrouped
                        ? Object.values(absensiGrouped).flat().length
                        : 0
                }}
            </span>
        </div>

        <div class="overflow-y-auto flex-grow custom-scrollbar p-0">
            <div
                v-if="
                    !absensiGrouped || Object.keys(absensiGrouped).length === 0
                "
                class="h-full flex flex-col items-center justify-center text-gray-400 text-sm"
            >
                <CheckBadgeIcon
                    class="w-12 h-12 mb-2 opacity-20 text-green-500"
                />
                Aman! Tidak ada permintaan izin.
            </div>

            <div
                v-else
                v-for="(group, className) in absensiGrouped"
                :key="className"
            >
                <div
                    class="bg-gray-100 px-6 py-1.5 text-[10px] font-bold text-gray-500 uppercase tracking-wider sticky top-0"
                >
                    {{ className }}
                </div>
                <div class="divide-y divide-gray-50">
                    <div
                        v-for="item in group"
                        :key="item.id"
                        class="px-6 py-3 flex items-center justify-between hover:bg-gray-50 transition"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold"
                            >
                                {{
                                    item.user.name.substring(0, 2).toUpperCase()
                                }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">
                                    {{ item.user.name }}
                                </p>
                                <span
                                    class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-yellow-100 text-yellow-700"
                                >
                                    {{ item.status }}
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-1">
                            <button
                                @click="updateStatus(item.id, 'approved')"
                                class="p-1.5 bg-green-100 text-green-600 rounded-lg hover:bg-green-200"
                                title="Terima"
                            >
                                <CheckCircleIcon class="w-5 h-5" />
                            </button>
                            <button
                                @click="updateStatus(item.id, 'rejected')"
                                class="p-1.5 bg-red-100 text-red-600 rounded-lg hover:bg-red-200"
                                title="Tolak"
                            >
                                <XCircleIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
