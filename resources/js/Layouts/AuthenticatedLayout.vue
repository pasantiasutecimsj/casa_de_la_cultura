<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const navigationOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-gradient-to-r from-brand to-brand-dark shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-20 items-center justify-between">
                    <div class="flex h-full items-center gap-10">
                        <Link :href="route('dashboard')" aria-label="Inicio">
                            <ApplicationLogo />
                        </Link>

                        <div class="hidden h-full sm:flex">
                            <NavLink
                                :href="route('dashboard')"
                                :active="route().current('dashboard')"
                            >
                                Inicio
                            </NavLink>
                        </div>
                    </div>

                    <div class="hidden sm:block">
                        <Dropdown align="right">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-white/70"
                                >
                                    {{ $page.props.auth.user.name }}
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 0 1 1.414 0L10 10.586l3.293-3.293a1 1 0 1 1 1.414 1.414l-4 4a1 1 0 0 1-1.414 0l-4-4a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('user.edit')">
                                    Cambiar contraseña
                                </DropdownLink>
                                <div class="my-1 border-t border-gray-100"></div>
                                <DropdownLink :href="route('logout')" method="post" as="button" danger>
                                    Cerrar sesión
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                    <button
                        type="button"
                        class="rounded-md p-2 text-blue-100 hover:bg-white/10 hover:text-white sm:hidden"
                        :aria-expanded="navigationOpen"
                        aria-label="Abrir navegación"
                        @click="navigationOpen = !navigationOpen"
                    >
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path v-if="!navigationOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 18 12-12M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div v-show="navigationOpen" class="border-t border-white/10 pb-3 sm:hidden">
                <ResponsiveNavLink
                    :href="route('dashboard')"
                    :active="route().current('dashboard')"
                >
                    Inicio
                </ResponsiveNavLink>

                <div class="mt-2 border-t border-white/10 pt-2">
                    <p class="px-4 py-2 text-sm text-blue-100">
                        {{ $page.props.auth.user.name }}
                    </p>
                    <ResponsiveNavLink
                        :href="route('user.edit')"
                        :active="route().current('user.*')"
                    >
                        Cambiar contraseña
                    </ResponsiveNavLink>
                    <div class="my-2 border-t border-white/10"></div>
                    <ResponsiveNavLink
                        :href="route('logout')"
                        method="post"
                        as="button"
                        danger
                    >
                        Cerrar sesión
                    </ResponsiveNavLink>
                </div>
            </div>
        </nav>

        <header v-if="$slots.header" class="bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main>
            <slot />
        </main>
    </div>
</template>
