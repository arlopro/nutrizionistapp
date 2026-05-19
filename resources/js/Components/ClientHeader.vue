<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Edit, Plus, MoreHorizontal, Send, ArchiveRestore, Archive, CalendarPlus, MessageCircle } from 'lucide-vue-next';

const props = defineProps<{
    client: any;
}>();

const emit = defineEmits<{
    newMeasurement:  [];
    newAppointment: [];
}>();

const archiving = ref(false);
function toggleArchive() {
    archiving.value = true;
    const newStatus = props.client.status === 'archived' ? 'active' : 'archived';
    router.put(route('nutritionist.clients.update', props.client.id), {
        ...props.client,
        name: props.client.user?.name,
        last_name: props.client.user?.last_name,
        email: props.client.user?.email,
        status: newStatus,
    }, {
        preserveScroll: true,
        onFinish: () => { archiving.value = false; },
    });
}

const showMenu = ref(false);
const sendingInvitation = ref(false);
function sendInvitation() {
    sendingInvitation.value = true;
    showMenu.value = false;
    router.post(route('nutritionist.clients.send-invitation', props.client.id), {}, {
        preserveScroll: true,
        onFinish: () => { sendingInvitation.value = false; },
    });
}

const initials = computed(() => {
    const name = props.client.user?.name ?? '';
    const last = props.client.user?.last_name ?? '';
    return ((name[0] ?? '') + (last[0] ?? '')).toUpperCase() || '?';
});

const statusMap: Record<string, { label: string; cls: string }> = {
    active: { label: 'Attivo', cls: 'bg-green-100 text-green-700 border-green-200' },
    inactive: { label: 'Inattivo', cls: 'bg-yellow-100 text-yellow-700 border-yellow-200' },
    archived: { label: 'Archiviato', cls: 'bg-gray-100 text-gray-600 border-gray-200' },
};
const statusInfo = computed(() => statusMap[props.client.status] ?? statusMap.inactive);

function formatDate(d: string | null) {
    if (!d) return null;
    return new Date(d).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: 'numeric' });
}

const dietLabel = computed(() => props.client.dietary_preferences || null);
const activityLabels: Record<string, string> = {
    sedentary: 'Sedentario', light: 'Leggero', moderate: 'Moderato', active: 'Attivo', very_active: 'Molto attivo',
};
const genderLabels: Record<string, string> = { male: 'Maschio', female: 'Femmina', other: 'Altro' };

const age = computed(() => {
    if (!props.client.date_of_birth) return null;
    const dob = new Date(props.client.date_of_birth);
    const diff = Date.now() - dob.getTime();
    return Math.floor(diff / (365.25 * 24 * 3600 * 1000));
});
</script>

<template>
    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
        <div class="flex items-start gap-5">
            <!-- Avatar -->
            <div class="flex-shrink-0">
                <div v-if="client.user?.avatar" class="h-20 w-20 rounded-full overflow-hidden">
                    <img :src="`/storage/${client.user.avatar}`" class="h-full w-full object-cover" :alt="initials" />
                </div>
                <div v-else class="h-20 w-20 rounded-full bg-green-100 flex items-center justify-center text-2xl font-bold text-green-700">
                    {{ initials }}
                </div>
            </div>

            <!-- Main info -->
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <h1 class="text-2xl font-bold text-gray-900">{{ client.user?.name }} {{ client.user?.last_name }}</h1>
                    <span :class="['inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium', statusInfo.cls]">
                        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
                        {{ statusInfo.label }}
                    </span>
                    <span v-if="client.created_at" class="text-sm text-gray-400">
                        cliente dal {{ formatDate(client.created_at) }}
                    </span>
                </div>

                <!-- Meta row -->
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500 mb-3">
                    <span v-if="client.gender">{{ genderLabels[client.gender] ?? client.gender }}</span>
                    <span v-if="age"> · {{ age }} anni</span>
                    <span v-if="dietLabel"> · {{ dietLabel }}</span>
                    <span v-if="client.activity_level"> · Attività {{ activityLabels[client.activity_level] ?? client.activity_level }}</span>
                    <span v-if="client.user?.email"> · {{ client.user.email }}</span>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap items-center gap-2">
                    <Link
                        :href="route('nutritionist.clients.edit', client.id)"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition"
                    >
                        <Edit class="h-4 w-4" /> Modifica
                    </Link>
                    <button
                        @click="emit('newMeasurement')"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition"
                    >
                        <Plus class="h-4 w-4" /> Misurazione
                    </button>
                    <button
                        @click="emit('newAppointment')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-700 transition shadow-sm"
                    >
                        <CalendarPlus class="h-4 w-4" /> Appuntamento
                    </button>

                    <!-- More menu -->
                    <div class="relative">
                        <button
                            @click="showMenu = !showMenu"
                            class="inline-flex items-center rounded-lg border border-gray-200 bg-white p-1.5 text-gray-500 hover:bg-gray-50 transition"
                        >
                            <MoreHorizontal class="h-4 w-4" />
                        </button>
                        <div v-if="showMenu" class="absolute right-0 z-20 mt-1 w-52 rounded-xl border border-gray-100 bg-white shadow-lg py-1 text-sm" @click.away="showMenu = false">
                            <Link
                                :href="route('nutritionist.messages.show', client.id)"
                                @click="showMenu = false"
                                class="flex w-full items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-50"
                            >
                                <MessageCircle class="h-3.5 w-3.5" />
                                Messaggi
                            </Link>
                            <div class="my-1 border-t border-gray-100"></div>
                            <button @click="sendInvitation" :disabled="sendingInvitation" class="flex w-full items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-50 disabled:opacity-40">
                                <Send class="h-3.5 w-3.5" />
                                {{ client.user?.invitation_sent_at ? 'Reinvia invito' : 'Invia invito' }}
                            </button>
                            <button @click="toggleArchive(); showMenu = false" :disabled="archiving" class="flex w-full items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-50 disabled:opacity-40">
                                <ArchiveRestore v-if="client.status === 'archived'" class="h-3.5 w-3.5" />
                                <Archive v-else class="h-3.5 w-3.5" />
                                {{ client.status === 'archived' ? 'Ripristina' : 'Archivia' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
