<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Plus, ClipboardCheck, Scale, Smile, Frown, Meh, SmilePlus, Annoyed } from 'lucide-vue-next';

defineProps<{
    checkIns: any;
}>();

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
}

function moodIcon(mood: number) {
    if (mood <= 1) return '😞';
    if (mood <= 2) return '😕';
    if (mood <= 3) return '😐';
    if (mood <= 4) return '🙂';
    return '😄';
}
</script>

<template>
    <Head title="Il mio Monitoraggio" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Il mio Monitoraggio</h1>
                <Link
                    :href="route('client.check-ins.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 px-4 py-2 text-sm font-medium text-white hover:from-primary-600 hover:to-primary-700 transition shadow-sm"
                >
                    <Plus class="h-4 w-4" />
                    Nuovo monitoraggio
                </Link>
            </div>
        </template>

        <!-- Empty state -->
        <div v-if="checkIns.data.length === 0" class="rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm">
            <ClipboardCheck class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun monitoraggio</h3>
            <p class="text-sm text-gray-500 mb-6">Inserisci il tuo primo monitoraggio settimanale per tracciare i tuoi progressi.</p>
            <Link
                :href="route('client.check-ins.create')"
                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 px-4 py-2 text-sm font-medium text-white hover:from-primary-600 hover:to-primary-700 transition shadow-sm"
            >
                <Plus class="h-4 w-4" />
                Primo monitoraggio
            </Link>
        </div>

        <!-- Check-in list -->
        <div v-else class="space-y-3">
            <Link
                v-for="checkIn in checkIns.data"
                :key="checkIn.id"
                :href="route('client.check-ins.show', checkIn.id)"
                class="block rounded-2xl bg-white border border-gray-100 shadow-sm p-5 hover:shadow-md transition"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-900">{{ formatDate(checkIn.date) }}</p>
                        <div class="flex items-center gap-4 mt-1 text-sm text-gray-500">
                            <span v-if="checkIn.weight_kg" class="flex items-center gap-1">
                                <Scale class="h-3.5 w-3.5" /> {{ checkIn.weight_kg }} kg
                            </span>
                            <span v-if="checkIn.mood">
                                Umore: {{ moodIcon(checkIn.mood) }}
                            </span>
                            <span v-if="checkIn.water_liters">
                                Acqua: {{ checkIn.water_liters }}L
                            </span>
                            <span v-if="checkIn.measurements?.length" class="text-gray-400">
                                {{ checkIn.measurements.length }} misure
                            </span>
                        </div>
                        <p v-if="checkIn.notes" class="text-sm text-gray-400 mt-1 line-clamp-1">{{ checkIn.notes }}</p>
                    </div>
                    <div v-if="checkIn.nutritionist_notes" class="ml-4">
                        <span class="rounded-full bg-primary-50 text-primary-700 px-2 py-0.5 text-xs font-medium">Feedback</span>
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
