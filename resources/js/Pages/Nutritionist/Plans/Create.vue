<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, BookTemplate, Flame, Beef, Wheat, Droplets } from 'lucide-vue-next';

const props = defineProps<{
    clients: any[];
    statuses: { value: string; label: string }[];
    templates: any[];
}>();

// Legge template_id dall'URL se presente
const urlParams = new URLSearchParams(window.location.search);
const preselectedTemplateId = urlParams.get('template_id') || '';

const form = useForm({
    client_id: '',
    title: '',
    description: '',
    start_date: new Date().toISOString().slice(0, 10),
    end_date: '',
    daily_calories: '',
    protein_grams: '',
    carbs_grams: '',
    fat_grams: '',
    status: 'draft',
    notes: '',
    template_id: preselectedTemplateId,
});

function selectTemplate(tpl: any) {
    form.template_id = tpl.id.toString();
    if (!form.title) form.title = tpl.title;
    if (!form.daily_calories && tpl.daily_calories) form.daily_calories = tpl.daily_calories;
    if (!form.protein_grams && tpl.protein_grams) form.protein_grams = tpl.protein_grams;
    if (!form.carbs_grams && tpl.carbs_grams) form.carbs_grams = tpl.carbs_grams;
    if (!form.fat_grams && tpl.fat_grams) form.fat_grams = tpl.fat_grams;
}

function clearTemplate() {
    form.template_id = '';
}

const selectedTemplate = () => props.templates.find(t => t.id.toString() === form.template_id);

function submit() {
    form.post(route('nutritionist.plans.store'));
}
</script>

<template>
    <Head title="Nuovo Piano Nutrizionale" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('nutritionist.plans.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">Nuovo Piano Nutrizionale</h1>
            </div>
        </template>

        <form @submit.prevent="submit" class="max-w-2xl">
            <!-- Template picker -->
            <div v-if="templates.length > 0" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <BookTemplate class="h-5 w-5 text-primary-500" />
                    <h2 class="text-base font-semibold text-gray-900">Parti da un template</h2>
                    <span class="text-xs text-gray-400">(opzionale)</span>
                </div>

                <!-- Template selezionato -->
                <div v-if="selectedTemplate()" class="rounded-xl bg-primary-50 border border-primary-100 p-4 flex items-center justify-between">
                    <div>
                        <p class="font-medium text-primary-800">{{ selectedTemplate()!.template_name }}</p>
                        <div class="flex gap-3 text-xs text-primary-600 mt-1">
                            <span v-if="selectedTemplate()!.daily_calories" class="flex items-center gap-1">
                                <Flame class="h-3 w-3" /> {{ selectedTemplate()!.daily_calories }} kcal
                            </span>
                            <span>{{ selectedTemplate()!.meals_count }} pasti</span>
                        </div>
                    </div>
                    <button type="button" @click="clearTemplate" class="text-xs text-primary-600 hover:text-primary-800 underline">Rimuovi</button>
                </div>

                <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <button
                        v-for="tpl in templates"
                        :key="tpl.id"
                        type="button"
                        @click="selectTemplate(tpl)"
                        class="rounded-xl border border-gray-200 p-3 text-left hover:border-primary-300 hover:bg-primary-50 transition"
                    >
                        <p class="text-sm font-medium text-gray-900 truncate">{{ tpl.template_name }}</p>
                        <div class="flex gap-2 text-xs text-gray-400 mt-1 flex-wrap">
                            <span v-if="tpl.daily_calories" class="flex items-center gap-0.5"><Flame class="h-3 w-3 text-orange-400" />{{ tpl.daily_calories }}</span>
                            <span v-if="tpl.protein_grams" class="flex items-center gap-0.5"><Beef class="h-3 w-3 text-red-400" />{{ tpl.protein_grams }}g</span>
                            <span v-if="tpl.carbs_grams" class="flex items-center gap-0.5"><Wheat class="h-3 w-3 text-amber-400" />{{ tpl.carbs_grams }}g</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Dati piano -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <InputLabel for="client_id" value="Cliente *" />
                        <select id="client_id" v-model="form.client_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                            <option value="">Seleziona cliente...</option>
                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.user.name }}</option>
                        </select>
                        <InputError :message="form.errors.client_id" class="mt-1" />
                    </div>
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
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ form.template_id ? 'Crea Piano da Template' : 'Crea Piano' }}
                </PrimaryButton>
                <Link :href="route('nutritionist.plans.index')" class="text-sm text-gray-600 hover:text-gray-900">Annulla</Link>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
