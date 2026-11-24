<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    danger: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() => props.danger
    ? 'text-red-700 hover:bg-red-50 focus:bg-red-50'
    : 'text-gray-700 hover:bg-gray-100 focus:bg-gray-100');
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-2 text-start text-sm transition focus:outline-none"
        :class="classes"
    >
        <slot />
    </Link>
</template>
