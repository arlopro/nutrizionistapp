<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Plus, Search, Apple, Flame, Beef, Wheat, Droplets, Pencil, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    foods: any;
    filters: { search?: string; category?: string };
    categories: { value: string; label: string }[];
}>();

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');

function confirmDelete(name: string, id: number) {
    if (!confirm('Eliminare ' + name + '?')) return;
    router.delete(route('nutritionist.foods.destroy', id));
}

let debounceTimer: any;
watch([search, category], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(route('nutritionist.foods.index'), {
            search: search.value || undefined,
            category: category.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
});

function categoryColor(c: string) {
    const map: Record<string, string> = {
        protein: 'bg-red-50 text-red-700',
        carbohydrate: 'bg-amber-50 text-amber-700',
        vegetable: 'bg-green-50 text-green-700',
        fruit: 'bg-purple-50 text-purple-700',
        dairy: 'bg-blue-50 text-blue-700',
        fat: 'bg-yellow-50 text-yellow-700',
        generic: 'bg-gray-50 text-gray-700',
    };
    return map[c] || 'bg-gray-50 text-gray-700';
}

function categoryLabel(c: string) {
    return props.categories.find(cat => cat.value === c)?.label || c;
}
</script>

<template>
    <Head title="Alimenti" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Alimenti</h1>
                <Link
                    :href="route('nutritionist.foods.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 px-4 py-2 text-sm font-medium text-white hover:from-primary-600 hover:to-primary-700 transition shadow-sm"
                >
                    <Plus class="h-4 w-4" />
                    Nuovo Alimento
                </Link>
            </div>
        </template>

        <!-- Filters -->
        <div class="mb-6 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cerca alimento..."
                    class="w-full rounded-lg border-gray-200 pl-10 pr-4 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500"
                />
            </div>
            <select
                v-model="category"
                class="rounded-lg border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500"
            >
                <option value="">Tutte le categorie</option>
                <option v-for="c in categories" :key="c.value" :value="c.value">{{ c.label }}</option>
            </select>
        </div>

        <!-- Empty state -->
        <div v-if="foods.data.length === 0" class="rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm">
            <Apple class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun alimento trovato</h3>
            <p class="text-sm text-gray-500 mb-6">Aggiungi alimenti per poterli usare nei piani nutrizionali.</p>
        </div>

        <!-- Food table -->
        <div v-else class="rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Alimento</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 hidden sm:table-cell">Categoria</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500">
                            <Flame class="inline h-3.5 w-3.5" /> Kcal
                        </th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 hidden md:table-cell">
                            <Beef class="inline h-3.5 w-3.5" /> Prot
                        </th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 hidden md:table-cell">
                            <Wheat class="inline h-3.5 w-3.5" /> Carb
                        </th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 hidden md:table-cell">
                            <Droplets class="inline h-3.5 w-3.5" /> Grassi
                        </th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="food in foods.data" :key="food.id" class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ food.name }}</p>
                            <span v-if="!food.nutritionist_id" class="text-xs text-gray-400">Globale</span>
                        </td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell">
                            <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', categoryColor(food.category)]">
                                {{ categoryLabel(food.category) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-medium text-gray-900">{{ food.calories_per_100g }}</td>
                        <td class="px-4 py-3 text-center text-gray-600 hidden md:table-cell">{{ food.protein_per_100g }}g</td>
                        <td class="px-4 py-3 text-center text-gray-600 hidden md:table-cell">{{ food.carbs_per_100g }}g</td>
                        <td class="px-4 py-3 text-center text-gray-600 hidden md:table-cell">{{ food.fat_per_100g }}g</td>
                        <td class="px-4 py-3 text-right">
                            <div v-if="food.nutritionist_id" class="inline-flex items-center gap-1">
                                <Link
                                    :href="route('nutritionist.foods.edit', food.id)"
                                    class="p-1.5 text-gray-400 hover:text-primary-600 transition rounded"
                                    title="Modifica"
                                >
                                    <Pencil class="h-4 w-4" />
                                </Link>
                                <button
                                    type="button"
                                    @click="confirmDelete(food.name, food.id)"
                                    class="p-1.5 text-gray-400 hover:text-red-500 transition rounded"
                                    title="Elimina"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="foods.last_page > 1" class="mt-6 flex justify-center gap-1">
            <Link
                v-for="link in foods.links"
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
