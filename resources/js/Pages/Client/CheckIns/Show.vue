<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowLeft, Scale, Droplets, Smile, Zap, Moon, Activity, Percent, MessageSquare, Pencil, Check, X } from 'lucide-vue-next';

const props = defineProps<{
    checkIn: any;
    measurementTypes: { value: string; label: string }[];
}>();

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
}

function measurementLabel(type: string) {
    return props.measurementTypes.find(m => m.value === type)?.label || type;
}

function ratingDots(value: number) {
    return value;
}

const editingPatientNotes = ref(false);
const patientNotesDraft = ref(props.checkIn.patient_notes || '');

function savePatientNotes() {
    router.patch(route('client.check-ins.patient-notes', props.checkIn.id), {
        patient_notes: patientNotesDraft.value || null,
    }, {
        preserveScroll: true,
        onSuccess: () => { editingPatientNotes.value = false; },
    });
}

function cancelEditPatientNotes() {
    patientNotesDraft.value = props.checkIn.patient_notes || '';
    editingPatientNotes.value = false;
}
</script>

<template>
    <Head :title="`Monitoraggio ${formatDate(checkIn.date)}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('client.check-ins.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">Monitoraggio {{ formatDate(checkIn.date) }}</h1>
            </div>
        </template>

        <div class="max-w-2xl">
            <!-- Dati principali -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div v-if="checkIn.weight_kg" class="text-center">
                        <Scale class="h-5 w-5 mx-auto text-primary-400 mb-1" />
                        <span class="text-xl font-bold text-gray-900">{{ checkIn.weight_kg }}</span>
                        <span class="text-xs text-gray-500 block">kg</span>
                    </div>
                    <div v-if="checkIn.water_liters" class="text-center">
                        <Droplets class="h-5 w-5 mx-auto text-blue-400 mb-1" />
                        <span class="text-xl font-bold text-gray-900">{{ checkIn.water_liters }}</span>
                        <span class="text-xs text-gray-500 block">litri acqua</span>
                    </div>
                    <div v-if="checkIn.mood" class="text-center">
                        <Smile class="h-5 w-5 mx-auto text-yellow-400 mb-1" />
                        <span class="text-xl font-bold text-gray-900">{{ checkIn.mood }}/5</span>
                        <span class="text-xs text-gray-500 block">umore</span>
                    </div>
                    <div v-if="checkIn.energy_level" class="text-center">
                        <Zap class="h-5 w-5 mx-auto text-orange-400 mb-1" />
                        <span class="text-xl font-bold text-gray-900">{{ checkIn.energy_level }}/5</span>
                        <span class="text-xs text-gray-500 block">energia</span>
                    </div>
                    <div v-if="checkIn.sleep_quality" class="text-center">
                        <Moon class="h-5 w-5 mx-auto text-indigo-400 mb-1" />
                        <span class="text-xl font-bold text-gray-900">{{ checkIn.sleep_quality }}/5</span>
                        <span class="text-xs text-gray-500 block">sonno</span>
                    </div>
                </div>
            </div>

            <!-- Composizione corporea -->
            <div v-if="checkIn.body_fat_percentage || checkIn.lean_mass_kg || checkIn.body_water_percentage" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-3">Composizione corporea</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div v-if="checkIn.body_fat_percentage" class="text-center">
                        <Percent class="h-5 w-5 mx-auto text-rose-400 mb-1" />
                        <span class="text-xl font-bold text-gray-900">{{ checkIn.body_fat_percentage }}%</span>
                        <span class="text-xs text-gray-500 block">massa grassa</span>
                    </div>
                    <div v-if="checkIn.lean_mass_kg" class="text-center">
                        <Activity class="h-5 w-5 mx-auto text-emerald-400 mb-1" />
                        <span class="text-xl font-bold text-gray-900">{{ checkIn.lean_mass_kg }}</span>
                        <span class="text-xs text-gray-500 block">kg massa magra</span>
                    </div>
                    <div v-if="checkIn.body_water_percentage" class="text-center">
                        <Droplets class="h-5 w-5 mx-auto text-cyan-400 mb-1" />
                        <span class="text-xl font-bold text-gray-900">{{ checkIn.body_water_percentage }}%</span>
                        <span class="text-xs text-gray-500 block">acqua corporea</span>
                    </div>
                </div>
            </div>

            <!-- Plicometria -->
            <div v-if="checkIn.skinfold_triceps || checkIn.skinfold_biceps || checkIn.skinfold_subscapular || checkIn.skinfold_suprailiac" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-3">Plicometria</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div v-if="checkIn.skinfold_triceps" class="rounded-lg bg-gray-50 px-4 py-2.5 text-center">
                        <span class="text-xs text-gray-500 block">Tricipitale</span>
                        <span class="text-lg font-semibold text-gray-900">{{ checkIn.skinfold_triceps }} mm</span>
                    </div>
                    <div v-if="checkIn.skinfold_biceps" class="rounded-lg bg-gray-50 px-4 py-2.5 text-center">
                        <span class="text-xs text-gray-500 block">Bicipitale</span>
                        <span class="text-lg font-semibold text-gray-900">{{ checkIn.skinfold_biceps }} mm</span>
                    </div>
                    <div v-if="checkIn.skinfold_subscapular" class="rounded-lg bg-gray-50 px-4 py-2.5 text-center">
                        <span class="text-xs text-gray-500 block">Sottoscapolare</span>
                        <span class="text-lg font-semibold text-gray-900">{{ checkIn.skinfold_subscapular }} mm</span>
                    </div>
                    <div v-if="checkIn.skinfold_suprailiac" class="rounded-lg bg-gray-50 px-4 py-2.5 text-center">
                        <span class="text-xs text-gray-500 block">Sovrailiaca</span>
                        <span class="text-lg font-semibold text-gray-900">{{ checkIn.skinfold_suprailiac }} mm</span>
                    </div>
                </div>
            </div>

            <!-- Misure -->
            <div v-if="checkIn.measurements?.length > 0" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-3">Misure</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div v-for="m in checkIn.measurements" :key="m.id" class="rounded-lg bg-gray-50 px-4 py-2.5 text-center">
                        <span class="text-xs text-gray-500 block">{{ measurementLabel(m.measurement_type) }}</span>
                        <span class="text-lg font-semibold text-gray-900">{{ m.value_cm }} cm</span>
                    </div>
                </div>
            </div>

            <!-- Foto -->
            <div v-if="checkIn.photos?.length > 0" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-3">Foto</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div v-for="photo in checkIn.photos" :key="photo.id" class="rounded-lg overflow-hidden">
                        <img :src="`/storage/${photo.file_path}`" class="w-full h-40 object-cover" />
                        <p class="text-xs text-gray-500 text-center py-1">{{ photo.photo_type }}</p>
                    </div>
                </div>
            </div>

            <!-- Note -->
            <div v-if="checkIn.notes" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <h2 class="text-base font-semibold text-gray-900 mb-2">Le mie note</h2>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ checkIn.notes }}</p>
            </div>

            <!-- Feedback nutrizionista -->
            <div v-if="checkIn.nutritionist_notes" class="rounded-2xl bg-primary-50 border border-primary-100 shadow-sm p-6 mb-6">
                <div class="flex items-center gap-2 mb-2">
                    <MessageSquare class="h-5 w-5 text-primary-600" />
                    <h2 class="text-base font-semibold text-primary-900">Feedback del nutrizionista</h2>
                </div>
                <p class="text-sm text-primary-800 whitespace-pre-line">{{ checkIn.nutritionist_notes }}</p>
            </div>

            <!-- Note paziente (editabili) -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <Pencil class="h-5 w-5 text-amber-500" />
                        <h2 class="text-base font-semibold text-gray-900">Le mie annotazioni</h2>
                    </div>
                    <button
                        v-if="!editingPatientNotes"
                        @click="editingPatientNotes = true"
                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium text-amber-600 hover:bg-amber-50 transition"
                    >
                        <Pencil class="h-3 w-3" /> {{ checkIn.patient_notes ? 'Modifica' : 'Aggiungi' }}
                    </button>
                </div>
                <p class="text-xs text-gray-400 mb-2">Sintomi, aderenza al piano, osservazioni per il nutrizionista.</p>

                <div v-if="editingPatientNotes">
                    <textarea
                        v-model="patientNotesDraft"
                        rows="4"
                        placeholder="Es: Ho avuto mal di stomaco martedì, ho sostituito il latte con quello di soia. Aderenza al piano ~80%."
                        class="w-full rounded-lg border-gray-200 text-sm focus:border-amber-400 focus:ring-amber-400 resize-none"
                    ></textarea>
                    <div class="flex justify-end gap-2 mt-2">
                        <button @click="cancelEditPatientNotes" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-100 transition">
                            <X class="h-3 w-3" /> Annulla
                        </button>
                        <button @click="savePatientNotes" class="inline-flex items-center gap-1 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-600 transition">
                            <Check class="h-3 w-3" /> Salva
                        </button>
                    </div>
                </div>
                <div v-else>
                    <p v-if="checkIn.patient_notes" class="text-sm text-gray-600 whitespace-pre-line">{{ checkIn.patient_notes }}</p>
                    <p v-else class="text-sm text-gray-400 italic">Nessuna annotazione. Clicca "Aggiungi" per scrivere.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
