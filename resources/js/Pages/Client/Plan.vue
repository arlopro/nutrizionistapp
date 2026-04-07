<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { FileText, ChefHat, Flame, Beef, Wheat, Droplets, Calendar, CheckCircle2, Circle, TrendingUp } from 'lucide-vue-next';

const props = defineProps<{
    plan: any | null;
    recipes: any[];
    mealTypes: { value: string; label: string }[];
    completedMealIds: number[];
    adherence: number | null;
    today: string;
}>();

const days = [
    { value: 0, label: 'Lunedì' },
    { value: 1, label: 'Martedì' },
    { value: 2, label: 'Mercoledì' },
    { value: 3, label: 'Giovedì' },
    { value: 4, label: 'Venerdì' },
    { value: 5, label: 'Sabato' },
    { value: 6, label: 'Domenica' },
];

// Mostra di default il giorno della settimana corrispondente ad oggi
const todayDayIndex = (() => {
    const d = new Date(props.today).getDay(); // 0=dom, 1=lun...
    return d === 0 ? 6 : d - 1; // converti a 0=lun...6=dom
})();
const activeDay = ref(todayDayIndex);

// Stato locale completamenti per ottimistic UI
const completedIds = ref(new Set(props.completedMealIds));

const dayMeals = computed(() => {
    if (!props.plan) return [];
    return (props.plan.meals || []).filter((m: any) => m.day_of_week === activeDay.value);
});

const todayMeals = computed(() => {
    if (!props.plan) return [];
    return (props.plan.meals || []).filter((m: any) => m.day_of_week === todayDayIndex);
});

const todayCompletedCount = computed(() => todayMeals.value.filter((m: any) => completedIds.value.has(m.id)).length);
const todayProgress = computed(() => {
    const total = todayMeals.value.length;
    return total > 0 ? Math.round((todayCompletedCount.value / total) * 100) : 0;
});

function isToday() {
    return activeDay.value === todayDayIndex;
}

function isMealCompleted(mealId: number) {
    return completedIds.value.has(mealId);
}

function toggleMeal(meal: any) {
    if (!isToday()) return; // spunta solo per oggi
    // Ottimistic UI
    if (completedIds.value.has(meal.id)) {
        completedIds.value.delete(meal.id);
    } else {
        completedIds.value.add(meal.id);
    }
    router.post(route('client.meals.toggle-complete', meal.id), {}, {
        preserveScroll: true,
        onError: () => {
            // Rollback in caso di errore
            if (completedIds.value.has(meal.id)) {
                completedIds.value.delete(meal.id);
            } else {
                completedIds.value.add(meal.id);
            }
        },
    });
}

function mealTypeLabel(type: string) {
    return props.mealTypes.find(m => m.value === type)?.label || type;
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
}

function mealMacros(meal: any) {
    let cal = 0, prot = 0, carb = 0, fat = 0;
    for (const item of meal.items || []) {
        if (item.food && item.quantity_grams) {
            const r = Number(item.quantity_grams) / 100;
            cal += item.food.calories_per_100g * r;
            prot += item.food.protein_per_100g * r;
            carb += item.food.carbs_per_100g * r;
            fat += item.food.fat_per_100g * r;
        } else if (item.recipe) {
            const recipe = props.recipes.find((rc: any) => rc.id === item.recipe_id);
            if (recipe) {
                cal += recipe.total_calories;
                prot += recipe.total_protein;
                carb += recipe.total_carbs;
                fat += recipe.total_fat;
            }
        }
    }
    return {
        calories: Math.round(cal),
        protein: Math.round(prot * 10) / 10,
        carbs: Math.round(carb * 10) / 10,
        fat: Math.round(fat * 10) / 10,
    };
}

const dayTotals = computed(() => {
    let cal = 0, prot = 0, carb = 0, fat = 0;
    for (const meal of dayMeals.value) {
        const m = mealMacros(meal);
        cal += m.calories;
        prot += m.protein;
        carb += m.carbs;
        fat += m.fat;
    }
    return { calories: cal, protein: Math.round(prot * 10) / 10, carbs: Math.round(carb * 10) / 10, fat: Math.round(fat * 10) / 10 };
});

function adherenceColor(pct: number) {
    if (pct >= 80) return 'text-green-600';
    if (pct >= 50) return 'text-amber-600';
    return 'text-red-500';
}
</script>

