<script setup>
const model = defineModel({
    type: String,
    required: true,
});
</script>

<template>
    <input
        class="rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand"
        v-model="model"
    />
</template>
