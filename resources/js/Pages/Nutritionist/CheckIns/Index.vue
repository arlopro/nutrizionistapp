<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import {
    AlertTriangle, CheckCircle2, ClipboardList, Frown,
    Scale, Droplets, Smile, Zap, Moon,
    TrendingDown, TrendingUp, Minus, Search, ChevronDown,
    ChevronRight, X,
} from 'lucide-vue-next';

const props = defineProps<{
    checkIns: any;
    filters: { client_id?: string; status?: string; search?: string };
    clients: any[];
    stats: {
        to_review: number;
        reviewed_this_week: number;
        total_this_month: number;
        low_mood: number;
    };
}>();

const search = ref(props.filters.search || '');
const clientId = ref(props.filters.client_id || '');
const status = ref(props.filters.status || 'all');

let searchTimer: ReturnType<typeof setTimeout>;
watch(search, (val) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => applyFilters(), 400);
});
watch([clientId, status], () => applyFilters());

function applyFilters() {
    router.get(route('nutritionist.check-ins.index'), {
        search: search.value || undefined,
        client_id: clientId.value || undefined,
        status: status.value !== 'all' ? status.value : undefined,
    }, { preserveState: true, replace: true });
}

function clearSearch() {
    search.value = '';
}

const reviewAllForm = useForm({});
function reviewAll() {
    reviewAllForm.post(route('nutritionist.check-ins.review-all'));
}

function formatDate(d: string) {
    const date = new Date(d);
    const today = new Date();
    const diffMs = today.getTime() - date.getTime();
    const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));
    const diffWeeks = Math.round(diffDays / 7);

    const base = date.toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: 'numeric' });
    if (diffDays === 0) return { base, relative: 'oggi' };
    if (diffDays === 1) return { base, relative: 'ieri' };
    if (diffDays < 7) return { base, relative: `${diffDays}gg fa` };
    if (diffWeeks === 1) return { base, relative: '1 sett fa' };
    return { base, relative: `${diffWeeks} sett fa` };
}

function initials(name: string) {
    return name?.split(' ').slice(0, 2).map((n: string) => n[0]).join('').toUpperCase() || '?';
}

function weightDelta(checkIn: any) {
    if (!checkIn.weight_kg) return null;
    const prev = checkIn._prev_weight;
    if (!prev) return null;
    return Math.round((Number(checkIn.weight_kg) - Number(prev)) * 10) / 10;
}

const statusTabs = [
    { key: 'all', label: 'Tutti', count: props.checkIns.total },
    { key: 'to_review', label: 'Da revisionare', dot: 'bg-amber-400', count: props.stats.to_review },
    { key: 'reviewed', label: 'Revisionati', dot: 'bg-green-500', count: props.stats.reviewed_this_week },
];
</script>

