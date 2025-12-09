<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import {
    ClipboardDocumentListIcon,
    ChevronDownIcon,
    DocumentMagnifyingGlassIcon,
    PlusIcon,
    PencilSquareIcon,
    TrashIcon,
    ClockIcon,
    UserIcon,
} from "@heroicons/vue/24/solid";

const props = defineProps({ tasks: Array });
const emit = defineEmits(["create", "edit", "grade"]);
const openTask = ref(null);

const toggleTask = (id) => (openTask.value = openTask.value === id ? null : id);

const deleteTask = (id) => {
    if (confirm("Hapus tugas ini? Data nilai siswa juga akan terhapus.")) {
        router.delete(route("tasks.destroy", id), { preserveScroll: true });
    }
};
</script>

<template>
    <div
        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-full"
    >
        <div
            class="px-5 py-4 border-b border-gray-200 bg-purple-50 flex justify-between items-center sticky top-0 z-10"
        >
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <ClipboardDocumentListIcon class="w-5 h-5 text-purple-600" />
                Tugas Kelas
            </h3>
            <button
                @click="$emit('create')"
                class="bg-purple-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-purple-700 flex items-center gap-1 shadow-sm transition"
            >
                <PlusIcon class="w-3 h-3" /> Buat Tugas
            </button>
        </div>

        <div
            class="divide-y divide-gray-100 overflow-y-auto flex-grow custom-scrollbar"
        >
            <div
                v-if="tasks.length === 0"
                class="p-8 text-center flex flex-col items-center justify-center text-gray-400"
            >
                <ClipboardDocumentListIcon class="w-12 h-12 mb-2 opacity-20" />
                <p class="text-sm italic">Belum ada tugas.</p>
            </div>

            <div v-for="task in tasks" :key="task.id" class="group">
                <div
                    class="px-5 py-3 hover:bg-purple-50/50 transition cursor-pointer"
                    @click="toggleTask(task.id)"
                >
                    <div class="flex justify-between items-start">
                        <div>
                            <h4
                                class="font-bold text-gray-800 text-sm line-clamp-1"
                            >
                                {{ task.title }}
                            </h4>
                            <div class="flex items-center gap-2 mt-1">
                                <span
                                    class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded border flex items-center gap-1"
                                >
                                    <ClockIcon class="w-3 h-3" />
                                    {{
                                        new Date(task.deadline).toLocaleString(
                                            "id-ID",
                                            {
                                                day: "numeric",
                                                month: "short",
                                                hour: "2-digit",
                                                minute: "2-digit",
                                            }
                                        )
                                    }}
                                </span>
                                <span
                                    class="text-[10px] font-bold text-purple-600 bg-purple-100 px-2 py-0.5 rounded"
                                >
                                    {{ task.submissions.length }} Dikumpul
                                </span>
                            </div>
                        </div>
                        <ChevronDownIcon
                            class="w-4 h-4 text-gray-400 transition-transform"
                            :class="{ 'rotate-180': openTask === task.id }"
                        />
                    </div>
                </div>

                <div
                    v-if="openTask === task.id"
                    class="bg-gray-50/80 border-t border-b border-gray-100 p-4 shadow-inner"
                >
                    <div class="flex gap-2 mb-3 pb-3 border-b border-gray-200">
                        <button
                            @click="$emit('edit', task)"
                            class="text-[10px] bg-white border border-gray-200 px-3 py-1.5 rounded hover:bg-blue-50 hover:text-blue-600 transition font-bold flex items-center gap-1"
                        >
                            <PencilSquareIcon class="w-3 h-3" /> Edit Soal
                        </button>
                        <button
                            @click="deleteTask(task.id)"
                            class="text-[10px] bg-white border border-gray-200 px-3 py-1.5 rounded hover:bg-red-50 hover:text-red-600 transition font-bold flex items-center gap-1"
                        >
                            <TrashIcon class="w-3 h-3" /> Hapus
                        </button>
                    </div>

                    <p
                        class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2"
                    >
                        Hasil Pekerjaan Siswa
                    </p>
                    <div
                        class="space-y-2 max-h-[200px] overflow-y-auto pr-1 custom-scrollbar"
                    >
                        <div
                            v-if="task.submissions.length === 0"
                            class="text-xs text-gray-400 italic"
                        >
                            Belum ada siswa yang mengumpulkan.
                        </div>

                        <div
                            v-for="sub in task.submissions"
                            :key="sub.id"
                            class="flex justify-between items-center bg-white p-2 rounded border border-gray-200 hover:border-purple-300 transition"
                        >
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-7 h-7 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-[10px] font-bold border border-purple-200"
                                >
                                    {{
                                        sub.student.name
                                            .substring(0, 2)
                                            .toUpperCase()
                                    }}
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-bold text-gray-700 truncate max-w-[120px]"
                                    >
                                        {{ sub.student.name }}
                                    </p>
                                    <p
                                        class="text-[10px]"
                                        :class="
                                            sub.score
                                                ? 'text-green-600 font-bold'
                                                : 'text-orange-500'
                                        "
                                    >
                                        {{
                                            sub.score
                                                ? `Nilai: ${sub.score}`
                                                : "Menunggu Dinilai"
                                        }}
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="
                                    $emit('grade', { task, submission: sub })
                                "
                                class="p-1.5 bg-purple-50 text-purple-600 rounded hover:bg-purple-600 hover:text-white border border-purple-200 transition"
                                title="Periksa"
                            >
                                <DocumentMagnifyingGlassIcon class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
