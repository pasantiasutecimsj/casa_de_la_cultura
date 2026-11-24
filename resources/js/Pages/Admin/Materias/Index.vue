<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    materias: {
        type: Array,
        required: true,
    },
});
const busqueda = ref('');
const orden = ref('nombre');
const materiasOrdenadas = computed(() => [...props.materias]
    .filter((materia) => materia.nombre.toLowerCase().includes(busqueda.value.toLowerCase()))
    .sort((a, b) => orden.value === 'nombre' ? a.nombre.localeCompare(b.nombre) : b.nombre.localeCompare(a.nombre)));

const eliminar = (materia) => {
    if (window.confirm(`¿Eliminar la materia "${materia.nombre}"?`)) {
        router.delete(route('admin.materias.destroy', materia.id));
    }
};
</script>

<template>
    <Head title="Materias" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Materias</h1>
                    <p class="mt-1 text-sm text-gray-600">Administra las materias y sus asignaciones.</p>
                </div>
                <Link
                    :href="route('admin.materias.create')"
                    class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark"
                >
                    Crear materia
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="flex flex-wrap gap-3 border-b border-gray-100 p-4">
                    <input v-model="busqueda" type="search" placeholder="Buscar materia..." class="w-full min-w-0 flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand sm:w-auto">
                    <select v-model="orden" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand"><option value="nombre">A-Z</option><option value="nombre-desc">Z-A</option></select>
                </div>
                <div v-if="materiasOrdenadas.length" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Materia</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">División</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="materia in materiasOrdenadas" :key="materia.id">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ materia.nombre }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm capitalize text-gray-600">{{ materia.division }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <div class="flex justify-end gap-3">
                                        <Link :href="route('admin.materias.show', materia.id)" class="font-medium text-brand hover:underline">Ver</Link>
                                        <Link :href="route('profesor.materias.show', materia.id)" class="font-medium text-brand hover:underline">Alumnos inscriptos</Link>
                                        <Link :href="route('admin.materias.edit', materia.id)" class="font-medium text-brand hover:underline">Editar</Link>
                                        <button type="button" class="font-medium text-red-700 hover:underline" @click="eliminar(materia)">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="p-8 text-center text-sm text-gray-500">Todavía no hay materias registradas.</p>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
