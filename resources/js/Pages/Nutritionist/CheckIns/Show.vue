<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS, CategoryScale, LinearScale, PointElement,
    LineElement, Tooltip, Filler, Legend,
} from 'chart.js';
import {
    ArrowLeft, Scale, Droplets, Smile, Zap, Moon,
    TrendingDown, TrendingUp, Minus, Activity, Percent,
    ImageIcon, CheckCircle2, ChevronLeft, ChevronRight,
    User, MessageSquare, ExternalLink, AlertTriangle, Edit3,
} from 'lucide-vue-next';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Filler, Legend);

const props = defineProps<{
    checkIn: any;
    prevId: number | null;
    nextId: number | null;
    weightHistory: { id: number; date: string; weight_kg: string | number }[];
    recentCheckIns: { id: number; date: string; weight_kg: string | number | null }[];
    prevWeightCheckIn: { id: number; date: string; weight_kg: string | number } | null;
    bodyCompHistory: { id: number; date: string; body_fat_percentage: string | number | null; lean_mass_kg: string | number | null; body_water_percentage: string | number | null }[];
    measurementTypes: { value: string; label: string }[];
}>();

function formatDate(d: string, opts?: Intl.DateTimeFormatOptions) {
    return new Date(d).toLocaleDateString('it-IT', opts || { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}

function formatShort(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: 'numeric' });
}

function measurementLabel(type: string) {
    return props.measurementTypes.find(m => m.value === type)?.label || type;
}

const notesForm = useForm({
    nutritionist_notes: props.checkIn.nutritionist_notes || '',
});

function submitNotes() {
    notesForm.patch(route('nutritionist.check-ins.notes', props.checkIn.id), { preserveScroll: true });
}

function submitReview() {
    notesForm.patch(route('nutritionist.check-ins.review', props.checkIn.id), { preserveScroll: true });
}

// Quick-reply chips
const quickReplies = ['Ottimo lavoro!', 'Continua così!', 'Aumenta l\'idratazione', 'Attenzione al sonno', 'Rivediamo il piano'];
function applyQuickReply(text: string) {
    notesForm.nutritionist_notes = notesForm.nutritionist_notes
        ? notesForm.nutritionist_notes + ' ' + text
        : text;
}

// Weight delta from previous
const weightChange = computed(() => {
    if (!props.prevWeightCheckIn || !props.checkIn.weight_kg) return null;
    return Math.round((Number(props.checkIn.weight_kg) - Number(props.prevWeightCheckIn.weight_kg)) * 10) / 10;
});

function deltaClass(val: number | null) {
    if (val === null) return 'text-gray-400';
    if (val < 0) return 'text-green-600';
    if (val > 0) return 'text-red-500';
    return 'text-gray-400';
}

function deltaPrefix(val: number | null) {
    if (val === null || val === 0) return '±0';
    return val > 0 ? `+${val}` : `${val}`;
}

// Weight chart
const chartData = computed(() => {
    const currentIdx = props.weightHistory.findIndex(w => w.id === props.checkIn.id);
    return {
        labels: props.weightHistory.map(w =>
            new Date(w.date).toLocaleDateString('it-IT', { day: '2-digit', month: 'short' })
        ),
        datasets: [
            {
                label: 'Peso',
                data: props.weightHistory.map(w => Number(w.weight_kg)),
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34,197,94,0.07)',
                borderWidth: 2,
                pointRadius: props.weightHistory.map((_, i) => i === currentIdx ? 7 : 3),
                pointBackgroundColor: props.weightHistory.map((_, i) => i === currentIdx ? '#16a34a' : '#22c55e'),
                pointBorderColor: props.weightHistory.map((_, i) => i === currentIdx ? '#fff' : '#22c55e'),
                pointBorderWidth: props.weightHistory.map((_, i) => i === currentIdx ? 2 : 0),
                tension: 0.35,
                fill: true,
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: (ctx: any) => `${ctx.parsed.y} kg` } },
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9ca3af' } },
        y: { grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, color: '#9ca3af', callback: (v: any) => `${v} kg` } },
    },
};

// Recent check-ins with weight delta
const recentWithDelta = computed(() => {
    return props.recentCheckIns.map((c, i) => {
        const prev = props.recentCheckIns[i + 1];
        let delta: number | null = null;
        if (prev?.weight_kg && c.weight_kg) {
            delta = Math.round((Number(c.weight_kg) - Number(prev.weight_kg)) * 10) / 10;
        }
        return { ...c, delta };
    });
});

