<script setup lang="ts">
import DevLayout from '@/Layouts/DevLayout.vue';
import { router } from '@inertiajs/vue3';
import { UserCog } from 'lucide-vue-next';

defineProps<{
    users: {
        data: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
}>();

function impersonate(userId: number) {
    if (!confirm('Vuoi impersonificare questo utente?')) return;
    router.post(route('dev.impersonate', userId));
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'short', year: 'numeric' });
}

const roleColors: Record<string, string> = {
    dev: 'bg-violet-500/20 text-violet-300 border-violet-500/30',
    nutritionist: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
    client: 'bg-blue-500/20 text-blue-300 border-blue-500/30',
};
</script>

<template>
    <DevLayout>
        <div class="space-y-5">
            <div>
                <h1 class="text-2xl font-extrabold text-white">Tutti gli utenti</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ users.total }} utenti nel sistema</p>
            </div>

            <div class="rounded-xl bg-gray-900 border border-gray-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Utente</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ruolo</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Registrato</th>
                            <th class="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <tr v-for="u in users.data" :key="u.id" class="hover:bg-gray-800/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-gray-600 to-gray-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                        {{ u.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-100">{{ u.name }} {{ u.last_name }}</div>
                                        <div class="text-xs text-gray-500">{{ u.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="role in u.roles"
                                        :key="role"
                                        :class="['inline-flex rounded-full border px-2 py-0.5 text-xs font-semibold', roleColors[role] ?? 'bg-gray-500/20 text-gray-300 border-gray-500/30']"
                                    >
                                        {{ role }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-gray-500 text-xs hidden lg:table-cell">{{ formatDate(u.created_at) }}</td>
                            <td class="px-4 py-4 text-right">
                                <button
                                    v-if="!u.roles.includes('dev')"
                                    @click="impersonate(u.id)"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-violet-600/20 border border-violet-500/30 px-3 py-1.5 text-xs font-semibold text-violet-300 hover:bg-violet-600/30 transition whitespace-nowrap"
                                >
                                    <UserCog class="h-3.5 w-3.5" />
                                    Impersona
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!users.data.length">
                            <td colspan="4" class="px-5 py-10 text-center text-gray-500">Nessun utente</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="users.last_page > 1" class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Pagina {{ users.current_page }} di {{ users.last_page }}</span>
                <div class="flex gap-2">
                    <button v-if="users.current_page > 1" @click="router.get(route('dev.users'), { page: users.current_page - 1 })" class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-1.5 text-gray-300 hover:bg-gray-700 transition">← Prec</button>
                    <button v-if="users.current_page < users.last_page" @click="router.get(route('dev.users'), { page: users.current_page + 1 })" class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-1.5 text-gray-300 hover:bg-gray-700 transition">Succ →</button>
                </div>
            </div>
        </div>
    </DevLayout>
</template>
