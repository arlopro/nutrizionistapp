<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import WeightChartFull from '@/Components/WeightChartFull.vue';
import MacroSummary from '@/Components/MacroSummary.vue';
import CheckInDetailModal from '@/Components/CheckInDetailModal.vue';
import AppointmentQuickModal from '@/Components/AppointmentQuickModal.vue';
import { UtensilsCrossed, Calendar, Image, User, ClipboardCheck, ChevronRight, Plus } from 'lucide-vue-next';

const props = defineProps<{
    client: any;
    activePlan: any;
}>();

const recentCheckIns = computed(() => (props.client.check_ins ?? []).slice(0, 5));

const selectedCheckIn = ref<any>(null);
const selectedAppointment = ref<any>(null);

const latestPhotos = computed(() => {
    const sessionWithPhotos = (props.client.check_ins ?? []).find((c: any) => c.photos?.length > 0);
    return sessionWithPhotos?.photos?.slice(0, 3) ?? [];
});

const upcomingAppointments = computed(() =>
    (props.client.appointments ?? []).filter((a: any) => new Date(a.starts_at) >= new Date() && !['cancelled','completed'].includes(a.status)).slice(0, 3)
);

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: 'numeric' });
}

function formatTime(d: string) {
    return new Date(d).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
}

function formatShortDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { weekday: 'short', day: 'numeric', month: 'short' });
}

function goalLabel(g: string) {
    const map: Record<string, string> = { weight_loss: 'Perdita peso', weight_gain: 'Aumento peso', maintenance: 'Mantenimento', muscle_gain: 'Massa muscolare', health: 'Salute' };
    return map[g] || g || '—';
}

function moodEmoji(m: number) {
    return ['😞', '😕', '😐', '🙂', '😊'][m - 1] ?? '';
}

function appointmentDayNum(d: string) {
    return new Date(d).getDate();
}
function appointmentMonthAbbr(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { month: 'short' }).toUpperCase();
}

const typeLabel: Record<string, string> = {
    first_visit: 'Prima visita', follow_up: 'Controllo', online: 'Online', other: 'Altro',
};
const locationLabel: Record<string, string> = { studio: 'Studio', online: 'Online', home: 'Casa' };
</script>

