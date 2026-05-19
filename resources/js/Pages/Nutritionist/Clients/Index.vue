<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import {
    Plus, Search, Users, Calendar, AlertTriangle, Activity,
    Download, Upload, ChevronDown, List, LayoutGrid, Utensils,
} from 'lucide-vue-next';

interface ClientRow {
    id: number;
    user: { name: string; last_name: string | null; email: string | null; phone: string | null };
    status: string;
    goal: string | null;
    current_weight: number | null;
    weight_delta: number | null;
    weight_history: number[];
    last_checkin_date: string | null;
    next_appointment_at: string | null;
    active_plan_name: string | null;
}

interface Stats {
    total: number;
    active: number;
    inactive: number;
    archived: number;
    without_checkin: number;
    appointments_today: number;
}

const props = defineProps<{
    clients: any;
    filters: { search?: string; status?: string; goal?: string; sort?: string };
    stats: Stats;
    goalCounts: Record<string, number>;
}>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const goal   = ref(props.filters.goal   || '');
const sort   = ref(props.filters.sort   || 'created_at');

const showImportModal = ref(false);
const importFile = ref<File | null>(null);
const importDragging = ref(false);
const importLoading = ref(false);

let debounceTimer: ReturnType<typeof setTimeout>;
watch([search, status, goal, sort], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(
            route('nutritionist.clients.index'),
            {
                search: search.value || undefined,
                status: status.value || undefined,
                goal:   goal.value   || undefined,
                sort:   sort.value !== 'created_at' ? sort.value : undefined,
            },
            { preserveState: true, replace: true },
        );
    }, 300);
});

function handleImportDrop(e: DragEvent) {
    importDragging.value = false;
    const file = e.dataTransfer?.files[0];
    if (file) importFile.value = file;
}

function handleImportFile(e: Event) {
    const input = e.target as HTMLInputElement;
    if (input.files?.[0]) importFile.value = input.files[0];
}

function submitImport() {
    if (!importFile.value) return;
    importLoading.value = true;
    const form = new FormData();
    form.append('file', importFile.value);
    router.post(route('nutritionist.clients.import'), form as any, {
        onFinish: () => {
            importLoading.value = false;
            showImportModal.value = false;
            importFile.value = null;
        },
    });
}

const GOALS = [
    {
        value: 'maintenance',
        label: 'Mantenimento',
        icon: '◎',
        pill: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
        pillActive: 'bg-emerald-600 text-white',
        filterActive: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-300',
        filterInactive: 'text-gray-600 hover:bg-gray-100',
    },
    {
        value: 'weight_loss',
        label: 'Perdita peso',
        icon: '↘',
        pill: 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
        pillActive: 'bg-blue-600 text-white',
        filterActive: 'bg-blue-50 text-blue-700 ring-1 ring-blue-300',
        filterInactive: 'text-gray-600 hover:bg-gray-100',
    },
    {
        value: 'muscle_gain',
        label: 'Massa muscolare',
        icon: '↑',
        pill: 'bg-violet-50 text-violet-700 ring-1 ring-violet-200',
        pillActive: 'bg-violet-600 text-white',
        filterActive: 'bg-violet-50 text-violet-700 ring-1 ring-violet-300',
        filterInactive: 'text-gray-600 hover:bg-gray-100',
    },
    {
        value: 'health',
        label: 'Salute',
        icon: '♥',
        pill: 'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
        pillActive: 'bg-orange-600 text-white',
        filterActive: 'bg-orange-50 text-orange-700 ring-1 ring-orange-300',
        filterInactive: 'text-gray-600 hover:bg-gray-100',
    },
    {
        value: 'weight_gain',
        label: 'Performance',
        icon: '⚡',
        pill: 'bg-red-50 text-red-700 ring-1 ring-red-200',
        pillActive: 'bg-red-600 text-white',
        filterActive: 'bg-red-50 text-red-700 ring-1 ring-red-300',
        filterInactive: 'text-gray-600 hover:bg-gray-100',
    },
] as const;

const STATUS_OPTS = [
    { value: '',         label: 'Tutti',      count: props.stats.total    },
    { value: 'active',   label: 'Attivi',     count: props.stats.active   },
    { value: 'inactive', label: 'Inattivi',   count: props.stats.inactive },
    { value: 'archived', label: 'Archiviati', count: props.stats.archived },
];

