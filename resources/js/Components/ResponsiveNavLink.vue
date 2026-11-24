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
    danger: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() => {
    if (props.danger) {
        return 'block w-full border-l-4 border-transparent px-4 py-2 text-start text-base font-medium text-red-200 transition hover:bg-red-900/20 hover:text-white focus:outline-none';
    }

    return props.active
        ? 'block w-full border-l-4 border-white bg-white/10 px-4 py-2 text-start text-base font-medium text-white focus:outline-none'
        : 'block w-full border-l-4 border-transparent px-4 py-2 text-start text-base font-medium text-blue-200 transition hover:border-white/50 hover:bg-white/10 hover:text-white focus:outline-none';
});

</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
