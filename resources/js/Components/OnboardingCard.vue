<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import {
    CheckCircle2, Circle, Settings, UserPlus, ChevronDown, ChevronUp,
    Sparkles, X, Phone, MessageSquare, Clock,
} from 'lucide-vue-next';

interface Step {
    key: string;
    label: string;
    desc: string;
    done: boolean;
    action: 'settings' | 'clients' | 'inline';
}

interface OnboardingData {
    steps: Step[];
    current: {
        client_tone: string | null;
        session_durations: Record<string, number>;
        phone: string | null;
    };
}

const props = defineProps<{ onboarding: OnboardingData }>();

const doneCount   = computed(() => props.onboarding.steps.filter(s => s.done).length);
const totalCount  = computed(() => props.onboarding.steps.length);
const progressPct = computed(() => Math.round((doneCount.value / totalCount.value) * 100));
const allDone     = computed(() => doneCount.value === totalCount.value);

// Quale step inline è aperto
const openInlineKey = ref<string | null>(null);

function toggleInline(key: string) {
    openInlineKey.value = openInlineKey.value === key ? null : key;
}

// Form per i campi inline
const inlineForm = useForm({
    client_tone:       props.onboarding.current.client_tone ?? '',
    session_durations: { ...props.onboarding.current.session_durations } as Record<string, number | ''>,
    phone:             props.onboarding.current.phone ?? '',
});

function saveInline(key: string) {
    const payload: Record<string, any> = {};
    if (key === 'client_tone')       payload.client_tone = inlineForm.client_tone || null;
    if (key === 'session_durations') payload.session_durations = inlineForm.session_durations;
    if (key === 'phone')             payload.phone = inlineForm.phone || null;

    router.patch(route('nutritionist.onboarding.update'), payload, {
        preserveScroll: true,
        onSuccess: () => { openInlineKey.value = null; },
    });
}

const appointmentTypes = [
    { value: 'first_visit', label: 'Prima visita', default: 60 },
    { value: 'follow_up',   label: 'Controllo',    default: 45 },
    { value: 'online',      label: 'Online',       default: 45 },
    { value: 'other',       label: 'Altro',        default: 60 },
];

const durationOptions = [15, 20, 30, 45, 60, 75, 90, 120];

function durationLabel(min: number): string {
    if (min < 60) return `${min} min`;
    const h = Math.floor(min / 60);
    const m = min % 60;
    return m === 0 ? `${h}h` : `${h}h ${m}min`;
}

const sessionDurationsComplete = computed(() =>
    appointmentTypes.every(t => !!inlineForm.session_durations[t.value])
);

function dismiss() {
    router.post(route('nutritionist.onboarding.dismiss'), {}, { preserveScroll: true });
}

const toneOptions = [
    { value: 'formal',   label: 'Formale',   desc: '"Lei" — professionale e distaccato' },
    { value: 'informal', label: 'Informale',  desc: '"Tu" — diretto e moderno' },
    { value: 'friendly', label: 'Amichevole', desc: '"Tu" con tono caldo e personale' },
];


function stepIcon(step: Step) {
    if (step.key === 'phone')              return Phone;
    if (step.key === 'client_tone')        return MessageSquare;
    if (step.key === 'session_durations')  return Clock;
    if (step.key === 'first_client')       return UserPlus;
    return Settings;
}
</script>

