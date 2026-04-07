<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
    plan: any;
    clients: any[];
    statuses: { value: string; label: string }[];
}>();

const form = useForm({
    title: props.plan.title,
    description: props.plan.description || '',
    start_date: props.plan.start_date?.slice(0, 10) || '',
    end_date: props.plan.end_date?.slice(0, 10) || '',
    daily_calories: props.plan.daily_calories || '',
    protein_grams: props.plan.protein_grams || '',
    carbs_grams: props.plan.carbs_grams || '',
    fat_grams: props.plan.fat_grams || '',
    status: props.plan.status,
    notes: props.plan.notes || '',
});

function submit() {
    form.put(route('nutritionist.plans.update', props.plan.id));
}
</script>

<template>
    <Head :title="`Modifica ${plan.title}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('nutritionist.plans.show', plan.id)" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">Modifica {{ plan.title }}</h1>
            </div>
        </template>

        <form @submit.prevent="submit" class="max-w-2xl">
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <InputLabel for="title" value="Titolo *" />
                        <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.title" class="mt-1" />
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel for="description" value="Descrizione" />
                        <textarea id="description" v-model="form.description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"></textarea>
                    </div>
                    <div>
                        <InputLabel for="start_date" value="Data inizio *" />
                        <TextInput id="start_date" v-model="form.start_date" type="date" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.start_date" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="end_date" value="Data fine" />
                        <TextInput id="end_date" v-model="form.end_date" type="date" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel for="status" value="Stato" />
                        <select id="status" v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                            <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Obiettivi macro giornalieri</h2>
                <div class="grid gap-4 sm:grid-cols-4">
                    <div>
                        <InputLabel for="daily_calories" value="Calorie (kcal)" />
                        <TextInput id="daily_calories" v-model="form.daily_calories" type="number" min="0" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel for="protein_grams" value="Proteine (g)" />
                        <TextInput id="protein_grams" v-model="form.protein_grams" type="number" min="0" step="0.1" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel for="carbs_grams" value="Carboidrati (g)" />
                        <TextInput id="carbs_grams" v-model="form.carbs_grams" type="number" min="0" step="0.1" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel for="fat_grams" value="Grassi (g)" />
                        <TextInput id="fat_grams" v-model="form.fat_grams" type="number" min="0" step="0.1" class="mt-1 block w-full" />
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <InputLabel for="notes" value="Note" />
                <textarea id="notes" v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"></textarea>
            </div>

            <div class="flex items-center gap-3">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Salva Modifiche</PrimaryButton>
                <Link :href="route('nutritionist.plans.show', plan.id)" class="text-sm text-gray-600 hover:text-gray-900">Annulla</Link>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
