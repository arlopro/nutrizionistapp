<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Plus, Search, ChefHat, Flame, Beef, Wheat, Droplets, Clock, Users } from 'lucide-vue-next';

const props = defineProps<{
    recipes: any;
    filters: { search?: string };
}>();

const search = ref(props.filters.search || '');

let debounceTimer: any;
watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(route('nutritionist.recipes.index'), {
            search: search.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
});
</script>

<template>
    <Head title="Ricette" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Ricette</h1>
                <Link
                    :href="route('nutritionist.recipes.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 px-4 py-2 text-sm font-medium text-white hover:from-primary-600 hover:to-primary-700 transition shadow-sm"
                >
                    <Plus class="h-4 w-4" />
                    Nuova Ricetta
                </Link>
            </div>
        </template>

        <!-- Search -->
        <div class="mb-6">
            <div class="relative max-w-md">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cerca ricetta..."
                    class="w-full rounded-lg border-gray-200 pl-10 pr-4 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500"
                />
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="recipes.data.length === 0" class="rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm">
            <ChefHat class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">Nessuna ricetta trovata</h3>
            <p class="text-sm text-gray-500 mb-6">Crea ricette combinando gli alimenti per usarle nei piani nutrizionali.</p>
            <Link
                :href="route('nutritionist.recipes.create')"
                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 px-4 py-2 text-sm font-medium text-white hover:from-primary-600 hover:to-primary-700 transition shadow-sm"
            >
                <Plus class="h-4 w-4" />
                Crea la tua prima ricetta
            </Link>
        </div>

        <!-- Recipe cards -->
        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="recipe in recipes.data"
                :key="recipe.id"
                :href="route('nutritionist.recipes.show', recipe.id)"
                class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5 hover:shadow-md transition group"
            >
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition">{{ recipe.name }}</h3>
                        <p v-if="recipe.description" class="text-sm text-gray-500 mt-0.5 line-clamp-2">{{ recipe.description }}</p>
                    </div>
                    <ChefHat class="h-5 w-5 text-gray-300 flex-shrink-0 ml-2" />
                </div>

                <div class="flex items-center gap-3 text-xs text-gray-500 mb-3">
                    <span class="flex items-center gap-1">
                        <Users class="h-3.5 w-3.5" />
                        {{ recipe.servings }} porz.
                    </span>
                    <span v-if="recipe.prep_time_minutes" class="flex items-center gap-1">
                        <Clock class="h-3.5 w-3.5" />
                        {{ recipe.prep_time_minutes + (recipe.cook_time_minutes || 0) }} min
                    </span>
                    <span class="text-gray-400">{{ recipe.ingredients_count }} ingredienti</span>
                </div>

                <div class="grid grid-cols-4 gap-2 rounded-lg bg-gray-50 p-2.5 text-center text-xs">
                    <div>
                        <Flame class="h-3.5 w-3.5 mx-auto text-orange-400 mb-0.5" />
                        <span class="font-semibold text-gray-900">{{ recipe.total_calories }}</span>
                        <span class="text-gray-400 block">kcal</span>
                    </div>
                    <div>
                        <Beef class="h-3.5 w-3.5 mx-auto text-red-400 mb-0.5" />
                        <span class="font-semibold text-gray-900">{{ recipe.total_protein }}g</span>
                        <span class="text-gray-400 block">prot</span>
                    </div>
                    <div>
                        <Wheat class="h-3.5 w-3.5 mx-auto text-amber-400 mb-0.5" />
                        <span class="font-semibold text-gray-900">{{ recipe.total_carbs }}g</span>
                        <span class="text-gray-400 block">carb</span>
                    </div>
                    <div>
                        <Droplets class="h-3.5 w-3.5 mx-auto text-yellow-400 mb-0.5" />
                        <span class="font-semibold text-gray-900">{{ recipe.total_fat }}g</span>
                        <span class="text-gray-400 block">grassi</span>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Pagination -->
        <div v-if="recipes.last_page > 1" class="mt-6 flex justify-center gap-1">
            <Link
                v-for="link in recipes.links"
                :key="link.label"
                :href="link.url || '#'"
                :class="[
                    'rounded-lg px-3 py-2 text-sm',
                    link.active ? 'bg-primary-500 text-white' : 'text-gray-600 hover:bg-gray-100',
                    !link.url ? 'opacity-50 pointer-events-none' : ''
                ]"
                v-html="link.label"
                preserve-state
            />
        </div>
    </AuthenticatedLayout>
</template>
