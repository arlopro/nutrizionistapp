<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import MacroSummary from '@/Components/MacroSummary.vue';
import { UtensilsCrossed, Download, Edit, Send, Plus, Copy, BookOpen, ChevronRight } from 'lucide-vue-next';

const props = defineProps<{
    client: any;
    activePlan: any;
}>();

const inactivePlans = computed(() =>
    (props.client.nutritional_plans ?? []).filter((p: any) => p.status !== 'active')
);

const mealTypeLabels: Record<string, string> = {
    breakfast: 'Colazione',
    morning_snack: 'Spuntino mattina',
    lunch: 'Pranzo',
    afternoon_snack: 'Spuntino pomeriggio',
    dinner: 'Cena',
    evening_snack: 'Spuntino sera',
};

function formatDate(d: string | null) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: 'numeric' });
}

// Group meals by type and sum calories
function mealSummary(plan: any) {
    if (!plan?.meals?.length) return [];
    const byType: Record<string, { kcal: number; count: number }> = {};
    for (const meal of plan.meals) {
        if (meal.day_of_week !== null && meal.day_of_week !== 1) continue; // show only first day
        const t = meal.meal_type ?? 'other';
        if (!byType[t]) byType[t] = { kcal: 0, count: 0 };
        byType[t].kcal += meal.total_calories ?? 0;
        byType[t].count++;
    }
    // If no day_of_week grouping, just show unique meal types
    if (!Object.keys(byType).length) {
        const unique: Record<string, number> = {};
        for (const meal of plan.meals) {
            const t = meal.meal_type ?? 'other';
            if (!unique[t]) unique[t] = meal.total_calories ?? 0;
        }
        return Object.entries(unique).map(([type, kcal]) => ({ type, kcal }));
    }
    return Object.entries(byType).map(([type, { kcal }]) => ({ type, kcal }));
}

function statusLabel(s: string) {
    return { draft: 'Bozza', active: 'Attivo', completed: 'Completato', archived: 'Archiviato' }[s] ?? s;
}
function statusCls(s: string) {
    return {
        active: 'bg-green-100 text-green-700',
        draft: 'bg-yellow-100 text-yellow-700',
        completed: 'bg-blue-100 text-blue-700',
        archived: 'bg-gray-100 text-gray-500',
    }[s] ?? 'bg-gray-100 text-gray-500';
}
</script>

