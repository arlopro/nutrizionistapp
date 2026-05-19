<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { computed } from 'vue';
import LabResultsList from '@/Components/LabResultsList.vue';
import NutritionistNotes from '@/Components/NutritionistNotes.vue';
import { Send, CheckCircle, Clock, FileText } from 'lucide-vue-next';

const props = defineProps<{
    client: any;
    anamnesisTemplates: { id: number; name: string }[];
}>();

// Latest completed anamnesis submission
const latestSubmission = computed(() =>
    (props.client.anamnesis_submissions ?? []).find((s: any) => s.status === 'completed')
);

// Send questionnaire modal
const showSendModal = ref(false);
const selectedTemplateId = ref<number | null>(null);
const sending = ref(false);

function sendAnamnesis() {
    if (!selectedTemplateId.value) return;
    sending.value = true;
    router.post(route('nutritionist.anamnesis.send'), {
        template_id: selectedTemplateId.value,
        client_id: props.client.id,
    }, {
        preserveScroll: true,
        onFinish: () => { sending.value = false; showSendModal.value = false; selectedTemplateId.value = null; },
    });
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <div class="grid gap-5 lg:grid-cols-2">
        <!-- Left: anamnesi -->
        <div class="space-y-5">
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <FileText class="h-4 w-4 text-gray-400" /> Anamnesi
                    </p>
                    <button
                        v-if="anamnesisTemplates.length"
                        @click="showSendModal = true"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-100 transition"
                    >
                        <Send class="h-3.5 w-3.5" /> Invia questionario
                    </button>
                </div>

                <!-- Anamnesi fields from latest submission or client profile -->
                <div class="space-y-3">
                    <div class="grid grid-cols-[130px,1fr] gap-y-3 text-sm">
                        <template v-if="client.allergies?.length || client.intolerances?.length">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 pt-0.5">Allergie e intolleranze</dt>
                            <dd class="text-gray-700">
                                <span v-if="!client.allergies?.length && !client.intolerances?.length" class="text-gray-400">Nessuna nota</span>
                                <span v-else>
                                    {{ [...(client.allergies ?? []), ...(client.intolerances ?? [])].join(', ') }}
                                </span>
                            </dd>
                        </template>
                        <template v-if="client.pathologies">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 pt-0.5">Patologie</dt>
                            <dd class="text-gray-700">{{ client.pathologies }}</dd>
                        </template>
                        <template v-if="client.dietary_preferences">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 pt-0.5">Preferenze alimentari</dt>
                            <dd class="text-gray-700">{{ client.dietary_preferences }}</dd>
                        </template>
                        <template v-if="client.notes">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-400 pt-0.5">Note</dt>
                            <dd class="text-gray-700">{{ client.notes }}</dd>
                        </template>
                        <template v-if="!client.pathologies && !client.notes && !client.allergies?.length && !client.intolerances?.length && !client.dietary_preferences">
                            <dd class="col-span-2 text-sm text-gray-400">Nessun dato anamnesi compilato</dd>
                        </template>
                    </div>

                    <!-- Last updated from submission -->
                    <div v-if="latestSubmission" class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-400">Ultima compilazione: {{ formatDate(latestSubmission.submitted_at ?? latestSubmission.sent_at) }}</p>
                        <Link
                            :href="route('nutritionist.anamnesis.submissions.show', latestSubmission.id)"
                            class="text-xs font-medium text-primary-600 hover:text-primary-700 transition inline-flex items-center gap-1"
                        >
                            Aggiorna manualmente →
                        </Link>
                    </div>
                    <div v-else class="flex justify-end pt-3 border-t border-gray-100">
                        <Link :href="route('nutritionist.clients.edit', client.id)" class="text-xs font-medium text-primary-600 hover:text-primary-700 transition">
                            Aggiorna manualmente →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Previous questionnaires -->
            <div v-if="client.anamnesis_submissions?.length" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-900 mb-3">Questionari inviati</p>
                <div class="space-y-1.5">
                    <Link
                        v-for="sub in client.anamnesis_submissions"
                        :key="sub.id"
                        :href="route('nutritionist.anamnesis.submissions.show', sub.id)"
                        class="flex items-center justify-between rounded-xl hover:bg-gray-50 px-2 py-2.5 transition"
                    >
                        <div>
                            <span class="text-sm text-gray-700">{{ sub.template?.name || 'Questionario' }}</span>
                            <span class="text-xs text-gray-400 ml-2">{{ formatDate(sub.sent_at) }}</span>
                        </div>
                        <span v-if="sub.status === 'completed'" class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">
                            <CheckCircle class="h-3 w-3" /> Compilato
                        </span>
                        <span v-else class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">
                            <Clock class="h-3 w-3" /> In attesa
                        </span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Right: esami clinici + note -->
        <div class="space-y-5">
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-semibold text-gray-900">Esami clinici</p>
                    <Link
                        :href="route('nutritionist.lab-results.create', { client_id: client.id })"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition"
                    >
                        + Carica esame
                    </Link>
                </div>
                <LabResultsList :lab-results="client.lab_results ?? []" :client-id="client.id" />
            </div>

            <NutritionistNotes :notes="client.nutritionist_notes" :client-id="client.id" />
        </div>
    </div>

    <!-- Send questionnaire modal -->
    <Teleport to="body">
        <div v-if="showSendModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showSendModal = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Invia questionario</h3>
                    <button @click="showSendModal = false" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 transition">✕</button>
                </div>
                <p class="text-sm text-gray-500 mb-4">Seleziona un template da inviare a <strong>{{ client.user?.name }}</strong>.</p>
                <select v-model="selectedTemplateId" class="block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500 mb-4">
                    <option :value="null">Seleziona template...</option>
                    <option v-for="t in anamnesisTemplates" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
                <div class="flex justify-end gap-2">
                    <button @click="showSendModal = false" class="rounded-lg px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 transition">Annulla</button>
                    <button @click="sendAnamnesis" :disabled="!selectedTemplateId || sending" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 transition disabled:opacity-50">
                        <Send class="h-3.5 w-3.5 inline mr-1" /> Invia
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