<template>
    <Head title="Monitoraggi" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400 mb-0.5">MONITORAGGI</p>
                <div class="flex items-center justify-between gap-4">
                    <h1 class="text-2xl font-bold text-gray-900">Check-in clienti</h1>
                    <div class="flex items-center gap-2">
                            <button
                            @click="reviewAll"
                            :disabled="reviewAllForm.processing || stats.to_review === 0"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-sm"
                        >
                            <CheckCircle2 class="h-4 w-4" />
                            Segna tutti revisionati
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Stats cards -->
        <div class="mb-6 grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-500 flex-shrink-0">
                    <AlertTriangle class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">DA REVISIONARE</p>
                    <p class="text-2xl font-bold text-gray-900 leading-tight">{{ stats.to_review }}</p>
                    <p class="text-xs text-gray-400">check-in non ancora visti</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-green-500 flex-shrink-0">
                    <CheckCircle2 class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">REVISIONATI</p>
                    <p class="text-2xl font-bold text-gray-900 leading-tight">{{ stats.reviewed_this_week }}</p>
                    <p class="text-xs text-gray-400">settimana corrente</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-500 flex-shrink-0">
                    <ClipboardList class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">TOTALI MESE</p>
                    <p class="text-2xl font-bold text-gray-900 leading-tight">{{ stats.total_this_month }}</p>
                    <p class="text-xs text-gray-400">questo mese</p>
                </div>
            </div>
            <div class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-500 flex-shrink-0">
                    <Frown class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">UMORE BASSO</p>
                    <p class="text-2xl font-bold text-gray-900 leading-tight">{{ stats.low_mood }}</p>
                    <p class="text-xs text-gray-400">≤ 2/5 — richiede attenzione</p>
                </div>
            </div>
        </div>

        <!-- Search + Filters bar -->
        <div class="mb-5 flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div class="relative flex-1 min-w-48">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cerca per cliente o nota..."
                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-9 text-sm text-gray-900 placeholder-gray-400 focus:border-gray-400 focus:outline-none focus:ring-0 shadow-sm"
                />
                <button v-if="search" @click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <X class="h-3.5 w-3.5" />
                </button>
            </div>

            <!-- Status tabs -->
            <div class="flex items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-sm">
                <button
                    v-for="tab in statusTabs"
                    :key="tab.key"
                    @click="status = tab.key"
                    :class="[
                        'flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition',
                        status === tab.key
                            ? 'bg-gray-900 text-white shadow-sm'
                            : 'text-gray-600 hover:text-gray-900'
                    ]"
                >
                    <span v-if="tab.dot" :class="['h-2 w-2 rounded-full', tab.dot, status === tab.key ? 'opacity-80' : '']"></span>
                    {{ tab.label }}
                    <span :class="['text-xs', status === tab.key ? 'text-gray-300' : 'text-gray-400']">{{ tab.count }}</span>
                </button>
            </div>

            <!-- Client filter -->
            <div class="relative">
                <select
                    v-model="clientId"
                    class="appearance-none rounded-xl border border-gray-200 bg-white py-2.5 pl-3.5 pr-8 text-sm text-gray-700 focus:border-gray-400 focus:outline-none focus:ring-0 shadow-sm cursor-pointer"
                >
                    <option value="">Tutti i clienti</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.user.name }}</option>
                </select>
                <ChevronDown class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
            </div>
        </div>

        <!-- Empty state -->
        <div v-if="checkIns.data.length === 0" class="rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm">
            <ClipboardList class="mx-auto h-12 w-12 text-gray-200 mb-4" />
            <h3 class="text-base font-semibold text-gray-900 mb-1">Nessun monitoraggio trovato</h3>
            <p class="text-sm text-gray-400">Prova a modificare i filtri di ricerca.</p>
        </div>

        <!-- List -->
        <div v-else>
            <!-- Count + column labels -->
            <div class="mb-2 flex items-center justify-between px-1">
                <p class="text-sm text-gray-500">{{ checkIns.total }} monitoraggi</p>
                <div class="hidden lg:flex items-center gap-4 text-xs text-gray-400 font-medium pr-28">
                    <span class="flex items-center gap-1"><Scale class="h-3.5 w-3.5" /> peso</span>
                    <span class="flex items-center gap-1"><Droplets class="h-3.5 w-3.5" /> idratazione</span>
                    <span class="flex items-center gap-1"><Smile class="h-3.5 w-3.5" /> umore</span>
                    <span class="flex items-center gap-1"><Zap class="h-3.5 w-3.5" /> energia</span>
                    <span class="flex items-center gap-1"><Moon class="h-3.5 w-3.5" /> sonno</span>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                <Link
                    v-for="(checkIn, i) in checkIns.data"
                    :key="checkIn.id"
                    :href="route('nutritionist.check-ins.show', checkIn.id)"
                    :class="[
                        'flex items-center gap-4 px-5 py-4 hover:bg-gray-50/80 transition group',
                        i > 0 ? 'border-t border-gray-100' : '',
                    ]"
                >
                    <!-- Status accent bar -->
                    <div :class="['w-1 self-stretch rounded-full flex-shrink-0', checkIn.reviewed_at ? 'bg-transparent' : 'bg-amber-400']"></div>

                    <!-- Avatar -->
                    <div :class="[
                        'flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full text-sm font-semibold',
                        checkIn.reviewed_at ? 'bg-gray-100 text-gray-500' : 'bg-green-100 text-green-700',
                    ]">
                        {{ initials(checkIn.client?.user?.name || '') }}
                    </div>

                    <!-- Name + date + note -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline gap-2 mb-0.5">
                            <span class="font-semibold text-gray-900 text-sm">{{ checkIn.client?.user?.name }}</span>
                            <span class="text-xs text-gray-400">{{ formatDate(checkIn.date).base }}</span>
                            <span class="text-xs text-gray-400">({{ formatDate(checkIn.date).relative }})</span>
                        </div>
                        <p v-if="checkIn.notes" class="text-xs text-gray-500 truncate max-w-xs">"{{ checkIn.notes }}"</p>
                    </div>

                    <!-- Metrics (hidden on small screens) -->
                    <div class="hidden lg:flex items-center gap-4 text-sm flex-shrink-0">
                        <!-- Peso -->
                        <div class="flex items-center gap-1.5 w-28">
                            <Scale class="h-3.5 w-3.5 text-gray-300 flex-shrink-0" />
                            <span v-if="checkIn.weight_kg" class="text-gray-700 font-medium">{{ checkIn.weight_kg }} kg</span>
                            <span v-else class="text-gray-300">—</span>
                        </div>

                        <!-- Idratazione -->
                        <div class="flex items-center gap-1 w-16">
                            <Droplets class="h-3.5 w-3.5 text-blue-300 flex-shrink-0" />
                            <span v-if="checkIn.water_liters" :class="[
                                'font-medium text-xs px-1.5 py-0.5 rounded-md',
                                checkIn.water_liters >= 2 ? 'bg-blue-50 text-blue-700' : 'bg-rose-50 text-rose-600'
                            ]">{{ checkIn.water_liters }} L</span>
                            <span v-else class="text-gray-300 text-xs">—</span>
                        </div>

                        <!-- Umore -->
                        <div class="flex items-center gap-1 w-14">
                            <Smile class="h-3.5 w-3.5 text-yellow-300 flex-shrink-0" />
                            <span v-if="checkIn.mood" :class="[
                                'font-medium text-xs px-1.5 py-0.5 rounded-md',
                                checkIn.mood >= 4 ? 'bg-green-50 text-green-700' : checkIn.mood <= 2 ? 'bg-rose-50 text-rose-600' : 'bg-gray-50 text-gray-600'
                            ]">{{ checkIn.mood }}/5</span>
                            <span v-else class="text-gray-300 text-xs">—</span>
                        </div>

                        <!-- Energia -->
                        <div class="flex items-center gap-1 w-14">
                            <Zap class="h-3.5 w-3.5 text-orange-300 flex-shrink-0" />
                            <span v-if="checkIn.energy_level" :class="[
                                'font-medium text-xs px-1.5 py-0.5 rounded-md',
                                checkIn.energy_level >= 4 ? 'bg-orange-50 text-orange-700' : checkIn.energy_level <= 2 ? 'bg-rose-50 text-rose-600' : 'bg-gray-50 text-gray-600'
                            ]">{{ checkIn.energy_level }}/5</span>
                            <span v-else class="text-gray-300 text-xs">—</span>
                        </div>

                        <!-- Sonno -->
                        <div class="flex items-center gap-1 w-14">
                            <Moon class="h-3.5 w-3.5 text-indigo-300 flex-shrink-0" />
                            <span v-if="checkIn.sleep_quality" :class="[
                                'font-medium text-xs px-1.5 py-0.5 rounded-md',
                                checkIn.sleep_quality >= 4 ? 'bg-indigo-50 text-indigo-700' : checkIn.sleep_quality <= 2 ? 'bg-rose-50 text-rose-600' : 'bg-gray-50 text-gray-600'
                            ]">{{ checkIn.sleep_quality }}/5</span>
                            <span v-else class="text-gray-300 text-xs">—</span>
                        </div>
                    </div>

                    <!-- Status badge -->
                    <div class="flex-shrink-0 w-32 text-right">
                        <span v-if="!checkIn.reviewed_at" class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                            <AlertTriangle class="h-3 w-3" />
                            Da revisionare
                        </span>
                        <span v-else class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">
                            <CheckCircle2 class="h-3 w-3" />
                            Revisionato
                        </span>
                    </div>

                    <ChevronRight class="h-4 w-4 text-gray-300 flex-shrink-0 group-hover:text-gray-400 transition" />
                </Link>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="checkIns.last_page > 1" class="mt-5 flex justify-center gap-1">
            <Link
                v-for="link in checkIns.links"
                :key="link.label"
                :href="link.url || '#'"
                :class="[
                    'rounded-lg px-3 py-2 text-sm font-medium',
                    link.active ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100',
                    !link.url ? 'opacity-40 pointer-events-none' : ''
                ]"
                v-html="link.label"
                preserve-state
            />
        </div>
    </AuthenticatedLayout>
</template>