function goalConfig(g: string | null) {
    return GOALS.find(x => x.value === g) ?? null;
}

function statusConfig(s: string) {
    const map: Record<string, { label: string; dot: string; text: string }> = {
        active:   { label: 'Attivo',     dot: 'bg-green-500',  text: 'text-green-700'  },
        inactive: { label: 'Inattivo',   dot: 'bg-yellow-400', text: 'text-yellow-700' },
        archived: { label: 'Archiviato', dot: 'bg-gray-400',   text: 'text-gray-500'   },
    };
    return map[s] ?? { label: s, dot: 'bg-gray-400', text: 'text-gray-500' };
}

function clientInitials(client: ClientRow): string {
    return (client.user?.name?.charAt(0) ?? '').toUpperCase()
         + (client.user?.last_name?.charAt(0) ?? '').toUpperCase();
}

function formatCheckinDate(dateStr: string | null): string {
    if (!dateStr) return '—';
    const date = new Date(dateStr);
    const now  = new Date();
    now.setHours(0, 0, 0, 0);
    date.setHours(0, 0, 0, 0);
    const diff = Math.round((now.getTime() - date.getTime()) / 86400000);
    if (diff === 0) return 'oggi';
    if (diff === 1) return 'ieri';
    return `${diff}g fa`;
}

function formatAppointment(dtStr: string | null): { text: string; isToday: boolean } {
    if (!dtStr) return { text: '—', isToday: false };
    const dt   = new Date(dtStr);
    const now  = new Date();
    const time = dt.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
    const startOfToday = new Date(now); startOfToday.setHours(0, 0, 0, 0);
    const startOfAppt  = new Date(dt);  startOfAppt.setHours(0, 0, 0, 0);
    const diff = Math.round((startOfAppt.getTime() - startOfToday.getTime()) / 86400000);
    if (diff === 0) return { text: `oggi · ${time}`, isToday: true };
    if (diff === 1) return { text: `domani · ${time}`, isToday: false };
    const day    = dt.getDate();
    const months = ['gen','feb','mar','apr','mag','giu','lug','ago','set','ott','nov','dic'];
    return { text: `${day} ${months[dt.getMonth()]} · ${time}`, isToday: false };
}

function buildSparkline(weights: number[]): { d: string; color: string } {
    if (!weights || weights.length < 2) return { d: '', color: '#9ca3af' };
    const W = 64, H = 22, pad = 2;
    const min   = Math.min(...weights);
    const max   = Math.max(...weights);
    const range = max - min || 0.1;
    const pts = weights.map((v, i) => {
        const x = pad + (i / (weights.length - 1)) * (W - 2 * pad);
        const y = H - pad - ((v - min) / range) * (H - 2 * pad);
        return `${x.toFixed(1)},${y.toFixed(1)}`;
    });
    const trend = weights[weights.length - 1] > weights[0] + 0.05 ? 'up'
                : weights[weights.length - 1] < weights[0] - 0.05 ? 'down'
                : 'flat';
    const color = trend === 'down' ? '#16a34a' : trend === 'up' ? '#dc2626' : '#9ca3af';
    return { d: `M ${pts.join(' L ')}`, color };
}

const sparklines = computed(() =>
    Object.fromEntries(
        (props.clients.data as ClientRow[]).map(c => [c.id, buildSparkline(c.weight_history)])
    )
);
</script>

