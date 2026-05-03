<script setup lang="ts">
import DevLayout from '@/Layouts/DevLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';

const props = defineProps<{
    activities: {
        data: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
    nutritionists: any[];
    filters: Record<string, string>;
}>();

const form = reactive({ ...props.filters });

function apply() {
    router.get(route('dev.activity'), form, { preserveState: true, replace: true });
}

function reset() {
    Object.keys(form).forEach(k => (form as any)[k] = '');
    apply();
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

const eventColors: Record<string, string> = {
    login: 'text-emerald-400',
    created: 'text-blue-400',
    updated: 'text-yellow-400',
    deleted: 'text-red-400',
    impersonate_start: 'text-orange-400',
    impersonate_stop: 'text-orange-300',
};
</script>

<template>
    <DevLayout>
        <div class="space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold text-white">Activity Log</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ activities.total }} eventi</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-xl bg-gray-900 border border-gray-800 p-4">
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                    <select v-model="form.causer_id" class="rounded-lg border border-gray-700 bg-gray-800 text-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-violet-500">
                        <option value="">Tutti i nutrizionisti</option>
                        <option v-for="n in nutritionists" :key="n.id" :value="n.id">{{ n.name }} {{ n.last_name }}</option>
                    </select>
                    <select v-model="form.event" class="rounded-lg border border-gray-700 bg-gray-800 text-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-violet-500">
                        <option value="">Tutti gli eventi</option>
                        <option value="login">login</option>
                        <option value="created">created</option>
                        <option value="updated">updated</option>
                        <option value="deleted">deleted</option>
                        <option value="impersonate_start">impersonate_start</option>
                        <option value="impersonate_stop">impersonate_stop</option>
                    </select>
                    <select v-model="form.subject_type" class="rounded-lg border border-gray-700 bg-gray-800 text-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-violet-500">
                        <option value="">Tutti i tipi</option>
                        <option value="User">User</option>
                        <option value="NutritionalPlan">NutritionalPlan</option>
                        <option value="ClientProfile">ClientProfile</option>
                        <option value="Appointment">Appointment</option>
                        <option value="Recipe">Recipe</option>
                    </select>
                    <input type="date" v-model="form.from_date" class="rounded-lg border border-gray-700 bg-gray-800 text-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-violet-500" />
                    <input type="date" v-model="form.to_date" class="rounded-lg border border-gray-700 bg-gray-800 text-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-1 focus:ring-violet-500" />
                </div>
                <div class="flex gap-2 mt-3">
                    <button @click="apply" class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700 transition">Filtra</button>
                    <button @click="reset" class="rounded-lg border border-gray-700 bg-gray-800 px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 transition">Reset</button>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-xl bg-gray-900 border border-gray-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Evento</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Causato da</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Soggetto</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <tr v-for="a in activities.data" :key="a.id" class="hover:bg-gray-800/40">
                            <td class="px-5 py-3">
                                <span :class="['font-mono text-xs font-semibold', eventColors[a.event] || 'text-gray-400']">
                                    {{ a.event || a.description }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs hidden md:table-cell">
                                <template v-if="a.causer">
                                    {{ a.causer.name }} {{ a.causer.last_name }}
                                    <span class="text-gray-600 ml-1">#{{ a.causer_id }}</span>
                                </template>
                                <span v-else class="text-gray-600">—</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs hidden lg:table-cell">
                                {{ a.subject_type?.split('\\').pop() }}
                                <span v-if="a.subject_id" class="text-gray-600">#{{ a.subject_id }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ formatDate(a.created_at) }}</td>
                        </tr>
                        <tr v-if="!activities.data.length">
                            <td colspan="4" class="px-5 py-10 text-center text-gray-500">Nessuna attività</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="activities.last_page > 1" class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Pagina {{ activities.current_page }} di {{ activities.last_page }}</span>
                <div class="flex gap-2">
                    <button v-if="activities.current_page > 1" @click="router.get(route('dev.activity'), { ...filters, page: activities.current_page - 1 })" class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-1.5 text-gray-300 hover:bg-gray-700 transition">← Prec</button>
                    <button v-if="activities.current_page < activities.last_page" @click="router.get(route('dev.activity'), { ...filters, page: activities.current_page + 1 })" class="rounded-lg border border-gray-700 bg-gray-800 px-3 py-1.5 text-gray-300 hover:bg-gray-700 transition">Succ →</button>
                </div>
            </div>
        </div>
    </DevLayout>
</template>