<template>
    <div class="grid gap-5 lg:grid-cols-5">
        <!-- Left: weight chart + check-ins -->
        <div class="lg:col-span-3 space-y-5">
            <!-- Weight chart -->
            <WeightChartFull
                :check-ins="client.check_ins ?? []"
                :goal-weight="client.goal_weight_kg ?? null"
                :initial-weight="Number(client.initial_weight_kg) || null"
            />

            <!-- Recent check-ins -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <ClipboardCheck class="h-4 w-4 text-gray-400" />
                        <p class="text-sm font-semibold text-gray-900">Monitoraggi recenti</p>
                    </div>
                    <Link :href="route('nutritionist.check-ins.index', { client_id: client.id })" class="text-xs text-primary-600 hover:text-primary-700 font-medium">
                        Tutti →
                    </Link>
                </div>
                <div v-if="!recentCheckIns.length" class="text-center py-6 text-sm text-gray-400">Nessun monitoraggio</div>
                <div v-else class="space-y-1.5">
                    <button
                        v-for="ci in recentCheckIns"
                        :key="ci.id"
                        @click="selectedCheckIn = ci"
                        class="flex w-full items-center justify-between rounded-xl hover:bg-gray-50 px-2 py-2.5 transition group text-left"
                    >
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <ClipboardCheck class="h-4 w-4 text-gray-400" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ formatDate(ci.date) }}</p>
                                <p v-if="ci.notes" class="text-xs text-gray-400 truncate max-w-[180px]">{{ ci.notes }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <span v-if="ci.mood" class="text-xs text-gray-400">{{ moodEmoji(ci.mood) }} {{ ci.mood }}/5</span>
                            <span v-if="ci.weight_kg" class="font-semibold text-gray-900">{{ ci.weight_kg }} kg</span>
                            <ChevronRight class="h-3.5 w-3.5 text-gray-300 group-hover:text-gray-500 transition" />
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Right sidebar -->
        <div class="lg:col-span-2 space-y-5">
            <!-- Active plan -->
            <div v-if="activePlan" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <UtensilsCrossed class="h-4 w-4 text-gray-400" />
                        <p class="text-sm font-semibold text-gray-900">Piano attivo</p>
                    </div>
                    <Link :href="route('nutritionist.plans.show', activePlan.id)" class="text-xs font-medium text-primary-600 hover:text-primary-700">Vedi →</Link>
                </div>
                <p class="text-base font-semibold text-gray-900 mb-0.5">{{ activePlan.title }}</p>
                <div class="flex items-center gap-1.5 mb-3">
                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Attivo</span>
                    <span class="text-xs text-gray-400">Dal {{ formatDate(activePlan.start_date) }}</span>
                </div>
                <MacroSummary
                    :kcal="activePlan.daily_calories"
                    :carbs-grams="Number(activePlan.carbs_grams)"
                    :protein-grams="Number(activePlan.protein_grams)"
                    :fat-grams="Number(activePlan.fat_grams)"
                    :compact="true"
                />
            </div>
            <div v-else class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <div class="flex items-center gap-2 mb-3">
                    <UtensilsCrossed class="h-4 w-4 text-gray-400" />
                    <p class="text-sm font-semibold text-gray-900">Piano attivo</p>
                </div>
                <p class="text-sm text-gray-400 mb-3">Nessun piano attivo</p>
                <Link :href="route('nutritionist.plans.create', { client_id: client.id })" class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-700 transition">
                    <Plus class="h-3.5 w-3.5" /> Crea piano
                </Link>
            </div>

            <!-- Upcoming appointments -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <Calendar class="h-4 w-4 text-gray-400" />
                        <p class="text-sm font-semibold text-gray-900">Prossimi appuntamenti</p>
                    </div>
                    <Link :href="route('nutritionist.appointments.index', { client_id: client.id })" class="text-xs font-medium text-primary-600 hover:text-primary-700">+ Nuovo</Link>
                </div>
                <div v-if="!upcomingAppointments.length" class="text-center py-4 text-sm text-gray-400">Nessun appuntamento</div>
                <div v-else class="space-y-2">
                    <button
                        v-for="apt in upcomingAppointments"
                        :key="apt.id"
                        @click="selectedAppointment = apt"
                        class="flex w-full items-center gap-3 rounded-xl hover:bg-gray-50 p-2 transition group text-left"
                    >
                        <div class="flex-shrink-0 w-10 rounded-lg bg-green-50 text-center py-1">
                            <p class="text-lg font-bold text-green-700 leading-none">{{ appointmentDayNum(apt.starts_at) }}</p>
                            <p class="text-[9px] font-medium text-green-600">{{ appointmentMonthAbbr(apt.starts_at) }}</p>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ typeLabel[apt.type] ?? apt.type }}</p>
                            <p class="text-xs text-gray-400">{{ formatTime(apt.starts_at) }}<span v-if="apt.ends_at"> – {{ formatTime(apt.ends_at) }}</span><span v-if="apt.location"> · {{ locationLabel[apt.location] ?? apt.location }}</span></p>
                        </div>
                        <ChevronRight class="h-3.5 w-3.5 text-gray-300 group-hover:text-gray-500 flex-shrink-0 transition" />
                    </button>
                </div>
            </div>

            <!-- Latest photos -->
            <div v-if="latestPhotos.length > 0" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <Image class="h-4 w-4 text-gray-400" />
                        <p class="text-sm font-semibold text-gray-900">Ultime foto</p>
                    </div>
                    <button
                        @click="$emit('changeTab', 'misure-foto')"
                        class="text-xs font-medium text-primary-600 hover:text-primary-700"
                    >Galleria →</button>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div v-for="photo in latestPhotos" :key="photo.id" class="relative rounded-xl overflow-hidden bg-gray-100 aspect-[3/4]">
                        <img :src="photo.url" class="h-full w-full object-cover" />
                        <div class="absolute bottom-0 left-0 right-0 bg-black/40 text-white text-[9px] text-center py-0.5">
                            {{ ({ front: 'fronte', side: 'lato', back: 'retro', other: 'altro' } as Record<string,string>)[photo.photo_type] }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Anagrafica summary -->
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                <div class="flex items-center gap-2 mb-3">
                    <User class="h-4 w-4 text-gray-400" />
                    <p class="text-sm font-semibold text-gray-900">Dati anagrafici</p>
                </div>
                <dl class="space-y-2 text-sm">
                    <div v-if="client.user?.email" class="flex gap-2">
                        <dt class="text-xs font-medium uppercase text-gray-400 w-20 flex-shrink-0">Email</dt>
                        <dd class="text-gray-700 truncate">{{ client.user.email }}</dd>
                    </div>
                    <div v-if="client.user?.phone" class="flex gap-2">
                        <dt class="text-xs font-medium uppercase text-gray-400 w-20 flex-shrink-0">Telefono</dt>
                        <dd class="text-gray-700">{{ client.user.phone }}</dd>
                    </div>
                    <div v-if="client.date_of_birth" class="flex gap-2">
                        <dt class="text-xs font-medium uppercase text-gray-400 w-20 flex-shrink-0">Nato il</dt>
                        <dd class="text-gray-700">{{ formatDate(client.date_of_birth) }}</dd>
                    </div>
                    <div v-if="client.gender" class="flex gap-2">
                        <dt class="text-xs font-medium uppercase text-gray-400 w-20 flex-shrink-0">Sesso</dt>
                        <dd class="text-gray-700">{{ ({ male: 'Maschio', female: 'Femmina', other: 'Altro' } as Record<string,string>)[client.gender] ?? client.gender }}</dd>
                    </div>
                    <div v-if="client.goal" class="flex gap-2">
                        <dt class="text-xs font-medium uppercase text-gray-400 w-20 flex-shrink-0">Obiettivo</dt>
                        <dd class="text-gray-700">{{ goalLabel(client.goal) }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <CheckInDetailModal
        v-if="selectedCheckIn"
        :check-in="selectedCheckIn"
        @close="selectedCheckIn = null"
    />
    <AppointmentQuickModal
        v-if="selectedAppointment"
        :appointment="selectedAppointment"
        @close="selectedAppointment = null"
        @deleted="selectedAppointment = null"
    />
</template>
