<script setup>
import { ref } from 'vue';

defineOptions({ inheritAttrs: false });

const model = defineModel({
    type: String,
    required: true,
});

const visible = ref(false);
</script>

<template>
    <div class="relative">
        <input
            v-bind="$attrs"
            v-model="model"
            :type="visible ? 'text' : 'password'"
            class="block w-full rounded-md border-gray-300 pe-11 shadow-sm focus:border-brand focus:ring-brand"
        />
        <button
            type="button"
            class="absolute inset-y-0 end-0 flex w-11 items-center justify-center rounded-e-md text-gray-500 transition hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand"
            :aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"
            :aria-pressed="visible"
            @click="visible = !visible"
        >
            <svg v-if="visible" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3 3 18 18M10.6 10.7a2 2 0 0 0 2.7 2.7M9.9 4.2A10.8 10.8 0 0 1 12 4c5.5 0 9 5.5 9 5.5a15 15 0 0 1-3 3.6M6.2 6.2C4.1 7.5 3 9.5 3 9.5S6.5 15 12 15c.7 0 1.4-.1 2-.3" />
            </svg>
            <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12s3.5-5.5 9-5.5 9 5.5 9 5.5-3.5 5.5-9 5.5S3 12 3 12Z" />
                <circle cx="12" cy="12" r="2.5" stroke-width="1.8" />
            </svg>
        </button>
    </div>
</template>
