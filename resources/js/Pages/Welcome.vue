<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Bienvenido" />

    <GuestLayout>
        <section class="mx-auto flex min-h-[calc(66vh-5rem)] max-w-3xl flex-col items-center justify-center px-4 text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-brand">Acceso institucional</p>
            <h1 class="mt-3 text-4xl font-bold text-gray-900">
                {{ $page.props.app.name }}
            </h1>
            <p class="mt-4 max-w-xl text-lg text-gray-600">
                Inicie sesión para acceder al sistema.
            </p>
            <Link
                :href="route('login')"
                class="mt-8 rounded-md bg-brand px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2"
            >
                Iniciar sesión
            </Link>
        </section>
    </GuestLayout>
</template>
