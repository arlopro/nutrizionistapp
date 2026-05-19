<script setup lang="ts">
defineProps<{
    kcal?: number | null;
    carbsGrams?: number | null;
    proteinGrams?: number | null;
    fatGrams?: number | null;
    compact?: boolean;
}>();

function pct(grams: number | null | undefined, kcal: number | null | undefined) {
    if (!grams || !kcal) return null;
    return Math.round((grams * 4 / kcal) * 100);
}
</script>

<template>
    <div :class="['grid gap-2', compact ? 'grid-cols-4' : 'grid-cols-2 sm:grid-cols-4']">
        <!-- Calories -->
        <div class="rounded-xl bg-gray-900 p-3 text-white">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Calorie</p>
            <p class="text-2xl font-bold mt-0.5">{{ kcal ?? '—' }}</p>
            <p class="text-xs text-gray-400">kcal</p>
        </div>
        <!-- Carbs -->
        <div class="rounded-xl bg-amber-50 border border-amber-100 p-3">
            <div class="flex justify-between items-start">
                <p class="text-xs font-medium uppercase tracking-wide text-amber-600">Carbo</p>
                <span v-if="kcal && carbsGrams" class="text-xs font-medium text-amber-500">{{ pct(carbsGrams, kcal) }}%</span>
            </div>
            <p class="text-2xl font-bold text-amber-700 mt-0.5">{{ carbsGrams ?? '—' }}<span class="text-sm font-normal text-amber-500">g</span></p>
            <div v-if="kcal && carbsGrams" class="mt-1.5 h-1 rounded-full bg-amber-100">
                <div class="h-full rounded-full bg-amber-400" :style="{ width: pct(carbsGrams, kcal) + '%' }"></div>
            </div>
        </div>
        <!-- Protein -->
        <div class="rounded-xl bg-green-50 border border-green-100 p-3">
            <div class="flex justify-between items-start">
                <p class="text-xs font-medium uppercase tracking-wide text-green-600">Proteine</p>
                <span v-if="kcal && proteinGrams" class="text-xs font-medium text-green-500">{{ pct(proteinGrams, kcal) }}%</span>
            </div>
            <p class="text-2xl font-bold text-green-700 mt-0.5">{{ proteinGrams ?? '—' }}<span class="text-sm font-normal text-green-500">g</span></p>
            <div v-if="kcal && proteinGrams" class="mt-1.5 h-1 rounded-full bg-green-100">
                <div class="h-full rounded-full bg-green-500" :style="{ width: pct(proteinGrams, kcal) + '%' }"></div>
            </div>
        </div>
        <!-- Fat -->
        <div class="rounded-xl bg-indigo-50 border border-indigo-100 p-3">
            <div class="flex justify-between items-start">
                <p class="text-xs font-medium uppercase tracking-wide text-indigo-600">Grassi</p>
                <span v-if="kcal && fatGrams" class="text-xs font-medium text-indigo-500">{{ Math.round((fatGrams * 9 / (kcal || 1)) * 100) }}%</span>
            </div>
            <p class="text-2xl font-bold text-indigo-700 mt-0.5">{{ fatGrams ?? '—' }}<span class="text-sm font-normal text-indigo-400">g</span></p>
            <div v-if="kcal && fatGrams" class="mt-1.5 h-1 rounded-full bg-indigo-100">
                <div class="h-full rounded-full bg-indigo-400" :style="{ width: Math.round((fatGrams * 9 / (kcal || 1)) * 100) + '%' }"></div>
            </div>
        </div>
    </div>
</template>
