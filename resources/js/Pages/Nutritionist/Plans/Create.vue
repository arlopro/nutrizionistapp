<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ArrowLeft, BookTemplate, Flame, Beef, Wheat, Droplets, Calculator, Zap, ChevronDown, ChevronUp } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps<{
    clients: any[];
    statuses: { value: string; label: string }[];
    templates: any[];
    activityLevels: { value: string; label: string }[];
    goals: { value: string; label: string }[];
    genders: { value: string; label: string }[];
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

// --- TDEE Calculator ---
const showCalculator = ref(false);
const calculating = ref(false);
const tdeeResult = ref<any>(null);

const tdeeForm = ref({
    weight_kg: '',
    height_cm: '',
    age: '',
    gender: '',
    activity_level: '',
    goal: '',
    formula: 'mifflin',
});

const selectedClient = computed(() => {
    if (!form.client_id) return null;
    return props.clients.find(c => c.id.toString() === form.client_id.toString());
});

// When client changes, pre-fill TDEE calculator with client data
watch(() => form.client_id, (newId) => {
    tdeeResult.value = null;
    if (!newId) return;
    const client = props.clients.find(c => c.id.toString() === newId.toString());
    if (!client) return;

    if (client.height_cm) tdeeForm.value.height_cm = client.height_cm;
    if (client.initial_weight_kg) tdeeForm.value.weight_kg = client.initial_weight_kg;
    if (client.current_weight) tdeeForm.value.weight_kg = client.current_weight;
    if (client.gender) tdeeForm.value.gender = client.gender;
    if (client.activity_level) tdeeForm.value.activity_level = client.activity_level;
    if (client.goal) tdeeForm.value.goal = client.goal;
    if (client.date_of_birth) {
        const dob = new Date(client.date_of_birth);
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
        tdeeForm.value.age = age.toString();
    }
});

const canCalculate = computed(() => {
    const f = tdeeForm.value;
    return f.weight_kg && f.height_cm && f.age && f.gender && f.activity_level;
});

async function calculateTdee() {
    if (!canCalculate.value) return;
    calculating.value = true;
    try {
        const { data } = await axios.post(route('nutritionist.plans.calculate-tdee'), {
            client_id: form.client_id || null,
            ...tdeeForm.value,
            age: parseInt(tdeeForm.value.age),
        });
        tdeeResult.value = data;
    } catch (e) {
        console.error(e);
    } finally {
        calculating.value = false;
    }
}

function applyTdeeResult() {
    if (!tdeeResult.value) return;
    form.daily_calories = tdeeResult.value.daily_calories;
    form.protein_grams = tdeeResult.value.protein_grams;
    form.carbs_grams = tdeeResult.value.carbs_grams;
    form.fat_grams = tdeeResult.value.fat_grams;
}

const formulaLabels: Record<string, string> = {
    mifflin: 'Mifflin-St Jeor',
    harris_benedict: 'Harris-Benedict',
};
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

            <!-- Obiettivi macro + TDEE calculator -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-gray-900">Obiettivi macro giornalieri</h2>
                    <button
                        type="button"
                        @click="showCalculator = !showCalculator"
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-600 hover:text-primary-800 transition"
                    >
                        <Calculator class="h-4 w-4" />
                        Calcolatore TDEE
                        <ChevronUp v-if="showCalculator" class="h-3.5 w-3.5" />
                        <ChevronDown v-else class="h-3.5 w-3.5" />
                    </button>
                </div>

                <!-- TDEE Calculator panel -->
                <div v-if="showCalculator" class="rounded-xl bg-gradient-to-br from-primary-50 to-blue-50 border border-primary-100 p-5 mb-5">
                    <div class="flex items-center gap-2 mb-4">
                        <Zap class="h-4 w-4 text-primary-600" />
                        <span class="text-sm font-semibold text-primary-800">Calcolo fabbisogno calorico</span>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3 mb-4">
                        <div>
                            <InputLabel value="Peso (kg) *" class="text-xs" />
                            <TextInput v-model="tdeeForm.weight_kg" type="number" step="0.1" min="20" class="mt-1 block w-full text-sm" placeholder="75.0" />
                        </div>
                        <div>
                            <InputLabel value="Altezza (cm) *" class="text-xs" />
                            <TextInput v-model="tdeeForm.height_cm" type="number" step="0.1" min="50" class="mt-1 block w-full text-sm" placeholder="175" />
                        </div>
                        <div>
                            <InputLabel value="Età *" class="text-xs" />
                            <TextInput v-model="tdeeForm.age" type="number" min="1" max="150" class="mt-1 block w-full text-sm" placeholder="30" />
                        </div>
                        <div>
                            <InputLabel value="Sesso *" class="text-xs" />
                            <select v-model="tdeeForm.gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                <option value="">Seleziona...</option>
                                <option v-for="g in genders" :key="g.value" :value="g.value">{{ g.label }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Livello attività *" class="text-xs" />
                            <select v-model="tdeeForm.activity_level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                <option value="">Seleziona...</option>
                                <option v-for="a in activityLevels" :key="a.value" :value="a.value">{{ a.label }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Obiettivo" class="text-xs" />
                            <select v-model="tdeeForm.goal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                <option value="">Mantenimento</option>
                                <option v-for="g in goals" :key="g.value" :value="g.value">{{ g.label }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mb-4">
                        <InputLabel value="Formula:" class="text-xs whitespace-nowrap" />
                        <label class="inline-flex items-center gap-1.5 text-sm cursor-pointer">
                            <input type="radio" v-model="tdeeForm.formula" value="mifflin" class="text-primary-600 focus:ring-primary-500" />
                            Mifflin-St Jeor
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-sm cursor-pointer">
                            <input type="radio" v-model="tdeeForm.formula" value="harris_benedict" class="text-primary-600 focus:ring-primary-500" />
                            Harris-Benedict
                        </label>
                    </div>

                    <button
                        type="button"
                        @click="calculateTdee"
                        :disabled="!canCalculate || calculating"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        <Calculator class="h-4 w-4" />
                        {{ calculating ? 'Calcolo...' : 'Calcola' }}
                    </button>

                    <!-- TDEE Results -->
                    <div v-if="tdeeResult" class="mt-4 rounded-lg bg-white border border-primary-200 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-gray-800">Risultato ({{ formulaLabels[tdeeResult.formula] }})</span>
                            <button
                                type="button"
                                @click="applyTdeeResult"
                                class="inline-flex items-center gap-1 rounded-md bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700 transition"
                            >
                                Applica valori
                            </button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                            <div class="rounded-lg bg-gray-50 p-2.5">
                                <p class="text-xs text-gray-500">BMR</p>
                                <p class="text-lg font-bold text-gray-900">{{ tdeeResult.bmr }}</p>
                                <p class="text-xs text-gray-400">kcal</p>
                            </div>
                            <div class="rounded-lg bg-blue-50 p-2.5">
                                <p class="text-xs text-blue-600">TDEE</p>
                                <p class="text-lg font-bold text-blue-700">{{ tdeeResult.tdee }}</p>
                                <p class="text-xs text-blue-400">kcal</p>
                            </div>
                            <div class="rounded-lg bg-orange-50 p-2.5 sm:col-span-2">
                                <p class="text-xs text-orange-600">Target giornaliero</p>
                                <p class="text-lg font-bold text-orange-700">{{ tdeeResult.daily_calories }}</p>
                                <p class="text-xs text-orange-400">kcal{{ tdeeResult.goal ? ' (' + goals.find(g => g.value === tdeeResult.goal)?.label + ')' : '' }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3 mt-3 text-center">
                            <div class="rounded-lg bg-red-50 p-2">
                                <p class="text-xs text-red-500">Proteine</p>
                                <p class="text-base font-bold text-red-700">{{ tdeeResult.protein_grams }}g</p>
                            </div>
                            <div class="rounded-lg bg-amber-50 p-2">
                                <p class="text-xs text-amber-500">Carboidrati</p>
                                <p class="text-base font-bold text-amber-700">{{ tdeeResult.carbs_grams }}g</p>
                            </div>
                            <div class="rounded-lg bg-yellow-50 p-2">
                                <p class="text-xs text-yellow-600">Grassi</p>
                                <p class="text-base font-bold text-yellow-700">{{ tdeeResult.fat_grams }}g</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual macro inputs -->
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
