<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    materia: {
        type: Object,
        required: true,
    },
    profesoresAsignados: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head :title="materia.nombre" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <Link :href="route('admin.materias.index')" class="text-sm font-medium text-brand hover:underline">← Materias</Link>
                    <h1 class="mt-2 text-xl font-semibold text-gray-900">{{ materia.nombre }}</h1>
                </div>
                <Link :href="route('admin.materias.edit', materia.id)" class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">Editar materia y profesores</Link>
            </div>
        </template>

        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <section class="rounded-xl bg-white p-6 shadow-sm sm:p-8">
                <dl class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-gray-500">Nombre</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ materia.nombre }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">División</dt>
                        <dd class="mt-1 font-medium capitalize text-gray-900">{{ materia.division }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-xl bg-white p-6 shadow-sm sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Profesores con acceso</h2>
                        <p class="mt-1 text-sm text-gray-600">Profesores asignados a esta materia.</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-brand-dark">{{ profesoresAsignados.length }}</span>
                </div>
                <div v-if="profesoresAsignados.length" class="mt-5 divide-y divide-gray-100">
                    <div v-for="profesor in profesoresAsignados" :key="profesor.id" class="flex flex-wrap justify-between gap-2 py-3 first:pt-0 last:pb-0">
                        <span class="font-medium text-gray-900">{{ profesor.name }}</span>
                        <span class="text-sm text-gray-500">{{ profesor.email }}</span>
                    </div>
                </div>
                <p v-else class="mt-5 text-sm text-gray-500">No hay profesores asignados a esta materia.</p>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
