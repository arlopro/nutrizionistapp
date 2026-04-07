<script setup lang="ts">
import DevLayout from '@/Layouts/DevLayout.vue';
import { router } from '@inertiajs/vue3';
import { Users, UtensilsCrossed, CalendarDays, UserCog } from 'lucide-vue-next';

defineProps<{
    nutritionists: {
        data: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
}>();

function impersonate(userId: number) {
    if (!confirm('Vuoi impersonificare questo nutrizionista?')) return;
    router.post(route('dev.impersonate', userId));
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<template>
    <DevLayout>
        <div class="space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold text-white">Nutrizionisti</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ nutritionists.total }} registrati</p>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-xl bg-gray-900 border border-gray-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nutrizionista</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Specializzazione</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pazienti</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Piani</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Appuntamenti</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden xl:table-cell">Registrato</th>
                            <th class="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <tr v-for="n in nutritionists.data" :key="n.id" class="hover:bg-gray-800/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-violet-500 to-violet-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                        {{ n.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-100">{{ n.name }} {{ n.last_name }}</div>
                                        <div class="text-xs text-gray-500">{{ n.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-400 hidden md:table-cell">
                                {{ n.specialization || '—' }}
                                <span v-if="n.city" class="ml-1 text-gray-600">· {{ n.city }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="font-bold text-emerald-400">{{ n.active_clients_count }}</div>
                                <div class="text-xs text-gray-600">/ {{ n.clients_count }} tot</div>
                            </td>
                            <td class="px-4 py-4 text-center text-gray-300 hidden lg:table-cell">{{ n.plans_count }}</td>
                            <td class="px-4 py-4 text-center text-gray-300 hidden lg:table-cell">{{ n.appointments_count }}</td>
                            <td class="px-4 py-4 text-gray-500 text-xs hidden xl:table-cell">{{ formatDate(n.created_at) }}</td>
                            <td class="px-4 py-4">
                                <button
                                    @click="impersonate(n.id)"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-violet-600/20 border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-300 hover:bg-violet-600/30 transition whitespace-nowrap"
                                >
                                    <UserCog class="h-3.5 w-3.5" />
                                    Impersona
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!nutritionists.data.length">
                            <td colspan="7" class="px-5 py-10 text-center text-gray-500">Nessun nutrizionista</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="nutritionists.last_page > 1" class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Pagina {{ nutritionists.current_page }} di {{ nutritionists.last_page }}</span>
                <div class="flex gap-2">
                    <button v-if="nutritionists.current_page > 1" @click="router.get(route('dev.nutritionists'), { page: nutritionists.current_page - 1 })" class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-1.5 text-gray-300 hover:bg-gray-700 transition">← Prec</button>
                    <button v-if="nutritionists.current_page < nutritionists.last_page" @click="router.get(route('dev.nutritionists'), { page: nutritionists.current_page + 1 })" class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-1.5 text-gray-300 hover:bg-gray-700 transition">Succ →</button>
                </div>
            </div>
        </div>
    </DevLayout>
</template>
