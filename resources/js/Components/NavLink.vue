<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center border-b-2 border-yellow-400 px-1 pt-1 text-sm font-medium text-white focus:outline-none'
        : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-blue-200 transition hover:border-white/50 hover:text-white focus:outline-none'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
