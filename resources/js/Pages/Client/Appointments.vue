<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, Clock, MapPin, User } from 'lucide-vue-next';

defineProps<{
    appointments: any;
}>();

function formatDateTime(d: string) {
    return new Date(d).toLocaleString('it-IT', { weekday: 'long', day: '2-digit', month: 'long', hour: '2-digit', minute: '2-digit' });
}

function formatTime(d: string) {
    return new Date(d).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
}

function statusColor(s: string) {
    const map: Record<string, string> = {
        scheduled: 'bg-blue-50 text-blue-700',
        confirmed: 'bg-green-50 text-green-700',
        completed: 'bg-gray-100 text-gray-700',
        cancelled: 'bg-red-50 text-red-700',
        no_show: 'bg-orange-50 text-orange-700',
    };
    return map[s] || 'bg-gray-100 text-gray-700';
}

function statusLabel(s: string) {
    const labels: Record<string, string> = {
        scheduled: 'Programmato',
        confirmed: 'Confermato',
        completed: 'Completato',
        cancelled: 'Cancellato',
        no_show: 'Non presentato',
    };
    return labels[s] || s;
}

function isPast(d: string) {
    return new Date(d) < new Date();
}
</script>

<template>
    <Head title="I miei Appuntamenti" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-900">I miei Appuntamenti</h1>
        </template>

        <!-- Empty state -->
        <div v-if="appointments.data.length === 0" class="rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm">
            <Calendar class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun appuntamento</h3>
            <p class="text-sm text-gray-500">Non hai ancora appuntamenti programmati.</p>
        </div>

        <!-- Appointments list -->
        <div v-else class="space-y-3">
            <div
                v-for="apt in appointments.data"
                :key="apt.id"
                :class="[
                    'rounded-2xl bg-white border shadow-sm p-5',
                    isPast(apt.starts_at) && apt.status !== 'completed' ? 'border-gray-200 opacity-75' : 'border-gray-100'
                ]"
            >
                <div class="flex items-center gap-2 mb-2">
                    <h3 class="font-semibold text-gray-900">{{ apt.title }}</h3>
                    <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusColor(apt.status)]">
                        {{ statusLabel(apt.status) }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                    <span class="flex items-center gap-1"><Calendar class="h-3.5 w-3.5" /> {{ formatDateTime(apt.starts_at) }}</span>
                    <span class="flex items-center gap-1"><Clock class="h-3.5 w-3.5" /> {{ formatTime(apt.starts_at) }} - {{ formatTime(apt.ends_at) }}</span>
                    <span v-if="apt.location" class="flex items-center gap-1"><MapPin class="h-3.5 w-3.5" /> {{ apt.location }}</span>
                </div>
                <p v-if="apt.description" class="text-sm text-gray-400 mt-2">{{ apt.description }}</p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="appointments.last_page > 1" class="mt-6 flex justify-center gap-1">
            <Link
                v-for="link in appointments.links"
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
