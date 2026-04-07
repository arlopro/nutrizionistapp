<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OnboardingCard from '@/Components/OnboardingCard.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Users, UserCheck, CalendarDays, ClipboardCheck,
    TrendingUp, Clock, UtensilsCrossed, Plus,
    ArrowRight, ChevronRight, Scale
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    stats: {
        clientCount: number;
        activeClientCount: number;
        todayAppointmentCount: number;
        planCount: number;
    };
    todayAppointments: any[];
    upcomingAppointments: any[];
    recentCheckIns: any[];
    onboarding: any | null;
}>();

function formatTime(dateStr: string) {
    return new Date(dateStr).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('it-IT', { day: 'numeric', month: 'short' });
}

function formatDateFull(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('it-IT', { weekday: 'short', day: 'numeric', month: 'short' });
}

function initials(name: string | undefined | null): string {
    if (!name) return '?';
    return name.split(' ').map((n: string) => n[0]).slice(0, 2).join('').toUpperCase();
}

const activeRate = computed(() => {
    if (!props.stats.clientCount) return 0;
    return Math.round((props.stats.activeClientCount / props.stats.clientCount) * 100);
});

const statCards = computed(() => [
    {
        label: 'Clienti totali',
        value: props.stats.clientCount,
        icon: Users,
        iconBg: 'bg-primary-50',
        iconColor: 'text-primary-600',
        badge: props.stats.clientCount > 0 ? `${activeRate.value}% attivi` : null,
        badgeColor: 'text-primary-600 bg-primary-50',
    },
    {
        label: 'Clienti attivi',
        value: props.stats.activeClientCount,
        icon: UserCheck,
        iconBg: 'bg-emerald-50',
        iconColor: 'text-emerald-600',
        badge: null,
        badgeColor: '',
    },
    {
        label: 'Appuntamenti oggi',
        value: props.stats.todayAppointmentCount,
        icon: CalendarDays,
        iconBg: 'bg-blue-50',
        iconColor: 'text-blue-600',
        badge: props.stats.todayAppointmentCount > 0 ? 'In programma' : null,
        badgeColor: 'text-blue-600 bg-blue-50',
    },
    {
        label: 'Piani creati',
        value: props.stats.planCount,
        icon: UtensilsCrossed,
        iconBg: 'bg-violet-50',
        iconColor: 'text-violet-600',
        badge: null,
        badgeColor: '',
    },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
        </template>

        <!-- Onboarding -->
        <OnboardingCard v-if="onboarding" :onboarding="onboarding" />

        <!-- Stats -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-8">
            <div
                v-for="card in statCards"
                :key="card.label"
                class="rounded-2xl bg-white border border-gray-100 p-5 shadow-sm"
            >
                <div class="flex items-start justify-between mb-3">
                    <div :class="['rounded-xl p-2.5', card.iconBg]">
                        <component :is="card.icon" :class="['h-5 w-5', card.iconColor]" />
                    </div>
                    <span
                        v-if="card.badge"
                        :class="['text-xs font-medium px-2 py-0.5 rounded-full', card.badgeColor]"
                    >
                        {{ card.badge }}
                    </span>
                </div>
                <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ card.value }}</p>
                <p class="text-sm text-gray-500 mt-0.5">{{ card.label }}</p>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="flex flex-wrap gap-3 mb-8">
            <Link
                :href="route('nutritionist.clients.create')"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-primary-700 transition-colors"
            >
                <Plus class="h-4 w-4" />
                Nuovo cliente
            </Link>
            <Link
                :href="route('nutritionist.appointments.index')"
                class="inline-flex items-center gap-2 rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors"
            >
                <CalendarDays class="h-4 w-4 text-gray-400" />
                Gestisci appuntamenti
            </Link>
            <Link
                :href="route('nutritionist.clients.index')"
                class="inline-flex items-center gap-2 rounded-xl bg-white border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors"
            >
                <Users class="h-4 w-4 text-gray-400" />
                Lista clienti
            </Link>
        </div>

        <!-- Main grid -->
        <div class="grid gap-6 lg:grid-cols-2 xl:grid-cols-3">

            <!-- Today's appointments -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm flex flex-col">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <Clock class="h-4 w-4 text-blue-500" />
                        <h2 class="text-sm font-semibold text-gray-900">Appuntamenti di oggi</h2>
                    </div>
                    <Link
                        :href="route('nutritionist.appointments.index')"
                        class="text-xs text-primary-600 hover:text-primary-800 font-medium flex items-center gap-0.5 transition-colors"
                    >
                        Tutti <ChevronRight class="h-3.5 w-3.5" />
                    </Link>
                </div>
                <div class="flex-1 p-5">
                    <div v-if="todayAppointments.length === 0" class="flex flex-col items-center justify-center py-10 text-gray-400">
                        <div class="rounded-2xl bg-gray-50 p-4 mb-3">
                            <CalendarDays class="h-8 w-8 text-gray-300" />
                        </div>
                        <p class="text-sm font-medium text-gray-500">Nessun appuntamento oggi</p>
                        <p class="text-xs text-gray-400 mt-1">Hai la giornata libera</p>
                    </div>
                    <div v-else class="space-y-2.5">
                        <div
                            v-for="apt in todayAppointments"
                            :key="apt.id"
                            class="flex items-center gap-3 rounded-xl bg-gray-50 px-3.5 py-3 hover:bg-gray-100/70 transition-colors"
                        >
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 text-white text-xs font-bold">
                                {{ initials(apt.client?.user?.name) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ apt.title }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ apt.client?.user?.name || 'Blocco orario' }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-semibold text-gray-900">{{ formatTime(apt.starts_at) }}</p>
                                <p class="text-xs text-gray-400">{{ formatTime(apt.ends_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming appointments -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm flex flex-col">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <CalendarDays class="h-4 w-4 text-primary-500" />
                        <h2 class="text-sm font-semibold text-gray-900">Prossimi appuntamenti</h2>
                    </div>
                    <Link
                        :href="route('nutritionist.appointments.index')"
                        class="text-xs text-primary-600 hover:text-primary-800 font-medium flex items-center gap-0.5 transition-colors"
                    >
                        Tutti <ChevronRight class="h-3.5 w-3.5" />
                    </Link>
                </div>
                <div class="flex-1 p-5">
                    <div v-if="upcomingAppointments.length === 0" class="flex flex-col items-center justify-center py-10 text-gray-400">
                        <div class="rounded-2xl bg-gray-50 p-4 mb-3">
                            <CalendarDays class="h-8 w-8 text-gray-300" />
                        </div>
                        <p class="text-sm font-medium text-gray-500">Nessun appuntamento futuro</p>
                        <Link
                            :href="route('nutritionist.appointments.index')"
                            class="mt-2 text-xs text-primary-600 hover:text-primary-800 font-medium inline-flex items-center gap-1 transition-colors"
                        >
                            Pianifica uno <ArrowRight class="h-3 w-3" />
                        </Link>
                    </div>
                    <div v-else class="space-y-2.5">
                        <div
                            v-for="apt in upcomingAppointments"
                            :key="apt.id"
                            class="flex items-center gap-3 rounded-xl bg-gray-50 px-3.5 py-3 hover:bg-gray-100/70 transition-colors"
                        >
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-primary-400 to-primary-600 text-white text-xs font-bold">
                                {{ initials(apt.client?.user?.name) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ apt.title }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ apt.client?.user?.name || 'Blocco orario' }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-xs font-semibold text-gray-900">{{ formatDateFull(apt.starts_at) }}</p>
                                <p class="text-xs text-gray-400">{{ formatTime(apt.starts_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent check-ins -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm flex flex-col lg:col-span-2 xl:col-span-1">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <ClipboardCheck class="h-4 w-4 text-emerald-500" />
                        <h2 class="text-sm font-semibold text-gray-900">Monitoraggi recenti</h2>
                    </div>
                    <Link
                        :href="route('nutritionist.check-ins.index')"
                        class="text-xs text-primary-600 hover:text-primary-800 font-medium flex items-center gap-0.5 transition-colors"
                    >
                        Tutti <ChevronRight class="h-3.5 w-3.5" />
                    </Link>
                </div>
                <div class="flex-1 p-5">
                    <div v-if="recentCheckIns.length === 0" class="flex flex-col items-center justify-center py-10 text-gray-400">
                        <div class="rounded-2xl bg-gray-50 p-4 mb-3">
                            <TrendingUp class="h-8 w-8 text-gray-300" />
                        </div>
                        <p class="text-sm font-medium text-gray-500">Nessun monitoraggio</p>
                        <p class="text-xs text-gray-400 mt-1">I check-in dei clienti appariranno qui</p>
                    </div>
                    <div v-else class="space-y-2.5">
                        <div
                            v-for="ci in recentCheckIns"
                            :key="ci.id"
                            class="flex items-center gap-3 rounded-xl bg-gray-50 px-3.5 py-3 hover:bg-gray-100/70 transition-colors"
                        >
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-emerald-600 text-white text-xs font-bold">
                                {{ initials(ci.client?.user?.name) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ ci.client?.user?.name }}</p>
                                <p class="text-xs text-gray-500">{{ formatDate(ci.date) }}</p>
                            </div>
                            <div v-if="ci.weight_kg" class="flex items-center gap-1 flex-shrink-0 text-right">
                                <Scale class="h-3.5 w-3.5 text-gray-400" />
                                <p class="text-sm font-semibold text-gray-900">{{ ci.weight_kg }} kg</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
