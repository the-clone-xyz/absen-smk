<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    UserGroupIcon,
    PlusIcon,
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon,
    FunnelIcon,
} from "@heroicons/vue/24/solid";
import { ref, watch } from "vue";

const props = defineProps({
    students: Object, // Data dari controller (Pagination)
    filters: Object, // Data search
});

const search = ref(props.filters.search || "");

// Fitur Search Otomatis (Tanpa Tombol)
watch(search, (value) => {
    router.get(
        route("admin.students.index"),
        { search: value },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        }
    );
});

const deleteStudent = (student) => {
    if (
        confirm(
            `Yakin hapus siswa ${student.user.name}? Akun dan data absensinya juga akan terhapus permanen.`
        )
    ) {
        router.delete(route("admin.students.destroy", student.id));
    }
};
</script>

<template>
    <Head title="Manajemen Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-bold text-xl text-red-800 leading-tight flex items-center gap-2"
                >
                    <UserGroupIcon class="w-6 h-6" /> Data Siswa
                </h2>
                <Link
                    :href="route('admin.students.create')"
                    class="bg-red-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-red-700 text-sm font-bold shadow transition active:scale-95"
                >
                    <PlusIcon class="w-5 h-5" /> Tambah Siswa
                </Link>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-end mb-4">
                    <div class="relative w-full max-w-md">
                        <div
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                        >
                            <MagnifyingGlassIcon
                                class="h-5 w-5 text-gray-400"
                            />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-red-500 focus:border-red-500 sm:text-sm transition duration-150 ease-in-out"
                            placeholder="Cari Nama atau NIS..."
                        />
                    </div>
                </div>

                <div
                    class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Siswa
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        NIS / NISN
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Kelas
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        TTL
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr
                                    v-for="item in students.data"
                                    :key="item.id"
                                    class="hover:bg-red-50/30 transition"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10"
                                            >
                                                <img
                                                    class="h-10 w-10 rounded-full object-cover border border-gray-200"
                                                    :src="
                                                        item.user.avatar
                                                            ? '/storage/' +
                                                              item.user.avatar
                                                            : `https://ui-avatars.com/api/?name=${item.user.name}&background=random`
                                                    "
                                                    alt=""
                                                />
                                            </div>
                                            <div class="ml-4">
                                                <div
                                                    class="text-sm font-bold text-gray-900"
                                                >
                                                    {{ item.user.name }}
                                                </div>
                                                <div
                                                    class="text-xs text-gray-500"
                                                >
                                                    {{ item.user.email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm text-gray-900 font-mono font-bold"
                                        >
                                            {{ item.nis }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ item.nisn || "-" }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="item.kelas"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                                        >
                                            {{ item.kelas.name }}
                                        </span>
                                        <span
                                            v-else
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"
                                        >
                                            Belum Ada Kelas
                                        </span>
                                    </td>

                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ item.pob }}, {{ item.dob }}
                                    </td>

                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"
                                    >
                                        <div class="flex justify-center gap-3">
                                            <button
                                                class="text-blue-600 hover:text-blue-900"
                                                title="Edit"
                                            >
                                                <PencilSquareIcon
                                                    class="w-5 h-5"
                                                />
                                            </button>

                                            <button
                                                @click="deleteStudent(item)"
                                                class="text-red-600 hover:text-red-900"
                                                title="Hapus"
                                            >
                                                <TrashIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="students.data.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-6 py-10 text-center text-gray-500"
                                    >
                                        Belum ada data siswa. Silakan tambah
                                        baru.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6"
                        v-if="students.links.length > 3"
                    >
                        <div class="flex items-center justify-between">
                            <div
                                class="flex-1 flex justify-between sm:hidden"
                            ></div>
                            <div
                                class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-center"
                            >
                                <nav
                                    class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                    aria-label="Pagination"
                                >
                                    <Link
                                        v-for="(link, k) in students.links"
                                        :key="k"
                                        :href="link.url || '#'"
                                        v-html="link.label"
                                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                        :class="{
                                            'z-10 bg-red-50 border-red-500 text-red-600':
                                                link.active,
                                            'bg-white border-gray-300 text-gray-500 hover:bg-gray-50':
                                                !link.active,
                                            'cursor-not-allowed opacity-50':
                                                !link.url,
                                        }"
                                    />
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
