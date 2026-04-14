<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import WeightChart from '@/Components/WeightChart.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ArrowLeft, Edit, Mail, Phone, Calendar, Ruler, Weight, Target, Activity, ClipboardCheck, UtensilsCrossed, TrendingDown, Send, CalendarDays, Archive, ArchiveRestore, FileText, CheckCircle, Clock, Plus } from 'lucide-vue-next';

const props = defineProps<{ client: any; anamnesisTemplates: { id: number; name: string }[] }>();

const archiving = ref(false);
function toggleArchive() {
    archiving.value = true;
    const newStatus = props.client.status === 'archived' ? 'active' : 'archived';
    router.put(route('nutritionist.clients.update', props.client.id), { ...props.client, name: props.client.user?.name, email: props.client.user?.email, status: newStatus }, {
        preserveScroll: true,
        onFinish: () => { archiving.value = false; },
    });
}

const weightWithHistory = computed(() =>
    (props.client.check_ins ?? []).filter((c: any) => c.weight_kg)
);

const weightDelta = computed(() => {
    const data = weightWithHistory.value;
    if (data.length < 2) return 0;
    return Math.round((Number(data[data.length - 1].weight_kg) - Number(data[0].weight_kg)) * 10) / 10;
});

function formatDate(d: string) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('it-IT', { day: 'numeric', month: 'long', year: 'numeric' });
}

function goalLabel(g: string) {
    const map: Record<string, string> = { weight_loss: 'Perdita peso', weight_gain: 'Aumento peso', maintenance: 'Mantenimento', muscle_gain: 'Massa muscolare', health: 'Salute' };
    return map[g] || g || '-';
}

function activityLabel(a: string) {
    const map: Record<string, string> = { sedentary: 'Sedentario', light: 'Leggero', moderate: 'Moderato', active: 'Attivo', very_active: 'Molto attivo' };
    return map[a] || a || '-';
}

function genderLabel(g: string) {
    const map: Record<string, string> = { male: 'Maschio', female: 'Femmina', other: 'Altro' };
    return map[g] || g || '-';
}

function statusColor(s: string) {
    return { active: 'bg-green-100 text-green-700', inactive: 'bg-yellow-100 text-yellow-700', archived: 'bg-gray-100 text-gray-600' }[s] || 'bg-gray-100 text-gray-600';
}

function statusLabel(s: string) {
    return { active: 'Attivo', inactive: 'Inattivo', archived: 'Archiviato' }[s] || s;
}

const sendingInvitation = ref(false);
function sendInvitation() {
    sendingInvitation.value = true;
    router.post(route('nutritionist.clients.send-invitation', props.client.id), {}, {
        preserveScroll: true,
        onFinish: () => { sendingInvitation.value = false; },
    });
}

function formatDateTime(d: string) {
    if (!d) return '';
    return new Date(d).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: 'numeric' });
}

// Anamnesis
const showSendAnamnesis = ref(false);
const selectedTemplateId = ref<number | null>(null);
const sendingAnamnesis = ref(false);

function sendAnamnesis() {
    if (!selectedTemplateId.value) return;
    sendingAnamnesis.value = true;
    router.post(route('nutritionist.anamnesis.send'), {
        template_id: selectedTemplateId.value,
        client_id: props.client.id,
    }, {
        preserveScroll: true,
        onFinish: () => {
            sendingAnamnesis.value = false;
            showSendAnamnesis.value = false;
            selectedTemplateId.value = null;
        },
    });
}
</script>

