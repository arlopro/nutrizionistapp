<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OnboardingCard from '@/Components/OnboardingCard.vue';
import { Head } from '@inertiajs/vue3';
import { Users, UserCheck, CalendarDays, ClipboardCheck, TrendingUp, Clock } from 'lucide-vue-next';

const props = defineProps<{
    stats: {
        clientCount: number;
        activeClientCount: number;
        todayAppointmentCount: number;
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
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
        </template>

        <!-- Onboarding card (sparisce quando completato o chiuso) -->
        <OnboardingCard v-if="onboarding" :onboarding="onboarding" />

        <!-- Stats cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-8">
            <div class="rounded-2xl bg-white border border-gray-100 p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-primary-50 p-3">
                        <Users class="h-6 w-6 text-primary-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Clienti totali</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.clientCount }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-gray-100 p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-green-50 p-3">
                        <UserCheck class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Clienti attivi</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.activeClientCount }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-gray-100 p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="rounded-xl bg-blue-50 p-3">
                        <CalendarDays class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Appuntamenti oggi</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.todayAppointmentCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Today's appointments -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm">
                <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
                    <Clock class="h-5 w-5 text-gray-400" />
                    <h2 class="text-base font-semibold text-gray-900">Appuntamenti di oggi</h2>
                </div>
                <div class="p-6">
                    <div v-if="todayAppointments.length === 0" class="text-center py-8 text-gray-400">
                        <CalendarDays class="mx-auto h-10 w-10 mb-2" />
                        <p class="text-sm">Nessun appuntamento per oggi</p>
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="apt in todayAppointments"
                            :key="apt.id"
                            class="flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3"
                        >
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-primary-400 to-primary-600 text-white text-xs font-bold">
                                {{ apt.client?.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ apt.title }}</p>
                                <p class="text-xs text-gray-500">{{ apt.client?.user?.name || 'Blocco orario' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">{{ formatTime(apt.starts_at) }}</p>
                                <p class="text-xs text-gray-500">{{ formatTime(apt.ends_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent check-ins -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm">
                <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
                    <ClipboardCheck class="h-5 w-5 text-gray-400" />
                    <h2 class="text-base font-semibold text-gray-900">Monitoraggi recenti</h2>
                </div>
                <div class="p-6">
                    <div v-if="recentCheckIns.length === 0" class="text-center py-8 text-gray-400">
                        <TrendingUp class="mx-auto h-10 w-10 mb-2" />
                        <p class="text-sm">Nessun monitoraggio recente</p>
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="ci in recentCheckIns"
                            :key="ci.id"
                            class="flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3"
                        >
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-primary-400 to-primary-600 text-white text-xs font-bold">
                                {{ ci.client?.user?.name?.charAt(0)?.toUpperCase() || '?' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ ci.client?.user?.name }}</p>
                                <p class="text-xs text-gray-500">{{ formatDate(ci.date) }}</p>
                            </div>
                            <div v-if="ci.weight_kg" class="text-right">
                                <p class="text-sm font-semibold text-gray-900">{{ ci.weight_kg }} kg</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
