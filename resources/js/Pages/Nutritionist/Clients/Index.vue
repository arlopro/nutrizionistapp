<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Plus, Search, User, Mail, Phone, ChevronRight, Target } from 'lucide-vue-next';

const props = defineProps<{
    clients: any;
    filters: { search?: string; status?: string };
}>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

let debounceTimer: any;
watch([search, status], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(route('nutritionist.clients.index'), {
            search: search.value || undefined,
            status: status.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
});

function statusColor(s: string) {
    return {
        active: 'bg-green-100 text-green-700',
        inactive: 'bg-yellow-100 text-yellow-700',
        archived: 'bg-gray-100 text-gray-600',
    }[s] || 'bg-gray-100 text-gray-600';
}

function statusLabel(s: string) {
    return { active: 'Attivo', inactive: 'Inattivo', archived: 'Archiviato' }[s] || s;
}

function goalLabel(g: string) {
    const map: Record<string, string> = {
        weight_loss: 'Perdita peso',
        weight_gain: 'Aumento peso',
        maintenance: 'Mantenimento',
        muscle_gain: 'Massa muscolare',
        health: 'Salute',
    };
    return map[g] || null;
}

function clientInitials(client: any): string {
    const first = client.user?.name?.charAt(0)?.toUpperCase() || '';
    const last = client.user?.last_name?.charAt(0)?.toUpperCase() || '';
    return first + last || first;
}
</script>

<template>
    <Head title="Clienti" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Clienti</h1>
                <Link
                    :href="route('nutritionist.clients.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 px-4 py-2 text-sm font-medium text-white hover:from-primary-600 hover:to-primary-700 transition shadow-sm"
                >
                    <Plus class="h-4 w-4" />
                    Nuovo Cliente
                </Link>
            </div>
        </template>

        <!-- Flash message -->
        <div v-if="$page.props.flash?.success" class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ $page.props.flash.success }}
        </div>

        <!-- Stats bar -->
        <div v-if="clients.total > 0" class="mb-6 grid grid-cols-3 gap-3">
            <div class="rounded-xl bg-white border border-gray-100 shadow-sm px-4 py-3 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ clients.total }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Totale</p>
            </div>
            <div class="rounded-xl bg-white border border-gray-100 shadow-sm px-4 py-3 text-center">
                <p class="text-2xl font-bold text-green-600">{{ clients.data.filter((c: any) => c.status === 'active').length }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Attivi</p>
            </div>
            <div class="rounded-xl bg-white border border-gray-100 shadow-sm px-4 py-3 text-center">
                <p class="text-2xl font-bold text-gray-400">{{ clients.data.filter((c: any) => c.status === 'archived').length }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Archiviati</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cerca per nome o email..."
                    class="w-full rounded-lg border-gray-200 pl-10 pr-4 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500"
                />
            </div>
            <select
                v-model="status"
                class="rounded-lg border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500"
            >
                <option value="">Tutti gli stati</option>
                <option value="active">Attivi</option>
                <option value="inactive">Inattivi</option>
                <option value="archived">Archiviati</option>
            </select>
        </div>

        <!-- Client list -->
        <div v-if="clients.data.length === 0" class="rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm">
            <User class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun cliente</h3>
            <p class="text-sm text-gray-500 mb-6">Inizia aggiungendo il tuo primo cliente.</p>
            <Link
                :href="route('nutritionist.clients.create')"
                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 px-4 py-2 text-sm font-medium text-white hover:from-primary-600 hover:to-primary-700 transition"
            >
                <Plus class="h-4 w-4" />
                Aggiungi Cliente
            </Link>
        </div>

        <div v-else class="space-y-3">
            <Link
                v-for="client in clients.data"
                :key="client.id"
                :href="route('nutritionist.clients.show', client.id)"
                class="flex items-center gap-4 rounded-2xl bg-white border border-gray-100 p-4 shadow-sm hover:shadow-md hover:border-primary-100 transition group"
            >
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 text-white text-base font-bold flex-shrink-0">
                    {{ clientInitials(client) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-gray-900">{{ client.user?.name }} {{ client.user?.last_name }}</p>
                        <span v-if="client.goal && goalLabel(client.goal)" class="hidden sm:inline-flex items-center gap-1 rounded-full bg-primary-50 px-2 py-0.5 text-xs font-medium text-primary-700">
                            <Target class="h-3 w-3" />
                            {{ goalLabel(client.goal) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="flex items-center gap-1 text-xs text-gray-500">
                            <Mail class="h-3 w-3" />
                            {{ client.user?.email }}
                        </span>
                        <span v-if="client.user?.phone" class="hidden sm:flex items-center gap-1 text-xs text-gray-500">
                            <Phone class="h-3 w-3" />
                            {{ client.user?.phone }}
                        </span>
                    </div>
                </div>
                <span :class="['rounded-full px-2.5 py-1 text-xs font-medium', statusColor(client.status)]">
                    {{ statusLabel(client.status) }}
                </span>
                <ChevronRight class="h-5 w-5 text-gray-300 group-hover:text-primary-500 transition" />
            </Link>
        </div>

        <!-- Pagination -->
        <div v-if="clients.last_page > 1" class="mt-6 flex justify-center gap-1">
            <Link
                v-for="link in clients.links"
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
