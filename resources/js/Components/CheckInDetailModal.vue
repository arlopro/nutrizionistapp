<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { X, ExternalLink } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    checkIn: any;
}>();

const emit = defineEmits<{ close: [] }>();

const lightboxUrl = ref<string | null>(null);

const measurementLabels: Record<string, string> = {
    waist: 'Vita', hips: 'Fianchi', chest: 'Petto',
    arm_left: 'Braccio sx', arm_right: 'Braccio dx',
    thigh_left: 'Coscia sx', thigh_right: 'Coscia dx',
    calf: 'Polpaccio', neck: 'Collo',
};

const photoTypeLabels: Record<string, string> = {
    front: 'Fronte', side: 'Lato', back: 'Retro', other: 'Altro',
};

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}

function moodLabel(m: number) {
    return ['Pessimo', 'Scarso', 'Nella media', 'Buono', 'Ottimo'][m - 1] ?? String(m);
}
</script>

<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="emit('close')">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Monitoraggio</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(checkIn.date) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('nutritionist.check-ins.show', checkIn.id)"
                            class="inline-flex items-center gap-1 rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition"
                        >
                            <ExternalLink class="h-3.5 w-3.5" /> Apri pagina
                        </Link>
                        <button @click="emit('close')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 transition">
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto p-5 space-y-5">
                    <!-- Main metrics -->
                    <div class="grid grid-cols-3 gap-3">
                        <div v-if="checkIn.weight_kg" class="rounded-xl bg-gray-50 p-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">Peso</p>
                            <p class="text-xl font-bold text-gray-900">{{ checkIn.weight_kg }}<span class="text-sm font-normal text-gray-400"> kg</span></p>
                        </div>
                        <div v-if="checkIn.body_fat_percentage" class="rounded-xl bg-gray-50 p-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">Massa grassa</p>
                            <p class="text-xl font-bold text-gray-900">{{ checkIn.body_fat_percentage }}<span class="text-sm font-normal text-gray-400">%</span></p>
                        </div>
                        <div v-if="checkIn.lean_mass_kg" class="rounded-xl bg-gray-50 p-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">Massa magra</p>
                            <p class="text-xl font-bold text-gray-900">{{ checkIn.lean_mass_kg }}<span class="text-sm font-normal text-gray-400"> kg</span></p>
                        </div>
                        <div v-if="checkIn.mood" class="rounded-xl bg-amber-50 p-3 text-center">
                            <p class="text-xs text-amber-500 mb-1">Umore</p>
                            <p class="text-sm font-semibold text-amber-700">{{ checkIn.mood }}/5 — {{ moodLabel(checkIn.mood) }}</p>
                        </div>
                    </div>

                    <!-- Circumferences -->
                    <div v-if="checkIn.measurements?.length" class="space-y-1.5">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Circonferenze</p>
                        <div class="grid grid-cols-3 gap-2">
                            <div v-for="m in checkIn.measurements" :key="m.id" class="rounded-lg bg-gray-50 px-3 py-2">
                                <p class="text-xs text-gray-400">{{ measurementLabels[m.measurement_type] ?? m.measurement_type }}</p>
                                <p class="text-sm font-semibold text-gray-900">{{ m.value_cm }} cm</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="checkIn.notes" class="rounded-xl bg-blue-50 px-3 py-2.5">
                        <p class="text-xs font-medium text-blue-600 mb-0.5">Note cliente</p>
                        <p class="text-sm text-gray-700">{{ checkIn.notes }}</p>
                    </div>
                    <div v-if="checkIn.nutritionist_notes" class="rounded-xl bg-green-50 px-3 py-2.5">
                        <p class="text-xs font-medium text-green-600 mb-0.5">Note nutrizionista</p>
                        <p class="text-sm text-gray-700">{{ checkIn.nutritionist_notes }}</p>
                    </div>

                    <!-- Photos -->
                    <div v-if="checkIn.photos?.length" class="space-y-2">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Foto</p>
                        <div class="grid grid-cols-3 gap-2">
                            <div
                                v-for="photo in checkIn.photos"
                                :key="photo.id"
                                class="relative rounded-xl overflow-hidden bg-gray-100 aspect-[3/4] cursor-pointer group"
                                @click="lightboxUrl = photo.url"
                            >
                                <img :src="photo.url" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-200" />
                                <div class="absolute bottom-0 left-0 right-0 bg-black/40 text-white text-[9px] text-center py-0.5">
                                    {{ photoTypeLabels[photo.photo_type] ?? photo.photo_type }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lightbox -->
        <div v-if="lightboxUrl" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/85 p-4" @click.self="lightboxUrl = null">
            <button @click="lightboxUrl = null" class="absolute top-4 right-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20 transition">
                <X class="h-5 w-5" />
            </button>
            <img :src="lightboxUrl" class="max-h-[85vh] max-w-full rounded-xl shadow-2xl object-contain" />
        </div>
    </Teleport>
</template>
