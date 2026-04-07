<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Plus, Search, FileText, Calendar, User, BookTemplate } from 'lucide-vue-next';

const props = defineProps<{
    plans: any;
    filters: { search?: string; status?: string; client_id?: string };
    statuses: { value: string; label: string }[];
    clients: any[];
}>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const clientId = ref(props.filters.client_id || '');

let debounceTimer: any;
watch([search, status, clientId], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(route('nutritionist.plans.index'), {
            search: search.value || undefined,
            status: status.value || undefined,
            client_id: clientId.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
});

function statusColor(s: string) {
    const map: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-700',
        active: 'bg-green-50 text-green-700',
        completed: 'bg-blue-50 text-blue-700',
        archived: 'bg-orange-50 text-orange-700',
    };
    return map[s] || 'bg-gray-100 text-gray-700';
}

function statusLabel(s: string) {
    return props.statuses.find(st => st.value === s)?.label || s;
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<template>
    <Head title="Piani Nutrizionali" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Piani Nutrizionali</h1>
                <Link
                    :href="route('nutritionist.plans.templates')"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                >
                    <BookTemplate class="h-4 w-4" />
                    Template
                </Link>
                <Link
                    :href="route('nutritionist.plans.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 px-4 py-2 text-sm font-medium text-white hover:from-primary-600 hover:to-primary-700 transition shadow-sm"
                >
                    <Plus class="h-4 w-4" />
                    Nuovo Piano
                </Link>
            </div>
        </template>

        <!-- Filters -->
        <div class="mb-6 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cerca piano..."
                    class="w-full rounded-lg border-gray-200 pl-10 pr-4 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500"
                />
            </div>
            <select v-model="status" class="rounded-lg border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tutti gli stati</option>
                <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
            <select v-model="clientId" class="rounded-lg border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tutti i clienti</option>
                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.user.name }}</option>
            </select>
        </div>

        <!-- Empty state -->
        <div v-if="plans.data.length === 0" class="rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm">
            <FileText class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun piano trovato</h3>
            <p class="text-sm text-gray-500 mb-6">Crea un piano nutrizionale per uno dei tuoi clienti.</p>
        </div>

        <!-- Plans list -->
        <div v-else class="space-y-3">
            <Link
                v-for="plan in plans.data"
                :key="plan.id"
                :href="route('nutritionist.plans.show', plan.id)"
                class="block rounded-2xl bg-white border border-gray-100 shadow-sm p-5 hover:shadow-md transition"
            >
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-semibold text-gray-900">{{ plan.title }}</h3>
                            <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusColor(plan.status)]">
                                {{ statusLabel(plan.status) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-gray-500">
                            <span class="flex items-center gap-1">
                                <User class="h-3.5 w-3.5" />
                                {{ plan.client?.user?.name || '—' }}
                            </span>
                            <span class="flex items-center gap-1">
                                <Calendar class="h-3.5 w-3.5" />
                                {{ formatDate(plan.start_date) }}
                                <template v-if="plan.end_date"> — {{ formatDate(plan.end_date) }}</template>
                            </span>
                        </div>
                        <p v-if="plan.description" class="text-sm text-gray-400 mt-1 line-clamp-1">{{ plan.description }}</p>
                    </div>
                    <div v-if="plan.daily_calories" class="text-right text-sm ml-4">
                        <span class="font-semibold text-gray-900">{{ plan.daily_calories }}</span>
                        <span class="text-gray-400 block text-xs">kcal/giorno</span>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Pagination -->
        <div v-if="plans.last_page > 1" class="mt-6 flex justify-center gap-1">
            <Link
                v-for="link in plans.links"
                :key="link.label"
                :href="link.url || '#'"
                :class="[
                    'rounded-lg px-3 py-2 text-sm',
                    link.active ? 'bg-primary-500 text-white' : 'text-gray-600 hover:bg-gray-100',
                    !link.url ? 'opacity-50 pointer-events-none' : ''
                ]"
                v-html="link.label"
                preserve-state
            />
        </div>
    </AuthenticatedLayout>
</template>
