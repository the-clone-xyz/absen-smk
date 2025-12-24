<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    UserIcon,
    AcademicCapIcon,
    IdentificationIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
    CameraIcon,
    EnvelopeIcon,
    PhoneIcon,
    MapPinIcon,
    CalendarDaysIcon,
} from "@heroicons/vue/24/solid";
import { ref } from "vue";

const props = defineProps({
    student: Object,
    classes: Array,
});

const photoPreview = ref(null);
const photoInput = ref(null);

const form = useForm({
    _method: "PUT",
    name: props.student.user.name,
    email: props.student.user.email,
    nis: props.student.nis,
    nisn: props.student.nisn,
    class_id: props.student.class_id,
    gender: props.student.gender || "", // Pastikan default string kosong jika null
    pob: props.student.pob,
    dob: props.student.dob,
    phone: props.student.phone,
    address: props.student.address,
    photo: null,
});

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.photo = file;
        photoPreview.value = URL.createObjectURL(file);
    }
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const submit = () => {
    form.post(route("admin.students.update", props.student.id), {
        onSuccess: () => {
            photoPreview.value = null;
            if (photoInput.value) photoInput.value.value = null;
        }
    });
};
</script>

<template>
    <Head title="Edit Profil Siswa" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.students.index')"
                    class="p-2.5 bg-white rounded-xl shadow-sm border border-gray-200 hover:bg-gray-50 transition text-gray-500"
                >
                    <ArrowLeftIcon class="w-5 h-5" />
                </Link>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        Edit Profil Siswa
                    </h2>
                    <p class="text-sm text-gray-500">Perbarui informasi data diri dan akademik siswa.</p>
                </div>
            </div>
        </template>

        <div class="py-10 px-4 sm:px-6 lg:px-8 bg-gray-50/50 min-h-screen">
            <div class="max-w-6xl mx-auto">
                
                <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 flex flex-col items-center text-center relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                            
                            <div class="relative mt-4 group cursor-pointer" @click="selectNewPhoto">
                                <div class="w-40 h-40 rounded-full overflow-hidden border-[6px] border-white shadow-xl bg-gray-200 relative z-10">
                                    <img
                                        v-if="photoPreview"
                                        :src="photoPreview"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                    />
                                    <img
                                        v-else-if="student.user.avatar"
                                        :src="'/storage/' + student.user.avatar"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400">
                                        <UserIcon class="w-20 h-20" />
                                    </div>
                                </div>
                                
                                <div class="absolute inset-0 z-20 rounded-full flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-all duration-300 backdrop-blur-sm">
                                    <CameraIcon class="w-8 h-8 text-white drop-shadow-md" />
                                </div>
                                <div class="absolute bottom-2 right-2 z-30 bg-white p-2 rounded-full shadow-md text-gray-700 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                                    <CameraIcon class="w-5 h-5" />
                                </div>
                            </div>

                            <div class="mt-4 w-full">
                                <h3 class="text-xl font-bold text-gray-800">{{ student.user.name }}</h3>
                                <p class="text-sm text-gray-500 font-mono">{{ student.nis }}</p>
                                
                                <div class="mt-4 flex justify-center gap-2">
                                    <span v-if="student.kelas" class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold uppercase tracking-wide">
                                        {{ student.kelas.name }}
                                    </span>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold uppercase tracking-wide">
                                        Siswa Aktif
                                    </span>
                                </div>
                            </div>

                            <input ref="photoInput" type="file" class="hidden" accept="image/*" @change="handleFileUpload" />
                        </div>

                        <div class="hidden lg:block bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                            <h4 class="font-bold text-gray-800 mb-4">Aksi</h4>
                            <div class="flex flex-col gap-3">
                                <Link :href="route('admin.students.index')" class="w-full py-3 px-4 rounded-xl border border-gray-300 text-gray-600 font-bold hover:bg-gray-50 text-center transition">
                                    Batal & Kembali
                                </Link>
                                <button type="submit" :disabled="form.processing" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold hover:shadow-lg hover:shadow-blue-500/30 transition transform active:scale-95 flex justify-center items-center gap-2">
                                    <CheckCircleIcon class="w-5 h-5" />
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2 space-y-8">
                        
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
                            <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
                                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                                    <UserIcon class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Informasi Akun</h3>
                                    <p class="text-sm text-gray-500">Detail dasar untuk login dan identifikasi sistem.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap</label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        class="w-full py-3 px-4 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-gray-800"
                                        placeholder="Nama Siswa"
                                    />
                                    <p class="text-red-500 text-xs mt-1" v-if="form.errors.name">{{ form.errors.name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Email (Login)</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <EnvelopeIcon class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <input
                                            v-model="form.email"
                                            type="email"
                                            class="w-full py-3 pl-10 pr-4 rounded-xl border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed"
                                            disabled
                                        />
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1 ml-1">Email dikunci untuk keamanan sistem.</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
                            <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
                                <div class="p-3 bg-green-50 text-green-600 rounded-xl">
                                    <AcademicCapIcon class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Data Akademik</h3>
                                    <p class="text-sm text-gray-500">Nomor induk dan penempatan kelas siswa.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">NIS (Wajib)</label>
                                    <input
                                        v-model="form.nis"
                                        type="text"
                                        class="w-full py-3 px-4 rounded-xl border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                                    />
                                    <p class="text-red-500 text-xs mt-1" v-if="form.errors.nis">{{ form.errors.nis }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">NISN</label>
                                    <input
                                        v-model="form.nisn"
                                        type="text"
                                        class="w-full py-3 px-4 rounded-xl border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Kelas</label>
                                    <div class="relative">
                                        <select
                                            v-model="form.class_id"
                                            class="w-full py-3 px-4 rounded-xl border-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all appearance-none"
                                        >
                                            <option :value="null">-- Pilih Kelas --</option>
                                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                                                {{ cls.name }}
                                            </option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none">
                                            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                    <p class="text-red-500 text-xs mt-1" v-if="form.errors.class_id">{{ form.errors.class_id }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
                            <div class="flex items-center gap-4 mb-6 pb-4 border-b border-gray-100">
                                <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                                    <IdentificationIcon class="w-6 h-6" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Biodata & Kontak</h3>
                                    <p class="text-sm text-gray-500">Informasi pribadi dan alamat siswa.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Tempat Lahir</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <MapPinIcon class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <input
                                            v-model="form.pob"
                                            type="text"
                                            class="w-full py-3 pl-10 pr-4 rounded-xl border-gray-200 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Lahir</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <CalendarDaysIcon class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <input
                                            v-model="form.dob"
                                            type="date"
                                            class="w-full py-3 pl-10 pr-4 rounded-xl border-gray-200 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Kelamin</label>
                                <div class="relative">
                                    <select
                                        v-model="form.gender"
                                        class="w-full py-3 px-4 rounded-xl border-gray-200 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all appearance-none bg-white"
                                    >
                                        <option value="" disabled>-- Pilih Jenis Kelamin --</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none">
                                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-red-500 text-xs mt-1" v-if="form.errors.gender">{{ form.errors.gender }}</p>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-bold text-gray-700 mb-2">No. HP / WhatsApp</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <PhoneIcon class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <input
                                        v-model="form.phone"
                                        type="text"
                                        class="w-full py-3 pl-10 pr-4 rounded-xl border-gray-200 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all"
                                        placeholder="08xxxxxxxx"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Alamat Lengkap</label>
                                <textarea
                                    v-model="form.address"
                                    rows="4"
                                    class="w-full py-3 px-4 rounded-xl border-gray-200 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all"
                                    placeholder="Nama Jalan, RT/RW, Kelurahan, Kecamatan..."
                                ></textarea>
                            </div>
                        </div>

                        <div class="lg:hidden bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky bottom-4 z-10">
                            <button type="submit" :disabled="form.processing" class="w-full py-3 px-4 rounded-xl bg-blue-600 text-white font-bold shadow-lg flex justify-center items-center gap-2">
                                <CheckCircleIcon class="w-5 h-5" />
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>