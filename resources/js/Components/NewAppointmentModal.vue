<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, UserPlus, RefreshCw, Video, MoreHorizontal } from 'lucide-vue-next';

const props = defineProps<{
    clientId: number;
    clientName: string;
    locations: string[];
}>();

const emit = defineEmits<{ close: [] }>();

const processing = ref(false);

const typeOptions = [
    { value: 'first_visit', label: 'Prima visita',      icon: UserPlus },
    { value: 'follow_up',   label: 'Controllo mensile', icon: RefreshCw },
    { value: 'online',      label: 'Online',             icon: Video },
    { value: 'other',       label: 'Altro',              icon: MoreHorizontal },
];

const today = new Date();
const form = ref({
    type:            'follow_up',
    date:            today.toISOString().split('T')[0],
    start_time:      '09:00',
    end_time:        '10:00',
    location:        '',
    location_custom: '',
    notes:           '',
});

const timeOptions = computed(() => {
    const opts: { value: string; label: string }[] = [];
    for (let h = 0; h < 24; h++) {
        for (let m = 0; m < 60; m += 5) {
            const val = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
            opts.push({ value: val, label: val });
        }
    }
    return opts;
});

function submit() {
    processing.value = true;
    const [sy, sm, sd] = form.value.date.split('-').map(Number);
    const [sh, smin]   = form.value.start_time.split(':').map(Number);
    const [eh, emin]   = form.value.end_time.split(':').map(Number);
    const startsAt = new Date(sy, sm - 1, sd, sh, smin, 0, 0).toISOString();
    const endsAt   = new Date(sy, sm - 1, sd, eh, emin, 0, 0).toISOString();
    const effectiveLoc = form.value.location === '__custom__' ? form.value.location_custom : form.value.location;

    router.post(route('nutritionist.appointments.store'), {
        client_id: props.clientId,
        type:      form.value.type,
        starts_at: startsAt,
        ends_at:   endsAt,
        location:  effectiveLoc || '',
        notes:     form.value.notes || '',
    }, {
        preserveScroll: true,
        onSuccess: () => emit('close'),
        onFinish:  () => { processing.value = false; },
    });
}
</script>

<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="emit('close')">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Nuovo appuntamento</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ clientName }}</p>
                    </div>
                    <button @click="emit('close')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 transition">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="p-5 space-y-4 overflow-y-auto max-h-[70vh]">
                    <!-- Tipo -->
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-2">Tipo di appuntamento</p>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                v-for="t in typeOptions"
                                :key="t.value"
                                type="button"
                                @click="form.type = t.value"
                                :class="[
                                    'flex items-center gap-2 rounded-xl border-2 px-3 py-2.5 text-sm font-medium transition',
                                    form.type === t.value
                                        ? 'border-primary-500 bg-primary-50 text-primary-800'
                                        : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50',
                                ]"
                            >
                                <component :is="t.icon" :class="['h-4 w-4 flex-shrink-0', form.type === t.value ? 'text-primary-600' : 'text-gray-400']" />
                                {{ t.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Data -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Data</label>
                        <input v-model="form.date" type="date" class="block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500" />
                    </div>

                    <!-- Orari -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Inizio</label>
                            <select v-model="form.start_time" class="block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                                <option v-for="t in timeOptions" :key="t.value" :value="t.value">{{ t.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Fine</label>
                            <select v-model="form.end_time" class="block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                                <option v-for="t in timeOptions" :key="t.value" :value="t.value">{{ t.label }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Luogo -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Luogo <span class="text-gray-400 font-normal">(facoltativo)</span></label>

                        <!-- Online: campo URL -->
                        <input
                            v-if="form.type === 'online'"
                            v-model="form.location"
                            type="url"
                            placeholder="https://meet.google.com/..."
                            class="block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500"
                        />

                        <!-- Altri tipi: select con opzioni salvate -->
                        <template v-else>
                            <select v-model="form.location" class="block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="">Nessun luogo</option>
                                <option v-for="loc in locations" :key="loc" :value="loc">{{ loc }}</option>
                                <option value="__custom__">Nuovo luogo...</option>
                            </select>
                            <input
                                v-if="form.location === '__custom__'"
                                v-model="form.location_custom"
                                type="text"
                                placeholder="Inserisci il luogo..."
                                class="mt-2 block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </template>
                    </div>

                    <!-- Note -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Note <span class="text-gray-400 font-normal">(facoltativo)</span></label>
                        <textarea v-model="form.notes" rows="2" placeholder="Note sull'appuntamento..." class="block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500 resize-none"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 p-5 border-t border-gray-100">
                    <button @click="emit('close')" class="rounded-lg px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 transition">Annulla</button>
                    <button
                        @click="submit"
                        :disabled="processing || !form.date || !form.start_time || !form.end_time"
                        class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 transition disabled:opacity-50"
                    >
                        {{ processing ? 'Creazione...' : 'Crea appuntamento' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
