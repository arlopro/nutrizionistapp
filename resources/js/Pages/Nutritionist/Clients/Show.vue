<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { ArrowLeft, Weight, Activity, Ruler, CalendarCheck } from 'lucide-vue-next';

import Tabs from '@/Components/Tabs.vue';
import StatCard from '@/Components/StatCard.vue';
import ClientHeader from '@/Components/ClientHeader.vue';
import NewMeasurementModal from '@/Components/NewMeasurementModal.vue';
import NewAppointmentModal from '@/Components/NewAppointmentModal.vue';

import Panoramica from './Tabs/Panoramica.vue';
import MisureFoto from './Tabs/MisureFoto.vue';
import PianiNutrizionali from './Tabs/PianiNutrizionali.vue';
import Appuntamenti from './Tabs/Appuntamenti.vue';
import AnamnesiEsami from './Tabs/AnamnesiEsami.vue';

// Import icons for tabs
import { LayoutDashboard, Diamond, Utensils, Calendar, FileText } from 'lucide-vue-next';

const props = defineProps<{
    client: any;
    activePlan: any;
    recentMessages: any[];
    anamnesisTemplates: { id: number; name: string }[];
    currentTab: string;
    currentSub: string;
    appointmentLocations: string[];
}>();

const page = usePage();

const nutritionistId = computed(() => (page.props as any).auth?.user?.id);

// Local state — avoids server round-trips on tab clicks
const activeTab = ref(props.currentTab);
const activeSub = ref(props.currentSub);

// Resync if an Inertia visit (e.g. form submission redirect) updates props
watch(() => props.currentTab, (v) => { activeTab.value = v; });
watch(() => props.currentSub, (v) => { activeSub.value = v; });

function setTab(key: string) {
    activeTab.value = key;
    activeSub.value = 'galleria';
    const url = new URL(window.location.href);
    url.searchParams.set('tab', key);
    url.searchParams.delete('sub');
    history.pushState({}, '', url.pathname + url.search);
}

function setSub(key: string) {
    activeSub.value = key;
    const url = new URL(window.location.href);
    url.searchParams.set('sub', key);
    history.pushState({}, '', url.pathname + url.search);
}

const tabs = [
    { key: 'panoramica', label: 'Panoramica', icon: LayoutDashboard },
    { key: 'misure-foto', label: 'Misure & Foto', icon: Diamond },
    { key: 'piani', label: 'Piani nutrizionali', icon: Utensils },
    { key: 'appuntamenti', label: 'Appuntamenti', icon: Calendar },
    { key: 'anamnesi', label: 'Anamnesi & Esami', icon: FileText },
];

// Stat cards computed values
const latestCheckIn = computed(() =>
    (props.client.check_ins ?? []).find((c: any) => c.weight_kg != null)
);

const weightDelta = computed(() => {
    const data = (props.client.check_ins ?? []).filter((c: any) => c.weight_kg != null);
    if (data.length < 2) return null;
    return Math.round((Number(data[0].weight_kg) - Number(data[data.length - 1].weight_kg)) * 10) / 10;
});

const nextAppointment = computed(() =>
    (props.client.appointments ?? [])
        .filter((a: any) => new Date(a.starts_at) >= new Date() && !['cancelled', 'completed'].includes(a.status))
        .sort((a: any, b: any) => new Date(a.starts_at).getTime() - new Date(b.starts_at).getTime())[0] ?? null
);

function formatNextApt(apt: any) {
    if (!apt) return null;
    return new Date(apt.starts_at).toLocaleDateString('it-IT', { weekday: 'short', day: 'numeric', month: 'short' });
}

function formatNextAptTime(apt: any) {
    if (!apt) return null;
    const start = new Date(apt.starts_at).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
    const end = apt.ends_at ? new Date(apt.ends_at).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' }) : null;
    return end ? `${start} – ${end}` : start;
}

const goalLabel = computed(() => {
    const map: Record<string, string> = { weight_loss: 'Perdita peso', weight_gain: 'Aumento peso', maintenance: 'Mantenimento', muscle_gain: 'Massa muscolare', health: 'Salute' };
    return props.client.goal ? (map[props.client.goal] ?? props.client.goal) : null;
});

