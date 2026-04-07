<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS, CategoryScale, LinearScale, PointElement,
    LineElement, Tooltip, Filler,
} from 'chart.js';
import { ArrowLeft, Scale, Droplets, Smile, Zap, Moon, User, TrendingDown, TrendingUp, Minus } from 'lucide-vue-next';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Filler);

const props = defineProps<{
    checkIn: any;
    weightHistory: { id: number; date: string; weight_kg: string | number }[];
    measurementTypes: { value: string; label: string }[];
}>();

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
}

function measurementLabel(type: string) {
    return props.measurementTypes.find(m => m.value === type)?.label || type;
}

const notesForm = useForm({
    nutritionist_notes: props.checkIn.nutritionist_notes || '',
});

function submitNotes() {
    notesForm.patch(route('nutritionist.check-ins.notes', props.checkIn.id), {
        preserveScroll: true,
    });
}

// Grafico peso con punto corrente evidenziato
const chartData = computed(() => {
    const currentIdx = props.weightHistory.findIndex(w => w.id === props.checkIn.id);
    return {
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
                pointRadius: props.weightHistory.map((_, i) => i === currentIdx ? 8 : 4),
                pointBackgroundColor: props.weightHistory.map((_, i) => i === currentIdx ? '#7c3aed' : '#22c55e'),
                pointBorderColor: props.weightHistory.map((_, i) => i === currentIdx ? '#7c3aed' : '#22c55e'),
                tension: 0.3,
                fill: true,
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: { label: (ctx: any) => `${ctx.parsed.y} kg` },
        },
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
        y: {
            grid: { color: '#f3f4f6' },
            ticks: { font: { size: 11 }, callback: (v: any) => `${v} kg` },
        },
    },
};

// Variazione rispetto al check-in precedente
const prevCheckIn = computed(() => {
    const idx = props.weightHistory.findIndex(w => w.id === props.checkIn.id);
    return idx > 0 ? props.weightHistory[idx - 1] : null;
});

const weightChange = computed(() => {
    if (!prevCheckIn.value || !props.checkIn.weight_kg) return null;
    return Math.round((Number(props.checkIn.weight_kg) - Number(prevCheckIn.value.weight_kg)) * 10) / 10;
});
</script>

<template>
    <Head :title="`Monitoraggio ${checkIn.client?.user?.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('nutritionist.check-ins.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Monitoraggio {{ formatDate(checkIn.date) }}</h1>
                    <p class="text-sm text-gray-500 flex items-center gap-1"><User class="h-3.5 w-3.5" /> {{ checkIn.client?.user?.name }}</p>
                </div>
            </div>
        </template>

        <div class="max-w-2xl">
            <!-- Dati principali -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div v-if="checkIn.weight_kg" class="text-center">
                        <Scale class="h-5 w-5 mx-auto text-primary-400 mb-1" />
                        <span class="text-xl font-bold text-gray-900">{{ checkIn.weight_kg }}</span>
                        <span class="text-xs text-gray-500 block">kg</span>
                        <!-- Variazione rispetto al precedente -->
                        <div v-if="weightChange !== null" class="mt-1 flex items-center justify-center gap-0.5">
                            <TrendingDown v-if="weightChange < 0" class="h-3 w-3 text-green-500" />
                            <TrendingUp v-else-if="weightChange > 0" class="h-3 w-3 text-red-400" />
                            <Minus v-else class="h-3 w-3 text-gray-400" />
                            <span :class="['text-xs font-medium', weightChange < 0 ? 'text-green-600' : weightChange > 0 ? 'text-red-500' : 'text-gray-400']">
                                {{ weightChange > 0 ? '+' : '' }}{{ weightChange }} kg
                            </span>
                        </div>
                    </div>
                    <div v-if="checkIn.water_liters" class="text-center">
                        <Droplets class="h-5 w-5 mx-auto text-blue-400 mb-1" />
                        <span class="text-xl font-bold text-gray-900">{{ checkIn.water_liters }}</span>
                        <span class="text-xs text-gray-500 block">litri</span>
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

            <!-- Grafico progressione peso (se ci sono almeno 2 misurazioni) -->
            <div v-if="weightHistory.length >= 2" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <div class="flex items-center gap-2 mb-4">
                    <TrendingDown class="h-5 w-5 text-gray-400" />
                    <h2 class="text-base font-semibold text-gray-900">Progressione peso</h2>
                    <span class="text-xs text-violet-600 bg-violet-50 rounded-full px-2 py-0.5 ml-auto">punto viola = questo monitoraggio</span>
                </div>
                <div class="relative h-48">
                    <Line :data="chartData" :options="chartOptions" />
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

            <!-- Note cliente -->
            <div v-if="checkIn.notes" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-2">Note del cliente</h2>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ checkIn.notes }}</p>
            </div>

            <!-- Feedback nutrizionista -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-3">Il tuo feedback</h2>
                <form @submit.prevent="submitNotes">
                    <textarea
                        v-model="notesForm.nutritionist_notes"
                        rows="4"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                        placeholder="Scrivi un commento o consiglio per il cliente..."
                    ></textarea>
                    <div class="mt-3">
                        <PrimaryButton :class="{ 'opacity-25': notesForm.processing }" :disabled="notesForm.processing">
                            Salva Feedback
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
