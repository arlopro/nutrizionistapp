<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { FileText, ChefHat, ChevronDown, ChevronUp, Utensils } from 'lucide-vue-next';

const props = defineProps<{
    plan: any | null;
    recipes: any[];
    mealTypes: { value: string; label: string }[];
    completedMealIds: number[];
    adherence: number | null;
    weeklyDayAdherence: (number | null)[];
    today: string;
}>();

const page = usePage();
const userName = computed(() => {
    const name = (page.props.auth as any)?.user?.name || '';
    return name.split(' ')[0];
});
const greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 12) return 'Buongiorno';
    if (h < 18) return 'Buon pomeriggio';
    return 'Buonasera';
});

const dayLabels = ['L', 'M', 'M', 'G', 'V', 'S', 'D'];
const days = [
    { value: 0, full: 'Lunedì' },
    { value: 1, full: 'Martedì' },
    { value: 2, full: 'Mercoledì' },
    { value: 3, full: 'Giovedì' },
    { value: 4, full: 'Venerdì' },
    { value: 5, full: 'Sabato' },
    { value: 6, full: 'Domenica' },
];

const todayDayIndex = (() => {
    const d = new Date(props.today).getDay();
    return d === 0 ? 6 : d - 1;
})();
const activeDay = ref(todayDayIndex);
const completedIds = ref(new Set(props.completedMealIds));
const expandedMeals = ref(new Set<number>());

const dayMeals = computed(() =>
    props.plan ? (props.plan.meals || []).filter((m: any) => m.day_of_week === activeDay.value) : []
);
const todayMeals = computed(() =>
    props.plan ? (props.plan.meals || []).filter((m: any) => m.day_of_week === todayDayIndex) : []
);
const todayCompletedCount = computed(() =>
    todayMeals.value.filter((m: any) => completedIds.value.has(m.id)).length
);
const nextMeal = computed(() => {
    if (activeDay.value !== todayDayIndex) return null;
    return todayMeals.value.find((m: any) => !completedIds.value.has(m.id)) ?? null;
});

function isToday() { return activeDay.value === todayDayIndex; }
function isMealCompleted(id: number) { return completedIds.value.has(id); }
function isExpanded(id: number) { return expandedMeals.value.has(id); }

function toggleMeal(meal: any) {
    if (!isToday()) return;
    if (completedIds.value.has(meal.id)) completedIds.value.delete(meal.id);
    else completedIds.value.add(meal.id);
    router.post(route('client.meals.toggle-complete', meal.id), {}, {
        preserveScroll: true,
        onError: () => {
            if (completedIds.value.has(meal.id)) completedIds.value.delete(meal.id);
            else completedIds.value.add(meal.id);
        },
    });
}

function toggleExpand(id: number) {
    if (expandedMeals.value.has(id)) expandedMeals.value.delete(id);
    else expandedMeals.value.add(id);
}

function mealTypeLabel(type: string) {
    return props.mealTypes.find(m => m.value === type)?.label || type;
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
            if (recipe) { cal += recipe.total_calories; prot += recipe.total_protein; carb += recipe.total_carbs; fat += recipe.total_fat; }
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
    const t = { calories: 0, protein: 0, carbs: 0, fat: 0 };
    for (const m of dayMeals.value) { const x = mealMacros(m); t.calories += x.calories; t.protein += x.protein; t.carbs += x.carbs; t.fat += x.fat; }
    return { calories: t.calories, protein: Math.round(t.protein * 10) / 10, carbs: Math.round(t.carbs * 10) / 10, fat: Math.round(t.fat * 10) / 10 };
});

const todayConsumed = computed(() => {
    const t = { calories: 0, protein: 0, carbs: 0, fat: 0 };
    for (const m of todayMeals.value) {
        if (!completedIds.value.has(m.id)) continue;
        const x = mealMacros(m); t.calories += x.calories; t.protein += x.protein; t.carbs += x.carbs; t.fat += x.fat;
    }
    return { calories: t.calories, protein: Math.round(t.protein * 10) / 10, carbs: Math.round(t.carbs * 10) / 10, fat: Math.round(t.fat * 10) / 10 };
});

const calProgress = computed(() => {
    const target = props.plan?.daily_calories;
    if (!target) return 0;
    return Math.min(Math.round((todayConsumed.value.calories / target) * 100), 100);
});

function macroBar(consumed: number, target: number | null) {
    if (!target || target <= 0) return 0;
    return Math.min((consumed / target) * 100, 100);
}

function mealItemsPreview(meal: any, max = 4): string {
    if (meal.free_text) return meal.free_text.split('\n')[0].substring(0, 80);
    const names = (meal.items || []).slice(0, max).map((i: any) => i.food?.name || i.recipe?.name).filter(Boolean);
    const rem = (meal.items?.length || 0) - names.length;
    return names.join(' · ') + (rem > 0 ? ` · +${rem} altri` : '');
}

