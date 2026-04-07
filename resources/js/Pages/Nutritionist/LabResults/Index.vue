<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { FlaskConical, User, Plus } from 'lucide-vue-next';

const props = defineProps<{
    labResults: any;
    filters: { client_id?: string };
    clients: any[];
}>();

const clientId = ref(props.filters.client_id || '');

watch(clientId, () => {
    router.get(route('nutritionist.lab-results.index'), {
        client_id: clientId.value || undefined,
    }, { preserveState: true, replace: true });
});

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
}

function markerSummary(lr: any) {
    const parts: string[] = [];
    if (lr.glucose) parts.push(`Glicemia: ${lr.glucose}`);
    if (lr.total_cholesterol) parts.push(`Col. tot: ${lr.total_cholesterol}`);
    if (lr.triglycerides) parts.push(`Trigl: ${lr.triglycerides}`);
    if (lr.tsh) parts.push(`TSH: ${lr.tsh}`);
    return parts.slice(0, 3).join(' · ') || 'Nessun valore inserito';
}
</script>

<template>
    <Head title="Esami Ematochimici" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Esami Ematochimici</h1>
                <Link :href="route('nutritionist.lab-results.create')" class="inline-flex items-center gap-1.5 rounded-xl bg-primary-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-600 transition">
                    <Plus class="h-4 w-4" /> Nuovo esame
                </Link>
            </div>
        </template>

        <!-- Filter -->
        <div class="mb-6">
            <select v-model="clientId" class="rounded-lg border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500">
                <option value="">Tutti i clienti</option>
                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.user.name }}</option>
            </select>
        </div>

        <!-- Empty state -->
        <div v-if="labResults.data.length === 0" class="rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm">
            <FlaskConical class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun esame registrato</h3>
            <p class="text-sm text-gray-500">Gli esami ematochimici dei tuoi clienti appariranno qui.</p>
        </div>

        <!-- Lab results list -->
        <div v-else class="space-y-3">
            <Link
                v-for="lr in labResults.data"
                :key="lr.id"
                :href="route('nutritionist.lab-results.show', lr.id)"
                class="block rounded-2xl bg-white border border-gray-100 shadow-sm p-5 hover:shadow-md transition"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="flex items-center gap-1 text-sm text-gray-500"><User class="h-3.5 w-3.5" /> {{ lr.client?.user?.name }}</span>
                            <span class="text-sm text-gray-400">&middot;</span>
                            <span class="text-sm font-medium text-gray-900">{{ formatDate(lr.date) }}</span>
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ markerSummary(lr) }}
                        </div>
                    </div>
                    <div v-if="lr.lab_name" class="text-xs text-gray-400">{{ lr.lab_name }}</div>
                </div>
            </Link>
        </div>

        <!-- Pagination -->
        <div v-if="labResults.last_page > 1" class="mt-6 flex justify-center gap-1">
            <Link
                v-for="link in labResults.links"
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
