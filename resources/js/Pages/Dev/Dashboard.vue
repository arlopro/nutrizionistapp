<script setup lang="ts">
import DevLayout from '@/Layouts/DevLayout.vue';
import { Link } from '@inertiajs/vue3';
import { Users, UtensilsCrossed, CalendarDays, TrendingUp, UserPlus, FileText } from 'lucide-vue-next';

defineProps<{
    stats: {
        totalNutritionists: number;
        totalClients: number;
        totalPlans: number;
        activePlans: number;
        totalAppointments: number;
        newNutritionistsThisMonth: number;
        newClientsThisMonth: number;
    };
    topNutritionists: any[];
    recentPlans: any[];
}>();

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<template>
    <DevLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-extrabold text-white">Dashboard</h1>
                <p class="text-sm text-gray-500 mt-0.5">Panoramica globale del SaaS</p>
            </div>

            <!-- Stats grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="stat in [
                    { label: 'Nutrizionisti', value: stats.totalNutritionists, sub: `+${stats.newNutritionistsThisMonth} questo mese`, icon: Users, color: 'text-violet-400', bg: 'bg-violet-500/10' },
                    { label: 'Pazienti totali', value: stats.totalClients, sub: `+${stats.newClientsThisMonth} questo mese`, icon: UserPlus, color: 'text-blue-400', bg: 'bg-blue-500/10' },
                    { label: 'Piani attivi', value: stats.activePlans, sub: `${stats.totalPlans} totali`, icon: UtensilsCrossed, color: 'text-emerald-400', bg: 'bg-emerald-500/10' },
                    { label: 'Appuntamenti', value: stats.totalAppointments, sub: 'totali', icon: CalendarDays, color: 'text-orange-400', bg: 'bg-orange-500/10' },
                ]" :key="stat.label"
                    class="rounded-xl bg-gray-900 border border-gray-800 p-5"
                >
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-gray-400">{{ stat.label }}</span>
                        <div :class="['rounded-lg p-2', stat.bg]">
                            <component :is="stat.icon" :class="['h-4 w-4', stat.color]" />
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-white">{{ stat.value }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ stat.sub }}</div>
                </div>
            </div>

            <!-- Two columns -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Top nutritionists -->
                <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-white">Top Nutrizionisti</h2>
                        <Link :href="route('dev.nutritionists')" class="text-xs text-violet-400 hover:text-violet-300">Vedi tutti →</Link>
                    </div>
                    <div class="space-y-3">
                        <div v-for="(n, i) in topNutritionists" :key="n.id" class="flex items-center gap-3">
                            <div class="h-7 w-7 rounded-full bg-gradient-to-br from-violet-500 to-violet-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ n.name.charAt(0) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-200 truncate">{{ n.name }} {{ n.last_name }}</div>
                                <div class="text-xs text-gray-500">{{ n.email }}</div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="text-sm font-bold text-emerald-400">{{ n.clients_count }}</div>
                                <div class="text-xs text-gray-500">pazienti</div>
                            </div>
                        </div>
                        <div v-if="!topNutritionists.length" class="text-sm text-gray-500 text-center py-4">Nessun nutrizionista</div>
                    </div>
                </div>

                <!-- Recent plans -->
                <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-white">Piani recenti</h2>
                        <Link :href="route('dev.plans')" class="text-xs text-violet-400 hover:text-violet-300">Vedi tutti →</Link>
                    </div>
                    <div class="space-y-2.5">
                        <div v-for="plan in recentPlans" :key="plan.id" class="flex items-start gap-3">
                            <div class="h-6 w-6 rounded-md bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <FileText class="h-3.5 w-3.5 text-emerald-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm text-gray-200 truncate">{{ plan.title }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ plan.nutritionist?.name }} → {{ plan.client?.user?.name }} {{ plan.client?.user?.last_name }}
                                </div>
                            </div>
                            <div class="text-xs text-gray-600 flex-shrink-0">{{ formatDate(plan.created_at) }}</div>
                        </div>
                        <div v-if="!recentPlans.length" class="text-sm text-gray-500 text-center py-4">Nessun piano</div>
                    </div>
                </div>
            </div>
        </div>
    </DevLayout>
</template>
