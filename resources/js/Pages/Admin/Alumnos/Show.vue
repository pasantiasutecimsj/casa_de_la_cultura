<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    alumno: {
        type: Object,
        required: true,
    },
});

const fecha = (value) => (value ? String(value).slice(0, 10) : '—');
</script>

<template>
    <Head :title="`${alumno.nombre} ${alumno.apellido}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <Link :href="route('admin.alumnos.index')" class="text-sm font-medium text-brand hover:underline">← Alumnos</Link>
                    <h1 class="mt-2 text-xl font-semibold text-gray-900">{{ alumno.nombre }} {{ alumno.apellido }}</h1>
                </div>
                <Link :href="route('admin.alumnos.edit', alumno.id)" class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark">Editar alumno</Link>
            </div>
        </template>

        <div class="mx-auto grid max-w-5xl gap-6 px-4 py-8 sm:px-6 lg:px-8">
            <section class="rounded-xl bg-white p-6 shadow-sm sm:p-8">
                <h2 class="text-lg font-semibold text-gray-900">Datos del alumno</h2>
                <dl class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div><dt class="text-sm text-gray-500">Cédula</dt><dd class="mt-1 font-medium text-gray-900">{{ alumno.ci }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Fecha de nacimiento</dt><dd class="mt-1 font-medium text-gray-900">{{ fecha(alumno.edad) }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Teléfono</dt><dd class="mt-1 font-medium text-gray-900">{{ alumno.telefono }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Correo</dt><dd class="mt-1 font-medium text-gray-900">{{ alumno.correo }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Dirección</dt><dd class="mt-1 font-medium text-gray-900">{{ alumno.direccion }}</dd></div>
                    <div><dt class="text-sm text-gray-500">Fecha de ingreso</dt><dd class="mt-1 font-medium text-gray-900">{{ fecha(alumno.fecha_ingreso) }}</dd></div>
                    <div class="sm:col-span-2 lg:col-span-3"><dt class="text-sm text-gray-500">Notas</dt><dd class="mt-1 whitespace-pre-wrap font-medium text-gray-900">{{ alumno.notas }}</dd></div>
                </dl>
            </section>

            <section class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Inscripciones y calificaciones</h2>
                </div>
                <div v-if="alumno.calificaciones.length" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Materia</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Inscripción</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Examen</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Nota</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="inscripcion in alumno.calificaciones" :key="inscripcion.id">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ inscripcion.materia?.nombre ?? 'Materia eliminada' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ fecha(inscripcion.fecha_inscripcion) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ fecha(inscripcion.fecha_examen) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ inscripcion.nota ?? 'Sin calificar' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="p-6 text-sm text-gray-500">Este alumno todavía no tiene inscripciones.</p>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
