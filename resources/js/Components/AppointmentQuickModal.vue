<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { X, Trash2, ExternalLink, MapPin, Clock } from 'lucide-vue-next';

const props = defineProps<{
    appointment: any;
}>();

const emit = defineEmits<{ close: []; deleted: [] }>();

const deleting = ref(false);

const typeLabel: Record<string, string> = {
    first_visit: 'Prima visita', follow_up: 'Controllo mensile',
    online: 'Online', other: 'Altro', blocked: 'Slot occupato',
};
const statusLabel: Record<string, string> = {
    scheduled: 'Programmato', confirmed: 'Confermato', completed: 'Completato',
    cancelled: 'Cancellato', no_show: 'Non presentato',
};
const statusCls: Record<string, string> = {
    scheduled: 'bg-amber-100 text-amber-700',
    confirmed: 'bg-green-100 text-green-700',
    completed: 'bg-gray-100 text-gray-500',
    cancelled: 'bg-red-100 text-red-600',
    no_show: 'bg-red-50 text-red-500',
};
const locationLabel: Record<string, string> = { studio: 'Studio', online: 'Online', home: 'Casa' };

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}
function formatTime(d: string) {
    return new Date(d).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
}

function markCompleted() {
    router.put(route('nutritionist.appointments.update', props.appointment.id), {
        status: 'completed',
    }, {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
}

function deleteAppointment() {
    if (!confirm('Eliminare questo appuntamento?')) return;
    deleting.value = true;
    router.delete(route('nutritionist.appointments.destroy', props.appointment.id), {
        preserveScroll: true,
        onSuccess: () => { deleting.value = false; emit('deleted'); emit('close'); },
        onError: () => { deleting.value = false; },
    });
}
</script>

<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="emit('close')">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
                <!-- Header -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">{{ typeLabel[appointment.type] ?? appointment.type }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(appointment.starts_at) }}</p>
                    </div>
                    <button @click="emit('close')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 transition">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Details -->
                <div class="p-5 space-y-3">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <Clock class="h-4 w-4 text-gray-400 flex-shrink-0" />
                        <span>{{ formatTime(appointment.starts_at) }}<span v-if="appointment.ends_at"> – {{ formatTime(appointment.ends_at) }}</span></span>
                    </div>
                    <div v-if="appointment.location" class="flex items-center gap-2 text-sm text-gray-600">
                        <MapPin class="h-4 w-4 text-gray-400 flex-shrink-0" />
                        <span>{{ locationLabel[appointment.location] ?? appointment.location }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span :class="['rounded-full px-2.5 py-0.5 text-xs font-medium', statusCls[appointment.status] ?? 'bg-gray-100 text-gray-500']">
                            {{ statusLabel[appointment.status] ?? appointment.status }}
                        </span>
                    </div>
                    <div v-if="appointment.notes" class="rounded-xl bg-gray-50 px-3 py-2.5 text-sm text-gray-600">
                        {{ appointment.notes }}
                    </div>
                </div>

                <!-- Actions -->
                <div class="px-5 pb-5 flex flex-col gap-2">
                    <a
                        :href="route('nutritionist.appointments.index')"
                        class="flex items-center justify-center gap-1.5 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition"
                    >
                        <ExternalLink class="h-3.5 w-3.5" /> Vai al calendario
                    </a>
                    <button
                        v-if="!['completed','cancelled'].includes(appointment.status)"
                        @click="markCompleted"
                        class="rounded-xl bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition"
                    >
                        Segna come completato
                    </button>
                    <button
                        @click="deleteAppointment"
                        :disabled="deleting"
                        class="flex items-center justify-center gap-1.5 rounded-xl border border-red-100 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition disabled:opacity-40"
                    >
                        <Trash2 class="h-3.5 w-3.5" /> Elimina appuntamento
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
