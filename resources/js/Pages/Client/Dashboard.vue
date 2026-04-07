<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import WeightChart from '@/Components/WeightChart.vue';
import { Head } from '@inertiajs/vue3';
import { UtensilsCrossed, CalendarDays, Scale, ClipboardCheck, TrendingDown, TrendingUp, Minus, Activity } from 'lucide-vue-next';

const props = defineProps<{
    activePlan: any;
    lastCheckIn: any;
    weightHistory: any[];
    nextAppointment: any;
    bmi: number | null;
    bmiCategory: string | null;
}>();

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('it-IT', { day: 'numeric', month: 'long', year: 'numeric' });
}

function formatDateTime(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('it-IT', { day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' });
}

const weightTrend = (() => {
    if (!props.weightHistory || props.weightHistory.length < 2) return null;
    const last = props.weightHistory[props.weightHistory.length - 1];
    const prev = props.weightHistory[props.weightHistory.length - 2];
    const diff = Number(last.weight_kg) - Number(prev.weight_kg);
    return { diff: Math.abs(diff).toFixed(1), direction: diff > 0 ? 'up' : diff < 0 ? 'down' : 'same' };
})();

function bmiColor(category: string | null) {
    if (!category) return 'text-gray-500';
    if (category === 'Normopeso') return 'text-green-600';
    if (category === 'Sovrappeso') return 'text-amber-600';
    if (category === 'Obesità') return 'text-red-600';
    return 'text-blue-600'; // Sottopeso
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-900">La mia Dashboard</h1>
        </template>

        <!-- Stats cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Current weight -->
            <div class="rounded-2xl bg-white border border-gray-100 p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-primary-50 p-3">
                        <Scale class="h-6 w-6 text-primary-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Peso attuale</p>
                        <div class="flex items-baseline gap-1">
                            <p class="text-2xl font-bold text-gray-900">
                                {{ lastCheckIn?.weight_kg || '--' }}
                            </p>
                            <span class="text-sm text-gray-400">kg</span>
                        </div>
                    </div>
                </div>
                <div v-if="weightTrend" class="mt-3 flex items-center gap-1 text-xs">
                    <component
                        :is="weightTrend.direction === 'down' ? TrendingDown : weightTrend.direction === 'up' ? TrendingUp : Minus"
                        :class="['h-3.5 w-3.5', weightTrend.direction === 'down' ? 'text-green-500' : weightTrend.direction === 'up' ? 'text-orange-500' : 'text-gray-400']"
                    />
                    <span :class="weightTrend.direction === 'down' ? 'text-green-600' : weightTrend.direction === 'up' ? 'text-orange-600' : 'text-gray-500'">
                        {{ weightTrend.diff }} kg rispetto all'ultimo monitoraggio
                    </span>
                </div>
            </div>

            <!-- BMI -->
            <div class="rounded-2xl bg-white border border-gray-100 p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-indigo-50 p-3">
                        <Activity class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">BMI</p>
                        <div class="flex items-baseline gap-1.5">
                            <p class="text-2xl font-bold text-gray-900">{{ bmi ?? '--' }}</p>
                            <span v-if="bmiCategory" :class="['text-xs font-medium', bmiColor(bmiCategory)]">{{ bmiCategory }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active plan -->
            <div class="rounded-2xl bg-white border border-gray-100 p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-green-50 p-3">
                        <UtensilsCrossed class="h-6 w-6 text-green-600" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-gray-500">Piano attivo</p>
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            {{ activePlan?.title || 'Nessun piano attivo' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Next appointment -->
            <div class="rounded-2xl bg-white border border-gray-100 p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-blue-50 p-3">
                        <CalendarDays class="h-6 w-6 text-blue-600" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-gray-500">Prossimo appuntamento</p>
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            {{ nextAppointment ? formatDateTime(nextAppointment.starts_at) : 'Nessuno' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weight chart -->
        <div v-if="weightHistory.length >= 2" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
            <div class="flex items-center gap-2 mb-4">
                <TrendingDown class="h-5 w-5 text-gray-400" />
                <h2 class="text-base font-semibold text-gray-900">Andamento peso</h2>
                <span class="text-xs text-gray-400 ml-1">(ultimi {{ weightHistory.length }} monitoraggi)</span>
            </div>
            <WeightChart :weight-history="weightHistory" />
        </div>

        <!-- Last check-in + CTA -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-purple-50 p-3">
                        <ClipboardCheck class="h-6 w-6 text-purple-600" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-gray-500">Ultimo monitoraggio</p>
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            {{ lastCheckIn ? formatDate(lastCheckIn.date) : 'Nessuno' }}
                        </p>
                        <p v-if="lastCheckIn?.weight_kg" class="text-xs text-gray-400">{{ lastCheckIn.weight_kg }} kg</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-gradient-to-r from-primary-500 to-primary-600 p-6 text-white shadow-lg shadow-primary-500/20 flex flex-col justify-between">
                <h2 class="text-base font-semibold mb-1">Benvenuto nella tua area personale</h2>
                <p class="text-primary-100 text-sm">Visualizza il piano, inserisci i monitoraggi e consulta gli appuntamenti.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
