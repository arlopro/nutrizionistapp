<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Trash2, Upload } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    measurementTypes: { value: string; label: string }[];
    photoTypes: { value: string; label: string }[];
}>();

const form = useForm({
    date: new Date().toISOString().slice(0, 10),
    weight_kg: '',
    notes: '',
    mood: null as number | null,
    energy_level: null as number | null,
    sleep_quality: null as number | null,
    water_liters: '',
    measurements: [] as { type: string; value: string }[],
    photos: [] as { file: File | null; type: string }[],
});

function addMeasurement() {
    form.measurements.push({ type: '', value: '' });
}

function removeMeasurement(index: number) {
    form.measurements.splice(index, 1);
}

const photoInputRef = ref<HTMLInputElement | null>(null);
const photoTypeToAdd = ref('front');

function previewUrl(file: File): string {
    return globalThis.URL.createObjectURL(file);
}

function triggerPhotoUpload() {
    photoInputRef.value?.click();
}

function onPhotoSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (input.files) {
        for (const file of Array.from(input.files)) {
            form.photos.push({ file, type: photoTypeToAdd.value });
        }
    }
    input.value = '';
}

function removePhoto(index: number) {
    form.photos.splice(index, 1);
}

function setRating(field: 'mood' | 'energy_level' | 'sleep_quality', value: number) {
    (form as any)[field] = (form as any)[field] === value ? null : value;
}

function submit() {
    form.post(route('client.check-ins.store'), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Nuovo Monitoraggio" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('client.check-ins.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">Nuovo Monitoraggio</h1>
            </div>
        </template>

        <form @submit.prevent="submit" class="max-w-2xl">
            <!-- Data e peso -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel for="date" value="Data *" />
                        <TextInput id="date" v-model="form.date" type="date" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.date" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="weight" value="Peso (kg)" />
                        <TextInput id="weight" v-model="form.weight_kg" type="number" step="0.1" min="20" max="300" class="mt-1 block w-full" placeholder="es. 75.5" />
                        <InputError :message="form.errors.weight_kg" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="water" value="Acqua giornaliera (litri)" />
                        <TextInput id="water" v-model="form.water_liters" type="number" step="0.1" min="0" max="10" class="mt-1 block w-full" placeholder="es. 2.0" />
                    </div>
                </div>
            </div>

            <!-- Benessere -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Come ti senti?</h2>
                <div class="grid gap-6 sm:grid-cols-3">
                    <div v-for="field in [
                        { key: 'mood' as const, label: 'Umore' },
                        { key: 'energy_level' as const, label: 'Energia' },
                        { key: 'sleep_quality' as const, label: 'Sonno' },
                    ]" :key="field.key">
                        <p class="text-sm font-medium text-gray-700 mb-2">{{ field.label }}</p>
                        <div class="flex gap-1">
                            <button
                                v-for="n in 5"
                                :key="n"
                                type="button"
                                @click="setRating(field.key, n)"
                                :class="[
                                    'w-9 h-9 rounded-lg text-sm font-medium transition',
                                    (form as any)[field.key] === n
                                        ? 'bg-primary-500 text-white'
                                        : 'bg-gray-100 text-gray-500 hover:bg-gray-200'
                                ]"
                            >
                                {{ n }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Misure -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-gray-900">Misure corporee (cm)</h2>
                    <button type="button" @click="addMeasurement" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-medium text-primary-600 hover:bg-primary-50 transition">
                        <Plus class="h-3.5 w-3.5" /> Aggiungi misura
                    </button>
                </div>
                <div v-if="form.measurements.length === 0" class="text-center py-4 text-sm text-gray-400">
                    Nessuna misura. Clicca "Aggiungi misura" per iniziare.
                </div>
                <div v-else class="space-y-2">
                    <div v-for="(m, index) in form.measurements" :key="index" class="flex items-center gap-3">
                        <select v-model="m.type" class="flex-1 rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Tipo...</option>
                            <option v-for="mt in measurementTypes" :key="mt.value" :value="mt.value">{{ mt.label }}</option>
                        </select>
                        <input v-model="m.value" type="number" step="0.1" min="0" placeholder="cm" class="w-24 rounded-md border-gray-300 text-sm text-center focus:border-primary-500 focus:ring-primary-500" />
                        <button type="button" @click="removeMeasurement(index)" class="p-1 text-gray-400 hover:text-red-500"><Trash2 class="h-4 w-4" /></button>
                    </div>
                </div>
            </div>

            <!-- Foto -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-gray-900">Foto progresso</h2>
                    <div class="flex items-center gap-2">
                        <select v-model="photoTypeToAdd" class="rounded-md border-gray-300 text-xs focus:border-primary-500 focus:ring-primary-500">
                            <option v-for="pt in photoTypes" :key="pt.value" :value="pt.value">{{ pt.label }}</option>
                        </select>
                        <button type="button" @click="triggerPhotoUpload" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-medium text-primary-600 hover:bg-primary-50 transition">
                            <Upload class="h-3.5 w-3.5" /> Carica
                        </button>
                    </div>
                </div>
                <input ref="photoInputRef" type="file" accept="image/*" multiple class="hidden" @change="onPhotoSelected" />
                <div v-if="form.photos.length === 0" class="text-center py-4 text-sm text-gray-400">
                    Nessuna foto caricata.
                </div>
                <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div v-for="(photo, index) in form.photos" :key="index" class="relative rounded-lg overflow-hidden bg-gray-100">
                        <img v-if="photo.file" :src="previewUrl(photo.file)" class="w-full h-32 object-cover" />
                        <div class="absolute bottom-0 left-0 right-0 bg-black/50 px-2 py-1 flex items-center justify-between">
                            <span class="text-xs text-white">{{ photoTypes.find(t => t.value === photo.type)?.label }}</span>
                            <button type="button" @click="removePhoto(index)" class="text-white/80 hover:text-white"><Trash2 class="h-3 w-3" /></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Note -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <InputLabel for="notes" value="Note" />
                <textarea id="notes" v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" placeholder="Come sta andando il piano? Difficoltà, osservazioni..."></textarea>
            </div>

            <div class="flex items-center gap-3">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Salva monitoraggio</PrimaryButton>
                <Link :href="route('client.check-ins.index')" class="text-sm text-gray-600 hover:text-gray-900">Annulla</Link>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
