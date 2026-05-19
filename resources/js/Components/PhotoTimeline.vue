<script setup lang="ts">
import { ref } from 'vue';
import { X } from 'lucide-vue-next';

defineProps<{
    checkIns: any[];
}>();

const lightboxPhoto = ref<string | null>(null);

function photoTypeLabel(t: string) {
    return { front: 'Fronte', side: 'Lato', back: 'Retro', other: 'Altro' }[t] ?? t;
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: 'numeric' });
}

function moodEmoji(m: number) {
    return ['😞', '😕', '😐', '🙂', '😊'][m - 1] ?? '';
}
</script>

<template>
    <div class="relative">
        <div v-if="checkIns.length === 0" class="text-center py-12 text-sm text-gray-400">Nessun monitoraggio disponibile</div>

        <div class="space-y-8">
            <div v-for="(ci, idx) in checkIns" :key="ci.id" class="relative flex gap-4">
                <!-- Timeline line + dot -->
                <div class="flex flex-col items-center">
                    <div :class="['h-3 w-3 rounded-full border-2 border-white ring-2 mt-0.5 flex-shrink-0', idx === 0 ? 'ring-green-500 bg-green-500' : 'ring-gray-300 bg-white']"></div>
                    <div v-if="idx < checkIns.length - 1" class="mt-1 w-px flex-1 bg-gray-200"></div>
                </div>

                <!-- Content -->
                <div class="flex-1 pb-2">
                    <!-- Date + badge -->
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-sm font-semibold text-gray-900">{{ formatDate(ci.date) }}</span>
                        <span v-if="idx === 0" class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">ultimo</span>
                    </div>

                    <div class="flex flex-col lg:flex-row gap-4">
                        <!-- Metrics -->
                        <div class="flex-1">
                            <div class="flex flex-wrap gap-4 text-sm mb-2">
                                <div v-if="ci.weight_kg">
                                    <p class="text-xs text-gray-400 uppercase">Peso</p>
                                    <p class="font-semibold text-gray-900">{{ ci.weight_kg }} kg</p>
                                </div>
                                <div v-if="ci.body_fat_percentage">
                                    <p class="text-xs text-gray-400 uppercase">M.G.</p>
                                    <p class="font-semibold text-gray-900">{{ ci.body_fat_percentage }}%</p>
                                </div>
                                <template v-for="m in ci.measurements" :key="m.id">
                                    <div v-if="['waist','hips','chest'].includes(m.measurement_type)">
                                        <p class="text-xs text-gray-400 uppercase">{{ ({ waist: 'Vita', hips: 'Fianchi', chest: 'Petto' } as Record<string,string>)[m.measurement_type] }}</p>
                                        <p class="font-semibold text-gray-900">{{ m.value_cm }} cm</p>
                                    </div>
                                </template>
                            </div>
                            <p v-if="ci.notes" class="text-sm text-gray-500 italic">{{ ci.notes }}</p>
                        </div>

                        <!-- Photos -->
                        <div v-if="ci.photos?.length" class="flex gap-2">
                            <div
                                v-for="photo in ci.photos.slice(0, 3)"
                                :key="photo.id"
                                class="relative w-20 rounded-lg overflow-hidden bg-gray-100 aspect-[3/4] cursor-pointer group"
                                @click="lightboxPhoto = photo.url"
                            >
                                <img :src="photo.url" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-200" />
                                <div class="absolute bottom-0 left-0 right-0 text-center bg-black/40 text-white text-[9px] py-0.5">
                                    {{ photoTypeLabel(photo.photo_type) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox -->
    <Teleport to="body">
        <div v-if="lightboxPhoto" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" @click.self="lightboxPhoto = null">
            <button @click="lightboxPhoto = null" class="absolute top-4 right-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20 transition">
                <X class="h-5 w-5" />
            </button>
            <img :src="lightboxPhoto" class="max-h-[85vh] max-w-full rounded-xl shadow-2xl object-contain" />
        </div>
    </Teleport>
</template>
