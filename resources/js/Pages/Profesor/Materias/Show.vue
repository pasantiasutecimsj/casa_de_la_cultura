<script setup>
import InputError from '@/Components/InputError.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    materia: { type: Object, required: true },
    alumnosInscritos: { type: Array, required: true },
    alumnosDisponibles: { type: Array, required: true },
});

const inscription = useForm({
    alumno_id: '',
    fecha_examen: '',
    nombre: '', apellido: '', edad: '', ci: '', telefono: '', direccion: '', correo: '', notas: '',
});
const nuevoAlumno = ref(false);
const busquedaInscritos = ref('');
const ordenInscritos = ref('nombre');
const confirmacionMenor = ref(false);
const alumnosInscritosOrdenados = computed(() => [...props.alumnosInscritos]
    .filter((row) => `${row.alumno?.nombre ?? ''} ${row.alumno?.apellido ?? ''}`.toLowerCase().includes(busquedaInscritos.value.toLowerCase()))
    .sort((a, b) => {
        const primero = `${a.alumno?.apellido ?? ''} ${a.alumno?.nombre ?? ''}`.toLowerCase();
        const segundo = `${b.alumno?.apellido ?? ''} ${b.alumno?.nombre ?? ''}`.toLowerCase();
        return ordenInscritos.value === 'nombre' ? primero.localeCompare(segundo) : segundo.localeCompare(primero);
    }));

const grades = {};

const enroll = () => {
    const fechaNacimiento = nuevoAlumno.value
        ? inscription.edad
        : props.alumnosDisponibles.find((alumno) => String(alumno.id) === String(inscription.alumno_id))?.edad;

    if (props.materia.division === 'mayores' && esMenorDeEdad(fechaNacimiento)) {
        confirmacionMenor.value = true;
        const confirmar = false && window.confirm(
            'El alumno es menor de edad y la materia está destinada a mayores. ¿Desea inscribirlo de todos modos?',
        );

        if (!confirmar) {
            return;
        }
    }

    inscription.post(route('profesor.materias.inscripciones.store', props.materia.id), {
        preserveScroll: true,
        onSuccess: () => inscription.reset(),
    });
};

const confirmarInscripcion = () => {
    confirmacionMenor.value = false;
    inscription.post(route('profesor.materias.inscripciones.store', props.materia.id), {
        preserveScroll: true,
        onSuccess: () => inscription.reset(),
    });
};

const esMenorDeEdad = (fechaNacimiento) => {
    if (!fechaNacimiento) {
        return false;
    }

    const nacimiento = new Date(`${String(fechaNacimiento).slice(0, 10)}T00:00:00`);
    const hoy = new Date();
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const cumpleEsteAño = new Date(hoy.getFullYear(), nacimiento.getMonth(), nacimiento.getDate());

    if (hoy < cumpleEsteAño) {
        edad -= 1;
    }

    return edad < 18;
};

const toggleNewStudent = () => {
    nuevoAlumno.value = !nuevoAlumno.value;
    inscription.clearErrors();
    inscription.reset('alumno_id', 'nombre', 'apellido', 'edad', 'ci', 'telefono', 'direccion', 'correo', 'notas');
};

const updateGrade = (row) => {
    const form = gradeForm(row);
    form.patch(route('profesor.materias.inscripciones.update', [props.materia.id, row.id]), { preserveScroll: true });
};

const gradeForm = (row) => grades[row.id] ??= useForm({
    nota: row.nota ?? '',
    fecha_examen: row.fecha_examen ? String(row.fecha_examen).slice(0, 10) : '',
});

const fecha = (value) => (value ? String(value).slice(0, 10) : '—');
</script>

