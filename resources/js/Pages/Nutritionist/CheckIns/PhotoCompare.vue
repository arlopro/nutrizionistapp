<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { ArrowLeft, User, Calendar, Scale, ImageIcon } from 'lucide-vue-next';

const props = defineProps<{
    client: any;
    checkIns: any[];
}>();

const leftCheckInId = ref<number | null>(props.checkIns.length >= 2 ? props.checkIns[props.checkIns.length - 1].id : null);
const rightCheckInId = ref<number | null>(props.checkIns.length >= 1 ? props.checkIns[0].id : null);

const leftCheckIn = computed(() => props.checkIns.find(c => c.id === leftCheckInId.value));
const rightCheckIn = computed(() => props.checkIns.find(c => c.id === rightCheckInId.value));

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'short', year: 'numeric' });
}

function photoTypeLabel(t: string) {
    const map: Record<string, string> = { front: 'Frontale', side: 'Laterale', back: 'Posteriore', other: 'Altro' };
    return map[t] || t;
}

// Group photos by type for side-by-side matching
const photoTypes = computed(() => {
    const types = new Set<string>();
    leftCheckIn.value?.photos?.forEach((p: any) => types.add(p.photo_type));
    rightCheckIn.value?.photos?.forEach((p: any) => types.add(p.photo_type));
    return Array.from(types);
});

function getPhoto(checkIn: any, type: string) {
    return checkIn?.photos?.find((p: any) => p.photo_type === type);
}
</script>

<template>
    <Head :title="`Confronto foto — ${client.user?.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('nutritionist.check-ins.index', { client_id: client.id })" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Confronto foto</h1>
                    <p class="text-sm text-gray-500 flex items-center gap-1"><User class="h-3.5 w-3.5" /> {{ client.user?.name }}</p>
                </div>
            </div>
        </template>

        <div v-if="checkIns.length < 2" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-12 text-center">
            <ImageIcon class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">Foto insufficienti</h3>
            <p class="text-sm text-gray-500">Servono almeno 2 check-in con foto per un confronto.</p>
        </div>

        <template v-else>
            <!-- Date selectors -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-4">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Prima (inizio)</label>
                    <select v-model="leftCheckInId" class="block w-full rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option v-for="ci in checkIns" :key="ci.id" :value="ci.id">
                            {{ formatDate(ci.date) }}{{ ci.weight_kg ? ` — ${ci.weight_kg} kg` : '' }} ({{ ci.photos.length }} foto)
                        </option>
                    </select>
                    <div v-if="leftCheckIn" class="mt-2 flex items-center gap-2 text-xs text-gray-400">
                        <Calendar class="h-3 w-3" /> {{ formatDate(leftCheckIn.date) }}
                        <Scale v-if="leftCheckIn.weight_kg" class="h-3 w-3 ml-2" /> <span v-if="leftCheckIn.weight_kg">{{ leftCheckIn.weight_kg }} kg</span>
                    </div>
                </div>
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-4">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Dopo (attuale)</label>
                    <select v-model="rightCheckInId" class="block w-full rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                        <option v-for="ci in checkIns" :key="ci.id" :value="ci.id">
                            {{ formatDate(ci.date) }}{{ ci.weight_kg ? ` — ${ci.weight_kg} kg` : '' }} ({{ ci.photos.length }} foto)
                        </option>
                    </select>
                    <div v-if="rightCheckIn" class="mt-2 flex items-center gap-2 text-xs text-gray-400">
                        <Calendar class="h-3 w-3" /> {{ formatDate(rightCheckIn.date) }}
                        <Scale v-if="rightCheckIn.weight_kg" class="h-3 w-3 ml-2" /> <span v-if="rightCheckIn.weight_kg">{{ rightCheckIn.weight_kg }} kg</span>
                    </div>
                </div>
            </div>

            <!-- Weight delta -->
            <div v-if="leftCheckIn?.weight_kg && rightCheckIn?.weight_kg" class="rounded-xl bg-gradient-to-r from-primary-50 to-green-50 p-3 mb-6 flex items-center justify-center gap-4">
                <span class="text-sm text-gray-700">
                    {{ leftCheckIn.weight_kg }} kg
                    <span class="mx-2">&rarr;</span>
                    {{ rightCheckIn.weight_kg }} kg
                </span>
                <span :class="[
                    'text-sm font-bold',
                    Number(rightCheckIn.weight_kg) - Number(leftCheckIn.weight_kg) < 0 ? 'text-green-600' : Number(rightCheckIn.weight_kg) - Number(leftCheckIn.weight_kg) > 0 ? 'text-red-500' : 'text-gray-500'
                ]">
                    {{ (Number(rightCheckIn.weight_kg) - Number(leftCheckIn.weight_kg) > 0 ? '+' : '') }}{{ (Number(rightCheckIn.weight_kg) - Number(leftCheckIn.weight_kg)).toFixed(1) }} kg
                </span>
            </div>

            <!-- Photo comparison by type -->
            <div v-if="photoTypes.length > 0" class="space-y-6">
                <div v-for="type in photoTypes" :key="type" class="rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-100 px-5 py-2">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ photoTypeLabel(type) }}</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-0.5 bg-gray-100">
                        <div class="bg-white p-2">
                            <div v-if="leftCheckIn" class="text-center">
                                <img
                                    v-if="getPhoto(leftCheckIn, type)"
                                    :src="`/storage/${getPhoto(leftCheckIn, type).file_path}`"
                                    class="w-full max-h-[500px] object-contain rounded-lg"
                                />
                                <div v-else class="py-20 text-sm text-gray-300">
                                    <ImageIcon class="h-8 w-8 mx-auto mb-2" />
                                    Nessuna foto
                                </div>
                                <p class="text-xs text-gray-400 mt-2">{{ leftCheckIn ? formatDate(leftCheckIn.date) : '' }}</p>
                            </div>
                        </div>
                        <div class="bg-white p-2">
                            <div v-if="rightCheckIn" class="text-center">
                                <img
                                    v-if="getPhoto(rightCheckIn, type)"
                                    :src="`/storage/${getPhoto(rightCheckIn, type).file_path}`"
                                    class="w-full max-h-[500px] object-contain rounded-lg"
                                />
                                <div v-else class="py-20 text-sm text-gray-300">
                                    <ImageIcon class="h-8 w-8 mx-auto mb-2" />
                                    Nessuna foto
                                </div>
                                <p class="text-xs text-gray-400 mt-2">{{ rightCheckIn ? formatDate(rightCheckIn.date) : '' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="rounded-2xl bg-white border border-gray-100 shadow-sm p-12 text-center">
                <p class="text-sm text-gray-400">Seleziona due check-in con foto per visualizzare il confronto.</p>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