<template>
    <Head title="Il mio Piano" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-900">Il mio Piano</h1>
        </template>

        <!-- No active plan -->
        <div v-if="!plan" class="rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm">
            <FileText class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun piano attivo</h3>
            <p class="text-sm text-gray-500">Il tuo nutrizionista non ha ancora attivato un piano per te.</p>
        </div>

        <template v-else>
            <!-- Plan info + adesione -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5 mb-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ plan.title }}</h2>
                        <div class="flex items-center gap-3 text-sm text-gray-500 mb-3">
                            <span class="flex items-center gap-1">
                                <Calendar class="h-3.5 w-3.5" />
                                Dal {{ formatDate(plan.start_date) }}
                                <template v-if="plan.end_date"> al {{ formatDate(plan.end_date) }}</template>
                            </span>
                        </div>
                        <p v-if="plan.description" class="text-sm text-gray-600 mb-3">{{ plan.description }}</p>
                    </div>
                    <!-- Adesione 7 giorni -->
                    <div v-if="adherence !== null" class="flex-shrink-0 text-center">
                        <div class="flex items-center gap-1">
                            <TrendingUp class="h-4 w-4 text-gray-400" />
                            <span class="text-xs text-gray-500">Adesione 7gg</span>
                        </div>
                        <p :class="['text-2xl font-bold', adherenceColor(adherence)]">{{ adherence }}%</p>
                    </div>
                </div>

                <div v-if="plan.daily_calories" class="grid grid-cols-4 gap-3 rounded-xl bg-gradient-to-r from-primary-50 to-green-50 p-3 text-center">
                    <div>
                        <Flame class="h-4 w-4 mx-auto text-orange-400 mb-0.5" />
                        <span class="text-lg font-bold text-gray-900">{{ plan.daily_calories }}</span>
                        <span class="text-xs text-gray-500 block">kcal/giorno</span>
                    </div>
                    <div v-if="plan.protein_grams">
                        <Beef class="h-4 w-4 mx-auto text-red-400 mb-0.5" />
                        <span class="text-lg font-bold text-gray-900">{{ plan.protein_grams }}g</span>
                        <span class="text-xs text-gray-500 block">proteine</span>
                    </div>
                    <div v-if="plan.carbs_grams">
                        <Wheat class="h-4 w-4 mx-auto text-amber-400 mb-0.5" />
                        <span class="text-lg font-bold text-gray-900">{{ plan.carbs_grams }}g</span>
                        <span class="text-xs text-gray-500 block">carboidrati</span>
                    </div>
                    <div v-if="plan.fat_grams">
                        <Droplets class="h-4 w-4 mx-auto text-yellow-400 mb-0.5" />
                        <span class="text-lg font-bold text-gray-900">{{ plan.fat_grams }}g</span>
                        <span class="text-xs text-gray-500 block">grassi</span>
                    </div>
                </div>
            </div>

            <!-- Progresso di oggi -->
            <div v-if="todayMeals.length > 0" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-4 mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Oggi — {{ todayCompletedCount }}/{{ todayMeals.length }} pasti completati</span>
                    <span :class="['text-sm font-bold', adherenceColor(todayProgress)]">{{ todayProgress }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div
                        class="h-2 rounded-full transition-all duration-300"
                        :class="todayProgress >= 80 ? 'bg-green-500' : todayProgress >= 50 ? 'bg-amber-400' : 'bg-red-400'"
                        :style="{ width: todayProgress + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Day tabs -->
            <div class="flex gap-1 mb-6 overflow-x-auto pb-1">
                <button
                    v-for="day in days"
                    :key="day.value"
                    @click="activeDay = day.value"
                    :class="[
                        'rounded-lg px-4 py-2 text-sm font-medium transition whitespace-nowrap',
                        activeDay === day.value
                            ? 'bg-primary-500 text-white shadow-sm'
                            : 'text-gray-600 hover:bg-gray-100'
                    ]"
                >
                    {{ day.label }}
                    <span v-if="day.value === todayDayIndex" class="ml-1 text-xs opacity-75">(oggi)</span>
                </button>
            </div>

            <!-- Day summary macro -->
            <div v-if="dayMeals.length > 0 && dayTotals.calories > 0" class="rounded-xl bg-gradient-to-r from-primary-50 to-green-50 p-3 mb-4 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Totale {{ days[activeDay]?.label }}</span>
                <div class="flex gap-4 text-xs">
                    <span><strong>{{ dayTotals.calories }}</strong> kcal</span>
                    <span><strong>{{ dayTotals.protein }}g</strong> prot</span>
                    <span><strong>{{ dayTotals.carbs }}g</strong> carb</span>
                    <span><strong>{{ dayTotals.fat }}g</strong> grassi</span>
                </div>
            </div>

            <!-- Avviso se si tenta di spuntare giorno non odierno -->
            <p v-if="!isToday() && dayMeals.length > 0" class="text-xs text-gray-400 mb-3 text-center">
                Puoi spuntare i pasti completati solo per il giorno corrente.
            </p>

            <!-- Meals -->
            <div class="space-y-4">
                <div v-for="meal in dayMeals" :key="meal.id" class="rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden">
                    <!-- Meal header con checkbox se è oggi -->
                    <div
                        :class="[
                            'px-5 py-3 border-b border-gray-100 flex items-center justify-between',
                            isToday() && isMealCompleted(meal.id) ? 'bg-green-50' : 'bg-gray-50'
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <button
                                v-if="isToday()"
                                @click="toggleMeal(meal)"
                                class="flex-shrink-0 transition"
                            >
                                <CheckCircle2
                                    v-if="isMealCompleted(meal.id)"
                                    class="h-5 w-5 text-green-500"
                                />
                                <Circle v-else class="h-5 w-5 text-gray-300 hover:text-green-400" />
                            </button>
                            <h3 :class="['font-semibold', isMealCompleted(meal.id) && isToday() ? 'text-green-700 line-through' : 'text-gray-900']">
                                {{ mealTypeLabel(meal.meal_type) }}
                            </h3>
                        </div>
                        <span v-if="mealMacros(meal).calories > 0" class="text-xs text-gray-400">
                            {{ mealMacros(meal).calories }} kcal
                        </span>
                    </div>

                    <!-- Free text meal -->
                    <div v-if="meal.free_text" class="px-5 py-4">
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ meal.free_text }}</p>
                        <p v-if="meal.notes" class="text-xs text-gray-400 mt-2 italic">{{ meal.notes }}</p>
                    </div>

                    <!-- Structured meal -->
                    <div v-else>
                        <div v-if="(meal.items || []).length === 0" class="px-5 py-4 text-center text-sm text-gray-400">
                            Nessun alimento per questo pasto.
                        </div>
                        <div v-else class="divide-y divide-gray-50">
                            <div v-for="item in meal.items" :key="item.id" class="flex items-center justify-between px-5 py-3">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <ChefHat v-if="item.recipe" class="h-3.5 w-3.5 text-primary-400" />
                                        <span class="text-sm font-medium text-gray-900">{{ item.food?.name || item.recipe?.name || '—' }}</span>
                                    </div>
                                    <div v-if="item.food && item.quantity_grams" class="text-xs text-gray-400 mt-0.5">
                                        {{ Math.round(item.food.calories_per_100g * Number(item.quantity_grams) / 100) }} kcal ·
                                        {{ (item.food.protein_per_100g * Number(item.quantity_grams) / 100).toFixed(1) }}g P ·
                                        {{ (item.food.carbs_per_100g * Number(item.quantity_grams) / 100).toFixed(1) }}g C ·
                                        {{ (item.food.fat_per_100g * Number(item.quantity_grams) / 100).toFixed(1) }}g G
                                    </div>
                                </div>
                                <span v-if="item.quantity_grams" class="text-sm text-gray-500">{{ item.quantity_grams }}g</span>
                            </div>
                        </div>
                        <p v-if="meal.notes" class="px-5 pb-3 text-xs text-gray-400 italic">{{ meal.notes }}</p>
                    </div>
                </div>
            </div>

            <div v-if="dayMeals.length === 0" class="rounded-2xl bg-white border border-gray-100 p-8 text-center shadow-sm">
                <p class="text-sm text-gray-400">Nessun pasto previsto per {{ days[activeDay]?.label }}.</p>
            </div>

            <!-- Notes -->
            <div v-if="plan.notes" class="mt-6 rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">Note del nutrizionista</h3>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ plan.notes }}</p>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
