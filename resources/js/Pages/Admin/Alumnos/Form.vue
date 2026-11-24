<script setup>
import InputError from '@/Components/InputError.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    alumno: {
        type: Object,
        default: null,
    },
});

const fecha = (value) => (value ? String(value).slice(0, 10) : '');

const form = useForm({
    nombre: props.alumno?.nombre ?? '',
    apellido: props.alumno?.apellido ?? '',
    edad: fecha(props.alumno?.edad),
    ci: props.alumno?.ci ?? '',
    telefono: props.alumno?.telefono ?? '',
    direccion: props.alumno?.direccion ?? '',
    correo: props.alumno?.correo ?? '',
    fecha_ingreso: fecha(props.alumno?.fecha_ingreso),
    notas: props.alumno?.notas ?? '',
});

const submit = () => {
    if (props.alumno) {
        form.put(route('admin.alumnos.update', props.alumno.id));
        return;
    }

    form.post(route('admin.alumnos.store'));
};
</script>

<template>
    <Head :title="alumno ? 'Editar alumno' : 'Crear alumno'" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <Link :href="route('admin.alumnos.index')" class="text-sm font-medium text-brand hover:underline">← Alumnos</Link>
                <h1 class="mt-2 text-xl font-semibold text-gray-900">{{ alumno ? 'Editar alumno' : 'Crear alumno' }}</h1>
            </div>
        </template>

        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <section class="rounded-xl bg-white p-6 shadow-sm sm:p-8">
                <form class="grid gap-5 sm:grid-cols-2" @submit.prevent="submit">
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input id="nombre" v-model="form.nombre" type="text" maxlength="20" required autofocus class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand">
                        <InputError class="mt-1" :message="form.errors.nombre" />
                    </div>
                    <div>
                        <label for="apellido" class="block text-sm font-medium text-gray-700">Apellido</label>
                        <input id="apellido" v-model="form.apellido" type="text" maxlength="20" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand">
                        <InputError class="mt-1" :message="form.errors.apellido" />
                    </div>
                    <div>
                        <label for="edad" class="block text-sm font-medium text-gray-700">Fecha de nacimiento</label>
                        <input id="edad" v-model="form.edad" type="date" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand">
                        <InputError class="mt-1" :message="form.errors.edad" />
                    </div>
                    <div>
                        <label for="ci" class="block text-sm font-medium text-gray-700">Cédula</label>
                        <input id="ci" v-model="form.ci" type="text" maxlength="8" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand">
                        <InputError class="mt-1" :message="form.errors.ci" />
                    </div>
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <input id="telefono" v-model="form.telefono" type="tel" maxlength="9" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand">
                        <InputError class="mt-1" :message="form.errors.telefono" />
                    </div>
                    <div>
                        <label for="correo" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                        <input id="correo" v-model="form.correo" type="email" maxlength="30" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand">
                        <InputError class="mt-1" :message="form.errors.correo" />
                    </div>
                    <div>
                        <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
                        <input id="direccion" v-model="form.direccion" type="text" maxlength="20" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand">
                        <InputError class="mt-1" :message="form.errors.direccion" />
                    </div>
                    <div>
                        <label for="fecha_ingreso" class="block text-sm font-medium text-gray-700">Fecha de ingreso</label>
                        <input id="fecha_ingreso" v-model="form.fecha_ingreso" type="date" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand">
                        <InputError class="mt-1" :message="form.errors.fecha_ingreso" />
                    </div>
                    <div class="sm:col-span-2">
                        <label for="notas" class="block text-sm font-medium text-gray-700">Notas</label>
                        <textarea id="notas" v-model="form.notas" maxlength="100" rows="3" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand"></textarea>
                        <InputError class="mt-1" :message="form.errors.notas" />
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-3 sm:col-span-2">
                        <Link :href="route('admin.alumnos.index')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancelar</Link>
                        <button type="submit" :disabled="form.processing" class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark disabled:opacity-50">
                            {{ form.processing ? 'Guardando…' : 'Guardar alumno' }}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
