<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { ClipboardCheck, Scale, User } from 'lucide-vue-next';

const props = defineProps<{
    checkIns: any;
    filters: { client_id?: string };
    clients: any[];
}>();

const clientId = ref(props.filters.client_id || '');

watch(clientId, () => {
    router.get(route('nutritionist.check-ins.index'), {
        client_id: clientId.value || undefined,
    }, { preserveState: true, replace: true });
});

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
}
</script>

<template>
    <Head title="Monitoraggio Clienti" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-900">Monitoraggio Clienti</h1>
        </template>

        <!-- Filter -->
        <div class="mb-6">
            <select v-model="clientId" class="rounded-lg border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tutti i clienti</option>
                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.user.name }}</option>
            </select>
        </div>

        <!-- Empty state -->
        <div v-if="checkIns.data.length === 0" class="rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm">
            <ClipboardCheck class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun monitoraggio</h3>
            <p class="text-sm text-gray-500">I monitoraggi dei tuoi clienti appariranno qui.</p>
        </div>

        <!-- Check-in list -->
        <div v-else class="space-y-3">
            <Link
                v-for="checkIn in checkIns.data"
                :key="checkIn.id"
                :href="route('nutritionist.check-ins.show', checkIn.id)"
                class="block rounded-2xl bg-white border border-gray-100 shadow-sm p-5 hover:shadow-md transition"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="flex items-center gap-1 text-sm text-gray-500"><User class="h-3.5 w-3.5" /> {{ checkIn.client?.user?.name }}</span>
                            <span class="text-sm text-gray-400">·</span>
                            <span class="text-sm font-medium text-gray-900">{{ formatDate(checkIn.date) }}</span>
                        </div>
                        <div class="flex items-center gap-4 text-sm text-gray-500">
                            <span v-if="checkIn.weight_kg" class="flex items-center gap-1"><Scale class="h-3.5 w-3.5" /> {{ checkIn.weight_kg }} kg</span>
                            <span v-if="checkIn.mood">Umore: {{ checkIn.mood }}/5</span>
                            <span v-if="checkIn.measurements?.length" class="text-gray-400">{{ checkIn.measurements.length }} misure</span>
                        </div>
                    </div>
                    <div>
                        <span v-if="!checkIn.nutritionist_notes" class="rounded-full bg-amber-50 text-amber-700 px-2 py-0.5 text-xs font-medium">Da revisionare</span>
                        <span v-else class="rounded-full bg-green-50 text-green-700 px-2 py-0.5 text-xs font-medium">Revisionato</span>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Pagination -->
        <div v-if="checkIns.last_page > 1" class="mt-6 flex justify-center gap-1">
            <Link
                v-for="link in checkIns.links"
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
