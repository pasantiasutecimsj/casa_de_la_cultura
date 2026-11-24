<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    alumnos: {
        type: Array,
        required: true,
    },
});
const busqueda = ref('');
const orden = ref('nombre');
const alumnosOrdenados = computed(() => [...props.alumnos]
    .filter((alumno) => `${alumno.nombre} ${alumno.apellido} ${alumno.ci}`.toLowerCase().includes(busqueda.value.toLowerCase()))
    .sort((a, b) => {
        const primero = `${a.apellido} ${a.nombre}`.toLowerCase();
        const segundo = `${b.apellido} ${b.nombre}`.toLowerCase();
        return orden.value === 'nombre' ? primero.localeCompare(segundo) : segundo.localeCompare(primero);
    }));

const eliminar = (alumno) => {
    if (window.confirm(`¿Eliminar a ${alumno.nombre} ${alumno.apellido}?`)) {
        router.delete(route('admin.alumnos.destroy', alumno.id));
    }
};
</script>

<template>
    <Head title="Alumnos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Alumnos</h1>
                    <p class="mt-1 text-sm text-gray-600">Administra los datos y el historial de los alumnos.</p>
                </div>
                <Link
                    :href="route('admin.alumnos.create')"
                    class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark"
                >
                    Crear alumno
                </Link>
            </div>
        </template>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="flex flex-wrap gap-3 border-b border-gray-100 p-4">
                    <input v-model="busqueda" type="search" placeholder="Buscar por nombre o cédula..." class="w-full min-w-0 flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand sm:w-auto">
                    <select v-model="orden" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand"><option value="nombre">A-Z</option><option value="nombre-desc">Z-A</option></select>
                </div>
                <div v-if="alumnosOrdenados.length" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Alumno</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Cédula</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Correo</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="alumno in alumnosOrdenados" :key="alumno.id">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ alumno.apellido }}, {{ alumno.nombre }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ alumno.ci }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ alumno.correo }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    <div class="flex justify-end gap-3">
                                        <Link :href="route('admin.alumnos.show', alumno.id)" class="font-medium text-brand hover:underline">Ver</Link>
                                        <Link :href="route('admin.alumnos.edit', alumno.id)" class="font-medium text-brand hover:underline">Editar</Link>
                                        <button type="button" class="font-medium text-red-700 hover:underline" @click="eliminar(alumno)">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="p-8 text-center text-sm text-gray-500">Todavía no hay alumnos registrados.</p>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
