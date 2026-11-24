<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Iniciar sesión" />

    <GuestLayout>
        <div class="mx-auto flex min-h-[calc(75vh-5rem)] max-w-7xl items-center justify-center px-4 py-12">
            <section class="w-full max-w-md rounded-xl bg-white p-6 shadow-lg sm:p-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Iniciar sesión</h1>
                    <p class="mt-1 text-sm text-gray-600">Ingrese con su cuenta institucional.</p>
                </div>

                <form class="space-y-5" @submit.prevent="submit">
                    <div>
                        <InputLabel for="email" value="Correo electrónico" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            autocomplete="username"
                            autofocus
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Contraseña" />
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full"
                            autocomplete="current-password"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <Checkbox v-model:checked="form.remember" />
                        Mantener la sesión iniciada
                    </label>

                    <PrimaryButton
                        class="w-full justify-center"
                        :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing"
                    >
                        Ingresar
                    </PrimaryButton>
                </form>
            </section>
        </div>
    </GuestLayout>
</template>
