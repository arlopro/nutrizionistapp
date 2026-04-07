<script setup lang="ts">
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Filler,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Filler);

const props = defineProps<{
    weightHistory: { date: string; weight_kg: string | number }[];
}>();

const chartData = computed(() => ({
    labels: props.weightHistory.map(w =>
        new Date(w.date).toLocaleDateString('it-IT', { day: '2-digit', month: 'short' })
    ),
    datasets: [
        {
            label: 'Peso (kg)',
            data: props.weightHistory.map(w => Number(w.weight_kg)),
            borderColor: '#22c55e',
            backgroundColor: 'rgba(34,197,94,0.08)',
            borderWidth: 2,
            pointRadius: 4,
            pointBackgroundColor: '#22c55e',
            tension: 0.3,
            fill: true,
        },
    ],
}));

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx: any) => `${ctx.parsed.y} kg`,
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { font: { size: 11 } },
        },
        y: {
            grid: { color: '#f3f4f6' },
            ticks: {
                font: { size: 11 },
                callback: (v: any) => `${v} kg`,
            },
        },
    },
};
</script>

<template>
    <div class="relative h-48">
        <Line :data="chartData" :options="options" />
    </div>
</template>
