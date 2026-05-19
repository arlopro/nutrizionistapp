<script setup lang="ts">
import { ref, computed } from 'vue';

const props = defineProps<{
    checkIns: any[];
}>();

const sessions = computed(() => props.checkIns.filter(c => c.photos?.length > 0));

const beforeId = ref<number | null>(null);
const afterId = ref<number | null>(null);

const beforeSession = computed(() =>
    beforeId.value ? sessions.value.find(s => s.id === beforeId.value) : sessions.value[sessions.value.length - 1] ?? null
);
const afterSession = computed(() =>
    afterId.value ? sessions.value.find(s => s.id === afterId.value) : sessions.value[0] ?? null
);

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: 'numeric' });
}

function photoOfType(session: any, type: string) {
    return session?.photos?.find((p: any) => p.photo_type === type) ?? null;
}

function measurementDelta(type: 'weight_kg' | 'body_fat_percentage' | 'lean_mass_kg', after: any, before: any) {
    const a = after ? Number(after[type]) : null;
    const b = before ? Number(before[type]) : null;
    if (a == null || b == null || isNaN(a) || isNaN(b)) return null;
    return Math.round((a - b) * 10) / 10;
}

function getCircumference(session: any, type: string) {
    return session?.measurements?.find((m: any) => m.measurement_type === type)?.value_cm ?? null;
}
</script>

<template>
    <div class="space-y-5">
        <div v-if="sessions.length < 2" class="text-center py-12 text-sm text-gray-400">
            Servono almeno due sessioni con foto per il confronto.
        </div>

        <template v-else>
            <!-- Selectors -->
            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-xl border-2 border-gray-200 p-4">
                    <p class="text-xs font-medium uppercase text-gray-400 mb-1">Prima</p>
                    <p class="text-lg font-bold text-gray-900">{{ beforeSession ? formatDate(beforeSession.date) : '—' }}</p>
                    <p v-if="beforeSession?.weight_kg" class="text-sm text-gray-500">{{ beforeSession.weight_kg }} kg</p>
                    <select
                        class="mt-2 block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500"
                        v-model="beforeId"
                        @change="(e) => beforeId = parseInt((e.target as HTMLSelectElement).value) || null"
                    >
                        <option :value="null">{{ sessions[sessions.length - 1] ? formatDate(sessions[sessions.length - 1].date) : '—' }}</option>
                        <option v-for="s in sessions" :key="s.id" :value="s.id">{{ formatDate(s.date) }}</option>
                    </select>
                </div>
                <div class="rounded-xl border-2 border-green-200 bg-green-50 p-4">
                    <p class="text-xs font-medium uppercase text-green-600 mb-1">Dopo</p>
                    <p class="text-lg font-bold text-gray-900">{{ afterSession ? formatDate(afterSession.date) : '—' }}</p>
                    <p v-if="afterSession?.weight_kg" class="text-sm text-gray-500">{{ afterSession.weight_kg }} kg</p>
                    <select
                        class="mt-2 block w-full rounded-lg border-green-200 text-sm focus:border-primary-500 focus:ring-primary-500"
                        v-model="afterId"
                        @change="(e) => afterId = parseInt((e.target as HTMLSelectElement).value) || null"
                    >
                        <option :value="null">{{ sessions[0] ? formatDate(sessions[0].date) : '—' }}</option>
                        <option v-for="s in sessions" :key="s.id" :value="s.id">{{ formatDate(s.date) }}</option>
                    </select>
                </div>
            </div>

            <!-- Photo pairs -->
            <div class="grid grid-cols-3 gap-4">
                <template v-for="type in ['front','side','back']" :key="type">
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-center text-gray-500 capitalize">
                            {{ { front: 'Fronte', side: 'Lato', back: 'Retro' }[type] }}
                        </p>
                        <div class="grid grid-cols-2 gap-1.5">
                            <div class="relative rounded-lg overflow-hidden bg-gray-100 aspect-[3/4]">
                                <img v-if="photoOfType(beforeSession, type)" :src="photoOfType(beforeSession, type).url" class="h-full w-full object-cover" />
                                <div v-else class="h-full w-full flex items-center justify-center text-xs text-gray-400">—</div>
                                <div class="absolute top-1 left-1 rounded bg-black/50 px-1 py-0.5 text-[9px] text-white">Prima</div>
                            </div>
                            <div class="relative rounded-lg overflow-hidden bg-gray-100 aspect-[3/4]">
                                <img v-if="photoOfType(afterSession, type)" :src="photoOfType(afterSession, type).url" class="h-full w-full object-cover" />
                                <div v-else class="h-full w-full flex items-center justify-center text-xs text-gray-400">—</div>
                                <div class="absolute top-1 left-1 rounded bg-green-700/80 px-1 py-0.5 text-[9px] text-white">Dopo</div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Delta metrics -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <template v-for="({ label, value, unit, positiveIsGood }) in [
                    { label: 'Peso', value: measurementDelta('weight_kg', afterSession, beforeSession), unit: 'kg', positiveIsGood: false },
                    { label: 'Massa grassa', value: measurementDelta('body_fat_percentage', afterSession, beforeSession), unit: '%', positiveIsGood: false },
                    { label: 'Massa magra', value: measurementDelta('lean_mass_kg', afterSession, beforeSession), unit: 'kg', positiveIsGood: true },
                ]" :key="label">
                    <div v-if="value !== null" :class="[
                        'rounded-xl p-3 text-center border',
                        positiveIsGood
                            ? (value > 0 ? 'bg-green-50 border-green-100' : value < 0 ? 'bg-red-50 border-red-100' : 'bg-gray-50 border-gray-100')
                            : (value < 0 ? 'bg-green-50 border-green-100' : value > 0 ? 'bg-red-50 border-red-100' : 'bg-gray-50 border-gray-100'),
                    ]">
                        <p class="text-xs font-medium text-gray-500">{{ label }}</p>
                        <p :class="[
                            'text-xl font-bold',
                            positiveIsGood
                                ? (value > 0 ? 'text-green-700' : value < 0 ? 'text-red-600' : 'text-gray-600')
                                : (value < 0 ? 'text-green-700' : value > 0 ? 'text-red-600' : 'text-gray-600'),
                        ]">
                            {{ value > 0 ? '+' : '' }}{{ value }} {{ unit }}
                        </p>
                    </div>
                </template>
            </div>
        </template>
    </div>
</template>