// Client info
const client = computed(() => props.checkIn.client);
const clientAge = computed(() => {
    if (!client.value?.date_of_birth) return null;
    const birth = new Date(client.value.date_of_birth);
    const today = new Date();
    return today.getFullYear() - birth.getFullYear();
});
const clientInitials = computed(() => {
    return client.value?.user?.name?.split(' ').slice(0, 2).map((n: string) => n[0]).join('').toUpperCase() || '?';
});
</script>

<template>
    <Head :title="`Check-in ${checkIn.client?.user?.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <!-- Breadcrumb + nav -->
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2 min-w-0">
                    <Link
                        :href="route('nutritionist.check-ins.index')"
                        class="flex items-center gap-1 rounded-lg px-2 py-1 text-sm text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition flex-shrink-0"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Monitoraggi
                    </Link>
                    <span class="text-gray-300">/</span>
                    <span class="text-sm font-semibold text-gray-900 truncate">{{ checkIn.client?.user?.name }}</span>
                    <span class="text-gray-300 hidden sm:block">/</span>
                    <span class="text-sm text-gray-500 hidden sm:block">{{ formatShort(checkIn.date) }}</span>
                    <!-- Prev / Next -->
                    <div class="flex items-center gap-1 ml-1 flex-shrink-0">
                        <Link
                            :href="prevId ? route('nutritionist.check-ins.show', prevId) : '#'"
                            :class="['rounded-md p-1 transition', prevId ? 'text-gray-500 hover:bg-gray-100 hover:text-gray-700' : 'text-gray-200 pointer-events-none']"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Link>
                        <Link
                            :href="nextId ? route('nutritionist.check-ins.show', nextId) : '#'"
                            :class="['rounded-md p-1 transition', nextId ? 'text-gray-500 hover:bg-gray-100 hover:text-gray-700' : 'text-gray-200 pointer-events-none']"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>

                <!-- Header actions -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    <Link
                        :href="route('nutritionist.clients.show', checkIn.client_id)"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm"
                    >
                        <User class="h-4 w-4" />
                        Profilo cliente
                    </Link>
                    <button
                        @click="submitReview"
                        :disabled="notesForm.processing"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-4 py-1.5 text-sm font-semibold text-white hover:bg-gray-800 disabled:opacity-40 transition shadow-sm"
                    >
                        <CheckCircle2 class="h-4 w-4" />
                        {{ checkIn.reviewed_at ? 'Ri-segna revisionato' : 'Segna revisionato' }}
                    </button>
                </div>
            </div>
        </template>

        <!-- 2-column layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- ── LEFT COLUMN ── -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Client headline -->
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-full bg-green-100 text-xl font-bold text-green-700">
                        {{ clientInitials }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5 mb-1">
                            <h1 class="text-xl font-bold text-gray-900">Check-in di {{ checkIn.client?.user?.name?.split(' ')[0] }}</h1>
                            <span v-if="!checkIn.reviewed_at" class="inline-flex items-center gap-1 rounded-full bg-amber-50 border border-amber-200 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                <AlertTriangle class="h-3 w-3" />
                                Da revisionare
                            </span>
                            <span v-else class="inline-flex items-center gap-1 rounded-full bg-green-50 border border-green-200 px-2.5 py-0.5 text-xs font-semibold text-green-700">
                                <CheckCircle2 class="h-3 w-3" />
                                Revisionato
                            </span>
                        </div>
                        <p class="text-sm text-gray-500">{{ formatDate(checkIn.date) }}</p>
                    </div>
                </div>

                <!-- Metric cards -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    <!-- Peso -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-1.5 mb-2">
                            <Scale class="h-4 w-4 text-gray-400" />
                            <span v-if="weightChange !== null" :class="['text-xs font-semibold flex items-center gap-0.5', deltaClass(weightChange)]">
                                <TrendingDown v-if="weightChange < 0" class="h-3 w-3" />
                                <TrendingUp v-else-if="weightChange > 0" class="h-3 w-3" />
                                <Minus v-else class="h-3 w-3" />
                                {{ deltaPrefix(weightChange) }} kg
                            </span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 leading-tight">{{ checkIn.weight_kg ?? '—' }}</p>
                        <p class="text-xs font-medium text-gray-400 mt-0.5 uppercase tracking-wide">PESO</p>
                        <p v-if="checkIn.weight_kg" class="text-xs text-gray-400 mt-0.5">kg</p>
                    </div>

                    <!-- Idratazione -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-1.5 mb-2">
                            <Droplets class="h-4 w-4 text-blue-400" />
                        </div>
                        <p class="text-2xl font-bold text-gray-900 leading-tight">{{ checkIn.water_liters ?? '—' }}</p>
                        <p class="text-xs font-medium text-gray-400 mt-0.5 uppercase tracking-wide">IDRATAZIONE</p>
                        <p v-if="checkIn.water_liters" class="text-xs text-gray-400 mt-0.5">litri/giorno</p>
                    </div>

                    <!-- Umore -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-1.5 mb-2">
                            <Smile class="h-4 w-4 text-yellow-400" />
                        </div>
                        <p class="text-2xl font-bold text-gray-900 leading-tight">{{ checkIn.mood ?? '—' }}<span v-if="checkIn.mood" class="text-base font-medium text-gray-400">/5</span></p>
                        <p class="text-xs font-medium text-gray-400 mt-0.5 uppercase tracking-wide">UMORE</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ checkIn.mood >= 4 ? 'ottimo' : checkIn.mood >= 3 ? 'buono' : checkIn.mood ? 'basso' : '' }}
                        </p>
                    </div>

                    <!-- Energia -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-1.5 mb-2">
                            <Zap class="h-4 w-4 text-orange-400" />
                        </div>
                        <p class="text-2xl font-bold text-gray-900 leading-tight">{{ checkIn.energy_level ?? '—' }}<span v-if="checkIn.energy_level" class="text-base font-medium text-gray-400">/5</span></p>
                        <p class="text-xs font-medium text-gray-400 mt-0.5 uppercase tracking-wide">ENERGIA</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ checkIn.energy_level >= 4 ? 'alta' : checkIn.energy_level >= 3 ? 'media' : checkIn.energy_level ? 'bassa' : '' }}
                        </p>
                    </div>

                    <!-- Sonno -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                        <div class="flex items-center gap-1.5 mb-2">
                            <Moon class="h-4 w-4 text-indigo-400" />
                        </div>
                        <p class="text-2xl font-bold text-gray-900 leading-tight">{{ checkIn.sleep_quality ?? '—' }}<span v-if="checkIn.sleep_quality" class="text-base font-medium text-gray-400">/5</span></p>
                        <p class="text-xs font-medium text-gray-400 mt-0.5 uppercase tracking-wide">SONNO</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ checkIn.sleep_quality >= 4 ? 'ottimo' : checkIn.sleep_quality >= 3 ? 'buono' : checkIn.sleep_quality ? 'disturbato' : '' }}
                        </p>
                    </div>
                </div>

                <!-- Nota cliente -->
                <div v-if="checkIn.notes || checkIn.patient_notes" class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <MessageSquare class="h-4 w-4 text-gray-400" />
                            <h2 class="text-sm font-semibold text-gray-900">Nota del check-in</h2>
                        </div>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">DAL CLIENTE</span>
                    </div>
                    <div class="p-5">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">{{ checkIn.client?.user?.name?.split(' ')[0]?.toUpperCase() }}</p>
                        <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ checkIn.notes || checkIn.patient_notes }}</p>
                    </div>
                </div>

                <!-- Composizione corporea -->
                <div v-if="checkIn.body_fat_percentage || checkIn.lean_mass_kg || checkIn.body_water_percentage" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Composizione corporea</h2>
                    <div class="grid grid-cols-3 gap-3">
                        <div v-if="checkIn.body_fat_percentage" class="rounded-xl bg-rose-50 p-3 text-center">
                            <Percent class="h-4 w-4 mx-auto text-rose-400 mb-1" />
                            <p class="text-lg font-bold text-gray-900">{{ checkIn.body_fat_percentage }}%</p>
                            <p class="text-xs text-gray-500">massa grassa</p>
                        </div>
                        <div v-if="checkIn.lean_mass_kg" class="rounded-xl bg-emerald-50 p-3 text-center">
                            <Activity class="h-4 w-4 mx-auto text-emerald-400 mb-1" />
                            <p class="text-lg font-bold text-gray-900">{{ checkIn.lean_mass_kg }}</p>
                            <p class="text-xs text-gray-500">kg massa magra</p>
                        </div>
                        <div v-if="checkIn.body_water_percentage" class="rounded-xl bg-cyan-50 p-3 text-center">
                            <Droplets class="h-4 w-4 mx-auto text-cyan-400 mb-1" />
                            <p class="text-lg font-bold text-gray-900">{{ checkIn.body_water_percentage }}%</p>
                            <p class="text-xs text-gray-500">acqua corporea</p>
                        </div>
                    </div>
                </div>

                <!-- Plicometria -->
                <div v-if="checkIn.skinfold_triceps || checkIn.skinfold_biceps || checkIn.skinfold_subscapular || checkIn.skinfold_suprailiac" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Plicometria</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div v-if="checkIn.skinfold_triceps" class="rounded-xl bg-gray-50 px-4 py-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">Tricipitale</p>
                            <p class="text-base font-semibold text-gray-900">{{ checkIn.skinfold_triceps }} mm</p>
                        </div>
                        <div v-if="checkIn.skinfold_biceps" class="rounded-xl bg-gray-50 px-4 py-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">Bicipitale</p>
                            <p class="text-base font-semibold text-gray-900">{{ checkIn.skinfold_biceps }} mm</p>
                        </div>
                        <div v-if="checkIn.skinfold_subscapular" class="rounded-xl bg-gray-50 px-4 py-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">Sottoscapolare</p>
                            <p class="text-base font-semibold text-gray-900">{{ checkIn.skinfold_subscapular }} mm</p>
                        </div>
                        <div v-if="checkIn.skinfold_suprailiac" class="rounded-xl bg-gray-50 px-4 py-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">Sovrailiaca</p>
                            <p class="text-base font-semibold text-gray-900">{{ checkIn.skinfold_suprailiac }} mm</p>
                        </div>
                    </div>
                </div>

                <!-- Misure -->
                <div v-if="checkIn.measurements?.length > 0" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Misure circumferenziali</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div v-for="m in checkIn.measurements" :key="m.id" class="rounded-xl bg-gray-50 px-4 py-3 text-center">
                            <p class="text-xs text-gray-400 mb-1">{{ measurementLabel(m.measurement_type) }}</p>
                            <p class="text-base font-semibold text-gray-900">{{ m.value_cm }} cm</p>
                        </div>
                    </div>
                </div>

                <!-- Grafico peso -->
                <div v-if="weightHistory.length >= 2" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-gray-900">Progressione peso</h2>
                        <span class="text-xs text-gray-400">{{ weightHistory.length }} misurazioni</span>
                    </div>
                    <div class="relative h-52">
                        <Line :data="chartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Foto -->
                <div v-if="checkIn.photos?.length > 0" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-gray-900">Foto</h2>
                        <Link
                            :href="route('nutritionist.check-ins.photo-compare', { client_id: checkIn.client_id })"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-violet-200 bg-violet-50 px-3 py-1.5 text-xs font-medium text-violet-700 hover:bg-violet-100 transition"
                        >
                            <ImageIcon class="h-3.5 w-3.5" />
                            Confronto prima/dopo
                        </Link>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div v-for="photo in checkIn.photos" :key="photo.id" class="rounded-xl overflow-hidden">
                            <img :src="`/storage/${photo.file_path}`" class="w-full h-40 object-cover" />
                            <p class="text-xs text-gray-400 text-center py-1.5">{{ photo.photo_type }}</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ── RIGHT COLUMN ── -->
            <div class="space-y-5">

                <!-- Feedback card -->
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                        <Edit3 class="h-4 w-4 text-gray-400" />
                        <h2 class="text-sm font-semibold text-gray-900">Il tuo feedback</h2>
                    </div>
                    <div class="p-5">
                        <!-- Quick replies -->
                        <div class="flex flex-wrap gap-1.5 mb-3">
                            <button
                                v-for="reply in quickReplies"
                                :key="reply"
                                @click="applyQuickReply(reply)"
                                class="rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-600 hover:border-gray-400 hover:text-gray-800 transition"
                            >
                                + {{ reply }}
                            </button>
                        </div>

                        <textarea
                            v-model="notesForm.nutritionist_notes"
                            rows="4"
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-3 text-sm text-gray-700 placeholder-gray-400 focus:border-gray-400 focus:bg-white focus:outline-none focus:ring-0 transition resize-none"
                            placeholder="Scrivi un commento o consiglio per il cliente... (opzionale)"
                        ></textarea>

                        <div class="mt-3 flex gap-2">
                            <button
                                @click="submitReview"
                                :disabled="notesForm.processing"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-gray-900 py-2.5 text-sm font-semibold text-white hover:bg-gray-800 disabled:opacity-40 transition"
                            >
                                <CheckCircle2 class="h-4 w-4" />
                                Segna come revisionato
                            </button>
                            <button
                                @click="submitNotes"
                                :disabled="notesForm.processing"
                                class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-40 transition"
                            >
                                Salva
                            </button>
                        </div>

                        <p v-if="checkIn.reviewed_at" class="mt-2.5 text-xs text-green-600 flex items-center gap-1">
                            <CheckCircle2 class="h-3 w-3" />
                            Revisionato il {{ new Date(checkIn.reviewed_at).toLocaleDateString('it-IT', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                        </p>
                    </div>
                </div>

                <!-- Contesto cliente -->
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <User class="h-4 w-4 text-gray-400" />
                            <h2 class="text-sm font-semibold text-gray-900">Contesto cliente</h2>
                        </div>
                        <Link
                            :href="route('nutritionist.clients.show', checkIn.client_id)"
                            class="text-xs font-medium text-gray-500 hover:text-gray-700 flex items-center gap-1 transition"
                        >
                            Profilo
                            <ExternalLink class="h-3 w-3" />
                        </Link>
                    </div>
                    <div class="p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-green-100 text-sm font-bold text-green-700">
                                {{ clientInitials }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ client?.user?.name }}</p>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span :class="[
                                        'text-xs font-medium px-2 py-0.5 rounded-full',
                                        client?.status?.value === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'
                                    ]">{{ client?.status?.value === 'active' ? 'Attivo' : client?.status?.value }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div v-if="client?.gender || clientAge || client?.height_cm" class="col-span-2 text-xs text-gray-500 mb-1">
                                <span v-if="client?.gender?.value">{{ client.gender.value === 'male' ? 'M' : 'F' }}</span>
                                <span v-if="clientAge">, {{ clientAge }} anni</span>
                                <span v-if="client?.height_cm">, {{ client.height_cm }} cm</span>
                            </div>

                            <div v-if="client?.goal" class="rounded-xl bg-rose-50 p-2.5">
                                <p class="text-xs text-rose-500 font-semibold uppercase tracking-wide mb-0.5">OBIETTIVO</p>
                                <p class="text-xs font-semibold text-gray-800">{{ client.goal?.value }}</p>
                            </div>
                            <div v-if="client?.initial_weight_kg" class="rounded-xl bg-gray-50 p-2.5">
                                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-0.5">PESO INIZIALE</p>
                                <p class="text-xs font-semibold text-gray-800">{{ client.initial_weight_kg }} kg</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Check-in recenti -->
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Check-in recenti</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <Link
                            v-for="recent in recentWithDelta"
                            :key="recent.id"
                            :href="route('nutritionist.check-ins.show', recent.id)"
                            :class="[
                                'flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition',
                                recent.id === checkIn.id ? 'bg-green-50/60' : '',
                            ]"
                        >
                            <div class="flex items-center gap-2.5">
                                <div :class="[
                                    'h-2 w-2 rounded-full flex-shrink-0',
                                    recent.id === checkIn.id ? 'bg-green-500' : 'bg-gray-200'
                                ]"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ formatShort(recent.date) }}</p>
                                    <p v-if="recent.id === checkIn.id" class="text-xs text-green-600">questo monitoraggio</p>
                                    <p v-else class="text-xs text-gray-400">monitoraggio</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p v-if="recent.weight_kg" class="text-sm font-semibold text-gray-900">{{ recent.weight_kg }} kg</p>
                                <p v-if="recent.delta !== null" :class="['text-xs font-medium flex items-center justify-end gap-0.5', deltaClass(recent.delta)]">
                                    <TrendingDown v-if="recent.delta < 0" class="h-3 w-3" />
                                    <TrendingUp v-else-if="recent.delta > 0" class="h-3 w-3" />
                                    <Minus v-else class="h-3 w-3" />
                                    {{ deltaPrefix(recent.delta) }} kg
                                </p>
                            </div>
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