<template>
    <Head :title="client.user?.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('nutritionist.clients.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">{{ client.user?.name }} {{ client.user?.last_name }}</h1>
                <span :class="['rounded-full px-2.5 py-1 text-xs font-medium', statusColor(client.status)]">
                    {{ statusLabel(client.status) }}
                </span>
                <div class="ml-auto flex items-center gap-2">
                    <button
                        type="button"
                        @click="toggleArchive"
                        :disabled="archiving"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition disabled:opacity-40"
                    >
                        <ArchiveRestore v-if="client.status === 'archived'" class="h-4 w-4" />
                        <Archive v-else class="h-4 w-4" />
                        {{ client.status === 'archived' ? 'Ripristina' : 'Archivia' }}
                    </button>
                    <Link :href="route('nutritionist.clients.edit', client.id)" class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:from-primary-600 hover:to-primary-700 transition shadow-sm">
                        <Edit class="h-4 w-4" />
                        Modifica
                    </Link>
                </div>
            </div>
        </template>

        <!-- Flash message -->
        <div v-if="$page.props.flash?.success" class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ $page.props.flash.success }}
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Left column: profile info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Client info card -->
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-semibold text-gray-900">Informazioni personali</h2>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex items-center gap-2 text-sm">
                            <Mail class="h-4 w-4 text-gray-400" />
                            <span class="text-gray-600">{{ client.user?.email }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <Phone class="h-4 w-4 text-gray-400" />
                            <span class="text-gray-600">{{ client.user?.phone || '-' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <Calendar class="h-4 w-4 text-gray-400" />
                            <span class="text-gray-600">{{ formatDate(client.date_of_birth) }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-600">{{ genderLabel(client.gender) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Invitation / account access -->
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">Accesso cliente</h2>
                            <p v-if="client.user?.invitation_sent_at" class="text-sm text-gray-400 mt-0.5">
                                Invito inviato il {{ formatDateTime(client.user.invitation_sent_at) }}
                            </p>
                            <p v-else class="text-sm text-gray-400 mt-0.5">Nessun invito inviato</p>
                        </div>
                        <button
                            type="button"
                            @click="sendInvitation"
                            :disabled="sendingInvitation"
                            class="inline-flex items-center gap-2 rounded-lg border border-primary-200 bg-primary-50 px-3 py-2 text-sm font-medium text-primary-700 hover:bg-primary-100 transition disabled:opacity-40"
                        >
                            <Send class="h-4 w-4" />
                            {{ client.user?.invitation_sent_at ? 'Reinvia invito' : 'Invia invito' }}
                        </button>
                    </div>
                </div>

                <!-- Physical data -->
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Dati fisici e obiettivi</h2>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-xl bg-gray-50 p-3 text-center">
                            <Ruler class="mx-auto h-5 w-5 text-gray-400 mb-1" />
                            <p class="text-lg font-bold text-gray-900">{{ client.height_cm || '-' }}</p>
                            <p class="text-xs text-gray-500">cm altezza</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3 text-center">
                            <Weight class="mx-auto h-5 w-5 text-gray-400 mb-1" />
                            <p class="text-lg font-bold text-gray-900">{{ client.current_weight || client.initial_weight_kg || '-' }}</p>
                            <p class="text-xs text-gray-500">kg peso attuale</p>
                            <p v-if="client.current_weight && client.initial_weight_kg && client.current_weight !== parseFloat(client.initial_weight_kg)" class="text-xs text-gray-400 mt-0.5">iniziale: {{ client.initial_weight_kg }} kg</p>
                        </div>
                        <div v-if="client.bmi" class="rounded-xl bg-indigo-50 p-3 text-center">
                            <Activity class="mx-auto h-5 w-5 text-indigo-400 mb-1" />
                            <p class="text-lg font-bold text-gray-900">{{ client.bmi }}</p>
                            <p class="text-xs text-indigo-600 font-medium">{{ client.bmi_category }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3 text-center">
                            <Target class="mx-auto h-5 w-5 text-gray-400 mb-1" />
                            <p class="text-sm font-semibold text-gray-900">{{ goalLabel(client.goal) }}</p>
                            <p class="text-xs text-gray-500">obiettivo</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3 text-center">
                            <Activity class="mx-auto h-5 w-5 text-gray-400 mb-1" />
                            <p class="text-sm font-semibold text-gray-900">{{ activityLabel(client.activity_level) }}</p>
                            <p class="text-xs text-gray-500">attivita'</p>
                        </div>
                    </div>

                    <!-- Allergies & intolerances -->
                    <div v-if="(client.allergies?.length || client.intolerances?.length)" class="mt-4 flex flex-wrap gap-2">
                        <span v-for="a in client.allergies" :key="a" class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700">{{ a }}</span>
                        <span v-for="t in client.intolerances" :key="t" class="rounded-full bg-orange-50 px-2.5 py-1 text-xs font-medium text-orange-700">{{ t }}</span>
                    </div>

                    <div v-if="client.pathologies" class="mt-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Patologie</p>
                        <p class="text-sm text-gray-700">{{ client.pathologies }}</p>
                    </div>
                    <div v-if="client.dietary_preferences" class="mt-3">
                        <p class="text-xs font-medium text-gray-500 mb-1">Preferenze alimentari</p>
                        <p class="text-sm text-gray-700">{{ client.dietary_preferences }}</p>
                    </div>
                    <div v-if="client.notes" class="mt-3">
                        <p class="text-xs font-medium text-gray-500 mb-1">Note</p>
                        <p class="text-sm text-gray-700">{{ client.notes }}</p>
                    </div>
                </div>
            </div>

            <!-- Right column: plans, check-ins, appointments -->
            <div class="space-y-6">
                <!-- Upcoming appointments -->
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <CalendarDays class="h-5 w-5 text-gray-400" />
                        <h2 class="text-base font-semibold text-gray-900">Prossimi appuntamenti</h2>
                    </div>
                    <div v-if="!client.appointments?.length" class="text-center py-4 text-sm text-gray-400">
                        Nessun appuntamento
                    </div>
                    <div v-else class="space-y-2">
                        <div v-for="apt in client.appointments" :key="apt.id" class="rounded-xl bg-gray-50 px-3 py-2.5">
                            <p class="text-sm font-medium text-gray-900">
                                {{ new Date(apt.starts_at).toLocaleDateString('it-IT', { weekday: 'short', day: 'numeric', month: 'short' }) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ new Date(apt.starts_at).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' }) }}
                                <span v-if="apt.ends_at"> – {{ new Date(apt.ends_at).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' }) }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Plans -->
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <UtensilsCrossed class="h-5 w-5 text-gray-400" />
                        <h2 class="text-base font-semibold text-gray-900">Piani nutrizionali</h2>
                    </div>
                    <div v-if="!client.nutritional_plans?.length" class="text-center py-4 text-sm text-gray-400">
                        Nessun piano
                    </div>
                    <div v-else class="space-y-2">
                        <div v-for="plan in client.nutritional_plans" :key="plan.id" class="rounded-xl bg-gray-50 px-3 py-2">
                            <p class="text-sm font-medium text-gray-900">{{ plan.title }}</p>
                            <p class="text-xs text-gray-500">{{ formatDate(plan.start_date) }} - {{ plan.status }}</p>
                        </div>
                    </div>
                </div>

                <!-- Grafico peso + check-in recenti -->
                <div v-if="client.check_ins?.filter((c: any) => c.weight_kg).length >= 2" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <TrendingDown class="h-5 w-5 text-gray-400" />
                            <h2 class="text-base font-semibold text-gray-900">Andamento peso</h2>
                        </div>
                        <!-- Variazione totale -->
                        <div v-if="weightWithHistory.length >= 2" class="text-right">
                            <span
                                :class="[
                                    'text-sm font-semibold',
                                    weightDelta < 0 ? 'text-green-600' : weightDelta > 0 ? 'text-red-500' : 'text-gray-500'
                                ]"
                            >
                                {{ weightDelta > 0 ? '+' : '' }}{{ weightDelta }} kg
                            </span>
                            <p class="text-xs text-gray-400">totale</p>
                        </div>
                    </div>
                    <WeightChart :weight-history="weightWithHistory" />

                    <!-- Ultimo peso -->
                    <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
                        <span>Ultimo: <strong class="text-gray-900">{{ weightWithHistory[weightWithHistory.length - 1]?.weight_kg }} kg</strong> ({{ formatDate(weightWithHistory[weightWithHistory.length - 1]?.date) }})</span>
                        <span>Inizio: <strong class="text-gray-900">{{ weightWithHistory[0]?.weight_kg }} kg</strong></span>
                    </div>
                </div>

                <!-- Check-in recenti (ultimi 8) -->
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <ClipboardCheck class="h-5 w-5 text-gray-400" />
                        <h2 class="text-base font-semibold text-gray-900">Monitoraggi recenti</h2>
                    </div>
                    <div v-if="!client.check_ins?.length" class="text-center py-4 text-sm text-gray-400">
                        Nessun monitoraggio
                    </div>
                    <div v-else class="space-y-1.5">
                        <Link
                            v-for="ci in [...client.check_ins].reverse().slice(0, 8)"
                            :key="ci.id"
                            :href="route('nutritionist.check-ins.show', ci.id)"
                            class="flex items-center justify-between rounded-xl bg-gray-50 hover:bg-gray-100 px-3 py-2 transition"
                        >
                            <span class="text-sm text-gray-700">{{ formatDate(ci.date) }}</span>
                            <div class="flex items-center gap-3">
                                <span v-if="ci.mood" class="text-xs text-gray-400">😊 {{ ci.mood }}/5</span>
                                <span v-if="ci.weight_kg" class="text-sm font-semibold text-gray-900">{{ ci.weight_kg }} kg</span>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Questionari anamnesi -->
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <FileText class="h-5 w-5 text-gray-400" />
                            <h2 class="text-base font-semibold text-gray-900">Questionari anamnesi</h2>
                        </div>
                        <button
                            v-if="anamnesisTemplates.length > 0"
                            @click="showSendAnamnesis = true"
                            class="inline-flex items-center gap-1 rounded-lg border border-primary-200 bg-primary-50 px-2.5 py-1.5 text-xs font-medium text-primary-700 hover:bg-primary-100 transition"
                        >
                            <Send class="h-3 w-3" /> Invia questionario
                        </button>
                    </div>
                    <div v-if="!client.anamnesis_submissions?.length" class="text-center py-4 text-sm text-gray-400">
                        Nessun questionario inviato
                    </div>
                    <div v-else class="space-y-1.5">
                        <Link
                            v-for="sub in client.anamnesis_submissions"
                            :key="sub.id"
                            :href="route('nutritionist.anamnesis.submissions.show', sub.id)"
                            class="flex items-center justify-between rounded-xl bg-gray-50 hover:bg-gray-100 px-3 py-2 transition"
                        >
                            <div>
                                <span class="text-sm text-gray-700">{{ sub.template?.name || 'Questionario' }}</span>
                                <span class="text-xs text-gray-400 ml-2">{{ formatDateTime(sub.sent_at) }}</span>
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
        </div>

        <!-- Modal: Invia questionario -->
        <div v-if="showSendAnamnesis" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showSendAnamnesis = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Invia questionario</h3>
                    <button @click="showSendAnamnesis = false" class="p-1 text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>
                <p class="text-sm text-gray-500 mb-4">
                    Seleziona un template di anamnesi da inviare a <strong>{{ client.user?.name }}</strong>.
                </p>
                <select v-model="selectedTemplateId" class="block w-full rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 mb-4">
                    <option :value="null">Seleziona template...</option>
                    <option v-for="t in anamnesisTemplates" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
                <div class="flex justify-end gap-2">
                    <button @click="showSendAnamnesis = false" class="rounded-lg px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 transition">Annulla</button>
                    <button @click="sendAnamnesis" :disabled="!selectedTemplateId || sendingAnamnesis" class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-600 transition disabled:opacity-50">
                        <Send class="h-3.5 w-3.5 inline mr-1" /> Invia
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
