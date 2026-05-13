<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useConfirm } from '@/Composables/useConfirm';

const { confirm: confirmDialog } = useConfirm();
import {
    ArrowLeft, Pencil, Trash2, Plus, Search, ChefHat,
    Flame, Beef, Wheat, Droplets, Calendar, User, X,
    Copy, FileText, LayoutList, ChevronsRight, BookTemplate, Download, GitBranch,
    Pill, Clock, Timer, Lock,
} from 'lucide-vue-next';

const page = usePage();
const features = computed(() => (page.props.subscription as any)?.features ?? {});
const hasPdfExport = computed(() => !!features.value.pdf_export);
const templateLimit = computed<number | null>(() => features.value.plan_template_limit ?? null);

const props = defineProps<{
    plan: any;
    foods: any[];
    recipes: any[];
    mealTypes: { value: string; label: string }[];
    statuses: { value: string; label: string }[];
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

const activeDay = ref(0);

// Modal: aggiungi pasto
const showAddMeal = ref(false);
const addMealForm = useForm({
    day_of_week: 0,
    meal_type: '',
    notes: '',
    free_text: '',
    mode: 'free' as 'free' | 'structured',
});

// Modal: aggiungi alimento strutturato
const showAddItem = ref(false);
const addItemMealId = ref<number | null>(null);
const itemSearch = ref('');
const itemTab = ref<'food' | 'recipe'>('food');

// Modal: aggiungi alternativa
const showAddAlternative = ref(false);
const addAlternativeItemId = ref<number | null>(null);
const altSearch = ref('');
const altTab = ref<'food' | 'recipe'>('food');

// Modal: salva come template
const showSaveTemplate = ref(false);
const templateNameInput = ref('');

function submitSaveTemplate() {
    if (!templateNameInput.value.trim()) return;
    router.post(route('nutritionist.plans.save-as-template', props.plan.id), {
        template_name: templateNameInput.value.trim(),
    }, {
        preserveScroll: true,
        onSuccess: () => { showSaveTemplate.value = false; templateNameInput.value = ''; },
    });
}

// Modal: duplica giorno
const showDuplicateDay = ref(false);
const duplicateDayTarget = ref<number>(1);

// Modal: applica giorno a settimana
const showApplyToWeek = ref(false);

const FILTER_DISPLAY_LIMIT = 50;

const filteredFoodsAll = computed(() => {
    if (!itemSearch.value) return props.foods;
    const term = itemSearch.value.toLowerCase();
    return props.foods.filter(f => f.name.toLowerCase().includes(term));
});
const filteredFoods = computed(() => filteredFoodsAll.value.slice(0, FILTER_DISPLAY_LIMIT));
const hasMoreFoods = computed(() => filteredFoodsAll.value.length > FILTER_DISPLAY_LIMIT);
const totalFoodsCount = computed(() => filteredFoodsAll.value.length);

const filteredRecipesAll = computed(() => {
    if (!itemSearch.value) return props.recipes;
    const term = itemSearch.value.toLowerCase();
    return props.recipes.filter(r => r.name.toLowerCase().includes(term));
});
const filteredRecipes = computed(() => filteredRecipesAll.value.slice(0, FILTER_DISPLAY_LIMIT));
const hasMoreRecipes = computed(() => filteredRecipesAll.value.length > FILTER_DISPLAY_LIMIT);
const totalRecipesCount = computed(() => filteredRecipesAll.value.length);

const filteredAltFoodsAll = computed(() => {
    if (!altSearch.value) return props.foods;
    const term = altSearch.value.toLowerCase();
    return props.foods.filter(f => f.name.toLowerCase().includes(term));
});
const filteredAltFoods = computed(() => filteredAltFoodsAll.value.slice(0, FILTER_DISPLAY_LIMIT));
const hasMoreAltFoods = computed(() => filteredAltFoodsAll.value.length > FILTER_DISPLAY_LIMIT);
const totalAltFoodsCount = computed(() => filteredAltFoodsAll.value.length);

const filteredAltRecipesAll = computed(() => {
    if (!altSearch.value) return props.recipes;
    const term = altSearch.value.toLowerCase();
    return props.recipes.filter(r => r.name.toLowerCase().includes(term));
});
const filteredAltRecipes = computed(() => filteredAltRecipesAll.value.slice(0, FILTER_DISPLAY_LIMIT));
const hasMoreAltRecipes = computed(() => filteredAltRecipesAll.value.length > FILTER_DISPLAY_LIMIT);
const totalAltRecipesCount = computed(() => filteredAltRecipesAll.value.length);

const dayMeals = computed(() => {
    return (props.plan.meals || []).filter((m: any) => m.day_of_week === activeDay.value);
});

// Tieni traccia della modalità locale di ogni pasto (libero/strutturato)
// Inizializzata dal campo free_text: se ha free_text → libero, altrimenti → strutturato
const mealModes = ref<Record<number, 'free' | 'structured'>>({});
const freeTextDrafts = ref<Record<number, string>>({});

function getMealMode(meal: any): 'free' | 'structured' {
    if (mealModes.value[meal.id] !== undefined) return mealModes.value[meal.id];
    return meal.free_text ? 'free' : 'structured';
}

function setMealMode(meal: any, mode: 'free' | 'structured') {
    mealModes.value[meal.id] = mode;
    if (mode === 'free' && freeTextDrafts.value[meal.id] === undefined) {
        freeTextDrafts.value[meal.id] = meal.free_text || '';
    }
}

function getFreeText(meal: any): string {
    if (freeTextDrafts.value[meal.id] !== undefined) return freeTextDrafts.value[meal.id];
    return meal.free_text || '';
}

function onFreeTextInput(meal: any, value: string) {
    freeTextDrafts.value[meal.id] = value;
    clearTimeout(freeTextTimers[meal.id]);
    freeTextTimers[meal.id] = globalThis.setTimeout(() => {
        saveFreeText(meal, value);
    }, 700);
}

let freeTextTimers: Record<number, any> = {};

function saveFreeText(meal: any, text: string) {
    router.patch(route('nutritionist.plans.meals.update', [props.plan.id, meal.id]), {
        free_text: text,
    }, { preserveScroll: true });
}

function mealTypeLabel(type: string) {
    return props.mealTypes.find(m => m.value === type)?.label || type;
}

function statusLabel(s: string) {
    return props.statuses.find(st => st.value === s)?.label || s;
}

function statusColor(s: string) {
    const map: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-700',
        active: 'bg-green-50 text-green-700',
        completed: 'bg-blue-50 text-blue-700',
        archived: 'bg-orange-50 text-orange-700',
    };
    return map[s] || 'bg-gray-100 text-gray-700';
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'short', year: 'numeric' });
}

