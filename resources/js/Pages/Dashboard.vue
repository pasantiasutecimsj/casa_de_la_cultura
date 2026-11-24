<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const isAdmin = computed(() =>
    page.props.auth.roles?.some((role) => role.key.toLowerCase() === 'admin'),
);
const isProfesor = computed(() =>
    page.props.auth.roles?.some((role) => role.key.toLowerCase() === 'profesor'),
);
</script>

<template>
    <Head title="Inicio" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-900">Inicio</h1>
        </template>

        <div class="py-10">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                <section v-if="isAdmin" class="rounded-xl bg-white p-6 shadow-sm">
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

                <section v-if="isAdmin" class="rounded-xl bg-white p-6 shadow-sm">
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

                <section class="rounded-xl bg-white p-6 shadow-sm lg:col-span-2">
                    <h2 class="text-lg font-semibold text-gray-900">Gestión académica</h2>
                    <p class="mt-1 text-sm text-gray-600">Accede a las herramientas disponibles para tu rol.</p>
                    <div class="mt-8 grid justify-center gap-6 sm:grid-cols-2">
                        <Link
                            v-if="isAdmin"
                            :href="route('admin.materias.index')"
                            class="group flex min-h-40 w-full min-w-0 flex-col items-center justify-center rounded-2xl bg-blue-600 p-5 text-center text-white shadow-lg transition hover:-translate-y-1 hover:bg-blue-700 hover:shadow-xl sm:w-72 sm:p-7"
                        >
                            <svg class="mb-3 h-12 w-12 text-blue-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5v-15Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 20.5A2.5 2.5 0 0 1 6.5 18H20M8 7h8M8 11h6" />
                            </svg>
                            <span class="text-lg font-bold">Administrar materias</span>
                            <span class="mt-1 text-sm text-blue-100">Crear, editar y asignar profesores</span>
                        </Link>
                        <Link
                            v-if="isAdmin"
                            :href="route('admin.alumnos.index')"
                            class="group flex min-h-40 w-full min-w-0 flex-col items-center justify-center rounded-2xl bg-blue-600 p-5 text-center text-white shadow-lg transition hover:-translate-y-1 hover:bg-blue-700 hover:shadow-xl sm:w-72 sm:p-7"
                        >
                            <svg class="mb-3 h-12 w-12 text-blue-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <circle cx="9" cy="8" r="3" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 19a5.5 5.5 0 0 1 11 0M16 6.5a2.5 2.5 0 0 1 0 5M16 14.5a4.5 4.5 0 0 1 4.5 4.5" />
                            </svg>
                            <span class="text-lg font-bold">Administrar alumnos</span>
                            <span class="mt-1 text-sm text-blue-100">Consultar datos e inscripciones</span>
                        </Link>
                        <Link
                            v-if="isProfesor"
                            :href="route('profesor.materias.index')"
                            class="group flex min-h-40 w-full min-w-0 flex-col items-center justify-center rounded-2xl border-2 border-gray-200 bg-white p-5 text-center text-gray-800 shadow-lg transition hover:-translate-y-1 hover:border-blue-300 hover:bg-blue-50 hover:shadow-xl sm:col-span-2 sm:mx-auto sm:w-72 sm:p-7"
                        >
                            <svg class="mb-3 h-12 w-12 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5v-15Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 20.5A2.5 2.5 0 0 1 6.5 18H20M8 7h8M8 11h6" />
                            </svg>
                            <span class="text-lg font-bold">Ver mis materias</span>
                            <span class="mt-1 text-sm text-gray-500">Gestionar alumnos y calificaciones</span>
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
