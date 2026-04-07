<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowLeft, Edit, Trash2, FlaskConical, TrendingUp, TrendingDown, Minus } from 'lucide-vue-next';

const props = defineProps<{
    labResult: any;
    history: any[];
}>();

const markers = [
    { key: 'glucose', label: 'Glicemia', unit: 'mg/dL', low: 70, high: 100 },
    { key: 'hba1c', label: 'HbA1c', unit: '%', low: 0, high: 5.7 },
    { key: 'total_cholesterol', label: 'Colesterolo Totale', unit: 'mg/dL', low: 0, high: 200 },
    { key: 'hdl_cholesterol', label: 'Colesterolo HDL', unit: 'mg/dL', low: 40, high: 999 },
    { key: 'ldl_cholesterol', label: 'Colesterolo LDL', unit: 'mg/dL', low: 0, high: 100 },
    { key: 'triglycerides', label: 'Trigliceridi', unit: 'mg/dL', low: 0, high: 150 },
    { key: 'creatinine', label: 'Creatinina', unit: 'mg/dL', low: 0.6, high: 1.2 },
    { key: 'tsh', label: 'TSH', unit: 'mUI/L', low: 0.4, high: 4.0 },
    { key: 'crp', label: 'PCR', unit: 'mg/L', low: 0, high: 5 },
    { key: 'zonulin', label: 'Zonulina', unit: 'ng/mL', low: 0, high: 48 },
    { key: 'calprotectin', label: 'Calprotectina', unit: 'µg/g', low: 0, high: 50 },
] as const;

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
}

function rangeStatus(key: string, value: number | null) {
    if (value === null || value === undefined) return 'none';
    const m = markers.find(mk => mk.key === key);
    if (!m) return 'none';
    if (value < m.low) return 'low';
    if (value > m.high) return 'high';
    return 'normal';
}

function rangeColor(status: string) {
    return {
        normal: 'bg-green-50 text-green-700 border-green-200',
        high: 'bg-red-50 text-red-700 border-red-200',
        low: 'bg-amber-50 text-amber-700 border-amber-200',
        none: 'bg-gray-50 text-gray-400 border-gray-100',
    }[status] || 'bg-gray-50 text-gray-400 border-gray-100';
}

function trend(key: string) {
    const vals = props.history.filter((h: any) => h[key] !== null).map((h: any) => ({ date: h.date, val: Number(h[key]) }));
    if (vals.length < 2) return 'stable';
    const last = vals[vals.length - 1].val;
    const prev = vals[vals.length - 2].val;
    if (last > prev) return 'up';
    if (last < prev) return 'down';
    return 'stable';
}

const deleting = ref(false);
function destroy() {
    if (!confirm('Eliminare questo esame?')) return;
    deleting.value = true;
    router.delete(route('nutritionist.lab-results.destroy', props.labResult.id));
}

const presentMarkers = markers.filter(m => props.labResult[m.key] !== null && props.labResult[m.key] !== undefined);
</script>

<template>
    <Head :title="`Esame – ${labResult.client?.user?.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('nutritionist.lab-results.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">Esame Ematochimico</h1>
                        <p class="text-sm text-gray-500">{{ labResult.client?.user?.name }} &middot; {{ formatDate(labResult.date) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('nutritionist.lab-results.edit', labResult.id)" class="inline-flex items-center gap-1.5 rounded-xl bg-white border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <Edit class="h-4 w-4" /> Modifica
                    </Link>
                    <button @click="destroy" :disabled="deleting" class="inline-flex items-center gap-1.5 rounded-xl bg-white border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition disabled:opacity-50">
                        <Trash2 class="h-4 w-4" /> Elimina
                    </button>
                </div>
            </div>
        </template>

        <!-- Lab info -->
        <div v-if="labResult.lab_name || labResult.notes" class="mb-6 rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
            <p v-if="labResult.lab_name" class="text-sm text-gray-500">Laboratorio: <span class="font-medium text-gray-700">{{ labResult.lab_name }}</span></p>
            <p v-if="labResult.notes" class="text-sm text-gray-600 mt-2">{{ labResult.notes }}</p>
        </div>

        <!-- Empty markers state -->
        <div v-if="presentMarkers.length === 0" class="rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm">
            <FlaskConical class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun valore inserito</h3>
            <p class="text-sm text-gray-500">Modifica l'esame per aggiungere i valori ematochimici.</p>
        </div>

        <!-- Markers grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="m in presentMarkers"
                :key="m.key"
                :class="['rounded-2xl border p-5 transition', rangeColor(rangeStatus(m.key, Number(labResult[m.key])))]"
            >
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium">{{ m.label }}</span>
                    <component
                        :is="trend(m.key) === 'up' ? TrendingUp : trend(m.key) === 'down' ? TrendingDown : Minus"
                        class="h-4 w-4 opacity-50"
                    />
                </div>
                <div class="text-2xl font-bold">{{ labResult[m.key] }}</div>
                <div class="text-xs mt-1 opacity-70">{{ m.unit }} · Rif: {{ m.low }}–{{ m.high === 999 ? '∞' : m.high }}</div>
            </div>
        </div>

        <!-- History table -->
        <div v-if="history.length > 1" class="mt-8">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Storico Esami</h2>
            <div class="overflow-x-auto rounded-2xl bg-white border border-gray-100 shadow-sm">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Data</th>
                            <th v-for="m in presentMarkers" :key="m.key" class="px-4 py-3 text-right font-medium text-gray-500">{{ m.label }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in [...history].reverse()" :key="row.id" :class="['border-b border-gray-50', row.id === labResult.id ? 'bg-primary-50/30 font-medium' : '']">
                            <td class="px-4 py-3 text-gray-700">{{ formatDate(row.date) }}</td>
                            <td v-for="m in presentMarkers" :key="m.key" class="px-4 py-3 text-right text-gray-600">
                                {{ row[m.key] ?? '–' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