<template>
    <div class="rounded-2xl border border-primary-100 bg-gradient-to-br from-primary-50 via-white to-emerald-50 shadow-sm mb-8 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-primary-100/60">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-primary-500 p-2.5 shadow-sm">
                        <Sparkles class="h-5 w-5 text-white" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">
                            {{ allDone ? 'Configurazione completata!' : 'Configura il tuo studio' }}
                        </h2>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ allDone
                                ? 'Ottimo lavoro! Hai completato tutti i passi iniziali.'
                                : `${doneCount} di ${totalCount} passi completati — ci vorranno solo pochi minuti` }}
                        </p>
                    </div>
                </div>

                <!-- Progress + dismiss -->
                <div class="flex items-center gap-3 flex-shrink-0">
                    <div class="text-right hidden sm:block">
                        <span class="text-2xl font-bold text-primary-600">{{ progressPct }}%</span>
                    </div>
                    <button
                        @click="dismiss"
                        class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 hover:bg-white/60 transition"
                        title="Chiudi"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <!-- Progress bar -->
            <div class="mt-4 h-2 rounded-full bg-primary-100 overflow-hidden">
                <div
                    class="h-full rounded-full bg-gradient-to-r from-primary-400 to-emerald-500 transition-all duration-700"
                    :style="{ width: progressPct + '%' }"
                ></div>
            </div>
        </div>

        <!-- Steps -->
        <div class="divide-y divide-gray-100/70">
            <div v-for="step in onboarding.steps" :key="step.key">
                <!-- Step row -->
                <div
                    :class="[
                        'flex items-center gap-4 px-6 py-4 transition-colors',
                        step.done ? 'opacity-60' : 'hover:bg-white/50',
                        step.action === 'inline' && !step.done ? 'cursor-pointer' : '',
                    ]"
                    @click="step.action === 'inline' && !step.done ? toggleInline(step.key) : null"
                >
                    <!-- Icona stato -->
                    <div class="flex-shrink-0">
                        <CheckCircle2 v-if="step.done" class="h-6 w-6 text-emerald-500" />
                        <Circle v-else class="h-6 w-6 text-gray-300" />
                    </div>

                    <!-- Icona tipo -->
                    <div :class="['rounded-lg p-2 flex-shrink-0', step.done ? 'bg-gray-100' : 'bg-white shadow-sm border border-gray-100']">
                        <component :is="stepIcon(step)" :class="['h-4 w-4', step.done ? 'text-gray-400' : 'text-primary-500']" />
                    </div>

                    <!-- Testo -->
                    <div class="flex-1 min-w-0">
                        <p :class="['text-sm font-medium', step.done ? 'text-gray-500 line-through' : 'text-gray-900']">
                            {{ step.label }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">{{ step.desc }}</p>
                    </div>

                    <!-- CTA -->
                    <div class="flex-shrink-0">
                        <!-- Link a impostazioni -->
                        <Link
                            v-if="step.action === 'settings' && !step.done"
                            :href="route('nutritionist.settings')"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-600 transition shadow-sm"
                            @click.stop
                        >
                            Vai alle impostazioni
                        </Link>

                        <!-- Link crea cliente -->
                        <Link
                            v-else-if="step.action === 'clients' && !step.done"
                            :href="route('nutritionist.clients.create')"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-600 transition shadow-sm"
                            @click.stop
                        >
                            Aggiungi cliente
                        </Link>

                        <!-- Toggle inline -->
                        <button
                            v-else-if="step.action === 'inline' && !step.done"
                            class="inline-flex items-center gap-1 text-xs text-primary-600 font-medium hover:text-primary-800 transition"
                        >
                            {{ openInlineKey === step.key ? 'Chiudi' : 'Configura' }}
                            <ChevronUp v-if="openInlineKey === step.key" class="h-3.5 w-3.5" />
                            <ChevronDown v-else class="h-3.5 w-3.5" />
                        </button>

                        <span v-else-if="step.done" class="text-xs text-emerald-600 font-medium">Completato</span>
                    </div>
                </div>

                <!-- Inline: Telefono -->
                <Transition name="slide-down">
                    <div v-if="step.key === 'phone' && openInlineKey === 'phone'" class="px-6 pb-5 pt-1">
                        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Numero di telefono</label>
                            <div class="flex gap-2">
                                <input
                                    v-model="inlineForm.phone"
                                    type="tel"
                                    placeholder="+39 333 1234567"
                                    class="flex-1 rounded-lg border-gray-200 text-sm focus:border-primary-400 focus:ring-primary-400"
                                    @keydown.enter="saveInline('phone')"
                                />
                                <button
                                    @click="saveInline('phone')"
                                    :disabled="!inlineForm.phone"
                                    class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-600 transition disabled:opacity-40"
                                >
                                    Salva
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- Inline: Tono comunicazione -->
                <Transition name="slide-down">
                    <div v-if="step.key === 'client_tone' && openInlineKey === 'client_tone'" class="px-6 pb-5 pt-1">
                        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4">
                            <p class="text-sm text-gray-600 mb-3">Come preferisci rivolgerti ai tuoi clienti nelle email e comunicazioni?</p>
                            <div class="grid gap-2 sm:grid-cols-3 mb-4">
                                <button
                                    v-for="opt in toneOptions"
                                    :key="opt.value"
                                    type="button"
                                    @click="inlineForm.client_tone = opt.value"
                                    :class="[
                                        'rounded-xl border-2 px-4 py-3 text-left transition',
                                        inlineForm.client_tone === opt.value
                                            ? 'border-primary-500 bg-primary-50'
                                            : 'border-gray-100 hover:border-gray-200 bg-white'
                                    ]"
                                >
                                    <p class="text-sm font-semibold text-gray-900">{{ opt.label }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ opt.desc }}</p>
                                </button>
                            </div>
                            <button
                                @click="saveInline('client_tone')"
                                :disabled="!inlineForm.client_tone"
                                class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-600 transition disabled:opacity-40"
                            >
                                Salva preferenza
                            </button>
                        </div>
                    </div>
                </Transition>

                <!-- Inline: Durata per tipo appuntamento -->
                <Transition name="slide-down">
                    <div v-if="step.key === 'session_durations' && openInlineKey === 'session_durations'" class="px-6 pb-5 pt-1">
                        <div class="rounded-xl bg-white border border-gray-100 shadow-sm p-4">
                            <p class="text-sm text-gray-600 mb-4">Imposta la durata tipica per ogni tipo di appuntamento. Verrà usata per pre-compilare l'orario di fine.</p>
                            <div class="space-y-4 mb-4">
                                <div v-for="apt in appointmentTypes" :key="apt.value">
                                    <p class="text-xs font-semibold text-gray-700 mb-1.5">{{ apt.label }}</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        <button
                                            v-for="min in durationOptions"
                                            :key="min"
                                            type="button"
                                            @click="inlineForm.session_durations[apt.value] = min"
                                            :class="[
                                                'rounded-lg border px-3 py-1.5 text-xs font-medium transition',
                                                Number(inlineForm.session_durations[apt.value]) === min
                                                    ? 'border-primary-500 bg-primary-50 text-primary-700'
                                                    : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'
                                            ]"
                                        >
                                            {{ durationLabel(min) }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button
                                @click="saveInline('session_durations')"
                                :disabled="!sessionDurationsComplete"
                                class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-600 transition disabled:opacity-40"
                            >
                                Salva durate
                            </button>
                            <p v-if="!sessionDurationsComplete" class="text-xs text-gray-400 mt-2">Imposta la durata per ogni tipo prima di salvare.</p>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100/60 flex items-center justify-between">
            <p class="text-xs text-gray-400">
                Puoi completare questi passaggi anche in seguito dalle impostazioni.
            </p>
            <button
                @click="dismiss"
                class="text-xs text-gray-400 hover:text-gray-600 transition underline underline-offset-2"
            >
                Salta per ora
            </button>
        </div>
    </div>
</template>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.2s ease;
    overflow: hidden;
}
.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    max-height: 0;
    padding-top: 0;
    padding-bottom: 0;
}
.slide-down-enter-to,
.slide-down-leave-from {
    opacity: 1;
    max-height: 400px;
}
</style>
