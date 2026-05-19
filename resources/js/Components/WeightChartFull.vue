<script setup lang="ts">
import { computed, ref } from 'vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Tooltip,
    Filler,
    Legend,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Filler, Legend);

const props = defineProps<{
    checkIns: { date: string; weight_kg: string | number | null }[];
    goalWeight?: number | null;
    initialWeight?: number | null;
}>();

type Range = '1M' | '3M' | '6M' | '1A';
const range = ref<Range>('3M');

const filtered = computed(() => {
    // Backend sends desc (newest first); reverse to get chronological order for the chart
    const all = [...props.checkIns].reverse().filter(c => c.weight_kg != null);
    if (!all.length) return [];
    const now = new Date();
    const cutoff = new Date(now);
    if (range.value === '1M') cutoff.setMonth(now.getMonth() - 1);
    else if (range.value === '3M') cutoff.setMonth(now.getMonth() - 3);
    else if (range.value === '6M') cutoff.setMonth(now.getMonth() - 6);
    else cutoff.setFullYear(now.getFullYear() - 1);
    return all.filter(c => new Date(c.date) >= cutoff);
});

const latestWeight = computed(() => {
    const a = props.checkIns.filter(c => c.weight_kg != null);
    return a.length ? Number(a[0].weight_kg) : null;
});

const delta = computed(() => {
    const data = filtered.value;
    if (data.length < 2) return null;
    // filtered is chronological: last = newest, first = oldest
    return Math.round((Number(data[data.length - 1].weight_kg) - Number(data[0].weight_kg)) * 10) / 10;
});

const chartData = computed(() => {
    const datasets: any[] = [
        {
            label: 'Peso',
            data: filtered.value.map(w => Number(w.weight_kg)),
            borderColor: '#16a34a',
            backgroundColor: 'rgba(22,163,74,0.06)',
            borderWidth: 2,
            pointRadius: 4,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#16a34a',
            pointBorderWidth: 2,
            tension: 0.3,
            fill: true,
        },
    ];

    if (props.goalWeight && filtered.value.length > 0) {
        datasets.push({
            label: 'Obiettivo',
            data: filtered.value.map(() => props.goalWeight),
            borderColor: '#6366f1',
            borderWidth: 1.5,
            borderDash: [5, 4],
            pointRadius: 0,
            fill: false,
            tension: 0,
        });
    }

    return {
        labels: filtered.value.map(w =>
            new Date(w.date).toLocaleDateString('it-IT', { day: '2-digit', month: 'short' })
        ),
        datasets,
    };
});

const options = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index' as const, intersect: false },
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx: any) => `${ctx.dataset.label}: ${ctx.parsed.y} kg`,
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { font: { size: 11 }, maxRotation: 0 },
        },
        y: {
            grid: { color: '#f3f4f6' },
            ticks: {
                font: { size: 11 },
                callback: (v: any) => `${parseFloat(Number(v).toFixed(1))} kg`,
            },
        },
    },
}));
</script>

<template>
    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
        <!-- Header -->
        <div class="flex items-start justify-between mb-1">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400 mb-0.5">Andamento peso</p>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-bold text-gray-900">{{ latestWeight ?? '—' }}</span>
                    <span class="text-sm text-gray-500">kg</span>
                    <span v-if="delta !== null" :class="['text-sm font-semibold', delta < 0 ? 'text-green-600' : delta > 0 ? 'text-red-500' : 'text-gray-500']">
                        {{ delta > 0 ? '+' : '' }}{{ delta }} kg nel periodo
                    </span>
                </div>
                <p v-if="goalWeight || initialWeight" class="text-xs text-gray-400 mt-0.5">
                    <span v-if="goalWeight">Obiettivo: {{ goalWeight }} kg</span>
                    <span v-if="goalWeight && initialWeight"> · </span>
                    <span v-if="initialWeight">Iniziale: {{ initialWeight }} kg</span>
                </p>
            </div>
            <!-- Range selector -->
            <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                <button v-for="r in (['1M','3M','6M','1A'] as Range[])" :key="r"
                    @click="range = r"
                    :class="['px-2.5 py-1 text-xs font-medium transition', range === r ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-50']"
                >{{ r }}</button>
            </div>
        </div>

        <!-- Chart -->
        <div v-if="filtered.length >= 2" class="relative h-52 mt-4">
            <Line :data="chartData" :options="options" />
        </div>
        <div v-else class="flex items-center justify-center h-32 text-sm text-gray-400">
            Dati insufficienti per il periodo selezionato
        </div>
    </div>
</template>
