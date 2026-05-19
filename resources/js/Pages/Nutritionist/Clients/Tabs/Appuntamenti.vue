<script setup lang="ts">
import { computed, ref } from 'vue';
import MonthCalendar from '@/Components/MonthCalendar.vue';
import ConversationPreview from '@/Components/ConversationPreview.vue';
import AppointmentQuickModal from '@/Components/AppointmentQuickModal.vue';
import { Calendar, ChevronRight, CheckCircle } from 'lucide-vue-next';

const props = defineProps<{
    client: any;
    recentMessages: any[];
    nutritionistId: number;
}>();

const emit = defineEmits<{ newAppointment: [] }>();

const selectedAppointment = ref<any>(null);

const upcoming = computed(() =>
    (props.client.appointments ?? [])
        .filter((a: any) => new Date(a.starts_at) >= new Date() && !['cancelled','completed'].includes(a.status))
        .sort((a: any, b: any) => new Date(a.starts_at).getTime() - new Date(b.starts_at).getTime())
);

const past = computed(() =>
    (props.client.appointments ?? [])
        .filter((a: any) => new Date(a.starts_at) < new Date() || ['completed'].includes(a.status))
        .sort((a: any, b: any) => new Date(b.starts_at).getTime() - new Date(a.starts_at).getTime())
        .slice(0, 10)
);

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: 'numeric' });
}
function formatTime(d: string) {
    return new Date(d).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
}
function dayNum(d: string) { return new Date(d).getDate(); }
function monthAbbr(d: string) { return new Date(d).toLocaleDateString('it-IT', { month: 'short' }).toUpperCase(); }

const typeLabel: Record<string, string> = {
    first_visit: 'Prima visita', follow_up: 'Controllo mensile', online: 'Follow-up piano', other: 'Altro',
};
const locationLabel: Record<string, string> = { studio: 'Studio', online: 'Online', home: 'Casa' };
const statusLabel: Record<string, string> = {
    scheduled: 'Programmato', confirmed: 'Confermato', completed: 'Completato', cancelled: 'Cancellato', no_show: 'Non presentato',
};
const statusCls: Record<string, string> = {
    scheduled: 'bg-amber-100 text-amber-700',
    confirmed: 'bg-green-100 text-green-700',
    completed: 'bg-gray-100 text-gray-500',
    cancelled: 'bg-red-100 text-red-600',
    no_show: 'bg-red-50 text-red-500',
};
</script>

<template>
    <div class="grid gap-5 lg:grid-cols-5">
        <!-- Left: upcoming + past -->
        <div class="lg:col-span-3 space-y-5">
            <!-- Upcoming -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <Calendar class="h-4 w-4 text-gray-400" /> Prossimi appuntamenti
                    </p>
                    <button
                        @click="emit('newAppointment')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-700 transition"
                    >
                        + Nuovo appuntamento
                    </button>
                </div>

                <div v-if="!upcoming.length" class="text-center py-8 text-sm text-gray-400">
                    Nessun appuntamento programmato
                </div>
                <div v-else class="space-y-2">
                    <button
                        v-for="apt in upcoming"
                        :key="apt.id"
                        @click="selectedAppointment = apt"
                        class="flex w-full items-center gap-3 rounded-xl border border-gray-100 p-3 hover:bg-gray-50 transition text-left"
                    >
                        <div class="flex-shrink-0 w-12 rounded-xl bg-green-50 text-center py-1.5">
                            <p class="text-xl font-bold text-green-700 leading-none">{{ dayNum(apt.starts_at) }}</p>
                            <p class="text-[9px] font-medium text-green-600">{{ monthAbbr(apt.starts_at) }}</p>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ typeLabel[apt.type] ?? apt.type }}</p>
                            <p class="text-xs text-gray-400">
                                {{ formatTime(apt.starts_at) }}<span v-if="apt.ends_at"> – {{ formatTime(apt.ends_at) }}</span>
                                <span v-if="apt.location"> · {{ locationLabel[apt.location] ?? apt.location }}</span>
                            </p>
                        </div>
                        <ChevronRight class="h-3.5 w-3.5 text-gray-300 flex-shrink-0" />
                    </button>
                </div>
            </div>

            <!-- Past appointments -->
            <div v-if="past.length > 0" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <CheckCircle class="h-4 w-4 text-gray-400" /> Storico
                </p>
                <div class="space-y-2">
                    <div v-for="apt in past" :key="apt.id" class="flex items-center gap-3 rounded-xl hover:bg-gray-50 px-2 py-2.5 transition">
                        <div class="text-xs text-gray-400 w-20 flex-shrink-0">{{ formatDate(apt.starts_at) }}</div>
                        <div class="text-xs text-gray-400 w-10 flex-shrink-0">{{ formatTime(apt.starts_at) }}</div>
                        <div class="flex-1 text-sm text-gray-700">{{ typeLabel[apt.type] ?? apt.type }}</div>
                        <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusCls[apt.status] ?? 'bg-gray-100 text-gray-500']">
                            {{ statusLabel[apt.status] ?? apt.status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: calendar + conversation -->
        <div class="lg:col-span-2 space-y-5">
            <MonthCalendar :appointments="client.appointments ?? []" />

            <ConversationPreview
                :messages="recentMessages"
                :client-id="client.id"
                :nutritionist-id="nutritionistId"
            />
        </div>
    </div>

    <AppointmentQuickModal
        v-if="selectedAppointment"
        :appointment="selectedAppointment"
        @close="selectedAppointment = null"
        @deleted="selectedAppointment = null"
    />
</template>