<template>
    <Head title="Clienti" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-[10px] font-bold tracking-[0.18em] text-gray-400 uppercase mb-0.5">Gestione</p>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Clienti</h1>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        :href="route('nutritionist.clients.export')"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition shadow-sm"
                    >
                        <Download class="h-3.5 w-3.5" /> Esporta
                    </a>
                    <button
                        @click="showImportModal = true"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition shadow-sm"
                    >
                        <Upload class="h-3.5 w-3.5" /> Importa
                    </button>
                    <Link
                        :href="route('nutritionist.clients.create')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-[#1a5c2a] px-4 py-1.5 text-sm font-semibold text-white hover:bg-[#154d23] transition shadow-sm"
                    >
                        <Plus class="h-4 w-4" /> Nuovo cliente
                    </Link>
                </div>
            </div>
        </template>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ $page.props.flash.success }}
        </div>

        <!-- Stats cards -->
        <div class="mb-5 grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5 flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gray-100 flex-shrink-0">
                    <Users class="h-5 w-5 text-gray-500" />
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold tracking-widest text-gray-400 uppercase">Clienti totali</p>
                    <p class="text-2xl font-bold text-gray-900 leading-tight">{{ stats.total }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ stats.active }} attivi · {{ stats.inactive }} inattivi</p>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5 flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 flex-shrink-0">
                    <Calendar class="h-5 w-5 text-emerald-600" />
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold tracking-widest text-gray-400 uppercase">Appuntamenti oggi</p>
                    <p class="text-2xl font-bold text-gray-900 leading-tight">{{ stats.appointments_today }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">prossime 24h</p>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5 flex items-center gap-4">
                <div class="relative flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 flex-shrink-0">
                    <AlertTriangle class="h-5 w-5 text-amber-500" />
                    <span v-if="stats.without_checkin > 0" class="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold tracking-widest text-gray-400 uppercase">Senza check-in</p>
                    <p class="text-2xl font-bold text-gray-900 leading-tight">{{ stats.without_checkin }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">da 14+ giorni</p>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5 flex items-center gap-4">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 flex-shrink-0">
                    <Activity class="h-5 w-5 text-indigo-500" />
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold tracking-widest text-gray-400 uppercase">Aderenza media</p>
                    <p class="text-2xl font-bold text-gray-900 leading-tight">—</p>
                    <p class="text-xs text-gray-400 mt-0.5">prossimamente</p>
                </div>
            </div>
        </div>

        <!-- Filter card -->
        <div class="mb-3 rounded-2xl bg-white border border-gray-100 shadow-sm p-4">
            <!-- Row 1: search + status tabs + sort + view toggle -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="relative flex-1 min-w-0">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cerca per nome, email, telefono..."
                        class="w-full rounded-lg border-gray-200 bg-gray-50 pl-9 pr-4 py-2 text-sm focus:border-green-500 focus:ring-green-500"
                    />
                </div>

                <div class="flex items-center gap-0.5 flex-shrink-0">
                    <button
                        v-for="opt in STATUS_OPTS"
                        :key="opt.value"
                        @click="status = opt.value"
                        :class="[
                            'inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-sm font-medium transition',
                            status === opt.value ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100',
                        ]"
                    >
                        {{ opt.label }}
                        <span :class="['text-xs tabular-nums', status === opt.value ? 'text-gray-400' : 'text-gray-400']">
                            {{ opt.count }}
                        </span>
                    </button>
                </div>

                <div class="relative flex-shrink-0">
                    <select
                        v-model="sort"
                        class="appearance-none rounded-lg border border-gray-200 bg-white pl-3 pr-7 py-2 text-sm text-gray-700 focus:border-green-500 focus:ring-green-500 cursor-pointer"
                    >
                        <option value="created_at">Data creazione</option>
                        <option value="last_checkin">Ultimo check-in</option>
                        <option value="name">Nome</option>
                    </select>
                    <ChevronDown class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-gray-400" />
                </div>

                <div class="flex items-center rounded-lg border border-gray-200 overflow-hidden flex-shrink-0">
                    <button class="p-2 bg-gray-100 text-gray-700 border-r border-gray-200">
                        <List class="h-4 w-4" />
                    </button>
                    <button class="p-2 text-gray-400 hover:bg-gray-50 transition">
                        <LayoutGrid class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <!-- Row 2: goal filter -->
            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2 flex-wrap">
                <span class="text-[10px] font-bold tracking-[0.15em] text-gray-400 uppercase mr-1">Obiettivo</span>

                <button
                    @click="goal = ''"
                    :class="[
                        'rounded-full px-3 py-1 text-sm font-medium transition',
                        goal === '' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100',
                    ]"
                >
                    Tutti
                </button>

                <button
                    v-for="g in GOALS"
                    :key="g.value"
                    @click="goal = goal === g.value ? '' : g.value"
                    :class="[
                        'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-sm font-medium transition',
                        goal === g.value ? g.pillActive : g.filterInactive,
                    ]"
                >
                    <span class="text-xs leading-none">{{ g.icon }}</span>
                    {{ g.label }}
                    <span class="text-xs opacity-60 tabular-nums">{{ goalCounts[g.value] ?? 0 }}</span>
                </button>
            </div>
        </div>

        <!-- Count + legend row -->
        <div class="mb-3 flex items-center justify-between px-1">
            <p class="text-sm font-medium text-gray-600">{{ clients.total }} clienti</p>
            <div class="flex items-center gap-4 text-xs text-gray-400">
                <span class="flex items-center gap-1.5">
                    <svg width="16" height="2" viewBox="0 0 16 2"><line x1="0" y1="1" x2="16" y2="1" stroke="#16a34a" stroke-width="1.5"/></svg>
                    trend in calo
                </span>
                <span class="flex items-center gap-1.5">
                    <svg width="16" height="2" viewBox="0 0 16 2"><line x1="0" y1="1" x2="16" y2="1" stroke="#dc2626" stroke-width="1.5"/></svg>
                    trend in salita
                </span>
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="clients.data.length === 0" class="rounded-2xl bg-white border border-gray-100 p-16 text-center shadow-sm">
            <Users class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Nessun cliente trovato</h3>
            <p class="text-sm text-gray-500 mb-6">Prova a modificare i filtri o aggiungi un nuovo cliente.</p>
            <Link
                :href="route('nutritionist.clients.create')"
                class="inline-flex items-center gap-2 rounded-lg bg-[#1a5c2a] px-4 py-2 text-sm font-semibold text-white hover:bg-[#154d23] transition"
            >
                <Plus class="h-4 w-4" /> Aggiungi Cliente
            </Link>
        </div>

        <!-- Table -->
        <div v-else class="rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full min-w-[640px]">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="text-left px-5 py-3 text-[10px] font-bold tracking-widest text-gray-400 uppercase whitespace-nowrap">Cliente</th>
                        <th class="text-left px-4 py-3 text-[10px] font-bold tracking-widest text-gray-400 uppercase whitespace-nowrap">Obiettivo</th>
                        <th class="text-left px-4 py-3 text-[10px] font-bold tracking-widest text-gray-400 uppercase whitespace-nowrap">Peso & Δ</th>
                        <th class="hidden lg:table-cell text-left px-4 py-3 text-[10px] font-bold tracking-widest text-gray-400 uppercase whitespace-nowrap">Piano attivo</th>
                        <th class="hidden md:table-cell text-left px-4 py-3 text-[10px] font-bold tracking-widest text-gray-400 uppercase whitespace-nowrap">Ultimo check-in</th>
                        <th class="hidden md:table-cell text-left px-4 py-3 text-[10px] font-bold tracking-widest text-gray-400 uppercase whitespace-nowrap">Prossimo appt.</th>
                        <th class="text-left px-4 py-3 text-[10px] font-bold tracking-widest text-gray-400 uppercase whitespace-nowrap">Stato</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr
                        v-for="client in (clients.data as ClientRow[])"
                        :key="client.id"
                        class="hover:bg-gray-50/60 transition-colors cursor-pointer"
                        @click="router.visit(route('nutritionist.clients.show', client.id))"
                    >
                        <!-- Cliente -->
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-white text-xs font-bold flex-shrink-0 select-none">
                                    {{ clientInitials(client) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">
                                        {{ client.user?.name }} {{ client.user?.last_name }}
                                    </p>
                                    <p class="text-xs text-gray-400 truncate">{{ client.user?.email }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Obiettivo -->
                        <td class="px-4 py-3.5">
                            <span
                                v-if="goalConfig(client.goal)"
                                :class="['inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium', goalConfig(client.goal)!.pill]"
                            >
                                <span class="text-[10px] leading-none">{{ goalConfig(client.goal)!.icon }}</span>
                                {{ goalConfig(client.goal)!.label }}
                            </span>
                            <span v-else class="text-gray-300 text-xs">—</span>
                        </td>

                        <!-- Peso & Δ -->
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <svg
                                    v-if="sparklines[client.id]?.d"
                                    width="64" height="22"
                                    class="flex-shrink-0 overflow-visible"
                                    viewBox="0 0 64 22"
                                >
                                    <path
                                        :d="sparklines[client.id].d"
                                        fill="none"
                                        :stroke="sparklines[client.id].color"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <div v-if="client.current_weight" class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 tabular-nums">
                                        {{ client.current_weight }}&thinsp;kg
                                    </p>
                                    <p
                                        v-if="client.weight_delta !== null"
                                        :class="['text-xs font-medium tabular-nums', client.weight_delta <= 0 ? 'text-green-600' : 'text-red-500']"
                                    >
                                        {{ client.weight_delta > 0 ? '+' : '' }}{{ client.weight_delta }}&thinsp;kg
                                    </p>
                                </div>
                                <span v-else class="text-gray-300 text-xs">—</span>
                            </div>
                        </td>

                        <!-- Piano attivo -->
                        <td class="hidden lg:table-cell px-4 py-3.5">
                            <div v-if="client.active_plan_name" class="flex items-center gap-1.5 text-sm text-gray-700 min-w-0">
                                <Utensils class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" />
                                <span class="truncate max-w-[130px]">{{ client.active_plan_name }}</span>
                            </div>
                            <span v-else class="text-xs text-gray-300">Nessun piano</span>
                        </td>

                        <!-- Ultimo check-in -->
                        <td class="hidden md:table-cell px-4 py-3.5">
                            <span class="text-sm text-gray-700">
                                {{ formatCheckinDate(client.last_checkin_date) }}
                            </span>
                        </td>

                        <!-- Prossimo appuntamento -->
                        <td class="hidden md:table-cell px-4 py-3.5">
                            <span
                                :class="[
                                    'text-sm font-medium',
                                    formatAppointment(client.next_appointment_at).isToday
                                        ? 'text-green-600'
                                        : 'text-gray-700',
                                ]"
                            >
                                {{ formatAppointment(client.next_appointment_at).text }}
                            </span>
                        </td>

                        <!-- Stato -->
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-1.5">
                                <span :class="['h-1.5 w-1.5 rounded-full flex-shrink-0', statusConfig(client.status).dot]"></span>
                                <span :class="['text-sm', statusConfig(client.status).text]">
                                    {{ statusConfig(client.status).label }}
                                </span>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="clients.last_page > 1" class="mt-5 flex justify-center gap-1">
            <Link
                v-for="link in clients.links"
                :key="link.label"
                :href="link.url || '#'"
                :class="[
                    'rounded-lg px-3 py-2 text-sm transition',
                    link.active ? 'bg-[#1a5c2a] text-white' : 'text-gray-600 hover:bg-gray-100',
                    !link.url ? 'opacity-40 pointer-events-none' : '',
                ]"
                v-html="link.label"
                preserve-state
            />
        </div>
        <!-- Import modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showImportModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
                    @click.self="showImportModal = false"
                >
                    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold text-gray-900">Importa clienti da CSV</h2>
                            <button @click="showImportModal = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <p class="text-sm text-gray-500 mb-4">
                            Carica un file CSV con i dati dei clienti. I clienti con email già esistente verranno saltati.
                        </p>

                        <a
                            :href="route('nutritionist.clients.import-sample')"
                            class="inline-flex items-center gap-1.5 text-sm text-green-700 font-medium hover:underline mb-5 block"
                        >
                            <Download class="h-4 w-4" />
                            Scarica file CSV di esempio
                        </a>

                        <!-- Drop zone -->
                        <div
                            class="rounded-xl border-2 border-dashed transition-colors p-8 text-center cursor-pointer"
                            :class="importDragging ? 'border-green-400 bg-green-50' : 'border-gray-200 hover:border-gray-300'"
                            @dragover.prevent="importDragging = true"
                            @dragleave="importDragging = false"
                            @drop.prevent="handleImportDrop"
                            @click="($refs.fileInput as HTMLInputElement).click()"
                        >
                            <Upload class="mx-auto h-8 w-8 text-gray-400 mb-2" />
                            <p class="text-sm text-gray-600 font-medium">
                                {{ importFile ? importFile.name : 'Trascina il file qui o clicca per selezionarlo' }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Solo file .csv · max 5 MB</p>
                            <input
                                ref="fileInput"
                                type="file"
                                accept=".csv,text/csv"
                                class="hidden"
                                @change="handleImportFile"
                            />
                        </div>

                        <div class="mt-4 flex gap-3 justify-end">
                            <button
                                @click="showImportModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition"
                            >
                                Annulla
                            </button>
                            <button
                                @click="submitImport"
                                :disabled="!importFile || importLoading"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#1a5c2a] rounded-lg hover:bg-[#154d23] transition disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg v-if="importLoading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                {{ importLoading ? 'Importazione...' : 'Importa' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>
