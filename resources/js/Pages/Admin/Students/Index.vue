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
    MapPinIcon,
    CalendarDaysIcon,
} from "@heroicons/vue/24/solid";
import { ref, watch } from "vue";
import { useSwal } from "@/Composables/useSwal";

const props = defineProps({
    students: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const Swal = useSwal();

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
    Swal.confirmDelete(student.user.name, () => {
        router.delete(route("admin.students.destroy", student.id), {
            preserveScroll: true,
            onStart: () => {},
            onFinish: () => {},
        });
    });
};
</script>

<template>
    <Head title="Manajemen Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <div
                class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-6"
            >
                <div>
                    <h2
                        class="font-bold text-3xl text-gray-800 leading-tight flex items-center gap-3"
                    >
                        <div
                            class="bg-gradient-to-br from-blue-500 to-indigo-600 p-2.5 rounded-xl text-white shadow-lg shadow-blue-500/30"
                        >
                            <UserGroupIcon class="w-7 h-7" />
                        </div>
                        <span>Data Siswa</span>
                    </h2>
                    <p class="text-gray-500 mt-1.5 text-sm ml-1">
                        Kelola data siswa, kelas, dan informasi akademik.
                    </p>
                </div>

                <Link
                    :href="route('admin.students.create')"
                    class="bg-blue-600 text-white px-5 py-2.5 rounded-xl flex items-center justify-center gap-2 hover:bg-blue-700 text-sm font-bold shadow-lg shadow-blue-600/20 transition-all active:scale-95 group"
                >
                    <PlusIcon
                        class="w-5 h-5 group-hover:scale-110 transition-transform"
                    />
                    <span>Tambah Siswa</span>
                </Link>
            </div>
        </template>

        <div class="py-10 px-4 sm:px-6 lg:px-8 bg-gray-50/50 min-h-screen">
            <div class="max-w-[90rem] mx-auto space-y-8">
                <div
                    class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-white p-5 rounded-3xl shadow-sm border border-gray-100"
                >
                    <div
                        class="text-sm font-medium text-gray-500 hidden sm:block pl-2"
                    >
                        Total Siswa:
                        <span class="text-gray-900 font-bold text-lg">{{
                            students.total
                        }}</span>
                    </div>
                    <div class="relative w-full max-w-md group">
                        <div
                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
                        >
                            <MagnifyingGlassIcon
                                class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors"
                            />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            class="block w-full pl-11 pr-4 py-2.5 border border-gray-200 rounded-xl leading-5 bg-gray-50/50 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-sm transition-all duration-200"
                            placeholder="Cari Nama, NIS, atau NISN..."
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:hidden">
                    <div
                        v-for="item in students.data"
                        :key="item.id"
                        class="bg-white p-6 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 flex flex-col gap-6 relative overflow-hidden group"
                    >
                        <div
                            class="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-blue-500 to-indigo-500"
                        ></div>

                        <div
                            class="flex items-center gap-5 pb-4 border-b border-gray-50"
                        >
                            <img
                                class="h-16 w-16 rounded-full object-cover border-[3px] border-gray-100 shadow-sm"
                                :src="
                                    item.user.avatar
                                        ? '/storage/' + item.user.avatar
                                        : `https://ui-avatars.com/api/?name=${item.user.name}&background=dde7f7&color=2563eb`
                                "
                                alt=""
                            />
                            <div>
                                <h3 class="font-bold text-xl text-gray-900">
                                    {{ item.user.name }}
                                </h3>
                                <p class="text-sm text-gray-500 font-medium">
                                    {{ item.user.email }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-y-4 text-sm">
                            <div>
                                <p
                                    class="text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"
                                >
                                    Identitas
                                </p>
                                <p
                                    class="font-mono text-gray-800 font-bold text-base"
                                >
                                    {{ item.nis }}
                                </p>
                                <p class="text-sm text-gray-400">
                                    {{ item.nisn || "-" }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"
                                >
                                    Kelas
                                </p>
                                <span
                                    v-if="item.kelas"
                                    class="px-3 py-1 inline-flex text-xs font-bold rounded-lg bg-blue-50 text-blue-700 border border-blue-100"
                                >
                                    {{ item.kelas.name }}
                                </span>
                                <span
                                    v-else
                                    class="text-gray-400 italic text-sm"
                                    >Belum diatur</span
                                >
                            </div>
                        </div>

                        <div class="flex gap-4 pt-2">
                            <Link
                                :href="route('admin.students.edit', item.id)"
                                class="flex-1 bg-white border border-gray-200 text-gray-700 py-2.5 rounded-xl text-sm font-bold hover:bg-blue-50 hover:border-blue-200 hover:text-blue-700 transition flex justify-center items-center gap-2 shadow-sm"
                            >
                                <PencilSquareIcon class="w-5 h-5" /> Edit
                            </Link>

                            <button
                                @click="deleteStudent(item)"
                                class="flex-1 bg-white border border-gray-200 text-red-500 py-2.5 rounded-xl text-sm font-bold hover:bg-red-50 hover:border-red-200 transition flex justify-center items-center gap-2 shadow-sm"
                            >
                                <TrashIcon class="w-5 h-5" /> Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    class="hidden sm:block bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-3xl overflow-hidden border border-gray-100"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/40">
                                <tr>
                                    <th
                                        class="px-8 py-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Siswa
                                    </th>
                                    <th
                                        class="px-6 py-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Identitas (NIS)
                                    </th>
                                    <th
                                        class="px-6 py-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Kelas
                                    </th>
                                    <th
                                        class="px-6 py-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        TTL
                                    </th>
                                    <th
                                        class="px-8 py-5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr
                                    v-for="item in students.data"
                                    :key="item.id"
                                    class="hover:bg-blue-50/30 transition-colors duration-200 group"
                                >
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-12 w-12"
                                            >
                                                <img
                                                    class="h-12 w-12 rounded-full object-cover border-[3px] border-white shadow-md group-hover:border-blue-200 transition-colors"
                                                    :src="
                                                        item.user.avatar
                                                            ? '/storage/' +
                                                              item.user.avatar
                                                            : `https://ui-avatars.com/api/?name=${item.user.name}&background=dde7f7&color=2563eb`
                                                    "
                                                    alt=""
                                                />
                                            </div>
                                            <div class="ml-4">
                                                <div
                                                    class="text-base font-bold text-gray-900 group-hover:text-blue-600 transition-colors"
                                                >
                                                    {{ item.user.name }}
                                                </div>
                                                <div
                                                    class="text-xs font-medium text-gray-500"
                                                >
                                                    {{ item.user.email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <div class="flex flex-col items-start">
                                            <span
                                                class="text-sm font-mono font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded-md"
                                                >{{ item.nis }}</span
                                            >
                                            <span
                                                class="text-xs text-gray-400 mt-1 pl-1"
                                                >{{ item.nisn || "-" }}</span
                                            >
                                        </div>
                                    </td>

                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <span
                                            v-if="item.kelas"
                                            class="px-3 py-1 inline-flex text-xs font-bold rounded-lg bg-blue-50 text-blue-700 border border-blue-100"
                                        >
                                            {{ item.kelas.name }}
                                        </span>
                                        <span
                                            v-else
                                            class="text-xs text-gray-400 italic flex items-center gap-1"
                                        >
                                            <AcademicCapIcon class="w-4 h-4" />
                                            Belum diatur
                                        </span>
                                    </td>

                                    <td
                                        class="px-6 py-5 whitespace-nowrap text-sm text-gray-600"
                                    >
                                        <div
                                            class="flex flex-col items-start gap-1"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <MapPinIcon
                                                    class="w-3.5 h-3.5 text-gray-400"
                                                />
                                                <span
                                                    class="font-medium text-gray-700"
                                                    >{{ item.pob }}</span
                                                >
                                            </div>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <CalendarDaysIcon
                                                    class="w-3.5 h-3.5 text-gray-400"
                                                />
                                                <span
                                                    class="text-xs text-gray-500"
                                                    >{{ item.dob }}</span
                                                >
                                            </div>
                                        </div>
                                    </td>

                                    <td
                                        class="px-8 py-5 whitespace-nowrap text-center text-sm font-medium"
                                    >
                                        <div
                                            class="flex justify-center items-center gap-2 opacity-80 group-hover:opacity-100 transition-opacity"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'admin.students.edit',
                                                        item.id
                                                    )
                                                "
                                                class="p-2 bg-white border border-gray-200 text-blue-600 rounded-xl hover:bg-blue-50 hover:border-blue-300 transition-all shadow-sm"
                                                title="Edit"
                                            >
                                                <PencilSquareIcon
                                                    class="w-5 h-5"
                                                />
                                            </Link>

                                            <button
                                                @click="deleteStudent(item)"
                                                class="p-2 bg-white border border-gray-200 text-red-500 rounded-xl hover:bg-red-50 hover:border-red-300 transition-all shadow-sm"
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
                                        class="px-8 py-24 text-center text-gray-500 bg-gray-50/30"
                                    >
                                        <div
                                            class="flex flex-col items-center justify-center gap-4"
                                        >
                                            <div
                                                class="bg-gray-100 p-5 rounded-full"
                                            >
                                                <UserGroupIcon
                                                    class="w-12 h-12 text-gray-300"
                                                />
                                            </div>
                                            <p
                                                class="font-bold text-xl text-gray-700"
                                            >
                                                Tidak ada data ditemukan
                                            </p>
                                            <p
                                                class="text-base text-gray-400 max-w-md"
                                            >
                                                Coba ubah kata kunci pencarian
                                                atau tambahkan siswa baru.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="bg-white px-8 py-5 border-t border-gray-100"
                        v-if="students.links.length > 3"
                    >
                        <div
                            class="flex flex-col sm:flex-row items-center justify-between gap-6"
                        >
                            <div class="text-sm text-gray-500 font-medium">
                                Menampilkan
                                <span class="font-bold text-gray-900"
                                    >{{ students.from }}-{{ students.to }}</span
                                >
                                dari
                                <span class="font-bold text-gray-900">{{
                                    students.total
                                }}</span>
                                siswa
                            </div>
                            <nav
                                class="relative z-0 inline-flex rounded-xl shadow-sm -space-x-px"
                                aria-label="Pagination"
                            >
                                <Link
                                    v-for="(link, k) in students.links"
                                    :key="k"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-bold transition-all"
                                    :class="{
                                        'z-10 bg-blue-600 border-blue-600 text-white hover:bg-blue-700':
                                            link.active,
                                        'bg-white border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700':
                                            !link.active,
                                        'cursor-not-allowed opacity-50':
                                            !link.url,
                                        'rounded-l-xl': k === 0,
                                        'rounded-r-xl':
                                            k === students.links.length - 1,
                                    }"
                                />
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