function openAddMeal() {
    addMealForm.day_of_week = activeDay.value;
    addMealForm.meal_type = '';
    addMealForm.notes = '';
    addMealForm.free_text = '';
    addMealForm.mode = 'free';
    showAddMeal.value = true;
}

function submitAddMeal() {
    addMealForm.post(route('nutritionist.plans.meals.store', props.plan.id), {
        preserveScroll: true,
        onSuccess: () => { showAddMeal.value = false; },
    });
}

async function deleteMeal(mealId: number) {
    const ok = await confirmDialog('Il pasto e tutti i suoi elementi verranno eliminati.', {
        title: 'Elimina pasto',
        confirmLabel: 'Elimina',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('nutritionist.plans.meals.destroy', [props.plan.id, mealId]), {
        preserveScroll: true,
    });
}

function openAddItem(mealId: number) {
    addItemMealId.value = mealId;
    itemSearch.value = '';
    itemTab.value = 'food';
    showAddItem.value = true;
}

function addFoodItem(food: any, mealId: number) {
    router.post(route('nutritionist.plans.meals.items.store', [props.plan.id, mealId]), {
        food_id: food.id,
        quantity_grams: 100,
    }, { preserveScroll: true, onSuccess: () => { showAddItem.value = false; } });
}

function addRecipeItem(recipe: any, mealId: number) {
    router.post(route('nutritionist.plans.meals.items.store', [props.plan.id, mealId]), {
        recipe_id: recipe.id,
    }, { preserveScroll: true, onSuccess: () => { showAddItem.value = false; } });
}

function openAddAlternative(itemId: number) {
    addAlternativeItemId.value = itemId;
    altSearch.value = '';
    altTab.value = 'food';
    showAddAlternative.value = true;
}

function addFoodAlternative(food: any, parentItemId: number) {
    router.post(route('nutritionist.plans.items.alternatives.store', [props.plan.id, parentItemId]), {
        food_id: food.id,
        quantity_grams: 100,
    }, { preserveScroll: true, onSuccess: () => { showAddAlternative.value = false; } });
}

function addRecipeAlternative(recipe: any, parentItemId: number) {
    router.post(route('nutritionist.plans.items.alternatives.store', [props.plan.id, parentItemId]), {
        recipe_id: recipe.id,
    }, { preserveScroll: true, onSuccess: () => { showAddAlternative.value = false; } });
}

function updateItemQuantity(itemId: number, quantity: number) {
    router.patch(route('nutritionist.plans.items.update', [props.plan.id, itemId]), {
        quantity_grams: quantity,
    }, { preserveScroll: true });
}

function deleteItem(itemId: number) {
    router.delete(route('nutritionist.plans.items.destroy', [props.plan.id, itemId]), {
        preserveScroll: true,
    });
}

async function deletePlan() {
    const ok = await confirmDialog('Il piano nutrizionale verrà eliminato definitivamente.', {
        title: 'Elimina piano',
        confirmLabel: 'Elimina',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('nutritionist.plans.destroy', props.plan.id));
}

function duplicatePlan() {
    router.post(route('nutritionist.plans.duplicate', props.plan.id), {}, {
        preserveScroll: false,
    });
}

function openDuplicateDay() {
    duplicateDayTarget.value = activeDay.value === 0 ? 1 : 0;
    showDuplicateDay.value = true;
}

function submitDuplicateDay() {
    router.post(route('nutritionist.plans.days.duplicate', [props.plan.id, activeDay.value]), {
        target_day: duplicateDayTarget.value,
    }, {
        preserveScroll: true,
        onSuccess: () => { showDuplicateDay.value = false; },
    });
}

async function applyDayToWeek() {
    const ok = await confirmDialog(`I pasti di ${days[activeDay.value].label} verranno copiati su tutti gli altri giorni, sostituendo quelli esistenti.`, {
        title: 'Applica a tutta la settimana',
        confirmLabel: 'Applica',
    });
    if (!ok) return;
    router.post(route('nutritionist.plans.days.apply-to-week', [props.plan.id, activeDay.value]), {}, {
        preserveScroll: true,
    });
}

// Calcolo macro pasto
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
            const recipe = props.recipes.find(rc => rc.id === item.recipe_id);
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
        if (getMealMode(meal) === 'structured') {
            const m = mealMacros(meal);
            cal += m.calories;
            prot += m.protein;
            carb += m.carbs;
            fat += m.fat;
        }
    }
    return { calories: cal, protein: Math.round(prot * 10) / 10, carbs: Math.round(carb * 10) / 10, fat: Math.round(fat * 10) / 10 };
});

let quantityTimers: Record<number, any> = {};
function onQuantityChange(itemId: number, value: number) {
    clearTimeout(quantityTimers[itemId]);
    quantityTimers[itemId] = globalThis.setTimeout(() => {
        updateItemQuantity(itemId, value);
    }, 600);
}

