<script setup>
import InputError from '@/Components/InputError.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    materia: {
        type: Object,
        default: null,
    },
    profesoresDisponibles: {
        type: Array,
        default: () => [],
    },
    profesoresAsignados: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    nombre: props.materia?.nombre ?? '',
    division: props.materia?.division ?? 'mayores',
});

const profesoresForm = useForm({
    usuario_ids: [...props.profesoresAsignados],
});

const submit = () => {
    if (props.materia) {
        form.put(route('admin.materias.update', props.materia.id));
        return;
    }

    form.post(route('admin.materias.store'));
};

const guardarProfesores = () => {
    profesoresForm.put(route('admin.materias.profesores.update', props.materia.id));
};
</script>

<template>
    <Head :title="materia ? 'Editar materia' : 'Crear materia'" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <Link :href="route('admin.materias.index')" class="text-sm font-medium text-brand hover:underline">← Materias</Link>
                <h1 class="mt-2 text-xl font-semibold text-gray-900">{{ materia ? 'Editar materia' : 'Crear materia' }}</h1>
            </div>
        </template>

        <div class="mx-auto grid max-w-4xl gap-6 px-4 py-8 sm:px-6 lg:px-8">
            <section class="rounded-xl bg-white p-6 shadow-sm sm:p-8">
                <form class="space-y-5" @submit.prevent="submit">
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input id="nombre" v-model="form.nombre" type="text" maxlength="50" required autofocus class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand">
                        <InputError class="mt-1" :message="form.errors.nombre" />
                    </div>
                    <div>
                        <label for="division" class="block text-sm font-medium text-gray-700">División</label>
                        <select id="division" v-model="form.division" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand">
                            <option value="mayores">Mayores</option>
                            <option value="menores">Menores</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.division" />
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <Link :href="route('admin.materias.index')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancelar</Link>
                        <button type="submit" :disabled="form.processing" class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark disabled:opacity-50">
                            {{ form.processing ? 'Guardando…' : 'Guardar materia' }}
                        </button>
                    </div>
                </form>
            </section>

            <section v-if="materia" class="rounded-xl bg-white p-6 shadow-sm sm:p-8">
                <h2 class="text-lg font-semibold text-gray-900">Profesores con acceso</h2>
                <p class="mt-1 text-sm text-gray-600">Selecciona los profesores que podrán consultar y gestionar esta materia.</p>
                <form class="mt-5 space-y-4" @submit.prevent="guardarProfesores">
                    <div v-if="profesoresDisponibles.length" class="grid gap-3 sm:grid-cols-2">
                        <label
                            v-for="profesor in profesoresDisponibles"
                            :key="profesor.id"
                            class="flex items-start gap-3 rounded-lg border border-gray-200 p-3"
                        >
                            <input v-model="profesoresForm.usuario_ids" type="checkbox" :value="profesor.id" class="mt-1 rounded border-gray-300 text-brand focus:ring-brand">
                            <span>
                                <span class="block text-sm font-medium text-gray-900">{{ profesor.name }}</span>
                                <span class="block text-xs text-gray-500">{{ profesor.email }}</span>
                            </span>
                        </label>
                    </div>
                    <p v-else class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">No hay profesores activos disponibles en este proyecto.</p>
                    <InputError :message="profesoresForm.errors.usuario_ids" />
                    <div class="flex justify-end">
                        <button type="submit" :disabled="profesoresForm.processing" class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark disabled:opacity-50">
                            Guardar asignaciones
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
