<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('user.password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => form.reset('current_password'),
    });
};
</script>

<template>
    <Head title="Usuario" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-900">Usuario</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $page.props.auth.user.email }}</p>
        </template>

        <div class="px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
            <section class="mx-auto max-w-xl rounded-xl bg-white p-5 shadow-sm sm:p-8">
                <h2 class="text-lg font-semibold text-gray-900">Cambiar contraseña</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Primero confirmamos la contraseña actual y después elegimos una nueva.
                </p>

                <form class="mt-6 space-y-5" @submit.prevent="submit">
                    <div>
                        <InputLabel for="current_password" value="Contraseña actual" />
                        <div class="mt-1">
                            <PasswordInput
                                id="current_password"
                                v-model="form.current_password"
                                autocomplete="current-password"
                                autofocus
                                required
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.current_password" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Nueva contraseña" />
                        <div class="mt-1">
                            <PasswordInput
                                id="password"
                                v-model="form.password"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirmar nueva contraseña" />
                        <div class="mt-1">
                            <PasswordInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <div class="flex flex-col-reverse gap-3 pt-1 sm:flex-row sm:items-center sm:justify-end">
                        <p
                            v-if="form.recentlySuccessful"
                            class="text-center text-sm font-medium text-green-700 sm:me-auto sm:text-start"
                            role="status"
                        >
                            Contraseña actualizada.
                        </p>
                        <PrimaryButton
                            class="w-full justify-center sm:w-auto"
                            :class="{ 'opacity-50': form.processing }"
                            :disabled="form.processing"
                        >
                            Guardar contraseña
                        </PrimaryButton>
                    </div>
                </form>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
