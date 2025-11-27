<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import {
    UserGroupIcon,
    CheckCircleIcon,
    UsersIcon,
    AcademicCapIcon,
    UserIcon,
    ArrowPathIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({
    usersData: Array,
});
// Fungsi untuk Mengubah Role (Dipanggil saat memilih dari dropdown)
const updateRole = (userId, newRole) => {
    // Pastikan admin tidak mengubah role dirinya sendiri
    const currentUser = props.usersData.find((u) => u.id === userId);
    if (currentUser.role === "admin" && newRole !== "admin") {
        alert(
            "Anda tidak bisa menurunkan peran Admin utama melalui fitur ini."
        );
        return;
    }

    if (
        confirm(
            `Yakin mengubah peran ${
                currentUser.name
            } menjadi ${newRole.toUpperCase()}?`
        )
    ) {
        router.patch(
            route("admin.users.updateRole", userId),
            {
                role: newRole,
            },
            {
                preserveScroll: true,
            }
        );
    }
};

const getRoleClass = (role) => {
    switch (role) {
        case "admin":
            return "bg-red-100 text-red-800 border-red-200";
        case "teacher":
            return "bg-blue-100 text-blue-800 border-blue-200";
        default:
            return "bg-green-100 text-green-800 border-green-200";
    }
};

const getRoleName = (role) => {
    switch (role) {
        case "admin":
            return "Administrator";
        case "teacher":
            return "Guru";
        default:
            return "Siswa";
    }
};
</script>

<template>
    <Head title="Manajemen Pengguna" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-bold text-xl text-red-800 leading-tight flex items-center gap-3"
                >
                    <UserGroupIcon class="w-6 h-6 text-red-600" />
                    Admin Panel (Manajemen User)
                </h2>
                <span class="text-xs font-bold text-gray-500"
                    >Total Pengguna: {{ usersData.length }}</span
                >
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div
                        class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-center gap-4"
                    >
                        <div class="p-3 bg-red-50 rounded-full text-red-600">
                            <UsersIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p
                                class="text-xs text-gray-500 font-bold uppercase"
                            >
                                Total Guru
                            </p>
                            <p class="text-2xl font-bold text-gray-800">
                                {{
                                    usersData.filter(
                                        (u) => u.role === "teacher"
                                    ).length
                                }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-center gap-4"
                    >
                        <div
                            class="p-3 bg-green-50 rounded-full text-green-600"
                        >
                            <AcademicCapIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p
                                class="text-xs text-gray-500 font-bold uppercase"
                            >
                                Total Siswa
                            </p>
                            <p class="text-2xl font-bold text-gray-800">
                                {{
                                    usersData.filter(
                                        (u) => u.role === "student"
                                    ).length
                                }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 flex items-center gap-4"
                    >
                        <div class="p-3 bg-blue-50 rounded-full text-blue-600">
                            <UserIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <p
                                class="text-xs text-gray-500 font-bold uppercase"
                            >
                                Super Admin
                            </p>
                            <p class="text-2xl font-bold text-gray-800">
                                {{
                                    usersData.filter((u) => u.role === "admin")
                                        .length
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white shadow-lg rounded-xl overflow-hidden border border-red-200"
                >
                    <div
                        class="px-6 py-4 border-b border-red-200 bg-red-50 flex justify-between items-center"
                    >
                        <h3 class="font-bold text-red-800">
                            Manajemen Peran Pengguna
                        </h3>
                        <button
                            class="bg-red-100 text-red-600 px-3 py-1 rounded text-xs font-bold hover:bg-red-200 flex items-center gap-1"
                        >
                            <ArrowPathIcon class="w-4 h-4" /> Refresh
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Nama
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Email / ID
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Peran Saat Ini
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Aksi Ganti Peran
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr
                                    v-for="user in usersData"
                                    :key="user.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-bold text-gray-900"
                                        >
                                            {{ user.name }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            {{ user.email }}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1">
                                            ID: {{ user.id }}
                                        </div>
                                    </td>

                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center"
                                    >
                                        <span
                                            class="px-3 py-1 text-xs font-bold rounded-full border"
                                            :class="getRoleClass(user.role)"
                                        >
                                            {{ getRoleName(user.role) }}
                                        </span>
                                    </td>

                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center"
                                    >
                                        <select
                                            :value="user.role"
                                            @change="
                                                updateRole(
                                                    user.id,
                                                    $event.target.value
                                                )
                                            "
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                                        >
                                            <option value="student">
                                                Siswa
                                            </option>
                                            <option value="teacher">
                                                Guru
                                            </option>
                                            <option value="admin">
                                                Administrator
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