// --- Supplements ---
const showAddSupplement = ref(false);
const addSupplementForm = useForm({
    name: '',
    dosage: '',
    dosage_unit: '',
    timing: '',
    duration: '',
    notes: '',
});

const editingSupplementId = ref<number | null>(null);
const editSupplementForm = useForm({
    name: '',
    dosage: '',
    dosage_unit: '',
    timing: '',
    duration: '',
    notes: '',
});

const dosageUnits = [
    { value: 'mg', label: 'mg' },
    { value: 'g', label: 'g' },
    { value: 'mcg', label: 'mcg (µg)' },
    { value: 'ml', label: 'ml' },
    { value: 'UI', label: 'UI' },
    { value: 'capsule', label: 'capsule' },
    { value: 'compresse', label: 'compresse' },
    { value: 'gocce', label: 'gocce' },
    { value: 'bustine', label: 'bustine' },
    { value: 'misurino', label: 'misurino' },
];

function openAddSupplement() {
    addSupplementForm.reset();
    showAddSupplement.value = true;
}

function submitAddSupplement() {
    addSupplementForm.post(route('nutritionist.plans.supplements.store', props.plan.id), {
        preserveScroll: true,
        onSuccess: () => { showAddSupplement.value = false; },
    });
}

function startEditSupplement(supplement: any) {
    editingSupplementId.value = supplement.id;
    editSupplementForm.name = supplement.name;
    editSupplementForm.dosage = supplement.dosage || '';
    editSupplementForm.dosage_unit = supplement.dosage_unit || '';
    editSupplementForm.timing = supplement.timing || '';
    editSupplementForm.duration = supplement.duration || '';
    editSupplementForm.notes = supplement.notes || '';
}

function cancelEditSupplement() {
    editingSupplementId.value = null;
}

function submitEditSupplement(supplementId: number) {
    editSupplementForm.patch(route('nutritionist.plans.supplements.update', [props.plan.id, supplementId]), {
        preserveScroll: true,
        onSuccess: () => { editingSupplementId.value = null; },
    });
}

