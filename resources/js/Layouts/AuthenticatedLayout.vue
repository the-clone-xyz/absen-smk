<script setup>
import { ref, watch } from "vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link, usePage } from "@inertiajs/vue3";
import {
    CheckCircleIcon,
    XCircleIcon,
    XMarkIcon,
    UserCircleIcon,
} from "@heroicons/vue/24/solid";

const showingNavigationDropdown = ref(false);
const page = usePage();
const showFlash = ref(false);

// --- LOGIKA ALERT PINTAR ---
watch(
    () => page.props.flash,
    (newFlash) => {
        if (newFlash.success || newFlash.error) {
            showFlash.value = true;
            // Otomatis sembunyikan setelah 4 detik
            setTimeout(() => {
                showFlash.value = false;
                page.props.flash.success = null;
                page.props.flash.error = null;
            }, 4000);
        }
    },
    { deep: true }
);
</script>

<template>
    <div>
        <div
            class="min-h-screen bg-gray-100 font-sans text-gray-900 antialiased"
        >
            <nav
                class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <img
                                        src="/logo_tamsis.png"
                                        class="block h-10 w-auto hover:scale-110 transition duration-300 drop-shadow-sm"
                                        alt="Logo Sekolah"
                                    />
                                </Link>
                            </div>

                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <template
                                    v-if="
                                        $page.props.auth.user.role === 'student'
                                    "
                                >
                                    <NavLink
                                        :href="route('dashboard')"
                                        :active="route().current('dashboard')"
                                    >
                                        Dashboard
                                    </NavLink>
                                    <NavLink
                                        :href="route('attendance.rekap')"
                                        :active="
                                            route().current('attendance.rekap')
                                        "
                                    >
                                        Riwayat
                                    </NavLink>
                                    <NavLink
                                        :href="route('student.card')"
                                        :active="
                                            route().current('student.card')
                                        "
                                    >
                                        Kartu Pelajar
                                    </NavLink>
                                </template>

                                <template
                                    v-if="
                                        $page.props.auth.user.role === 'teacher'
                                    "
                                >
                                    <NavLink
                                        :href="route('teacher.dashboard')"
                                        :active="
                                            route().current('teacher.dashboard')
                                        "
                                    >
                                        Panel Guru
                                    </NavLink>

                                    <NavLink
                                        :href="route('teacher.approval.index')"
                                        :active="
                                            route().current(
                                                'teacher.approval.index'
                                            )
                                        "
                                    >
                                        Persetujuan Izin
                                    </NavLink>
                                </template>

                                <template
                                    v-if="
                                        $page.props.auth.user.role === 'admin'
                                    "
                                >
                                    <NavLink
                                        :href="route('admin.dashboard')"
                                        :active="
                                            route().current('admin.dashboard')
                                        "
                                    >
                                        Admin Panel
                                    </NavLink>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-bold rounded-md text-gray-600 bg-white hover:text-gray-900 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                <UserCircleIcon
                                                    class="w-5 h-5 mr-2 text-green-600"
                                                />
                                                {{ $page.props.auth.user.name }}
                                                <svg
                                                    class="ms-2 -me-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden border-t border-gray-200 bg-gray-50"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <template
                            v-if="$page.props.auth.user.role === 'student'"
                        >
                            <ResponsiveNavLink
                                :href="route('dashboard')"
                                :active="route().current('dashboard')"
                            >
                                Dashboard
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('attendance.rekap')"
                                :active="route().current('attendance.rekap')"
                            >
                                Riwayat
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('student.card')"
                                :active="route().current('student.card')"
                            >
                                Kartu Pelajar
                            </ResponsiveNavLink>
                        </template>

                        <template
                            v-if="$page.props.auth.user.role === 'teacher'"
                        >
                            <ResponsiveNavLink
                                :href="route('teacher.dashboard')"
                                :active="route().current('teacher.dashboard')"
                            >
                                Panel Guru
                            </ResponsiveNavLink>
                        </template>

                        <template v-if="$page.props.auth.user.role === 'admin'">
                            <ResponsiveNavLink
                                :href="route('admin.dashboard')"
                                :active="route().current('admin.dashboard')"
                            >
                                Admin Panel
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="font-medium text-sm text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header
                class="bg-white shadow-sm sticky top-16 z-40"
                v-if="$slots.header"
            >
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main class="relative">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                    <Transition
                        enter-active-class="transform ease-out duration-300 transition"
                        enter-from-class="translate-y-2 opacity-0 scale-95"
                        enter-to-class="translate-y-0 opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-100"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="$page.props.flash.success && showFlash"
                            class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-md shadow-md flex justify-between items-start"
                        >
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <CheckCircleIcon
                                        class="h-6 w-6 text-green-500"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div class="ml-3">
                                    <h3
                                        class="text-sm font-bold text-green-800"
                                    >
                                        Berhasil!
                                    </h3>
                                    <div class="mt-1 text-sm text-green-700">
                                        {{ $page.props.flash.success }}
                                    </div>
                                </div>
                            </div>
                            <button
                                @click="showFlash = false"
                                class="text-green-400 hover:text-green-600 transition"
                            >
                                <XMarkIcon class="w-5 h-5" />
                            </button>
                        </div>

                        <div
                            v-else-if="$page.props.flash.error && showFlash"
                            class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md shadow-md flex justify-between items-start"
                        >
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <XCircleIcon
                                        class="h-6 w-6 text-red-500"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-bold text-red-800">
                                        Terjadi Kesalahan
                                    </h3>
                                    <div class="mt-1 text-sm text-red-700">
                                        {{ $page.props.flash.error }}
                                    </div>
                                </div>
                            </div>
                            <button
                                @click="showFlash = false"
                                class="text-red-400 hover:text-red-600 transition"
                            >
                                <XMarkIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </Transition>
                </div>

                <slot />
            </main>
        </div>
    </div>
</template>

<style>
.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