// New measurement modal
const showMeasurementModal = ref(false);

// New appointment modal
const showAppointmentModal = ref(false);
</script>

<template>
    <Head :title="client.user?.name + ' ' + (client.user?.last_name ?? '')" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('nutritionist.clients.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <nav class="flex items-center gap-1 text-sm text-gray-500">
                    <Link :href="route('nutritionist.clients.index')" class="hover:text-gray-700 transition">Clienti</Link>
                    <span class="mx-1 text-gray-300">/</span>
                    <span class="font-medium text-gray-900">{{ client.user?.name }} {{ client.user?.last_name }}</span>
                </nav>
            </div>
        </template>

        <div class="space-y-4">
            <!-- Flash -->
            <div v-if="$page.props.flash?.success" class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                {{ $page.props.flash.error }}
            </div>

            <!-- Client header card -->
            <ClientHeader :client="client" @new-measurement="showMeasurementModal = true" @new-appointment="showAppointmentModal = true" />

            <!-- Stat cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <StatCard
                    label="Peso attuale"
                    :value="client.current_weight"
                    unit="kg"
                    :delta="weightDelta !== null ? String(weightDelta) : null"
                    :delta-positive-is-good="false"
                    sub="da inizio"
                    :icon="Weight"
                />
                <StatCard
                    label="BMI"
                    :value="client.bmi"
                    :sub="client.bmi_category ?? undefined"
                    accent="indigo"
                    :icon="Activity"
                />
                <StatCard
                    label="Altezza"
                    :value="client.height_cm"
                    unit="cm"
                    :sub="goalLabel ?? undefined"
                    :icon="Ruler"
                />
                <div v-if="nextAppointment" class="rounded-xl border border-green-100 bg-green-50 p-4 flex flex-col gap-1">
                    <div class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-green-600">
                        <CalendarCheck class="h-3.5 w-3.5" />
                        Prossimo appuntamento
                    </div>
                    <div class="text-xl font-bold text-gray-900 leading-tight">{{ formatNextApt(nextAppointment) }}</div>
                    <div class="text-xs text-green-700">{{ formatNextAptTime(nextAppointment) }}</div>
                </div>
                <StatCard
                    v-else
                    label="Prossimo appuntamento"
                    :value="null"
                    sub="Nessuno programmato"
                    accent="default"
                    :icon="CalendarCheck"
                />
            </div>

            <!-- Tabs -->
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                <Tabs :tabs="tabs" :active="activeTab" @change="setTab" />
                <div class="p-5">
                    <Panoramica
                        v-if="activeTab === 'panoramica'"
                        :client="client"
                        :active-plan="activePlan"
                        @change-tab="(t) => {}"
                    />
                    <MisureFoto
                        v-else-if="activeTab === 'misure-foto'"
                        :client="client"
                        :sub="activeSub"
                        @new-measurement="showMeasurementModal = true"
                        @change-sub="setSub"
                    />
                    <PianiNutrizionali
                        v-else-if="activeTab === 'piani'"
                        :client="client"
                        :active-plan="activePlan"
                    />
                    <Appuntamenti
                        v-else-if="activeTab === 'appuntamenti'"
                        :client="client"
                        :recent-messages="recentMessages"
                        :nutritionist-id="nutritionistId"
                        @new-appointment="showAppointmentModal = true"
                    />
                    <AnamnesiEsami
                        v-else-if="activeTab === 'anamnesi'"
                        :client="client"
                        :anamnesis-templates="anamnesisTemplates"
                    />
                </div>
            </div>
        </div>

        <!-- New measurement modal -->
        <NewMeasurementModal
            v-if="showMeasurementModal"
            :client-id="client.id"
            @close="showMeasurementModal = false"
        />

        <!-- New appointment modal -->
        <NewAppointmentModal
            v-if="showAppointmentModal"
            :client-id="client.id"
            :client-name="`${client.user?.name ?? ''} ${client.user?.last_name ?? ''}`.trim()"
            :locations="appointmentLocations"
            @close="showAppointmentModal = false"
        />
    </AuthenticatedLayout>
</template>
