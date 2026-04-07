<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { BookTemplate, Trash2, Flame, Beef, Wheat, Droplets, UtensilsCrossed, ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
    templates: any[];
}>();

function deleteTemplate(id: number, name: string) {
    if (!confirm(`Eliminare il template "${name}"?`)) return;
    router.delete(route('nutritionist.plans.templates.destroy', id));
}
</script>

<template>
    <Head title="Template Piani" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('nutritionist.plans.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <h1 class="text-xl font-semibold text-gray-900">Template Piani</h1>
                </div>
                <Link
                    :href="route('nutritionist.plans.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 px-4 py-2 text-sm font-medium text-white hover:from-primary-600 hover:to-primary-700 transition shadow-sm"
                >
                    <UtensilsCrossed class="h-4 w-4" />
                    Nuovo Piano da Template
                </Link>
            </div>
        </template>

        <!-- Empty state -->
        <div v-if="templates.length === 0" class="rounded-2xl bg-white border border-gray-100 p-16 text-center shadow-sm">
            <BookTemplate class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun template</h3>
            <p class="text-sm text-gray-500 mb-4">Apri un piano e clicca "Salva come template" per crearne uno.</p>
        </div>

        <!-- Template grid -->
        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="tpl in templates"
                :key="tpl.id"
                class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5 flex flex-col"
            >
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 truncate">{{ tpl.template_name }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5 truncate">{{ tpl.title }}</p>
                    </div>
                    <button @click="deleteTemplate(tpl.id, tpl.template_name)" class="ml-2 p-1.5 text-gray-300 hover:text-red-500 transition flex-shrink-0">
                        <Trash2 class="h-4 w-4" />
                    </button>
                </div>

                <!-- Macro -->
                <div v-if="tpl.daily_calories" class="flex gap-3 text-xs mb-3">
                    <span class="flex items-center gap-1 text-gray-500"><Flame class="h-3.5 w-3.5 text-orange-400" /> {{ tpl.daily_calories }} kcal</span>
                    <span v-if="tpl.protein_grams" class="flex items-center gap-1 text-gray-500"><Beef class="h-3.5 w-3.5 text-red-400" /> {{ tpl.protein_grams }}g P</span>
                    <span v-if="tpl.carbs_grams" class="flex items-center gap-1 text-gray-500"><Wheat class="h-3.5 w-3.5 text-amber-400" /> {{ tpl.carbs_grams }}g C</span>
                    <span v-if="tpl.fat_grams" class="flex items-center gap-1 text-gray-500"><Droplets class="h-3.5 w-3.5 text-yellow-400" /> {{ tpl.fat_grams }}g G</span>
                </div>

                <p class="text-xs text-gray-400 mb-4">{{ tpl.meals_count }} pasti configurati</p>

                <div class="mt-auto">
                    <Link
                        :href="route('nutritionist.plans.create', { template_id: tpl.id })"
                        class="block w-full text-center rounded-lg bg-primary-50 px-4 py-2 text-sm font-medium text-primary-700 hover:bg-primary-100 transition"
                    >
                        Usa questo template
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
