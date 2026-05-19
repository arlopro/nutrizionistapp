<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    checkIns: any[];
}>();

// Most recent 8, newest first
const sessions = computed(() =>
    props.checkIns.filter(c => c.weight_kg || c.body_fat_percentage || c.measurements?.length > 0).slice(0, 8)
);

function measurementOf(ci: any, type: string) {
    return ci.measurements?.find((m: any) => m.measurement_type === type)?.value_cm ?? null;
}

// Delta between first and last session
const first = computed(() => sessions.value[sessions.value.length - 1] ?? null);
const last = computed(() => sessions.value[0] ?? null);

function delta(key: 'weight_kg' | 'body_fat_percentage' | 'lean_mass_kg') {
    if (!first.value || !last.value) return null;
    const a = Number(last.value[key]);
    const b = Number(first.value[key]);
    if (isNaN(a) || isNaN(b)) return null;
    return Math.round((a - b) * 10) / 10;
}

function deltaCircumference(type: string) {
    const a = measurementOf(last.value, type);
    const b = measurementOf(first.value, type);
    if (a == null || b == null) return null;
    return Math.round((Number(a) - Number(b)) * 10) / 10;
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: '2-digit' });
}

function deltaClass(v: number | null, positiveIsGood = false) {
    if (v === null || v === 0) return 'text-gray-400';
    if (positiveIsGood) return v > 0 ? 'text-green-600' : 'text-red-500';
    return v < 0 ? 'text-green-600' : 'text-red-500';
}
</script>

<template>
    <div class="space-y-4">
        <div v-if="sessions.length === 0" class="text-center py-8 text-sm text-gray-400">Nessuna misurazione disponibile</div>

        <template v-else>
            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="px-3 py-2.5 text-left text-xs font-medium uppercase text-gray-400">Data</th>
                            <th class="px-3 py-2.5 text-right text-xs font-medium uppercase text-gray-400">Peso</th>
                            <th class="px-3 py-2.5 text-right text-xs font-medium uppercase text-gray-400">M.G.</th>
                            <th class="px-3 py-2.5 text-right text-xs font-medium uppercase text-gray-400">M.M.</th>
                            <th class="px-3 py-2.5 text-right text-xs font-medium uppercase text-gray-400">Vita</th>
                            <th class="px-3 py-2.5 text-right text-xs font-medium uppercase text-gray-400">Fianchi</th>
                            <th class="px-3 py-2.5 text-right text-xs font-medium uppercase text-gray-400">Petto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="(ci, idx) in sessions" :key="ci.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <span class="font-medium text-gray-900">{{ formatDate(ci.date) }}</span>
                                <span v-if="idx === 0" class="ml-1.5 rounded-full bg-green-100 px-1.5 py-0.5 text-[10px] font-medium text-green-700">ultima</span>
                            </td>
                            <td class="px-3 py-2.5 text-right font-semibold text-gray-900">{{ ci.weight_kg ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-right text-gray-600">{{ ci.body_fat_percentage ? ci.body_fat_percentage + '%' : '—' }}</td>
                            <td class="px-3 py-2.5 text-right text-gray-600">{{ ci.lean_mass_kg ? ci.lean_mass_kg + ' kg' : '—' }}</td>
                            <td class="px-3 py-2.5 text-right text-gray-600">{{ measurementOf(ci, 'waist') ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-right text-gray-600">{{ measurementOf(ci, 'hips') ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-right text-gray-600">{{ measurementOf(ci, 'chest') ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Summary deltas -->
            <div v-if="sessions.length >= 2" class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <div v-if="deltaCircumference('waist') !== null" class="rounded-xl border border-gray-100 bg-gray-50 p-3 text-center">
                    <p class="text-xs text-gray-400">Vita</p>
                    <p :class="['text-lg font-bold', deltaClass(deltaCircumference('waist'))]">
                        {{ (deltaCircumference('waist') ?? 0) > 0 ? '+' : '' }}{{ deltaCircumference('waist') }} cm
                    </p>
                </div>
                <div v-if="deltaCircumference('hips') !== null" class="rounded-xl border border-gray-100 bg-gray-50 p-3 text-center">
                    <p class="text-xs text-gray-400">Fianchi</p>
                    <p :class="['text-lg font-bold', deltaClass(deltaCircumference('hips'))]">
                        {{ (deltaCircumference('hips') ?? 0) > 0 ? '+' : '' }}{{ deltaCircumference('hips') }} cm
                    </p>
                </div>
                <div v-if="delta('body_fat_percentage') !== null" class="rounded-xl border border-gray-100 bg-gray-50 p-3 text-center">
                    <p class="text-xs text-gray-400">M. Grassa</p>
                    <p :class="['text-lg font-bold', deltaClass(delta('body_fat_percentage'))]">
                        {{ (delta('body_fat_percentage') ?? 0) > 0 ? '+' : '' }}{{ delta('body_fat_percentage') }}%
                    </p>
                </div>
                <div v-if="delta('lean_mass_kg') !== null" class="rounded-xl border border-gray-100 bg-gray-50 p-3 text-center">
                    <p class="text-xs text-gray-400">M. Magra</p>
                    <p :class="['text-lg font-bold', deltaClass(delta('lean_mass_kg'), true)]">
                        {{ (delta('lean_mass_kg') ?? 0) > 0 ? '+' : '' }}{{ delta('lean_mass_kg') }} kg
                    </p>
                </div>
            </div>
        </template>
    </div>
</template>