// SVG circle ring
const CIRCLE_R = 40;
const CIRCLE_CIRC = 2 * Math.PI * CIRCLE_R;
const circleOffset = computed(() => CIRCLE_CIRC * (1 - calProgress.value / 100));

function dayBg(pct: number | null): string {
    if (pct === null) return 'bg-gray-100 text-gray-400';
    if (pct >= 80) return 'bg-green-500 text-white';
    if (pct >= 50) return 'bg-amber-400 text-white';
    if (pct > 0) return 'bg-red-400 text-white';
    return 'bg-gray-200 text-gray-500';
}

function adherenceColor(pct: number) {
    if (pct >= 80) return 'text-green-600';
    if (pct >= 50) return 'text-amber-500';
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
            <!-- Greeting header -->
            <div class="rounded-2xl bg-gradient-to-br from-green-50 to-emerald-50 border border-green-100 p-6 mb-5">
                <p class="text-xs font-semibold text-green-600 uppercase tracking-wider mb-1">{{ plan.title }}</p>
                <h2 class="text-3xl font-bold text-gray-900 mb-1">
                    {{ greeting }}, <em class="text-green-600 not-italic font-extrabold">{{ userName }}</em>
                </h2>
                <p v-if="plan.daily_calories" class="text-gray-600 text-sm">
                    Oggi puoi mangiare <strong>{{ plan.daily_calories }} kcal</strong>
                    distribuiti su <strong>{{ todayMeals.length }} pasti</strong>
                </p>
            </div>

            <!-- Prossimo pasto -->
            <div v-if="nextMeal" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5 mb-5">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Prossimo pasto</p>
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                            <Utensils class="h-5 w-5 text-green-600" />
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-gray-900">{{ mealTypeLabel(nextMeal.meal_type) }}</span>
                                <span v-if="mealMacros(nextMeal).calories > 0" class="text-sm text-gray-400">
                                    · {{ mealMacros(nextMeal).calories }} kcal
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">{{ mealItemsPreview(nextMeal) }}</p>
                        </div>
                    </div>
                    <button
                        @click="toggleExpand(nextMeal.id); activeDay = todayDayIndex"
                        class="flex-shrink-0 flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition"
                    >
                        Vai al pasto
                        <ChevronDown class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <!-- Two-column layout -->
            <div class="flex flex-col lg:flex-row gap-5">

                <!-- Left: day tabs + meals -->
                <div class="flex-1 min-w-0">
                    <!-- Day tabs -->
                    <div class="flex gap-1 mb-4 overflow-x-auto pb-1">
                        <button
                            v-for="day in days" :key="day.value"
                            @click="activeDay = day.value"
                            :class="['rounded-xl px-3.5 py-2 text-sm font-medium transition whitespace-nowrap',
                                activeDay === day.value
                                    ? 'bg-green-600 text-white shadow-sm'
                                    : 'text-gray-600 hover:bg-gray-100']"
                        >
                            {{ day.full }}
                            <span v-if="day.value === todayDayIndex" class="ml-1 text-xs opacity-70">(oggi)</span>
                        </button>
                    </div>

                    <!-- Day header row -->
                    <div v-if="dayMeals.length > 0" class="flex items-center justify-between mb-3 px-0.5">
                        <h3 class="font-semibold text-gray-900">Pasti di {{ days[activeDay]?.full }}</h3>
                        <span v-if="isToday()" class="text-sm text-gray-400">
                            {{ todayCompletedCount }} di {{ todayMeals.length }} fatti
                        </span>
                        <template v-else-if="dayTotals.calories > 0">
                            <span class="text-sm text-gray-400">{{ dayTotals.calories }} kcal previste</span>
                        </template>
                    </div>

                    <p v-if="!isToday() && dayMeals.length > 0" class="text-xs text-gray-400 mb-3 text-center">
                        Puoi spuntare i pasti completati solo per il giorno corrente.
                    </p>

                    <!-- Meals -->
                    <div class="space-y-2.5">
                        <div
                            v-for="meal in dayMeals" :key="meal.id"
                            :class="['rounded-2xl bg-white border shadow-sm overflow-hidden',
                                isToday() && isMealCompleted(meal.id) ? 'border-green-100' : 'border-gray-100']"
                        >
                            <!-- Meal row -->
                            <div
                                class="flex items-center gap-3 px-4 py-3.5 cursor-pointer select-none"
                                :class="isToday() && isMealCompleted(meal.id) ? 'bg-green-50/60' : 'bg-white'"
                                @click="toggleExpand(meal.id)"
                            >
                                <!-- Checkbox -->
                                <button
                                    v-if="isToday()"
                                    @click.stop="toggleMeal(meal)"
                                    class="flex-shrink-0"
                                >
                                    <div v-if="isMealCompleted(meal.id)"
                                        class="w-7 h-7 rounded-full bg-green-500 flex items-center justify-center shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div v-else class="w-7 h-7 rounded-full border-2 border-gray-200 hover:border-green-400 transition"></div>
                                </button>
                                <div v-else class="w-7 h-7 rounded-full border-2 border-gray-150 flex-shrink-0"></div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span :class="['font-semibold text-sm',
                                            isToday() && isMealCompleted(meal.id) ? 'text-green-700 line-through' : 'text-gray-900']">
                                            {{ mealTypeLabel(meal.meal_type) }}
                                        </span>
                                        <template v-if="mealMacros(meal).calories > 0">
                                            <span class="text-xs font-medium text-amber-500">C{{ mealMacros(meal).carbs }}</span>
                                            <span class="text-xs font-medium text-red-500">P{{ mealMacros(meal).protein }}</span>
                                            <span class="text-xs font-medium text-blue-500">G{{ mealMacros(meal).fat }}</span>
                                        </template>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-0.5 truncate">{{ mealItemsPreview(meal) }}</p>
                                </div>

                                <!-- Calories + arrow -->
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span v-if="mealMacros(meal).calories > 0" class="text-base font-bold text-gray-800">
                                        {{ mealMacros(meal).calories }}
                                        <span class="text-xs font-normal text-gray-400">kcal</span>
                                    </span>
                                    <ChevronUp v-if="isExpanded(meal.id)" class="h-4 w-4 text-gray-400" />
                                    <ChevronDown v-else class="h-4 w-4 text-gray-400" />
                                </div>
                            </div>

                            <!-- Expanded detail -->
                            <Transition
                                enter-active-class="transition-all duration-200 ease-out"
                                enter-from-class="opacity-0"
                                enter-to-class="opacity-100"
                                leave-active-class="transition-all duration-150 ease-in"
                                leave-from-class="opacity-100"
                                leave-to-class="opacity-0"
                            >
                                <div v-if="isExpanded(meal.id)" class="border-t border-gray-100">
                                    <div v-if="meal.free_text" class="px-5 py-4">
                                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ meal.free_text }}</p>
                                    </div>
                                    <div v-else class="divide-y divide-gray-50">
                                        <div v-if="(meal.items || []).length === 0" class="px-5 py-4 text-sm text-gray-400 text-center">
                                            Nessun alimento per questo pasto.
                                        </div>
                                        <div v-for="item in meal.items" :key="item.id"
                                            class="flex items-center justify-between px-5 py-3">
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <ChefHat v-if="item.recipe" class="h-3.5 w-3.5 text-green-400" />
                                                    <span class="text-sm font-medium text-gray-900">{{ item.food?.name || item.recipe?.name || '—' }}</span>
                                                </div>
                                                <div v-if="item.food && item.quantity_grams" class="text-xs text-gray-400 mt-0.5">
                                                    {{ Math.round(item.food.calories_per_100g * Number(item.quantity_grams) / 100) }} kcal
                                                    · P{{ (item.food.protein_per_100g * Number(item.quantity_grams) / 100).toFixed(1) }}g
                                                    · C{{ (item.food.carbs_per_100g * Number(item.quantity_grams) / 100).toFixed(1) }}g
                                                    · G{{ (item.food.fat_per_100g * Number(item.quantity_grams) / 100).toFixed(1) }}g
                                                </div>
                                            </div>
                                            <span v-if="item.quantity_grams" class="text-sm font-medium text-gray-500">{{ item.quantity_grams }}g</span>
                                        </div>
                                    </div>
                                    <p v-if="meal.notes" class="px-5 pb-4 text-xs text-gray-400 italic">{{ meal.notes }}</p>
                                </div>
                            </Transition>
                        </div>
                    </div>

                    <!-- Empty day -->
                    <div v-if="dayMeals.length === 0" class="rounded-2xl bg-white border border-gray-100 p-8 text-center shadow-sm">
                        <p class="text-sm text-gray-400">Nessun pasto previsto per {{ days[activeDay]?.full }}.</p>
                    </div>

                    <!-- Nutritionist notes -->
                    <div v-if="plan.notes" class="mt-4 rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Note del nutrizionista</h3>
                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ plan.notes }}</p>
                    </div>
                </div>

                <!-- Right sidebar -->
                <div class="w-full lg:w-72 xl:w-80 flex-shrink-0 space-y-4">

                    <!-- Bilancio di oggi -->
                    <div v-if="plan.daily_calories" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-4">Bilancio di oggi</p>

                        <div class="flex items-center gap-4 mb-5">
                            <!-- Circular ring -->
                            <div class="relative w-[88px] h-[88px] flex-shrink-0">
                                <svg class="w-[88px] h-[88px] -rotate-90" viewBox="0 0 96 96">
                                    <circle cx="48" cy="48" r="40" fill="none" stroke="#f3f4f6" stroke-width="8"/>
                                    <circle
                                        cx="48" cy="48" r="40"
                                        fill="none"
                                        stroke="#16a34a"
                                        stroke-width="8"
                                        stroke-linecap="round"
                                        :stroke-dasharray="CIRCLE_CIRC"
                                        :stroke-dashoffset="circleOffset"
                                        style="transition: stroke-dashoffset 0.6s ease"
                                    />
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-lg font-bold text-gray-900 leading-none">{{ todayConsumed.calories }}</span>
                                    <span class="text-[10px] font-medium text-gray-400 uppercase mt-0.5">kcal</span>
                                </div>
                            </div>

                            <div>
                                <p class="text-2xl font-bold text-gray-900 leading-none">{{ todayConsumed.calories }}</p>
                                <p class="text-sm text-gray-400 mt-1">/ {{ plan.daily_calories }} kcal</p>
                                <p class="text-sm font-semibold mt-1" :class="adherenceColor(calProgress)">{{ calProgress }}% completato</p>
                            </div>
                        </div>

                        <!-- Macro bars -->
                        <div class="space-y-3">
                            <div v-if="plan.carbs_grams">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-500 font-medium">Carboidrati</span>
                                    <span class="text-gray-600 font-semibold">{{ todayConsumed.carbs }} / {{ plan.carbs_grams }}g</span>
                                </div>
                                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-amber-400 rounded-full transition-all duration-500"
                                        :style="{ width: macroBar(todayConsumed.carbs, plan.carbs_grams) + '%' }"></div>
                                </div>
                            </div>
                            <div v-if="plan.protein_grams">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-500 font-medium">Proteine</span>
                                    <span class="text-gray-600 font-semibold">{{ todayConsumed.protein }} / {{ plan.protein_grams }}g</span>
                                </div>
                                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-red-400 rounded-full transition-all duration-500"
                                        :style="{ width: macroBar(todayConsumed.protein, plan.protein_grams) + '%' }"></div>
                                </div>
                            </div>
                            <div v-if="plan.fat_grams">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-500 font-medium">Grassi</span>
                                    <span class="text-gray-600 font-semibold">{{ todayConsumed.fat }} / {{ plan.fat_grams }}g</span>
                                </div>
                                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-400 rounded-full transition-all duration-500"
                                        :style="{ width: macroBar(todayConsumed.fat, plan.fat_grams) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Aderenza settimana -->
                    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">Settimana</p>
                            <span v-if="adherence !== null" class="text-xs text-gray-400">
                                Aderenza
                                <strong :class="adherenceColor(adherence)">{{ adherence }}%</strong>
                            </span>
                        </div>
                        <div class="grid grid-cols-7 gap-1">
                            <div v-for="(pct, i) in weeklyDayAdherence" :key="i" class="flex flex-col items-center gap-1.5">
                                <div :class="['w-9 h-9 rounded-xl flex items-center justify-center text-[11px] font-bold',
                                    i === todayDayIndex ? 'ring-2 ring-offset-1 ring-green-400' : '',
                                    dayBg(pct)]">
                                    {{ pct !== null ? pct : '—' }}
                                </div>
                                <span class="text-[10px] text-gray-400">{{ dayLabels[i] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Piano info / target macros -->
                    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Obiettivi giornalieri</p>
                        <div v-if="plan.daily_calories || plan.protein_grams || plan.carbs_grams || plan.fat_grams" class="grid grid-cols-2 gap-2">
                            <div v-if="plan.daily_calories" class="rounded-xl bg-orange-50 p-3 text-center">
                                <p class="text-xl font-bold text-orange-500">{{ plan.daily_calories }}</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">kcal / giorno</p>
                            </div>
                            <div v-if="plan.protein_grams" class="rounded-xl bg-red-50 p-3 text-center">
                                <p class="text-xl font-bold text-red-500">{{ plan.protein_grams }}g</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">proteine</p>
                            </div>
                            <div v-if="plan.carbs_grams" class="rounded-xl bg-amber-50 p-3 text-center">
                                <p class="text-xl font-bold text-amber-500">{{ plan.carbs_grams }}g</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">carboidrati</p>
                            </div>
                            <div v-if="plan.fat_grams" class="rounded-xl bg-blue-50 p-3 text-center">
                                <p class="text-xl font-bold text-blue-500">{{ plan.fat_grams }}g</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">grassi</p>
                            </div>
                        </div>
                        <div v-else>
                            <p class="text-sm text-gray-400">Nessun obiettivo calorico impostato.</p>
                        </div>
                        <p v-if="plan.description" class="text-sm text-gray-500 mt-3 pt-3 border-t border-gray-100">{{ plan.description }}</p>
                    </div>

                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
