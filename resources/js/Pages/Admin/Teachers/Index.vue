<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    UserGroupIcon,
    PlusIcon,
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon,
    AcademicCapIcon,
} from "@heroicons/vue/24/solid";
import { ref, watch } from "vue";

const props = defineProps({
    teachers: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");

watch(search, (value) => {
    router.get(
        route("admin.teachers.index"),
        { search: value },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        }
    );
});

const deleteTeacher = (item) => {
    if (confirm(`Yakin hapus Guru ${item.user.name}?`)) {
        router.delete(route("admin.teachers.destroy", item.id));
    }
};
</script>

<template>
    <Head title="Manajemen Guru" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-bold text-xl text-red-800 leading-tight flex items-center gap-2"
                >
                    <AcademicCapIcon class="w-6 h-6" /> Data Guru
                </h2>
                <Link
                    :href="route('admin.teachers.create')"
                    class="bg-red-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-red-700 text-sm font-bold shadow transition active:scale-95"
                >
                    <PlusIcon class="w-5 h-5" /> Tambah Guru
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
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 sm:text-sm"
                            placeholder="Cari Nama / NIP..."
                        />
                    </div>
                </div>

                <div
                    class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200"
                >
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                >
                                    Nama Guru
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                >
                                    NIP
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase"
                                >
                                    Kontak
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr
                                v-for="item in teachers.data"
                                :key="item.id"
                                class="hover:bg-red-50/30"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <img
                                            class="h-10 w-10 rounded-full object-cover border border-gray-200"
                                            :src="
                                                item.user.avatar
                                                    ? '/storage/' +
                                                      item.user.avatar
                                                    : `https://ui-avatars.com/api/?name=${item.user.name}&background=random`
                                            "
                                        />
                                        <div class="ml-4">
                                            <div
                                                class="text-sm font-bold text-gray-900"
                                            >
                                                {{ item.user.name }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ item.user.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 font-mono text-sm text-gray-700"
                                >
                                    {{ item.nip }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ item.phone }} <br />
                                    <span class="text-xs text-gray-400">{{
                                        item.address || "-"
                                    }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button
                                        @click="deleteTeacher(item)"
                                        class="text-red-600 hover:text-red-800"
                                    >
                                        <TrashIcon class="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="teachers.data.length === 0">
                                <td
                                    colspan="4"
                                    class="px-6 py-8 text-center text-gray-500"
                                >
                                    Belum ada data guru.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6"
                    v-if="teachers.links.length > 3"
                >
                    <div class="flex justify-end gap-1">
                        <Link
                            v-for="(link, k) in teachers.links"
                            :key="k"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="px-3 py-1 border rounded text-sm"
                            :class="
                                link.active
                                    ? 'bg-red-600 text-white'
                                    : 'bg-white text-gray-500 hover:bg-gray-100'
                            "
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
