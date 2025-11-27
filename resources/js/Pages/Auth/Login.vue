<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk - Absensi Siswa" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel
                    for="email"
                    value="Email Siswa"
                    class="text-green-800 font-bold"
                />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full border-green-300 focus:border-green-500 focus:ring-green-500 rounded-lg bg-green-50"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="contoh: budi@smk.com"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password"
                    value="Kata Sandi"
                    class="text-green-800 font-bold"
                />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full border-green-300 focus:border-green-500 focus:ring-green-500 rounded-lg bg-green-50"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox
                        name="remember"
                        v-model:checked="form.remember"
                        class="text-green-600 focus:ring-green-500"
                    />
                    <span class="ms-2 text-sm text-gray-600">Ingat Saya</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="underline text-sm text-gray-600 hover:text-green-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                    Lupa password?
                </Link>

                <PrimaryButton
                    class="ms-4 bg-green-700 hover:bg-green-800 focus:bg-green-700 active:bg-green-900 border-transparent shadow-lg shadow-green-700/30 transition-all py-3 px-6 rounded-lg"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Masuk Sekarang
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
