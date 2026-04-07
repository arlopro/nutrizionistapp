<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    client: any;
    genders: { value: string; label: string }[];
    activityLevels: { value: string; label: string }[];
    goals: { value: string; label: string }[];
}>();

const activeTab = ref<'base' | 'clinical' | 'billing'>('base');

const tabs = [
    { key: 'base',     label: 'Dati base' },
    { key: 'clinical', label: 'Dati clinici' },
    { key: 'billing',  label: 'Fatturazione' },
] as const;

const form = useForm({
    name: props.client.user?.name || '',
    last_name: props.client.user?.last_name || '',
    email: props.client.user?.email || '',
    phone: props.client.user?.phone || '',
    date_of_birth: props.client.date_of_birth?.split('T')[0] || '',
    gender: props.client.gender || '',
    height_cm: props.client.height_cm || '',
    initial_weight_kg: props.client.initial_weight_kg || '',
    activity_level: props.client.activity_level || '',
    goal: props.client.goal || '',
    allergies: props.client.allergies || [],
    intolerances: props.client.intolerances || [],
    pathologies: props.client.pathologies || '',
    dietary_preferences: props.client.dietary_preferences || '',
    notes: props.client.notes || '',
    status: props.client.status || 'active',
    fiscal_code: props.client.fiscal_code || '',
    billing_name: props.client.billing_name || '',
    billing_address: props.client.billing_address || '',
    billing_city: props.client.billing_city || '',
    billing_zip: props.client.billing_zip || '',
    billing_province: props.client.billing_province || '',
    vat_number: props.client.vat_number || '',
});

const allergyInput = ref('');
const intoleranceInput = ref('');

function addAllergy() {
    if (allergyInput.value.trim()) { form.allergies.push(allergyInput.value.trim()); allergyInput.value = ''; }
}
function removeAllergy(i: number) { form.allergies.splice(i, 1); }
function addIntolerance() {
    if (intoleranceInput.value.trim()) { form.intolerances.push(intoleranceInput.value.trim()); intoleranceInput.value = ''; }
}
function removeIntolerance(i: number) { form.intolerances.splice(i, 1); }

function submit() { form.put(route('nutritionist.clients.update', props.client.id)); }

const selectClass = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm';
const textareaClass = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm';
</script>

