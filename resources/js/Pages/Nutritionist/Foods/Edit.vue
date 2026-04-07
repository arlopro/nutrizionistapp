<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
    food: any;
    categories: { value: string; label: string }[];
}>();

const form = useForm({
    name: props.food.name,
    category: props.food.category,
    calories_per_100g: props.food.calories_per_100g,
    protein_per_100g: props.food.protein_per_100g,
    carbs_per_100g: props.food.carbs_per_100g,
    fat_per_100g: props.food.fat_per_100g,
    fiber_per_100g: props.food.fiber_per_100g || '',
    notes: props.food.notes || '',
});

function submit() {
    form.put(route('nutritionist.foods.update', props.food.id));
}
</script>

<template>
    <Head :title="`Modifica ${food.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('nutritionist.foods.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">Modifica {{ food.name }}</h1>
            </div>
        </template>

        <form @submit.prevent="submit" class="max-w-2xl">
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <InputLabel for="name" value="Nome *" />
                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.name" class="mt-1" />
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel for="category" value="Categoria *" />
                        <select id="category" v-model="form.category" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                            <option v-for="c in categories" :key="c.value" :value="c.value">{{ c.label }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Valori nutrizionali (per 100g)</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <InputLabel for="calories" value="Calorie (kcal) *" />
                        <TextInput id="calories" v-model="form.calories_per_100g" type="number" step="0.1" min="0" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <InputLabel for="protein" value="Proteine (g) *" />
                        <TextInput id="protein" v-model="form.protein_per_100g" type="number" step="0.1" min="0" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <InputLabel for="carbs" value="Carboidrati (g) *" />
                        <TextInput id="carbs" v-model="form.carbs_per_100g" type="number" step="0.1" min="0" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <InputLabel for="fat" value="Grassi (g) *" />
                        <TextInput id="fat" v-model="form.fat_per_100g" type="number" step="0.1" min="0" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <InputLabel for="fiber" value="Fibre (g)" />
                        <TextInput id="fiber" v-model="form.fiber_per_100g" type="number" step="0.1" min="0" class="mt-1 block w-full" />
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <InputLabel for="notes" value="Note" />
                <textarea id="notes" v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"></textarea>
            </div>

            <div class="flex items-center gap-3">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Salva Modifiche</PrimaryButton>
                <Link :href="route('nutritionist.foods.index')" class="text-sm text-gray-600 hover:text-gray-900">Annulla</Link>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
