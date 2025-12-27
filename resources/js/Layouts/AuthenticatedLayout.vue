<script setup>
import { ref, watch, computed } from "vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link, usePage } from "@inertiajs/vue3";
import { useSwal } from "@/Composables/useSwal"; // Pastikan composable ini ada
import Footer from "@/Components/Footer.vue";
import { UserCircleIcon } from "@heroicons/vue/24/solid";

// Inisialisasi SweetAlert dari Composable Bapak
const Swal = useSwal();
const showingNavigationDropdown = ref(false);
const page = usePage();

const user = computed(() => page.props.auth.user);
const role = computed(() => user.value?.role);

// --- PERBAIKAN: Watcher Hanya Untuk Toast ---
watch(
    () => page.props.flash,
    (newFlash) => {
        // Hapus logika showFlash (Banner)
        // Cukup panggil Toast saja
        if (newFlash.success) {
            Swal.toast("success", newFlash.success);
        }
        if (newFlash.error) {
            Swal.toast("error", newFlash.error);
        }
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <div>
        <div
            class="min-h-screen bg-gray-50 flex flex-col font-sans text-gray-900 antialiased"
        >
            <nav
                class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50 flex-none"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <img
                                        src="/logo_tamsis.png"
                                        class="block h-10 w-auto hover:scale-110 transition duration-300 drop-shadow-sm"
                                        alt="Logo"
                                        @error="
                                            $event.target.src =
                                                'https://ui-avatars.com/api/?name=SMK&background=0D8ABC&color=fff'
                                        "
                                    />
                                </Link>
                            </div>

                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <template v-if="role === 'student'">
                                    <NavLink
                                        :href="route('student.dashboard')"
                                        :active="
                                            route().current('student.dashboard')
                                        "
                                        >Dashboard</NavLink
                                    >
                                    <NavLink
                                        :href="
                                            route('student.attendance.rekap')
                                        "
                                        :active="
                                            route().current(
                                                'student.attendance.rekap'
                                            )
                                        "
                                        >Riwayat Absen</NavLink
                                    >
                                    <NavLink
                                        :href="route('student.card')"
                                        :active="
                                            route().current('student.card')
                                        "
                                        >Kartu Pelajar</NavLink
                                    >
                                    <NavLink
                                        :href="route('student.attendance.izin')"
                                        :active="
                                            route().current(
                                                'student.attendance.izin'
                                            )
                                        "
                                        >Izin</NavLink
                                    >
                                </template>
                                <template v-if="role === 'teacher'">
                                    <NavLink
                                        :href="route('teacher.dashboard')"
                                        :active="
                                            route().current('teacher.dashboard')
                                        "
                                        >Panel Guru</NavLink
                                    >
                                    <NavLink
                                        :href="route('teacher.approval.index')"
                                        :active="
                                            route().current(
                                                'teacher.approval.index'
                                            )
                                        "
                                        >Persetujuan Izin</NavLink
                                    >
                                </template>
                                <template v-if="role === 'admin'">
                                    <NavLink
                                        :href="route('admin.dashboard')"
                                        :active="
                                            route().current('admin.dashboard')
                                        "
                                        >Dashboard</NavLink
                                    >
                                    <NavLink
                                        :href="route('admin.students.index')"
                                        :active="
                                            route().current('admin.students.*')
                                        "
                                        >Data Siswa</NavLink
                                    >
                                    <NavLink
                                        :href="route('admin.users.index')"
                                        :active="
                                            route().current('admin.users.index')
                                        "
                                        >Data User</NavLink
                                    >
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
                                                {{ user?.name }}
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
                                            >Profile</DropdownLink
                                        >
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                            >Log Out</DropdownLink
                                        >
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
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out"
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
                        <template v-if="role === 'student'">
                            <ResponsiveNavLink
                                :href="route('student.dashboard')"
                                :active="route().current('student.dashboard')"
                                >Dashboard</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                :href="route('student.attendance.rekap')"
                                :active="
                                    route().current('student.attendance.rekap')
                                "
                                >Riwayat</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                :href="route('student.card')"
                                :active="route().current('student.card')"
                                >Kartu Pelajar</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                :href="route('student.attendance.izin')"
                                :active="
                                    route().current('student.attendance.izin')
                                "
                                >Izin</ResponsiveNavLink
                            >
                        </template>
                        <template v-if="role === 'teacher'">
                            <ResponsiveNavLink
                                :href="route('teacher.dashboard')"
                                :active="route().current('teacher.dashboard')"
                                >Panel Guru</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                :href="route('teacher.approval.index')"
                                :active="
                                    route().current('teacher.approval.index')
                                "
                                >Persetujuan Izin</ResponsiveNavLink
                            >
                        </template>
                        <template v-if="role === 'admin'">
                            <ResponsiveNavLink
                                :href="route('admin.dashboard')"
                                :active="route().current('admin.dashboard')"
                                >Dashboard</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                :href="route('admin.students.index')"
                                :active="route().current('admin.students.*')"
                                >Data Siswa</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                :href="route('admin.users.index')"
                                :active="route().current('admin.users.index')"
                                >Data User</ResponsiveNavLink
                            >
                        </template>
                    </div>
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">
                                {{ user?.name }}
                            </div>
                            <div class="font-medium text-sm text-gray-500">
                                {{ user?.email }}
                            </div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')"
                                >Profile</ResponsiveNavLink
                            >
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                                >Log Out</ResponsiveNavLink
                            >
                        </div>
                    </div>
                </div>
            </nav>

            <header
                class="bg-white shadow-sm sticky top-16 z-40 flex-none"
                v-if="$slots.header"
            >
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main class="relative flex-1 bg-gray-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 pb-12">
                    <slot />
                </div>
            </main>
            <Footer />
        </div>
    </div>
</template>
