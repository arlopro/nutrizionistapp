<script setup lang="ts">
import DevLayout from '@/Layouts/DevLayout.vue';
import { router } from '@inertiajs/vue3';

defineProps<{
    plans: {
        data: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
}>();

function statusColor(s: string) {
    const m: Record<string, string> = {
        active: 'bg-emerald-500/20 text-emerald-400',
        draft: 'bg-gray-500/20 text-gray-400',
        completed: 'bg-blue-500/20 text-blue-400',
        archived: 'bg-orange-500/20 text-orange-400',
    };
    return m[s] ?? 'bg-gray-500/20 text-gray-400';
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<template>
    <DevLayout>
        <div class="space-y-5">
            <div>
                <h1 class="text-2xl font-extrabold text-white">Piani nutrizionali</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ plans.total }} piani totali</p>
            </div>

            <div class="rounded-xl bg-gray-900 border border-gray-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Piano</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Nutrizionista</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Paziente</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stato</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Pasti</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden xl:table-cell">Creato</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <tr v-for="plan in plans.data" :key="plan.id" class="hover:bg-gray-800/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-100 truncate max-w-48">{{ plan.title }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ plan.start_date ? formatDate(plan.start_date) : '—' }}
                                    <template v-if="plan.end_date"> → {{ formatDate(plan.end_date) }}</template>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-300 hidden md:table-cell">
                                {{ plan.nutritionist?.name }} {{ plan.nutritionist?.last_name }}
                            </td>
                            <td class="px-5 py-4 text-gray-300 hidden md:table-cell">
                                {{ plan.client?.user?.name }} {{ plan.client?.user?.last_name }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span :class="['inline-flex rounded-full px-2 py-0.5 text-xs font-semibold', statusColor(plan.status?.value ?? plan.status)]">
                                    {{ plan.status?.value ?? plan.status }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center text-gray-300 hidden lg:table-cell">{{ plan.meals_count }}</td>
                            <td class="px-4 py-4 text-gray-500 text-xs hidden xl:table-cell">{{ formatDate(plan.created_at) }}</td>
                        </tr>
                        <tr v-if="!plans.data.length">
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500">Nessun piano</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="plans.last_page > 1" class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Pagina {{ plans.current_page }} di {{ plans.last_page }}</span>
                <div class="flex gap-2">
                    <button v-if="plans.current_page > 1" @click="router.get(route('dev.plans'), { page: plans.current_page - 1 })" class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-1.5 text-gray-300 hover:bg-gray-700 transition">← Prec</button>
                    <button v-if="plans.current_page < plans.last_page" @click="router.get(route('dev.plans'), { page: plans.current_page + 1 })" class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-1.5 text-gray-300 hover:bg-gray-700 transition">Succ →</button>
                </div>
            </div>
        </div>
    </DevLayout>
</template>