<template>
    <Head :title="materia.nombre" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <Link :href="route('profesor.materias.index')" class="text-sm font-medium text-brand hover:underline">← Mis materias</Link>
                <h1 class="mt-2 text-xl font-semibold text-gray-900">{{ materia.nombre }}</h1>
                <p class="mt-1 text-sm capitalize text-gray-600">División: {{ materia.division }}</p>
            </div>
        </template>

        <div class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:px-8">
            <section class="rounded-xl bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-gray-900">Inscribir alumno</h2>
                    <button type="button" class="text-sm font-medium text-brand hover:underline" @click="toggleNewStudent">{{ nuevoAlumno ? 'Elegir alumno existente' : 'Registrar alumno nuevo' }}</button>
                </div>
                <form v-if="!nuevoAlumno" class="mt-4 grid gap-4 sm:grid-cols-[1fr_180px_auto] sm:items-end" @submit.prevent="enroll">
                    <div>
                        <label for="alumno_id" class="block text-sm font-medium text-gray-700">Alumno</label>
                        <select id="alumno_id" v-model="inscription.alumno_id" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand">
                            <option value="">Seleccionar alumno</option>
                            <option v-for="alumno in alumnosDisponibles" :key="alumno.id" :value="alumno.id">{{ alumno.apellido }}, {{ alumno.nombre }} ({{ alumno.ci }})</option>
                        </select>
                        <InputError class="mt-1" :message="inscription.errors.alumno_id" />
                    </div>
                    <div>
                        <label for="fecha_examen" class="block text-sm font-medium text-gray-700">Fecha de examen</label>
                        <input id="fecha_examen" v-model="inscription.fecha_examen" type="date" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand">
                        <InputError class="mt-1" :message="inscription.errors.fecha_examen" />
                    </div>
                    <button type="submit" :disabled="inscription.processing || !alumnosDisponibles.length" class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark disabled:opacity-50">Inscribir</button>
                </form>
                <form v-else class="mt-4 grid gap-4 sm:grid-cols-2" @submit.prevent="enroll">
                    <div v-for="field in [
                        ['nombre', 'Nombre', 'text'], ['apellido', 'Apellido', 'text'], ['edad', 'Fecha de nacimiento', 'date'],
                        ['ci', 'Cédula', 'text'], ['telefono', 'Teléfono', 'text'], ['direccion', 'Dirección', 'text'],
                        ['correo', 'Correo electrónico', 'email']
                    ]" :key="field[0]">
                        <label :for="field[0]" class="block text-sm font-medium text-gray-700">{{ field[1] }}</label>
                        <input :id="field[0]" v-model="inscription[field[0]]" :type="field[2]" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand">
                        <InputError class="mt-1" :message="inscription.errors[field[0]]" />
                    </div>
                    <div class="sm:col-span-2">
                        <label for="notas" class="block text-sm font-medium text-gray-700">Observaciones</label>
                        <textarea id="notas" v-model="inscription.notas" rows="3" maxlength="100" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand"></textarea>
                        <InputError class="mt-1" :message="inscription.errors.notas" />
                    </div>
                    <div>
                        <label for="nuevo_fecha_examen" class="block text-sm font-medium text-gray-700">Fecha de examen</label>
                        <input id="nuevo_fecha_examen" v-model="inscription.fecha_examen" type="date" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand">
                        <InputError class="mt-1" :message="inscription.errors.fecha_examen" />
                    </div>
                    <div class="flex items-end justify-end">
                        <button type="submit" :disabled="inscription.processing" class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark disabled:opacity-50">Registrar e inscribir</button>
                    </div>
                </form>
                <p v-if="!alumnosDisponibles.length" class="mt-3 text-sm text-gray-500">Todos los alumnos ya están inscriptos.</p>
            </section>

            <section class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="flex flex-wrap items-center gap-3 border-b border-gray-100 p-4">
                    <h2 class="mr-auto text-lg font-semibold text-gray-900">Alumnos inscriptos</h2>
                    <input v-model="busquedaInscritos" type="search" placeholder="Buscar alumno..." class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand">
                    <select v-model="ordenInscritos" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand"><option value="nombre">A-Z</option><option value="nombre-desc">Z-A</option></select>
                </div>
                <div v-if="alumnosInscritosOrdenados.length" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Alumno</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Inscripción</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Examen</th><th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Nota</th><th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Acción</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="row in alumnosInscritosOrdenados" :key="row.id">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ row.alumno?.apellido }}, {{ row.alumno?.nombre }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ fecha(row.fecha_inscripcion) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600"><input v-model="gradeForm(row).fecha_examen" type="date" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand"></td>
                                <td class="px-6 py-4 text-sm text-gray-600"><input v-model="gradeForm(row).nota" type="number" min="0" class="w-24 rounded-md border-gray-300 text-sm shadow-sm focus:border-brand focus:ring-brand"></td>
                                <td class="px-6 py-4 text-right"><button type="button" :disabled="gradeForm(row).processing" class="font-medium text-brand hover:underline disabled:opacity-50" @click="updateGrade(row)">Guardar datos</button><InputError class="mt-1 text-left" :message="gradeForm(row).errors.nota || gradeForm(row).errors.fecha_examen" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="p-6 text-sm text-gray-500">Todavía no hay alumnos inscriptos.</p>
            </section>
        </div>
        <div v-if="confirmacionMenor" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 px-4" role="dialog" aria-modal="true">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div class="rounded-xl border border-yellow-300 bg-yellow-50 p-4">
                    <h2 class="text-lg font-bold text-yellow-900">Atención: alumno menor de edad</h2>
                    <p class="mt-2 text-sm text-yellow-800">El alumno es menor de edad y esta materia está destinada a mayores. ¿Desea inscribirlo de todos modos?</p>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700" @click="confirmacionMenor = false">Cancelar</button>
                    <button type="button" class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700" @click="confirmarInscripcion">Confirmar inscripción</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