<template>
    <Head :title="`Modifica ${client.user?.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('nutritionist.clients.show', client.id)" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">Modifica {{ client.user?.name }} {{ client.user?.last_name }}</h1>
            </div>
        </template>

        <form @submit.prevent="submit" class="max-w-3xl">
            <!-- Tab bar -->
            <div class="flex border-b border-gray-200 mb-6">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    @click="activeTab = tab.key"
                    :class="[
                        'px-5 py-3 text-sm font-medium border-b-2 -mb-px transition',
                        activeTab === tab.key
                            ? 'border-primary-500 text-primary-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]"
                >
                    {{ tab.label }}
                </button>
            </div>

            <!-- Tab: Dati base -->
            <div v-show="activeTab === 'base'" class="space-y-6">
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Anagrafica</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="name" value="Nome *" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.name" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="last_name" value="Cognome" />
                            <TextInput id="last_name" v-model="form.last_name" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.last_name" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="email" value="Email *" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.email" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="phone" value="Telefono" />
                            <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel for="date_of_birth" value="Data di nascita" />
                            <TextInput id="date_of_birth" v-model="form.date_of_birth" type="date" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel for="gender" value="Sesso" />
                            <select id="gender" v-model="form.gender" :class="selectClass">
                                <option value="">—</option>
                                <option v-for="g in genders" :key="g.value" :value="g.value">{{ g.label }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="status" value="Stato" />
                            <select id="status" v-model="form.status" :class="selectClass">
                                <option value="active">Attivo</option>
                                <option value="inactive">Inattivo</option>
                                <option value="archived">Archiviato</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Dati clinici -->
            <div v-show="activeTab === 'clinical'" class="space-y-6">
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Dati fisici e obiettivo</h2>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <InputLabel for="height_cm" value="Altezza (cm)" />
                            <TextInput id="height_cm" v-model="form.height_cm" type="number" step="0.1" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel for="initial_weight_kg" value="Peso iniziale (kg)" />
                            <TextInput id="initial_weight_kg" v-model="form.initial_weight_kg" type="number" step="0.1" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel for="activity_level" value="Livello attività" />
                            <select id="activity_level" v-model="form.activity_level" :class="selectClass">
                                <option value="">—</option>
                                <option v-for="a in activityLevels" :key="a.value" :value="a.value">{{ a.label }}</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-3">
                            <InputLabel for="goal" value="Obiettivo" />
                            <select id="goal" v-model="form.goal" :class="selectClass">
                                <option value="">—</option>
                                <option v-for="g in goals" :key="g.value" :value="g.value">{{ g.label }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Allergie e intolleranze</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Allergie" />
                            <div class="flex gap-2 mt-1">
                                <TextInput v-model="allergyInput" type="text" class="block w-full" placeholder="Aggiungi..." @keydown.enter.prevent="addAllergy" />
                                <button type="button" @click="addAllergy" class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">+</button>
                            </div>
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                <span v-for="(a, i) in form.allergies" :key="i" class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700">
                                    {{ a }} <button type="button" @click="removeAllergy(i)" class="hover:text-red-900">&times;</button>
                                </span>
                            </div>
                        </div>
                        <div>
                            <InputLabel value="Intolleranze" />
                            <div class="flex gap-2 mt-1">
                                <TextInput v-model="intoleranceInput" type="text" class="block w-full" placeholder="Aggiungi..." @keydown.enter.prevent="addIntolerance" />
                                <button type="button" @click="addIntolerance" class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">+</button>
                            </div>
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                <span v-for="(t, i) in form.intolerances" :key="i" class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2.5 py-1 text-xs font-medium text-orange-700">
                                    {{ t }} <button type="button" @click="removeIntolerance(i)" class="hover:text-orange-900">&times;</button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Note cliniche</h2>
                    <div class="grid gap-4">
                        <div>
                            <InputLabel for="pathologies" value="Patologie" />
                            <textarea id="pathologies" v-model="form.pathologies" rows="2" :class="textareaClass"></textarea>
                        </div>
                        <div>
                            <InputLabel for="dietary_preferences" value="Preferenze alimentari" />
                            <textarea id="dietary_preferences" v-model="form.dietary_preferences" rows="2" :class="textareaClass"></textarea>
                        </div>
                        <div>
                            <InputLabel for="notes" value="Note" />
                            <textarea id="notes" v-model="form.notes" rows="3" :class="textareaClass"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Fatturazione -->
            <div v-show="activeTab === 'billing'" class="space-y-6">
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-1">Dati di fatturazione</h2>
                    <p class="text-sm text-gray-400 mb-4">Tutti i campi sono opzionali</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="fiscal_code" value="Codice Fiscale" />
                            <TextInput id="fiscal_code" v-model="form.fiscal_code" type="text" maxlength="16" class="mt-1 block w-full uppercase" placeholder="RSSMRA80A01H501Z" />
                            <InputError :message="form.errors.fiscal_code" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="vat_number" value="Partita IVA" />
                            <TextInput id="vat_number" v-model="form.vat_number" type="text" maxlength="20" class="mt-1 block w-full" placeholder="IT12345678901" />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel for="billing_name" value="Ragione sociale / Nome fatturazione" />
                            <TextInput id="billing_name" v-model="form.billing_name" type="text" class="mt-1 block w-full" placeholder="Se diverso dal nome del cliente" />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel for="billing_address" value="Indirizzo" />
                            <TextInput id="billing_address" v-model="form.billing_address" type="text" class="mt-1 block w-full" placeholder="Via Roma, 1" />
                        </div>
                        <div>
                            <InputLabel for="billing_city" value="Città" />
                            <TextInput id="billing_city" v-model="form.billing_city" type="text" class="mt-1 block w-full" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <InputLabel for="billing_zip" value="CAP" />
                                <TextInput id="billing_zip" v-model="form.billing_zip" type="text" maxlength="10" class="mt-1 block w-full" placeholder="20100" />
                            </div>
                            <div>
                                <InputLabel for="billing_province" value="Provincia" />
                                <TextInput id="billing_province" v-model="form.billing_province" type="text" maxlength="5" class="mt-1 block w-full uppercase" placeholder="MI" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Azioni -->
            <div class="flex items-center gap-3 mt-6">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Salva Modifiche</PrimaryButton>
                <Link :href="route('nutritionist.clients.show', client.id)" class="text-sm text-gray-600 hover:text-gray-900">Annulla</Link>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
