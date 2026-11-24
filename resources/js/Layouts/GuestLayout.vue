<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-gradient-to-r from-brand to-brand-dark shadow-sm">
            <div class="mx-auto flex h-20 max-w-7xl items-center px-4 sm:px-6 lg:px-8">
                <Link :href="route('home')" aria-label="Inicio">
                    <ApplicationLogo />
                </Link>
            </div>
        </nav>

        <main>
            <slot />
        </main>
    </div>
</template>
