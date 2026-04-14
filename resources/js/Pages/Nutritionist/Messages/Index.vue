<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { MessageSquare, Search, User, ChevronRight } from 'lucide-vue-next';

const props = defineProps<{
    threads: {
        id: number;
        name: string;
        avatar: string | null;
        unread_count: number;
        last_message: { body: string; created_at: string; sender_id: number } | null;
    }[];
    clients: { id: number; name: string }[];
}>();

const search = ref('');

const filteredThreads = computed(() => {
    const q = search.value.toLowerCase();
    if (!q) return props.threads;
    return props.threads.filter(t => t.name.toLowerCase().includes(q));
});

const clientsWithoutThread = computed(() => {
    const threadIds = new Set(props.threads.map(t => t.id));
    return props.clients.filter(c => !threadIds.has(c.id));
});

const filteredNewClients = computed(() => {
    const q = search.value.toLowerCase();
    if (!q) return clientsWithoutThread.value;
    return clientsWithoutThread.value.filter(c => c.name.toLowerCase().includes(q));
});

function openThread(clientId: number) {
    router.get(route('nutritionist.messages.show', clientId));
}

function timeAgo(dateStr: string): string {
    const diff = Date.now() - new Date(dateStr).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return 'ora';
    if (mins < 60) return `${mins}m`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours}h`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days}g`;
    return new Date(dateStr).toLocaleDateString('it-IT', { day: '2-digit', month: 'short' });
}
</script>

<template>
    <Head title="Messaggi" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Messaggi</h1>
            </div>
        </template>

        <div class="max-w-2xl">
            <!-- Search -->
            <div class="relative mb-4">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cerca cliente..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500"
                />
            </div>

            <!-- Thread list -->
            <div v-if="filteredThreads.length > 0" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden divide-y divide-gray-50">
                <button
                    v-for="thread in filteredThreads"
                    :key="thread.id"
                    @click="openThread(thread.id)"
                    class="w-full flex items-center gap-3 px-4 py-3.5 hover:bg-gray-50 transition text-left"
                >
                    <div class="relative flex-shrink-0">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                            <span class="text-sm font-bold text-primary-600">{{ thread.name.charAt(0).toUpperCase() }}</span>
                        </div>
                        <span
                            v-if="thread.unread_count > 0"
                            class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-primary-500 text-white text-[10px] font-bold flex items-center justify-center"
                        >
                            {{ thread.unread_count > 9 ? '9+' : thread.unread_count }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <span :class="['text-sm font-medium', thread.unread_count > 0 ? 'text-gray-900' : 'text-gray-700']">{{ thread.name }}</span>
                            <span v-if="thread.last_message" class="text-xs text-gray-400 flex-shrink-0 ml-2">{{ timeAgo(thread.last_message.created_at) }}</span>
                        </div>
                        <p v-if="thread.last_message" :class="['text-xs truncate mt-0.5', thread.unread_count > 0 ? 'text-gray-700 font-medium' : 'text-gray-400']">
                            {{ thread.last_message.body }}
                        </p>
                    </div>
                    <ChevronRight class="h-4 w-4 text-gray-300 flex-shrink-0" />
                </button>
            </div>

            <!-- New conversation starters -->
            <div v-if="filteredNewClients.length > 0" class="mt-6">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Inizia una conversazione</h3>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden divide-y divide-gray-50">
                    <button
                        v-for="client in filteredNewClients"
                        :key="client.id"
                        @click="openThread(client.id)"
                        class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition text-left"
                    >
                        <div class="h-9 w-9 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                            <User class="h-4 w-4 text-gray-400" />
                        </div>
                        <span class="text-sm text-gray-600">{{ client.name }}</span>
                        <ChevronRight class="h-4 w-4 text-gray-300 flex-shrink-0 ml-auto" />
                    </button>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="filteredThreads.length === 0 && filteredNewClients.length === 0" class="rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm">
                <MessageSquare class="mx-auto h-12 w-12 text-gray-300 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun messaggio</h3>
                <p class="text-sm text-gray-500">I messaggi con i tuoi clienti appariranno qui.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
