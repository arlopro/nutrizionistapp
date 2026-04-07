<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Trash2, Clock, Users, Flame, Beef, Wheat, Droplets } from 'lucide-vue-next';

const props = defineProps<{
    recipe: any;
}>();

function deleteRecipe() {
    if (confirm('Sei sicuro di voler eliminare questa ricetta?')) {
        router.delete(route('nutritionist.recipes.destroy', props.recipe.id));
    }
}
</script>

<template>
    <Head :title="recipe.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('nutritionist.recipes.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <h1 class="text-xl font-semibold text-gray-900">{{ recipe.name }}</h1>
                </div>
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('nutritionist.recipes.edit', recipe.id)"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                    >
                        <Pencil class="h-3.5 w-3.5" />
                        Modifica
                    </Link>
                    <button
                        @click="deleteRecipe"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition"
                    >
                        <Trash2 class="h-3.5 w-3.5" />
                        Elimina
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-3xl">
            <!-- Info + Macro -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <p v-if="recipe.description" class="text-sm text-gray-600 mb-4">{{ recipe.description }}</p>

                <div class="flex items-center gap-4 text-sm text-gray-500 mb-5">
                    <span class="flex items-center gap-1.5">
                        <Users class="h-4 w-4" />
                        {{ recipe.servings }} porzioni
                    </span>
                    <span v-if="recipe.prep_time_minutes" class="flex items-center gap-1.5">
                        <Clock class="h-4 w-4" />
                        Prep: {{ recipe.prep_time_minutes }} min
                    </span>
                    <span v-if="recipe.cook_time_minutes" class="flex items-center gap-1.5">
                        <Clock class="h-4 w-4" />
                        Cottura: {{ recipe.cook_time_minutes }} min
                    </span>
                </div>

                <div class="grid grid-cols-4 gap-3 rounded-xl bg-gradient-to-r from-primary-50 to-green-50 p-4 text-center">
                    <div>
                        <Flame class="h-4 w-4 mx-auto text-orange-400 mb-1" />
                        <span class="text-lg font-bold text-gray-900">{{ recipe.total_calories }}</span>
                        <span class="text-xs text-gray-500 block">kcal</span>
                    </div>
                    <div>
                        <Beef class="h-4 w-4 mx-auto text-red-400 mb-1" />
                        <span class="text-lg font-bold text-gray-900">{{ recipe.total_protein }}g</span>
                        <span class="text-xs text-gray-500 block">proteine</span>
                    </div>
                    <div>
                        <Wheat class="h-4 w-4 mx-auto text-amber-400 mb-1" />
                        <span class="text-lg font-bold text-gray-900">{{ recipe.total_carbs }}g</span>
                        <span class="text-xs text-gray-500 block">carboidrati</span>
                    </div>
                    <div>
                        <Droplets class="h-4 w-4 mx-auto text-yellow-400 mb-1" />
                        <span class="text-lg font-bold text-gray-900">{{ recipe.total_fat }}g</span>
                        <span class="text-xs text-gray-500 block">grassi</span>
                    </div>
                </div>
            </div>

            <!-- Ingredienti -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Ingredienti</h2>
                <div class="space-y-2">
                    <div
                        v-for="ingredient in recipe.ingredients"
                        :key="ingredient.id"
                        class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-2.5"
                    >
                        <span class="text-sm font-medium text-gray-900">{{ ingredient.food.name }}</span>
                        <span class="text-sm text-gray-500">{{ ingredient.quantity_grams }}g</span>
                    </div>
                </div>
            </div>

            <!-- Procedimento -->
            <div v-if="recipe.instructions" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-3">Procedimento</h2>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ recipe.instructions }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
