<script setup>
import { Link } from "@inertiajs/vue3";
import {
    AcademicCapIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    ArrowRightIcon,
} from "@heroicons/vue/24/outline";
</script>

<template>
    <footer class="bg-white border-t border-slate-100 mt-auto relative">
        <div
            class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 opacity-70"
        ></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-8">
            <div
                class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-10 border-b border-slate-50 pb-10"
            >
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="p-2.5 bg-indigo-50 rounded-xl text-indigo-600"
                        >
                            <AcademicCapIcon class="w-7 h-7" />
                        </div>
                        <div>
                            <h3
                                class="text-lg font-black text-slate-800 leading-none tracking-tight"
                            >
                                SMK Tamansiswa
                            </h3>
                            <p
                                class="text-[10px] font-bold text-indigo-500 tracking-widest uppercase mt-1"
                            >
                                Lubuk Pakam 2
                            </p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500 leading-relaxed max-w-sm">
                        Mewujudkan pendidikan berkualitas dengan sistem
                        manajemen digital yang terintegrasi, transparan, dan
                        efisien.
                    </p>
                </div>

                <div>
                    <h4
                        class="text-xs font-black text-slate-800 uppercase tracking-widest mb-5"
                    >
                        Navigasi Utama
                    </h4>
                    <ul class="space-y-3 text-sm font-medium text-slate-500">
                        <li>
                            <Link
                                :href="route('dashboard')"
                                class="flex items-center gap-2 hover:text-indigo-600 hover:translate-x-1 transition-all duration-300"
                            >
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-indigo-500"
                                ></span>
                                Dashboard
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="#"
                                class="flex items-center gap-2 hover:text-indigo-600 hover:translate-x-1 transition-all duration-300"
                            >
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-indigo-500"
                                ></span>
                                Jadwal Pelajaran
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="#"
                                class="flex items-center gap-2 hover:text-indigo-600 hover:translate-x-1 transition-all duration-300"
                            >
                                <span
                                    class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-indigo-500"
                                ></span>
                                Perpustakaan
                            </Link>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4
                        class="text-xs font-black text-slate-800 uppercase tracking-widest mb-5"
                    >
                        Hubungi Kami
                    </h4>
                    <ul class="space-y-4 text-sm text-slate-500">
                        <li class="flex items-start gap-3">
                            <MapPinIcon
                                class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5"
                            />
                            <span class="leading-snug"
                                >Jl. Kartini No. 01, Lubuk Pakam, Sumatera
                                Utara</span
                            >
                        </li>
                        <li class="flex items-center gap-3">
                            <PhoneIcon
                                class="w-5 h-5 text-indigo-500 shrink-0"
                            />
                            <span class="font-semibold text-slate-700"
                                >083809252223</span
                            >
                        </li>
                        <li class="flex items-center gap-3">
                            <EnvelopeIcon
                                class="w-5 h-5 text-indigo-500 shrink-0"
                            />
                            <span class="font-semibold text-slate-700"
                                >yogisyahputra713@gmail.com</span
                            >
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="flex flex-col md:flex-row justify-between items-center gap-4 pt-2"
            >
                <p class="text-xs text-slate-400 font-medium">
                    &copy; {{ new Date().getFullYear() }}
                    <span class="text-slate-600 font-bold"
                        >Tim IT Tamansiswa</span
                    >. All rights reserved.
                </p>
                <div class="flex gap-6 text-xs font-bold text-slate-400">
                    <Link
                        href="#"
                        class="hover:text-indigo-600 transition-colors"
                        >Privacy Policy</Link
                    >
                    <Link
                        href="#"
                        class="hover:text-indigo-600 transition-colors"
                        >Terms of Service</Link
                    >
                </div>
            </div>
        </div>
    </footer>
</template>
