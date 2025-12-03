<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import {
    EnvelopeIcon,
    LockClosedIcon,
    ArrowRightIcon,
    AcademicCapIcon,
    EyeIcon,
    EyeSlashIcon,
} from "@heroicons/vue/24/outline";

defineProps({
    status: String,
    schoolName: String, // <--- Menerima Data Nama Sekolah
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Masuk - Portal Akademik" />

    <div
        class="min-h-screen flex bg-white font-sans selection:bg-emerald-500 selection:text-white"
    >
        <div
            class="hidden lg:flex w-1/2 relative bg-emerald-900 items-center justify-center overflow-hidden"
        >
            <img
                src="/storage/images/login-bg.jpg"
                class="absolute inset-0 w-full h-full object-cover opacity-60 grayscale hover:grayscale-0 transition-all duration-1000 ease-in-out transform hover:scale-105"
                alt="Kegiatan Sekolah"
            />
            <div
                class="absolute inset-0 bg-gradient-to-t from-emerald-900 via-emerald-900/40 to-transparent"
            ></div>

            <div class="relative z-10 p-12 text-center">
                <div
                    class="inline-flex items-center justify-center w-20 h-20 bg-white/10 backdrop-blur-md rounded-2xl mb-6 border border-white/20 shadow-2xl"
                >
                    <AcademicCapIcon class="w-10 h-10 text-white" />
                </div>
                <h2
                    class="text-3xl md:text-4xl font-extrabold text-white tracking-tight mb-4 uppercase leading-tight"
                >
                    {{ schoolName }}
                </h2>
                <p
                    class="text-lg text-emerald-100 max-w-md mx-auto leading-relaxed font-light"
                >
                    "Mewujudkan Generasi Berkarakter, Kompeten, dan Unggul dalam
                    Teknologi Informasi."
                </p>
            </div>
            <div
                class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20"
            ></div>
        </div>

        <div
            class="w-full lg:w-1/2 flex flex-col items-center justify-center p-8 sm:p-12 md:p-16 bg-white relative"
        >
            <div
                class="lg:hidden absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-emerald-500 to-teal-500"
            ></div>

            <div class="w-full max-w-sm">
                <div class="mb-10 text-left">
                    <h3 class="text-3xl font-extrabold text-slate-900 mb-2">
                        Selamat Datang 👋
                    </h3>
                    <p class="text-slate-500">
                        Silakan masuk dengan akun akademik Anda.
                    </p>
                </div>

                <div
                    v-if="status"
                    class="mb-6 bg-emerald-50 border border-emerald-100 text-emerald-600 px-4 py-3 rounded-xl text-sm font-medium"
                >
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label
                            class="block text-sm font-bold text-slate-700 mb-2"
                            >Email / Username</label
                        >
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"
                            >
                                <EnvelopeIcon class="h-5 w-5 text-slate-400" />
                            </div>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block transition-all placeholder:text-slate-400"
                                placeholder="nama@sekolah.sch.id"
                            />
                        </div>
                        <p
                            v-if="form.errors.email"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label
                                class="block text-sm font-bold text-slate-700"
                                >Kata Sandi</label
                            >
                        </div>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none"
                            >
                                <LockClosedIcon
                                    class="h-5 w-5 text-slate-400"
                                />
                            </div>
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                class="w-full pl-11 pr-12 py-3.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block transition-all placeholder:text-slate-400"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center cursor-pointer text-slate-400 hover:text-emerald-600 transition-colors focus:outline-none"
                            >
                                <EyeIcon v-if="!showPassword" class="h-5 w-5" />
                                <EyeSlashIcon v-else class="h-5 w-5" />
                            </button>
                        </div>
                        <p
                            v-if="form.errors.password"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="flex items-center">
                        <input
                            id="remember-me"
                            type="checkbox"
                            v-model="form.remember"
                            class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded"
                        />
                        <label
                            for="remember-me"
                            class="ml-2 block text-sm text-slate-600"
                            >Ingat saya di perangkat ini</label
                        >
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-emerald-500/20 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all transform active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Memuat...</span>
                        <span v-else class="flex items-center gap-2"
                            >Masuk Portal <ArrowRightIcon class="w-4 h-4"
                        /></span>
                    </button>
                </form>

                <div class="mt-8 text-center border-t border-slate-100 pt-6">
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Lupa kata sandi? Hubungi
                        <span class="font-bold text-emerald-700"
                            >Administrator Sekolah</span
                        >
                        untuk melakukan reset akun.
                    </p>
                    <p class="mt-4 text-xs text-slate-400 font-medium">
                        &copy; 2025 {{ schoolName }}.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
