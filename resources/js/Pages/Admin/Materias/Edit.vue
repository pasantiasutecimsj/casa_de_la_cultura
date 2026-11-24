<script setup>
import MateriaForm from './Form.vue';

defineProps({
    materia: {
        type: Object,
        required: true,
    },
    profesoresDisponibles: {
        type: Array,
        required: true,
    },
    profesoresAsignados: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <MateriaForm
        :materia="materia"
        :profesores-disponibles="profesoresDisponibles"
        :profesores-asignados="profesoresAsignados"
    />
</template>
