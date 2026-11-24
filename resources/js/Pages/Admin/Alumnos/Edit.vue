<script setup>
import AlumnoForm from './Form.vue';

defineProps({
    alumno: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AlumnoForm :alumno="alumno" />
</template>
