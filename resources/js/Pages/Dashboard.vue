<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Inicio" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-900">Inicio</h1>
            <p v-if="$page.props.auth.project" class="mt-1 text-sm text-gray-600">
                {{ $page.props.auth.project.name }}
            </p>
        </template>

        <div class="py-10">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                <section class="rounded-xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Usuario</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500">Nombre</dt>
                            <dd class="font-medium text-gray-900">{{ $page.props.auth.user.name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Correo electrónico</dt>
                            <dd class="font-medium text-gray-900">{{ $page.props.auth.user.email }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Roles</h2>
                    <ul v-if="$page.props.auth.roles.length" class="mt-4 flex flex-wrap gap-2">
                        <li
                            v-for="role in $page.props.auth.roles"
                            :key="role.id"
                            class="rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-brand-dark"
                        >
                            {{ role.name }}
                        </li>
                    </ul>
                    <p v-else class="mt-4 text-sm text-gray-500">No hay roles asignados.</p>
                </section>

                <section class="rounded-xl border border-dashed border-gray-300 bg-white p-6 lg:col-span-2">
                    <h2 class="text-lg font-semibold text-gray-900">Área de trabajo</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Reemplace esta sección por los módulos del nuevo proyecto.
                    </p>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
