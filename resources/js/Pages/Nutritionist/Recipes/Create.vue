<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Trash2, Search, Flame, Beef, Wheat, Droplets } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
    foods: {
        id: number;
        name: string;
        category: string;
        calories_per_100g: number;
        protein_per_100g: number;
        carbs_per_100g: number;
        fat_per_100g: number;
    }[];
}>();

const form = useForm({
    name: '',
    description: '',
    instructions: '',
    servings: 1,
    prep_time_minutes: '',
    cook_time_minutes: '',
    ingredients: [] as { food_id: number; quantity_grams: number; food_name: string }[],
});

const foodSearch = ref('');
const showFoodDropdown = ref(false);

const filteredFoods = computed(() => {
    if (!foodSearch.value) return props.foods.slice(0, 20);
    const term = foodSearch.value.toLowerCase();
    return props.foods.filter(f => f.name.toLowerCase().includes(term)).slice(0, 20);
});

function addIngredient(food: typeof props.foods[0]) {
    if (form.ingredients.some(i => i.food_id === food.id)) return;
    form.ingredients.push({
        food_id: food.id,
        quantity_grams: 100,
        food_name: food.name,
    });
    foodSearch.value = '';
    showFoodDropdown.value = false;
}

function removeIngredient(index: number) {
    form.ingredients.splice(index, 1);
}

function getFoodById(id: number) {
    return props.foods.find(f => f.id === id);
}

const totals = computed(() => {
    let cal = 0, prot = 0, carb = 0, fat = 0;
    for (const ing of form.ingredients) {
        const food = getFoodById(ing.food_id);
        if (!food) continue;
        const ratio = ing.quantity_grams / 100;
        cal += food.calories_per_100g * ratio;
        prot += food.protein_per_100g * ratio;
        carb += food.carbs_per_100g * ratio;
        fat += food.fat_per_100g * ratio;
    }
    return {
        calories: Math.round(cal * 10) / 10,
        protein: Math.round(prot * 10) / 10,
        carbs: Math.round(carb * 10) / 10,
        fat: Math.round(fat * 10) / 10,
    };
});

function hideFoodDropdown() {
    globalThis.setTimeout(() => showFoodDropdown.value = false, 200);
}

function submit() {
    form.post(route('nutritionist.recipes.store'));
}
</script>

<template>
    <Head title="Nuova Ricetta" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('nutritionist.recipes.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">Nuova Ricetta</h1>
            </div>
        </template>

        <form @submit.prevent="submit" class="max-w-3xl">
            <!-- Info base -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <InputLabel for="name" value="Nome ricetta *" />
                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.name" class="mt-1" />
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel for="description" value="Descrizione" />
                        <textarea id="description" v-model="form.description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"></textarea>
                    </div>
                    <div>
                        <InputLabel for="servings" value="Porzioni *" />
                        <TextInput id="servings" v-model="form.servings" type="number" min="1" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.servings" class="mt-1" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <InputLabel for="prep_time" value="Prep (min)" />
                            <TextInput id="prep_time" v-model="form.prep_time_minutes" type="number" min="0" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel for="cook_time" value="Cottura (min)" />
                            <TextInput id="cook_time" v-model="form.cook_time_minutes" type="number" min="0" class="mt-1 block w-full" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ingredienti -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Ingredienti *</h2>

                <!-- Cerca alimento -->
                <div class="relative mb-4">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <input
                        v-model="foodSearch"
                        @focus="showFoodDropdown = true"
                        @blur="hideFoodDropdown"
                        type="text"
                        placeholder="Cerca un alimento da aggiungere..."
                        class="w-full rounded-lg border-gray-200 pl-10 pr-4 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500"
                    />
                    <div v-if="showFoodDropdown && filteredFoods.length > 0" class="absolute z-10 mt-1 w-full rounded-lg bg-white border border-gray-200 shadow-lg max-h-60 overflow-y-auto">
                        <button
                            v-for="food in filteredFoods"
                            :key="food.id"
                            type="button"
                            @mousedown.prevent="addIngredient(food)"
                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 flex items-center justify-between"
                            :class="{ 'opacity-50 cursor-not-allowed': form.ingredients.some(i => i.food_id === food.id) }"
                        >
                            <span class="font-medium text-gray-900">{{ food.name }}</span>
                            <span class="text-xs text-gray-400">{{ food.calories_per_100g }} kcal/100g</span>
                        </button>
                    </div>
                </div>

                <InputError :message="form.errors.ingredients" class="mb-3" />

                <!-- Lista ingredienti aggiunti -->
                <div v-if="form.ingredients.length === 0" class="text-center py-6 text-sm text-gray-400">
                    Nessun ingrediente aggiunto. Cerca e aggiungi alimenti sopra.
                </div>
                <div v-else class="space-y-2">
                    <div
                        v-for="(ingredient, index) in form.ingredients"
                        :key="ingredient.food_id"
                        class="flex items-center gap-3 rounded-lg bg-gray-50 px-4 py-2.5"
                    >
                        <span class="flex-1 text-sm font-medium text-gray-900">{{ ingredient.food_name }}</span>
                        <div class="flex items-center gap-1.5">
                            <input
                                v-model.number="ingredient.quantity_grams"
                                type="number"
                                min="0.1"
                                step="0.1"
                                class="w-20 rounded-md border-gray-300 text-sm text-center focus:border-primary-500 focus:ring-primary-500"
                            />
                            <span class="text-xs text-gray-400">g</span>
                        </div>
                        <button type="button" @click="removeIngredient(index)" class="p-1 text-gray-400 hover:text-red-500 transition">
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <!-- Macro totali -->
                <div v-if="form.ingredients.length > 0" class="mt-4 grid grid-cols-4 gap-2 rounded-lg bg-gradient-to-r from-primary-50 to-green-50 p-3 text-center text-xs">
                    <div>
                        <Flame class="h-3.5 w-3.5 mx-auto text-orange-400 mb-0.5" />
                        <span class="font-bold text-gray-900">{{ totals.calories }}</span>
                        <span class="text-gray-500 block">kcal totali</span>
                    </div>
                    <div>
                        <Beef class="h-3.5 w-3.5 mx-auto text-red-400 mb-0.5" />
                        <span class="font-bold text-gray-900">{{ totals.protein }}g</span>
                        <span class="text-gray-500 block">proteine</span>
                    </div>
                    <div>
                        <Wheat class="h-3.5 w-3.5 mx-auto text-amber-400 mb-0.5" />
                        <span class="font-bold text-gray-900">{{ totals.carbs }}g</span>
                        <span class="text-gray-500 block">carboidrati</span>
                    </div>
                    <div>
                        <Droplets class="h-3.5 w-3.5 mx-auto text-yellow-400 mb-0.5" />
                        <span class="font-bold text-gray-900">{{ totals.fat }}g</span>
                        <span class="text-gray-500 block">grassi</span>
                    </div>
                </div>
            </div>

            <!-- Istruzioni -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <InputLabel for="instructions" value="Procedimento" />
                <textarea id="instructions" v-model="form.instructions" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" placeholder="Descrivi i passaggi per preparare la ricetta..."></textarea>
            </div>

            <div class="flex items-center gap-3">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Salva Ricetta</PrimaryButton>
                <Link :href="route('nutritionist.recipes.index')" class="text-sm text-gray-600 hover:text-gray-900">Annulla</Link>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
