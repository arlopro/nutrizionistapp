<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Scale, Droplets, Smile, Zap, Moon } from 'lucide-vue-next';

const props = defineProps<{
    checkIn: any;
    measurementTypes: { value: string; label: string }[];
}>();

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
}

function measurementLabel(type: string) {
    return props.measurementTypes.find(m => m.value === type)?.label || type;
}

function ratingDots(value: number) {
    return value;
}
</script>

<template>
    <Head :title="`Monitoraggio ${formatDate(checkIn.date)}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('client.check-ins.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">Monitoraggio {{ formatDate(checkIn.date) }}</h1>
            </div>
        </template>

        <div class="max-w-2xl">
            <!-- Dati principali -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div v-if="checkIn.weight_kg" class="text-center">
                        <Scale class="h-5 w-5 mx-auto text-primary-400 mb-1" />
                        <span class="text-xl font-bold text-gray-900">{{ checkIn.weight_kg }}</span>
                        <span class="text-xs text-gray-500 block">kg</span>
                    </div>
                    <div v-if="checkIn.water_liters" class="text-center">
                        <Droplets class="h-5 w-5 mx-auto text-blue-400 mb-1" />
                        <span class="text-xl font-bold text-gray-900">{{ checkIn.water_liters }}</span>
                        <span class="text-xs text-gray-500 block">litri acqua</span>
                    </div>
                    <div v-if="checkIn.mood" class="text-center">
                        <Smile class="h-5 w-5 mx-auto text-yellow-400 mb-1" />
                        <span class="text-xl font-bold text-gray-900">{{ checkIn.mood }}/5</span>
                        <span class="text-xs text-gray-500 block">umore</span>
                    </div>
                    <div v-if="checkIn.energy_level" class="text-center">
                        <Zap class="h-5 w-5 mx-auto text-orange-400 mb-1" />
                        <span class="text-xl font-bold text-gray-900">{{ checkIn.energy_level }}/5</span>
                        <span class="text-xs text-gray-500 block">energia</span>
                    </div>
                    <div v-if="checkIn.sleep_quality" class="text-center">
                        <Moon class="h-5 w-5 mx-auto text-indigo-400 mb-1" />
                        <span class="text-xl font-bold text-gray-900">{{ checkIn.sleep_quality }}/5</span>
                        <span class="text-xs text-gray-500 block">sonno</span>
                    </div>
                </div>
            </div>

            <!-- Misure -->
            <div v-if="checkIn.measurements?.length > 0" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-3">Misure</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div v-for="m in checkIn.measurements" :key="m.id" class="rounded-lg bg-gray-50 px-4 py-2.5 text-center">
                        <span class="text-xs text-gray-500 block">{{ measurementLabel(m.measurement_type) }}</span>
                        <span class="text-lg font-semibold text-gray-900">{{ m.value_cm }} cm</span>
                    </div>
                </div>
            </div>

            <!-- Foto -->
            <div v-if="checkIn.photos?.length > 0" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-3">Foto</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div v-for="photo in checkIn.photos" :key="photo.id" class="rounded-lg overflow-hidden">
                        <img :src="`/storage/${photo.file_path}`" class="w-full h-40 object-cover" />
                        <p class="text-xs text-gray-500 text-center py-1">{{ photo.photo_type }}</p>
                    </div>
                </div>
            </div>

            <!-- Note -->
            <div v-if="checkIn.notes" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-2">Le mie note</h2>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ checkIn.notes }}</p>
            </div>

            <!-- Feedback nutrizionista -->
            <div v-if="checkIn.nutritionist_notes" class="rounded-2xl bg-primary-50 border border-primary-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-primary-900 mb-2">Feedback del nutrizionista</h2>
                <p class="text-sm text-primary-800 whitespace-pre-line">{{ checkIn.nutritionist_notes }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
