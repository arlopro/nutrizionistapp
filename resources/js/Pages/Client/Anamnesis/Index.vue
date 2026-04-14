<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { FileText, CheckCircle, Clock } from 'lucide-vue-next';

defineProps<{
    submissions: any[];
}>();

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
}
</script>

<template>
    <Head title="Questionari" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-900">Questionari</h1>
        </template>

        <div v-if="submissions.length === 0" class="rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm">
            <FileText class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun questionario</h3>
            <p class="text-sm text-gray-500">Il tuo nutrizionista non ha ancora inviato questionari da compilare.</p>
        </div>

        <div v-else class="space-y-3">
            <Link
                v-for="sub in submissions"
                :key="sub.id"
                :href="route('client.anamnesis.show', sub.id)"
                class="block rounded-2xl bg-white border border-gray-100 shadow-sm p-5 hover:shadow-md transition"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <FileText class="h-5 w-5 text-primary-500" />
                            <p class="font-semibold text-gray-900">{{ sub.template?.name || 'Questionario' }}</p>
                        </div>
                        <p v-if="sub.template?.description" class="text-sm text-gray-500 mt-1">{{ sub.template.description }}</p>
                        <p class="text-xs text-gray-400 mt-1">Inviato il {{ formatDate(sub.sent_at) }}</p>
                    </div>
                    <div>
                        <span v-if="sub.status === 'completed'" class="inline-flex items-center gap-1 rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">
                            <CheckCircle class="h-3.5 w-3.5" /> Compilato
                        </span>
                        <span v-else class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                            <Clock class="h-3.5 w-3.5" /> Da compilare
                        </span>
                    </div>
                </div>
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
