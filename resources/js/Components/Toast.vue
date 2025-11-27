<script setup>
import { onMounted, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import {
    CheckCircleIcon,
    XCircleIcon,
    XMarkIcon,
} from "@heroicons/vue/24/solid";

const page = usePage();
const show = ref(false);
const message = ref("");
const type = ref("success"); // success atau error

// Fungsi Reset Pesan (Biar hilang otomatis)
const hide = () => {
    show.value = false;
};

// Pantau Pesan dari Laravel
watch(
    () => page.props.flash,
    (flash) => {
        if (flash.success) {
            type.value = "success";
            message.value = flash.success;
            show.value = true;
        } else if (flash.error) {
            type.value = "error";
            message.value = flash.error;
            show.value = true;
        }

        // Hilang otomatis setelah 3 detik
        if (show.value) {
            setTimeout(() => {
                show.value = false;
            }, 3000);
        }
    },
    { deep: true }
);
</script>

<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="fixed top-5 right-5 z-[9999] w-full max-w-sm overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5"
            :class="
                type === 'success'
                    ? 'bg-white border-l-4 border-green-500'
                    : 'bg-white border-l-4 border-red-500'
            "
        >
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <CheckCircleIcon
                            v-if="type === 'success'"
                            class="h-6 w-6 text-green-400"
                        />
                        <XCircleIcon v-else class="h-6 w-6 text-red-400" />
                    </div>

                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900">
                            {{ type === "success" ? "Berhasil!" : "Gagal!" }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ message }}
                        </p>
                    </div>

                    <div class="ml-4 flex flex-shrink-0">
                        <button
                            @click="hide"
                            type="button"
                            class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none"
                        >
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
