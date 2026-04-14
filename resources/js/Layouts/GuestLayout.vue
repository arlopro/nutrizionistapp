<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen">
        <!-- Left side: decorative gradient -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary-500 via-primary-600 to-primary-800 items-center justify-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-20 left-10 w-72 h-72 bg-white rounded-full blur-3xl"></div>
                <div class="absolute bottom-20 right-10 w-96 h-96 bg-white rounded-full blur-3xl"></div>
            </div>
            <div class="relative z-10 text-center px-12">
                <ApplicationLogo class="mx-auto h-20 w-20 mb-8 drop-shadow-lg" />
                <h1 class="text-4xl font-bold text-white mb-4">NutrizionistApp</h1>
                <p class="text-primary-100 text-lg max-w-md">
                    La piattaforma per nutrizionisti per gestire clienti, piani alimentari e appuntamenti.
                </p>
            </div>
        </div>

        <!-- Right side: form -->
        <div class="flex w-full lg:w-1/2 flex-col items-center justify-center bg-gray-50 px-6 py-12">
            <div class="lg:hidden mb-8 text-center">
                <Link href="/">
                    <img src="/images/logo-nutrizionistapp.png" alt="NutrizionistApp" class="h-10 w-auto mx-auto" />
                </Link>
            </div>

            <div class="w-full max-w-md">
                <div class="rounded-2xl bg-white px-8 py-10 shadow-sm border border-gray-100">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
