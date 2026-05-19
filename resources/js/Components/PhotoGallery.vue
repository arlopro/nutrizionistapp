<script setup lang="ts">
import { ref, computed } from 'vue';
import { X, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps<{
    checkIns: any[];
}>();

// Sessions with photos, newest first
const sessions = computed(() =>
    props.checkIns.filter(c => c.photos?.length > 0)
);

const selectedSession = ref<any>(null);
const lightboxPhoto = ref<string | null>(null);

function openLightbox(url: string) {
    lightboxPhoto.value = url;
}

function photoTypeLabel(t: string) {
    return { front: 'Fronte', side: 'Lato', back: 'Retro', other: 'Altro' }[t] ?? t;
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: 'numeric' });
}

const displaySession = computed(() => selectedSession.value ?? sessions.value[0] ?? null);

function formatDateShort(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: '2-digit' });
}
</script>

<template>
    <div class="space-y-4">
        <!-- Session selector -->
        <div v-if="sessions.length > 0" class="flex flex-wrap gap-2">
            <button
                v-for="s in sessions"
                :key="s.id"
                @click="selectedSession = s"
                :class="[
                    'flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium border transition',
                    displaySession?.id === s.id
                        ? 'bg-gray-900 text-white border-gray-900'
                        : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50',
                ]"
            >
                {{ formatDateShort(s.date) }}
                <span v-if="s.weight_kg" class="text-xs opacity-70">{{ s.weight_kg }}kg</span>
            </button>
        </div>

        <!-- Photo grid -->
        <div v-if="displaySession" class="grid grid-cols-3 gap-3">
            <div
                v-for="photo in displaySession.photos"
                :key="photo.id"
                class="relative rounded-xl overflow-hidden bg-gray-100 aspect-[3/4] cursor-pointer group"
                @click="openLightbox(photo.url)"
            >
                <img :src="photo.url" :alt="photoTypeLabel(photo.photo_type)" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-200" />
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/50 to-transparent px-2 py-1.5">
                    <p class="text-xs font-medium text-white">{{ photoTypeLabel(photo.photo_type) }}</p>
                </div>
            </div>
        </div>

        <div v-else class="flex items-center justify-center h-40 rounded-xl bg-gray-50 text-sm text-gray-400">
            Nessuna foto disponibile
        </div>

        <!-- Footer -->
        <p v-if="displaySession" class="text-xs text-gray-400">
            Sessione del {{ formatDate(displaySession.date) }}
            <span v-if="displaySession.weight_kg"> · peso {{ displaySession.weight_kg }} kg</span>
        </p>
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
