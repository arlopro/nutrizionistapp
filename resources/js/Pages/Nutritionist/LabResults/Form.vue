<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps<{
    labResult?: any;
    clients: any[];
    selectedClientId?: number | null;
    isEdit?: boolean;
}>();

const markers = [
    { key: 'glucose', label: 'Glicemia', unit: 'mg/dL', range: '70 – 100' },
    { key: 'hba1c', label: 'HbA1c', unit: '%', range: '< 5.7' },
    { key: 'total_cholesterol', label: 'Colesterolo Totale', unit: 'mg/dL', range: '< 200' },
    { key: 'hdl_cholesterol', label: 'Colesterolo HDL', unit: 'mg/dL', range: '> 40' },
    { key: 'ldl_cholesterol', label: 'Colesterolo LDL', unit: 'mg/dL', range: '< 100' },
    { key: 'triglycerides', label: 'Trigliceridi', unit: 'mg/dL', range: '< 150' },
    { key: 'creatinine', label: 'Creatinina', unit: 'mg/dL', range: '0.6 – 1.2' },
    { key: 'tsh', label: 'TSH', unit: 'mUI/L', range: '0.4 – 4.0' },
    { key: 'crp', label: 'PCR', unit: 'mg/L', range: '< 5' },
    { key: 'zonulin', label: 'Zonulina', unit: 'ng/mL', range: '< 48' },
    { key: 'calprotectin', label: 'Calprotectina', unit: 'µg/g', range: '< 50' },
] as const;

const form = useForm({
    client_id: props.labResult?.client_id ?? props.selectedClientId ?? '',
    date: props.labResult?.date?.split('T')[0] ?? new Date().toISOString().split('T')[0],
    lab_name: props.labResult?.lab_name ?? '',
    notes: props.labResult?.notes ?? '',
    glucose: props.labResult?.glucose ?? '',
    hba1c: props.labResult?.hba1c ?? '',
    total_cholesterol: props.labResult?.total_cholesterol ?? '',
    hdl_cholesterol: props.labResult?.hdl_cholesterol ?? '',
    ldl_cholesterol: props.labResult?.ldl_cholesterol ?? '',
    triglycerides: props.labResult?.triglycerides ?? '',
    creatinine: props.labResult?.creatinine ?? '',
    tsh: props.labResult?.tsh ?? '',
    crp: props.labResult?.crp ?? '',
    zonulin: props.labResult?.zonulin ?? '',
    calprotectin: props.labResult?.calprotectin ?? '',
});

function submit() {
    if (props.isEdit && props.labResult) {
        form.put(route('nutritionist.lab-results.update', props.labResult.id));
    } else {
        form.post(route('nutritionist.lab-results.store'));
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Modifica Esame' : 'Nuovo Esame'" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('nutritionist.lab-results.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">{{ isEdit ? 'Modifica Esame' : 'Nuovo Esame Ematochimico' }}</h1>
            </div>
        </template>

        <form @submit.prevent="submit" class="space-y-6 max-w-3xl">
            <!-- Client + Date + Lab -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <InputLabel value="Cliente" />
                        <select v-model="form.client_id" :disabled="isEdit" class="mt-1 w-full rounded-lg border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-50">
                            <option value="">Seleziona cliente</option>
                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.user.name }}</option>
                        </select>
                        <InputError :message="form.errors.client_id" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Data esame" />
                        <TextInput v-model="form.date" type="date" class="mt-1 w-full" />
                        <InputError :message="form.errors.date" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Laboratorio" />
                        <TextInput v-model="form.lab_name" type="text" class="mt-1 w-full" placeholder="Nome laboratorio" />
                        <InputError :message="form.errors.lab_name" class="mt-1" />
                    </div>
                </div>
            </div>

            <!-- Markers -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Valori ematochimici</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="m in markers" :key="m.key">
                        <InputLabel :value="`${m.label} (${m.unit})`" />
                        <TextInput v-model="(form as any)[m.key]" type="number" step="0.01" class="mt-1 w-full" :placeholder="m.range" />
                        <InputError :message="(form.errors as any)[m.key]" class="mt-1" />
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                <InputLabel value="Note" />
                <textarea v-model="form.notes" rows="3" class="mt-1 w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Note aggiuntive..."></textarea>
                <InputError :message="form.errors.notes" class="mt-1" />
            </div>

            <div class="flex justify-end">
                <PrimaryButton :disabled="form.processing">
                    {{ isEdit ? 'Aggiorna' : 'Salva esame' }}
                </PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
