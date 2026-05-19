<script setup lang="ts">
import { computed } from 'vue';
import SubTabs from '@/Components/SubTabs.vue';
import PhotoGallery from '@/Components/PhotoGallery.vue';
import PhotoTimeline from '@/Components/PhotoTimeline.vue';
import PhotoCompare from '@/Components/PhotoCompare.vue';
import MeasurementsTable from '@/Components/MeasurementsTable.vue';
import { LayoutGrid, AlignJustify, GitCompare, Download } from 'lucide-vue-next';

const props = defineProps<{
    client: any;
    sub: string;
}>();

const emit = defineEmits<{
    newMeasurement: [];
    changeSub: [key: string];
}>();

const subTabs = [
    { key: 'galleria', label: 'Galleria & misure', icon: LayoutGrid },
    { key: 'timeline', label: 'Timeline', icon: AlignJustify },
    { key: 'confronto', label: 'Confronto', icon: GitCompare },
];

const checkIns = computed(() => props.client.check_ins ?? []);
</script>

<template>
    <div class="space-y-5">
        <!-- Sub-tab bar + actions -->
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <SubTabs :tabs="subTabs" :active="sub" @change="emit('changeSub', $event)" />
            <div class="flex items-center gap-2">
                <button class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                    <Download class="h-3.5 w-3.5" /> Export PDF
                </button>
                <button
                    @click="emit('newMeasurement')"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-700 transition"
                >
                    + Nuova misurazione
                </button>
            </div>
        </div>

        <!-- Galleria & misure -->
        <div v-if="sub === 'galleria'" class="grid gap-5 lg:grid-cols-2">
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <LayoutGrid class="h-4 w-4 text-gray-400" /> Foto progressi
                </p>
                <PhotoGallery :check-ins="checkIns" />
            </div>
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="i-lucide-diamond h-4 w-4 text-gray-400"></span> Storico misure
                </p>
                <MeasurementsTable :check-ins="checkIns" />
            </div>
        </div>

        <!-- Timeline -->
        <div v-else-if="sub === 'timeline'" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <AlignJustify class="h-4 w-4 text-gray-400" /> Timeline cronologica
            </p>
            <PhotoTimeline :check-ins="checkIns" />
        </div>

        <!-- Confronto -->
        <div v-else-if="sub === 'confronto'" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-gray-900 mb-5 flex items-center gap-2">
                <GitCompare class="h-4 w-4 text-gray-400" /> Confronto before / after
            </p>
            <PhotoCompare :check-ins="checkIns" />
        </div>
    </div>
</template>