async function deleteSupplement(supplementId: number) {
    const ok = await confirmDialog('L\'integratore verrà rimosso dal piano.', {
        title: 'Elimina integratore',
        confirmLabel: 'Elimina',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('nutritionist.plans.supplements.destroy', [props.plan.id, supplementId]), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="plan.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <Link :href="route('nutritionist.plans.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-semibold text-gray-900">{{ plan.title }}</h1>
                            <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusColor(plan.status)]">
                                {{ statusLabel(plan.status) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-gray-500 mt-0.5">
                            <span class="flex items-center gap-1"><User class="h-3.5 w-3.5" /> {{ plan.client?.user?.name }}</span>
                            <span class="flex items-center gap-1">
                                <Calendar class="h-3.5 w-3.5" />
                                {{ formatDate(plan.start_date) }}
                                <template v-if="plan.end_date"> — {{ formatDate(plan.end_date) }}</template>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <a
                        v-if="hasPdfExport"
                        :href="route('nutritionist.plans.pdf', plan.id)"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100 transition"
                    >
                        <Download class="h-3.5 w-3.5" /> Esporta PDF
                    </a>
                    <Link
                        v-else
                        :href="route('nutritionist.billing')"
                        title="Disponibile dal piano Starter"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-400 cursor-not-allowed opacity-70"
                    >
                        <Lock class="h-3.5 w-3.5" /> Esporta PDF
                        <span class="ml-1 rounded bg-amber-100 px-1.5 py-0.5 text-xs font-semibold text-amber-700">Starter+</span>
                    </Link>
                    <button @click="duplicatePlan" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <Copy class="h-3.5 w-3.5" /> Duplica piano
                    </button>
                    <button @click="showSaveTemplate = true" class="inline-flex items-center gap-1.5 rounded-lg border border-primary-200 px-3 py-2 text-sm font-medium text-primary-700 hover:bg-primary-50 transition">
                        <BookTemplate class="h-3.5 w-3.5" /> Salva template
                        <span v-if="templateLimit !== null" class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500">max {{ templateLimit }}</span>
                    </button>
                    <Link :href="route('nutritionist.plans.edit', plan.id)" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <Pencil class="h-3.5 w-3.5" /> Modifica Info
                    </Link>
                    <button @click="deletePlan" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition">
                        <Trash2 class="h-3.5 w-3.5" /> Elimina
                    </button>
                </div>
            </div>
        </template>

        <!-- Macro obiettivi -->
        <div v-if="plan.daily_calories" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-4 mb-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-500">Obiettivi giornalieri</h3>
                <div class="flex gap-6 text-sm">
                    <div class="text-center">
                        <span class="font-bold text-gray-900">{{ plan.daily_calories }}</span>
                        <span class="text-gray-400 ml-1">kcal</span>
                    </div>
                    <div v-if="plan.protein_grams" class="text-center">
                        <span class="font-bold text-gray-900">{{ plan.protein_grams }}g</span>
                        <span class="text-gray-400 ml-1">prot</span>
                    </div>
                    <div v-if="plan.carbs_grams" class="text-center">
                        <span class="font-bold text-gray-900">{{ plan.carbs_grams }}g</span>
                        <span class="text-gray-400 ml-1">carb</span>
                    </div>
                    <div v-if="plan.fat_grams" class="text-center">
                        <span class="font-bold text-gray-900">{{ plan.fat_grams }}g</span>
                        <span class="text-gray-400 ml-1">grassi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Day tabs + azioni giorno -->
        <div class="flex items-center justify-between gap-2 mb-4 flex-wrap">
            <div class="flex gap-1 overflow-x-auto pb-1">
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
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button @click="openDuplicateDay" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                    <Copy class="h-3.5 w-3.5" /> Copia giorno
                </button>
                <button @click="applyDayToWeek" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                    <ChevronsRight class="h-3.5 w-3.5" /> Applica a settimana
                </button>
            </div>
        </div>

        <!-- Day summary (solo pasti strutturati) -->
        <div v-if="dayTotals.calories > 0" class="rounded-xl bg-gradient-to-r from-primary-50 to-green-50 p-3 mb-4 flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">Totale {{ days[activeDay]?.label }}</span>
            <div class="flex gap-4 text-xs">
                <span class="flex items-center gap-1"><Flame class="h-3.5 w-3.5 text-orange-400" /> <strong>{{ dayTotals.calories }}</strong> kcal</span>
                <span class="flex items-center gap-1"><Beef class="h-3.5 w-3.5 text-red-400" /> <strong>{{ dayTotals.protein }}g</strong> prot</span>
                <span class="flex items-center gap-1"><Wheat class="h-3.5 w-3.5 text-amber-400" /> <strong>{{ dayTotals.carbs }}g</strong> carb</span>
                <span class="flex items-center gap-1"><Droplets class="h-3.5 w-3.5 text-yellow-400" /> <strong>{{ dayTotals.fat }}g</strong> grassi</span>
            </div>
        </div>

        <!-- Pasti del giorno -->
        <div class="space-y-4 mb-6">
            <div v-for="meal in dayMeals" :key="meal.id" class="rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden">
                <!-- Meal header -->
                <div class="flex items-center justify-between px-5 py-3 bg-gray-50 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold text-gray-900">{{ mealTypeLabel(meal.meal_type) }}</h3>
                        <span v-if="getMealMode(meal) === 'structured' && mealMacros(meal).calories > 0" class="text-xs text-gray-400">
                            {{ mealMacros(meal).calories }} kcal
                        </span>
                    </div>
                    <div class="flex items-center gap-1">
                        <!-- Toggle modalità -->
                        <div class="flex items-center rounded-lg border border-gray-200 overflow-hidden mr-1">
                            <button
                                @click="setMealMode(meal, 'free')"
                                :class="[
                                    'flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium transition',
                                    getMealMode(meal) === 'free'
                                        ? 'bg-amber-50 text-amber-700'
                                        : 'text-gray-500 hover:bg-gray-50'
                                ]"
                                title="Testo libero"
                            >
                                <FileText class="h-3.5 w-3.5" /> Libero
                            </button>
                            <button
                                @click="setMealMode(meal, 'structured')"
                                :class="[
                                    'flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium transition border-l border-gray-200',
                                    getMealMode(meal) === 'structured'
                                        ? 'bg-primary-50 text-primary-700'
                                        : 'text-gray-500 hover:bg-gray-50'
                                ]"
                                title="Strutturato"
                            >
                                <LayoutList class="h-3.5 w-3.5" /> Strutturato
                            </button>
                        </div>

                        <!-- Azioni modalità strutturata -->
                        <template v-if="getMealMode(meal) === 'structured'">
                            <button @click="openAddItem(meal.id)" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium text-primary-600 hover:bg-primary-50 transition">
                                <Plus class="h-3.5 w-3.5" /> Aggiungi
                            </button>
                        </template>
                        <button @click="deleteMeal(meal.id)" class="p-1.5 text-gray-400 hover:text-red-500 transition">
                            <Trash2 class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>

                <!-- Modalità: TESTO LIBERO -->
                <div v-if="getMealMode(meal) === 'free'" class="p-4">
                    <textarea
                        :value="getFreeText(meal)"
                        @input="onFreeTextInput(meal, ($event.target as HTMLTextAreaElement).value)"
                        rows="3"
                        placeholder="Es: Yogurt greco 150g + cereali integrali + 1 frutto&#10;oppure: Pasta al pomodoro 80g (cruda) + insalata verde"
                        class="w-full rounded-lg border-gray-200 text-sm focus:border-amber-400 focus:ring-amber-400 resize-none placeholder-gray-300"
                    ></textarea>
                    <p class="text-xs text-gray-400 mt-1">Scrivi liberamente il contenuto del pasto. Autosalvataggio attivo.</p>
                </div>

                <!-- Modalità: STRUTTURATA -->
                <div v-else>
                    <div v-if="(meal.items || []).filter((i: any) => !i.alternative_of).length === 0" class="px-5 py-6 text-center text-sm text-gray-400">
                        Nessun alimento. Clicca "Aggiungi" per iniziare.
                    </div>
                    <div v-else class="divide-y divide-gray-50">
                        <template v-for="item in (meal.items || []).filter((i: any) => !i.alternative_of)" :key="item.id">
                            <!-- Item principale -->
                            <div class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-1.5">
                                            <ChefHat v-if="item.recipe" class="h-3.5 w-3.5 text-primary-400" />
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ item.food?.name || item.recipe?.name || '—' }}
                                            </span>
                                        </div>
                                        <div v-if="item.food && item.quantity_grams" class="text-xs text-gray-400 mt-0.5">
                                            {{ Math.round(item.food.calories_per_100g * Number(item.quantity_grams) / 100) }} kcal ·
                                            {{ (item.food.protein_per_100g * Number(item.quantity_grams) / 100).toFixed(1) }}g P ·
                                            {{ (item.food.carbs_per_100g * Number(item.quantity_grams) / 100).toFixed(1) }}g C ·
                                            {{ (item.food.fat_per_100g * Number(item.quantity_grams) / 100).toFixed(1) }}g G
                                        </div>
                                        <div v-if="item.food && item.quantity_grams && (item.food.sodium_mg || item.food.calcium_mg || item.food.iron_mg || item.food.vitamin_d_mcg || item.food.vitamin_b12_mcg || item.food.glycemic_index)" class="text-xs text-teal-500 mt-0.5">
                                            <template v-if="item.food.sodium_mg">Na {{ (item.food.sodium_mg * Number(item.quantity_grams) / 100).toFixed(0) }}mg</template>
                                            <template v-if="item.food.potassium_mg">{{ item.food.sodium_mg ? ' · ' : '' }}K {{ (item.food.potassium_mg * Number(item.quantity_grams) / 100).toFixed(0) }}mg</template>
                                            <template v-if="item.food.calcium_mg"> · Ca {{ (item.food.calcium_mg * Number(item.quantity_grams) / 100).toFixed(0) }}mg</template>
                                            <template v-if="item.food.iron_mg"> · Fe {{ (item.food.iron_mg * Number(item.quantity_grams) / 100).toFixed(1) }}mg</template>
                                            <template v-if="item.food.vitamin_d_mcg"> · VitD {{ (item.food.vitamin_d_mcg * Number(item.quantity_grams) / 100).toFixed(1) }}mcg</template>
                                            <template v-if="item.food.vitamin_b12_mcg"> · B12 {{ (item.food.vitamin_b12_mcg * Number(item.quantity_grams) / 100).toFixed(1) }}mcg</template>
                                            <template v-if="item.food.glycemic_index"> · IG {{ item.food.glycemic_index }}</template>
                                        </div>
                                        <div v-else-if="item.recipe" class="text-xs text-gray-400 mt-0.5">
                                            Ricetta completa
                                        </div>
                                    </div>
                                    <div v-if="item.food" class="flex items-center gap-1.5">
                                        <input
                                            :value="item.quantity_grams"
                                            @input="onQuantityChange(item.id, Number(($event.target as HTMLInputElement).value))"
                                            type="number"
                                            min="0.1"
                                            step="0.1"
                                            class="w-20 rounded-md border-gray-300 text-sm text-center focus:border-primary-500 focus:ring-primary-500"
                                        />
                                        <span class="text-xs text-gray-400">g</span>
                                    </div>
                                    <button
                                        @click="openAddAlternative(item.id)"
                                        class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-violet-600 hover:bg-violet-50 transition"
                                        title="Aggiungi alternativa"
                                    >
                                        <GitBranch class="h-3.5 w-3.5" />
                                        <span class="hidden sm:inline">Alt.</span>
                                    </button>
                                    <button @click="deleteItem(item.id)" class="p-1 text-gray-400 hover:text-red-500 transition">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>

                                <!-- Alternative dell'item -->
                                <div v-if="(item.alternatives || []).length > 0" class="mt-2 ml-4 space-y-1.5">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <div class="h-px flex-1 bg-violet-100"></div>
                                        <span class="text-xs text-violet-400 font-medium uppercase tracking-wide">oppure</span>
                                        <div class="h-px flex-1 bg-violet-100"></div>
                                    </div>
                                    <div
                                        v-for="alt in item.alternatives"
                                        :key="alt.id"
                                        class="flex items-center gap-3 rounded-lg bg-violet-50 px-3 py-2"
                                    >
                                        <GitBranch class="h-3.5 w-3.5 text-violet-300 flex-shrink-0" />
                                        <div class="flex-1">
                                            <span class="text-sm text-gray-800">{{ alt.food?.name || alt.recipe?.name || '—' }}</span>
                                            <div v-if="alt.food && alt.quantity_grams" class="text-xs text-gray-400 mt-0.5">
                                                {{ Math.round(alt.food.calories_per_100g * Number(alt.quantity_grams) / 100) }} kcal ·
                                                {{ (alt.food.protein_per_100g * Number(alt.quantity_grams) / 100).toFixed(1) }}g P ·
                                                {{ (alt.food.carbs_per_100g * Number(alt.quantity_grams) / 100).toFixed(1) }}g C ·
                                                {{ (alt.food.fat_per_100g * Number(alt.quantity_grams) / 100).toFixed(1) }}g G
                                            </div>
                                        </div>
                                        <div v-if="alt.food" class="flex items-center gap-1.5">
                                            <input
                                                :value="alt.quantity_grams"
                                                @input="onQuantityChange(alt.id, Number(($event.target as HTMLInputElement).value))"
                                                type="number"
                                                min="0.1"
                                                step="0.1"
                                                class="w-20 rounded-md border-gray-200 bg-white text-sm text-center focus:border-violet-400 focus:ring-violet-400"
                                            />
                                            <span class="text-xs text-gray-400">g</span>
                                        </div>
                                        <button @click="deleteItem(alt.id)" class="p-1 text-gray-400 hover:text-red-500 transition">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aggiungi pasto -->
        <button
            @click="openAddMeal"
            class="w-full rounded-2xl border-2 border-dashed border-gray-200 py-4 text-sm font-medium text-gray-400 hover:border-primary-300 hover:text-primary-500 transition"
        >
            <Plus class="h-4 w-4 inline mr-1" />
            Aggiungi pasto a {{ days[activeDay]?.label }}
        </button>

        <!-- Integratori / Supplementi -->
        <div class="mt-10 mb-6">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <Pill class="h-5 w-5 text-teal-500" />
                    <h2 class="text-lg font-semibold text-gray-900">Integratori / Supplementi</h2>
                </div>
                <button @click="openAddSupplement" class="inline-flex items-center gap-1.5 rounded-lg border border-teal-200 bg-teal-50 px-3 py-2 text-sm font-medium text-teal-700 hover:bg-teal-100 transition">
                    <Plus class="h-3.5 w-3.5" /> Aggiungi
                </button>
            </div>

            <div v-if="(plan.supplements || []).length === 0" class="rounded-2xl border-2 border-dashed border-gray-200 py-8 text-center text-sm text-gray-400">
                <Pill class="h-6 w-6 mx-auto mb-2 text-gray-300" />
                Nessun integratore aggiunto. Clicca "Aggiungi" per inserire un protocollo di integrazione.
            </div>

            <div v-else class="space-y-2">
                <div v-for="supplement in plan.supplements" :key="supplement.id" class="rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
                    <!-- Vista normale -->
                    <div v-if="editingSupplementId !== supplement.id" class="px-5 py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-900">{{ supplement.name }}</span>
                                    <span v-if="supplement.dosage" class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2 py-0.5 text-xs font-medium text-teal-700">
                                        {{ supplement.dosage }}{{ supplement.dosage_unit ? ' ' + supplement.dosage_unit : '' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                                    <span v-if="supplement.timing" class="flex items-center gap-1">
                                        <Clock class="h-3 w-3" /> {{ supplement.timing }}
                                    </span>
                                    <span v-if="supplement.duration" class="flex items-center gap-1">
                                        <Timer class="h-3 w-3" /> {{ supplement.duration }}
                                    </span>
                                </div>
                                <p v-if="supplement.notes" class="text-xs text-gray-400 mt-1 italic">{{ supplement.notes }}</p>
                            </div>
                            <div class="flex items-center gap-1 ml-3">
                                <button @click="startEditSupplement(supplement)" class="p-1.5 text-gray-400 hover:text-teal-600 transition">
                                    <Pencil class="h-3.5 w-3.5" />
                                </button>
                                <button @click="deleteSupplement(supplement.id)" class="p-1.5 text-gray-400 hover:text-red-500 transition">
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Vista editing inline -->
                    <div v-else class="px-5 py-4 bg-teal-50/30">
                        <form @submit.prevent="submitEditSupplement(supplement.id)" class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="col-span-2">
                                    <input v-model="editSupplementForm.name" type="text" placeholder="Nome integratore *" required class="block w-full rounded-md border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500" />
                                </div>
                                <div>
                                    <input v-model="editSupplementForm.dosage" type="text" placeholder="Dosaggio (es: 1000)" class="block w-full rounded-md border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500" />
                                </div>
                                <div>
                                    <select v-model="editSupplementForm.dosage_unit" class="block w-full rounded-md border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                                        <option value="">Unità...</option>
                                        <option v-for="u in dosageUnits" :key="u.value" :value="u.value">{{ u.label }}</option>
                                    </select>
                                </div>
                                <div>
                                    <input v-model="editSupplementForm.timing" type="text" placeholder="Timing (es: a colazione)" class="block w-full rounded-md border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500" />
                                </div>
                                <div>
                                    <input v-model="editSupplementForm.duration" type="text" placeholder="Durata (es: 3 mesi)" class="block w-full rounded-md border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500" />
                                </div>
                                <div class="col-span-2">
                                    <input v-model="editSupplementForm.notes" type="text" placeholder="Note (opzionale)" class="block w-full rounded-md border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500" />
                                </div>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="cancelEditSupplement" class="rounded-lg px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-100 transition">Annulla</button>
                                <button type="submit" :disabled="editSupplementForm.processing" class="rounded-lg bg-teal-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-teal-600 transition">Salva</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Aggiungi Integratore -->
        <div v-if="showAddSupplement" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showAddSupplement = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <Pill class="h-5 w-5 text-teal-500" />
                        <h3 class="text-lg font-semibold text-gray-900">Aggiungi integratore</h3>
                    </div>
                    <button @click="showAddSupplement = false" class="p-1 text-gray-400 hover:text-gray-600"><X class="h-5 w-5" /></button>
                </div>
                <form @submit.prevent="submitAddSupplement" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome integratore *</label>
                        <input v-model="addSupplementForm.name" type="text" required placeholder="Es: Vitamina D3, Omega-3, Creatina..." class="block w-full rounded-md border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dosaggio</label>
                            <input v-model="addSupplementForm.dosage" type="text" placeholder="Es: 1000, 2, 500" class="block w-full rounded-md border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unità</label>
                            <select v-model="addSupplementForm.dosage_unit" class="block w-full rounded-md border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500">
                                <option value="">Seleziona...</option>
                                <option v-for="u in dosageUnits" :key="u.value" :value="u.value">{{ u.label }}</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Timing / Quando assumerlo</label>
                        <input v-model="addSupplementForm.timing" type="text" placeholder="Es: a colazione, dopo allenamento, prima di dormire" class="block w-full rounded-md border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Durata</label>
                        <input v-model="addSupplementForm.duration" type="text" placeholder="Es: 3 mesi, continuativo, 30 giorni" class="block w-full rounded-md border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Note (opzionale)</label>
                        <textarea v-model="addSupplementForm.notes" rows="2" placeholder="Es: Assumere con pasto grasso per migliore assorbimento" class="block w-full rounded-md border-gray-300 text-sm focus:border-teal-500 focus:ring-teal-500 resize-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showAddSupplement = false" class="rounded-lg px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 transition">Annulla</button>
                        <button type="submit" :disabled="addSupplementForm.processing" class="rounded-lg bg-gradient-to-r from-teal-500 to-teal-600 px-4 py-2 text-sm font-medium text-white hover:from-teal-600 hover:to-teal-700 transition">Aggiungi</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Aggiungi Pasto -->
        <div v-if="showAddMeal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showAddMeal = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Aggiungi pasto</h3>
                    <button @click="showAddMeal = false" class="p-1 text-gray-400 hover:text-gray-600"><X class="h-5 w-5" /></button>
                </div>
                <form @submit.prevent="submitAddMeal">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo pasto *</label>
                        <select v-model="addMealForm.meal_type" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                            <option value="">Seleziona...</option>
                            <option v-for="mt in mealTypes" :key="mt.value" :value="mt.value">{{ mt.label }}</option>
                        </select>
                    </div>

                    <!-- Toggle modalità nel form -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Modalità inserimento</label>
                        <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                            <button
                                type="button"
                                @click="addMealForm.mode = 'free'"
                                :class="['flex-1 flex items-center justify-center gap-1.5 py-2 text-sm font-medium transition', addMealForm.mode === 'free' ? 'bg-amber-50 text-amber-700' : 'text-gray-500 hover:bg-gray-50']"
                            >
                                <FileText class="h-4 w-4" /> Testo libero
                            </button>
                            <button
                                type="button"
                                @click="addMealForm.mode = 'structured'"
                                :class="['flex-1 flex items-center justify-center gap-1.5 py-2 text-sm font-medium border-l border-gray-200 transition', addMealForm.mode === 'structured' ? 'bg-primary-50 text-primary-700' : 'text-gray-500 hover:bg-gray-50']"
                            >
                                <LayoutList class="h-4 w-4" /> Strutturato
                            </button>
                        </div>
                    </div>

                    <div v-if="addMealForm.mode === 'free'" class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contenuto pasto</label>
                        <textarea
                            v-model="addMealForm.free_text"
                            rows="4"
                            placeholder="Es: Yogurt greco 150g + cereali integrali + 1 banana"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-400 focus:ring-amber-400 text-sm resize-none"
                        ></textarea>
                    </div>
                    <div v-else class="mb-4">
                        <p class="text-xs text-gray-500 bg-primary-50 rounded-lg p-3">
                            Il pasto verra' creato vuoto. Potrai aggiungere alimenti subito dopo dalla scheda del pasto.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Note (opzionale)</label>
                        <input v-model="addMealForm.notes" type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" placeholder="Es: da consumare entro 30min dall'allenamento" />
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showAddMeal = false" class="rounded-lg px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 transition">Annulla</button>
                        <button type="submit" :disabled="addMealForm.processing" class="rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 px-4 py-2 text-sm font-medium text-white hover:from-primary-600 hover:to-primary-700 transition">Aggiungi</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Aggiungi Alimento -->
        <div v-if="showAddItem" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showAddItem = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6 max-h-[80vh] flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Aggiungi alimento</h3>
                    <button @click="showAddItem = false" class="p-1 text-gray-400 hover:text-gray-600"><X class="h-5 w-5" /></button>
                </div>
                <div class="flex gap-1 mb-3">
                    <button @click="itemTab = 'food'" :class="['rounded-lg px-3 py-1.5 text-sm font-medium transition', itemTab === 'food' ? 'bg-primary-500 text-white' : 'text-gray-600 hover:bg-gray-100']">Alimenti</button>
                    <button @click="itemTab = 'recipe'" :class="['rounded-lg px-3 py-1.5 text-sm font-medium transition', itemTab === 'recipe' ? 'bg-primary-500 text-white' : 'text-gray-600 hover:bg-gray-100']">Ricette</button>
                </div>
                <div class="relative mb-3">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input v-model="itemSearch" type="text" placeholder="Cerca..." class="w-full rounded-lg border-gray-200 pl-10 pr-4 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500" />
                </div>
                <div class="flex-1 overflow-y-auto -mx-2">
                    <template v-if="itemTab === 'food'">
                        <button
                            v-for="food in filteredFoods"
                            :key="food.id"
                            type="button"
                            @click="addFoodItem(food, addItemMealId!)"
                            class="w-full text-left px-4 py-3 hover:bg-gray-50 rounded-lg mx-0 flex items-center justify-between transition"
                        >
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ food.name }}</span>
                                <span class="text-xs text-gray-400 block">{{ food.calories_per_100g }} kcal · {{ food.protein_per_100g }}g P · {{ food.carbs_per_100g }}g C · {{ food.fat_per_100g }}g G /100g</span>
                                <span v-if="food.sodium_mg || food.calcium_mg || food.iron_mg || food.glycemic_index" class="text-xs text-teal-500 block">
                                    <template v-if="food.sodium_mg">Na {{ food.sodium_mg }}mg</template>
                                    <template v-if="food.calcium_mg"> · Ca {{ food.calcium_mg }}mg</template>
                                    <template v-if="food.iron_mg"> · Fe {{ food.iron_mg }}mg</template>
                                    <template v-if="food.glycemic_index"> · IG {{ food.glycemic_index }}</template>
                                </span>
                            </div>
                            <Plus class="h-4 w-4 text-gray-300 flex-shrink-0" />
                        </button>
                        <div v-if="filteredFoods.length === 0" class="py-8 text-center text-sm text-gray-400">Nessun alimento trovato</div>
                        <div v-if="hasMoreFoods" class="py-2 text-center text-xs text-gray-400">Mostrati {{ filteredFoods.length }} di {{ totalFoodsCount }} — affina la ricerca per trovare altri risultati</div>
                    </template>
                    <template v-else>
                        <button
                            v-for="recipe in filteredRecipes"
                            :key="recipe.id"
                            type="button"
                            @click="addRecipeItem(recipe, addItemMealId!)"
                            class="w-full text-left px-4 py-3 hover:bg-gray-50 rounded-lg mx-0 flex items-center justify-between transition"
                        >
                            <div>
                                <span class="text-sm font-medium text-gray-900 flex items-center gap-1"><ChefHat class="h-3.5 w-3.5 text-primary-400" /> {{ recipe.name }}</span>
                                <span class="text-xs text-gray-400 block">{{ recipe.total_calories }} kcal · {{ recipe.total_protein }}g P · {{ recipe.total_carbs }}g C · {{ recipe.total_fat }}g G</span>
                            </div>
                            <Plus class="h-4 w-4 text-gray-300 flex-shrink-0" />
                        </button>
                        <div v-if="filteredRecipes.length === 0" class="py-8 text-center text-sm text-gray-400">Nessuna ricetta trovata</div>
                        <div v-if="hasMoreRecipes" class="py-2 text-center text-xs text-gray-400">Mostrati {{ filteredRecipes.length }} di {{ totalRecipesCount }} — affina la ricerca per trovare altri risultati</div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Modal: Aggiungi Alternativa -->
        <div v-if="showAddAlternative" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showAddAlternative = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6 max-h-[80vh] flex flex-col">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-2">
                        <GitBranch class="h-5 w-5 text-violet-500" />
                        <h3 class="text-lg font-semibold text-gray-900">Aggiungi alternativa</h3>
                    </div>
                    <button @click="showAddAlternative = false" class="p-1 text-gray-400 hover:text-gray-600"><X class="h-5 w-5" /></button>
                </div>
                <p class="text-xs text-gray-500 mb-4">L'alimento scelto sarà mostrato come opzione alternativa a quello principale.</p>
                <div class="flex gap-1 mb-3">
                    <button @click="altTab = 'food'" :class="['rounded-lg px-3 py-1.5 text-sm font-medium transition', altTab === 'food' ? 'bg-violet-500 text-white' : 'text-gray-600 hover:bg-gray-100']">Alimenti</button>
                    <button @click="altTab = 'recipe'" :class="['rounded-lg px-3 py-1.5 text-sm font-medium transition', altTab === 'recipe' ? 'bg-violet-500 text-white' : 'text-gray-600 hover:bg-gray-100']">Ricette</button>
                </div>
                <div class="relative mb-3">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input v-model="altSearch" type="text" placeholder="Cerca..." class="w-full rounded-lg border-gray-200 pl-10 pr-4 py-2.5 text-sm focus:border-violet-400 focus:ring-violet-400" />
                </div>
                <div class="flex-1 overflow-y-auto -mx-2">
                    <template v-if="altTab === 'food'">
                        <button
                            v-for="food in filteredAltFoods"
                            :key="food.id"
                            type="button"
                            @click="addFoodAlternative(food, addAlternativeItemId!)"
                            class="w-full text-left px-4 py-3 hover:bg-violet-50 rounded-lg mx-0 flex items-center justify-between transition"
                        >
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ food.name }}</span>
                                <span class="text-xs text-gray-400 block">{{ food.calories_per_100g }} kcal · {{ food.protein_per_100g }}g P · {{ food.carbs_per_100g }}g C · {{ food.fat_per_100g }}g G /100g</span>
                            </div>
                            <GitBranch class="h-4 w-4 text-violet-300 flex-shrink-0" />
                        </button>
                        <div v-if="filteredAltFoods.length === 0" class="py-8 text-center text-sm text-gray-400">Nessun alimento trovato</div>
                        <div v-if="hasMoreAltFoods" class="py-2 text-center text-xs text-gray-400">Mostrati {{ filteredAltFoods.length }} di {{ totalAltFoodsCount }} — affina la ricerca per trovare altri risultati</div>
                    </template>
                    <template v-else>
                        <button
                            v-for="recipe in filteredAltRecipes"
                            :key="recipe.id"
                            type="button"
                            @click="addRecipeAlternative(recipe, addAlternativeItemId!)"
                            class="w-full text-left px-4 py-3 hover:bg-violet-50 rounded-lg mx-0 flex items-center justify-between transition"
                        >
                            <div>
                                <span class="text-sm font-medium text-gray-900 flex items-center gap-1"><ChefHat class="h-3.5 w-3.5 text-violet-400" /> {{ recipe.name }}</span>
                                <span class="text-xs text-gray-400 block">{{ recipe.total_calories }} kcal · {{ recipe.total_protein }}g P · {{ recipe.total_carbs }}g C · {{ recipe.total_fat }}g G</span>
                            </div>
                            <GitBranch class="h-4 w-4 text-violet-300 flex-shrink-0" />
                        </button>
                        <div v-if="filteredAltRecipes.length === 0" class="py-8 text-center text-sm text-gray-400">Nessuna ricetta trovata</div>
                        <div v-if="hasMoreAltRecipes" class="py-2 text-center text-xs text-gray-400">Mostrati {{ filteredAltRecipes.length }} di {{ totalAltRecipesCount }} — affina la ricerca per trovare altri risultati</div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Modal: Salva come template -->
        <div v-if="showSaveTemplate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showSaveTemplate = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Salva come template</h3>
                    <button @click="showSaveTemplate = false" class="p-1 text-gray-400 hover:text-gray-600"><X class="h-5 w-5" /></button>
                </div>
                <p class="text-sm text-gray-500 mb-4">Assegna un nome al template. Potrai riusarlo per creare nuovi piani velocemente.</p>
                <input
                    v-model="templateNameInput"
                    type="text"
                    placeholder="Es: Piano dimagrimento 1600kcal"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm mb-4"
                    @keydown.enter="submitSaveTemplate"
                />
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showSaveTemplate = false" class="rounded-lg px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 transition">Annulla</button>
                    <button type="button" @click="submitSaveTemplate" :disabled="!templateNameInput.trim()" class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-600 transition disabled:opacity-50">Salva</button>
                </div>
            </div>
        </div>

        <!-- Modal: Duplica giorno -->
        <div v-if="showDuplicateDay" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showDuplicateDay = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Copia giorno</h3>
                    <button @click="showDuplicateDay = false" class="p-1 text-gray-400 hover:text-gray-600"><X class="h-5 w-5" /></button>
                </div>
                <p class="text-sm text-gray-600 mb-4">
                    Copia i pasti di <strong>{{ days[activeDay]?.label }}</strong> in:
                </p>
                <select v-model="duplicateDayTarget" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm mb-4">
                    <option v-for="day in days" :key="day.value" :value="day.value" :disabled="day.value === activeDay">
                        {{ day.label }}{{ day.value === activeDay ? ' (giorno attuale)' : '' }}
                    </option>
                </select>
                <p class="text-xs text-amber-600 bg-amber-50 rounded-lg p-2 mb-4">I pasti esistenti nel giorno di destinazione verranno sostituiti.</p>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showDuplicateDay = false" class="rounded-lg px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 transition">Annulla</button>
                    <button type="button" @click="submitDuplicateDay" class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-600 transition">Copia</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