<template>
    <div class="grid gap-5 lg:grid-cols-5">
        <!-- Main: active plan -->
        <div class="lg:col-span-3 space-y-5">
            <!-- Active plan card -->
            <div v-if="activePlan" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="h-10 w-10 rounded-xl bg-gray-100 flex items-center justify-center">
                            <UtensilsCrossed class="h-5 w-5 text-gray-500" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-base font-bold text-gray-900">{{ activePlan.title }}</p>
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Attivo</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Dal {{ formatDate(activePlan.start_date) }}
                                <span v-if="activePlan.end_date"> · fino al {{ formatDate(activePlan.end_date) }}</span>
                                <span v-else> · in corso</span>
                                <span v-if="activePlan.meals?.length"> · {{ [...new Set(activePlan.meals.map((m: any) => m.meal_type))].length }} pasti / giorno</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <Link :href="route('nutritionist.plans.pdf', activePlan.id)" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                        <Download class="h-3.5 w-3.5" /> Esporta PDF
                    </Link>
                    <Link :href="route('nutritionist.plans.edit', activePlan.id)" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                        <Edit class="h-3.5 w-3.5" /> Modifica
                    </Link>
                </div>

                <!-- Macros -->
                <MacroSummary
                    :kcal="activePlan.daily_calories"
                    :carbs-grams="Number(activePlan.carbs_grams)"
                    :protein-grams="Number(activePlan.protein_grams)"
                    :fat-grams="Number(activePlan.fat_grams)"
                />

                <!-- Meal structure -->
                <div v-if="mealSummary(activePlan).length > 0" class="mt-5">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400 mb-3">Struttura pasti</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <div v-for="meal in mealSummary(activePlan)" :key="meal.type" class="rounded-lg bg-gray-50 px-3 py-2.5">
                            <p class="text-xs font-medium text-gray-500">{{ mealTypeLabels[meal.type] ?? meal.type }}</p>
                            <p v-if="meal.kcal" class="text-sm font-bold text-gray-900 mt-0.5">{{ Math.round(meal.kcal) }} kcal</p>
                            <div v-if="activePlan.daily_calories && meal.kcal" class="mt-1.5 h-1 rounded-full bg-gray-200">
                                <div class="h-full rounded-full bg-primary-400" :style="{ width: Math.min(100, Math.round(meal.kcal / activePlan.daily_calories * 100)) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="activePlan.notes" class="mt-4 rounded-xl bg-gray-50 px-3 py-2.5">
                    <p class="text-xs text-gray-500"><span class="font-medium">Note:</span> {{ activePlan.notes }}</p>
                </div>
            </div>

            <!-- No active plan -->
            <div v-else class="rounded-2xl bg-white border border-gray-100 shadow-sm p-8 text-center">
                <UtensilsCrossed class="mx-auto h-10 w-10 text-gray-200 mb-3" />
                <p class="text-sm font-medium text-gray-600 mb-1">Nessun piano attivo</p>
                <p class="text-xs text-gray-400 mb-4">Crea un nuovo piano nutrizionale per questo cliente.</p>
            </div>

            <!-- Previous plans -->
            <div v-if="inactivePlans.length > 0" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <BookOpen class="h-4 w-4 text-gray-400" /> Piani precedenti
                </p>
                <div class="space-y-2">
                    <Link
                        v-for="plan in inactivePlans"
                        :key="plan.id"
                        :href="route('nutritionist.plans.show', plan.id)"
                        class="flex items-center gap-3 rounded-xl border border-gray-100 hover:bg-gray-50 p-3 transition group"
                    >
                        <div class="h-8 w-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                            <UtensilsCrossed class="h-4 w-4 text-gray-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ plan.title }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-gray-400">{{ formatDate(plan.start_date) }} → {{ plan.end_date ? formatDate(plan.end_date) : 'in corso' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span v-if="plan.daily_calories" class="text-xs text-gray-500">{{ plan.daily_calories }} kcal</span>
                            <span v-if="plan.carbs_grams" class="text-xs font-medium text-amber-600">{{ plan.carbs_grams }}g carbo</span>
                            <span v-if="plan.protein_grams" class="text-xs font-medium text-green-600">{{ plan.protein_grams }}g prot</span>
                            <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusCls(plan.status)]">
                                {{ statusLabel(plan.status) }}
                            </span>
                            <ChevronRight class="h-3.5 w-3.5 text-gray-300 group-hover:text-gray-500 flex-shrink-0 transition" />
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Right: create new plan -->
        <div class="lg:col-span-2 space-y-5">
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-900 mb-1 flex items-center gap-2">
                    <Plus class="h-4 w-4 text-gray-400" /> Crea nuovo piano
                </p>
                <p class="text-xs text-gray-400 mb-4">Costruisci un nuovo piano per {{ client.user?.name }} a partire da una base o da zero.</p>
                <div class="space-y-2">
                    <Link
                        :href="route('nutritionist.plans.create', { client_id: client.id })"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-primary-700 transition"
                    >
                        <Plus class="h-4 w-4" /> Da zero
                    </Link>
                    <Link
                        v-if="activePlan"
                        :href="route('nutritionist.plans.duplicate', activePlan.id)"
                        class="flex w-full items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition"
                    >
                        <Copy class="h-4 w-4 text-gray-400" /> Duplica piano attivo
                    </Link>
                    <Link
                        :href="route('nutritionist.plans.templates')"
                        class="flex w-full items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition"
                    >
                        <BookOpen class="h-4 w-4 text-gray-400" /> Da template libreria
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
