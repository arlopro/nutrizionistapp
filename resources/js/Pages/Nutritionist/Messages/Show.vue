<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, nextTick, onMounted } from 'vue';
import { ArrowLeft, Send, User } from 'lucide-vue-next';

const props = defineProps<{
    client: { id: number; user: { id: number; name: string; last_name?: string; avatar?: string } };
    messages: {
        id: number;
        body: string;
        sender_id: number;
        read_at: string | null;
        created_at: string;
        sender: { id: number; name: string; last_name?: string };
    }[];
}>();

const page = usePage();
const authUser = computed(() => (page.props as any).auth?.user);
const clientName = computed(() => {
    const u = props.client.user;
    return u.name + (u.last_name ? ' ' + u.last_name : '');
});

const chatContainer = ref<HTMLElement | null>(null);

const form = useForm({ body: '' });

function sendMessage() {
    if (!form.body.trim()) return;
    form.post(route('nutritionist.messages.store', props.client.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('body');
            nextTick(() => scrollToBottom());
        },
    });
}

function scrollToBottom() {
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
}

onMounted(() => scrollToBottom());

function formatTime(dateStr: string): string {
    return new Date(dateStr).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
}

function formatDate(dateStr: string): string {
    const d = new Date(dateStr);
    const today = new Date();
    if (d.toDateString() === today.toDateString()) return 'Oggi';
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    if (d.toDateString() === yesterday.toDateString()) return 'Ieri';
    return d.toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
}

// Group messages by date
const groupedMessages = computed(() => {
    const groups: { date: string; messages: typeof props.messages }[] = [];
    let currentDate = '';
    for (const msg of props.messages) {
        const date = formatDate(msg.created_at);
        if (date !== currentDate) {
            currentDate = date;
            groups.push({ date, messages: [] });
        }
        groups[groups.length - 1].messages.push(msg);
    }
    return groups;
});
</script>

<template>
    <Head :title="`Messaggi – ${clientName}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <a :href="route('nutritionist.messages.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 transition">
                    <ArrowLeft class="h-5 w-5" />
                </a>
                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                    <span class="text-xs font-bold text-primary-600">{{ clientName.charAt(0).toUpperCase() }}</span>
                </div>
                <h1 class="text-lg font-semibold text-gray-900">{{ clientName }}</h1>
            </div>
        </template>

        <div class="max-w-2xl flex flex-col" style="height: calc(100vh - 180px)">
            <!-- Messages -->
            <div ref="chatContainer" class="flex-1 overflow-y-auto px-2 py-4 space-y-1">
                <div v-if="messages.length === 0" class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <User class="mx-auto h-10 w-10 text-gray-300 mb-3" />
                        <p class="text-sm text-gray-400">Nessun messaggio. Scrivi il primo!</p>
                    </div>
                </div>

                <div v-for="group in groupedMessages" :key="group.date">
                    <div class="flex items-center justify-center my-4">
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500">{{ group.date }}</span>
                    </div>

                    <div v-for="msg in group.messages" :key="msg.id" class="mb-2">
                        <div :class="['flex', msg.sender_id === authUser?.id ? 'justify-end' : 'justify-start']">
                            <div
                                :class="[
                                    'max-w-[75%] rounded-2xl px-4 py-2.5 shadow-sm',
                                    msg.sender_id === authUser?.id
                                        ? 'bg-primary-500 text-white rounded-br-md'
                                        : 'bg-white border border-gray-100 text-gray-800 rounded-bl-md'
                                ]"
                            >
                                <p class="text-sm whitespace-pre-wrap break-words">{{ msg.body }}</p>
                                <p :class="['text-[10px] mt-1', msg.sender_id === authUser?.id ? 'text-primary-200' : 'text-gray-400']">
                                    {{ formatTime(msg.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input -->
            <div class="border-t border-gray-100 bg-white rounded-b-2xl p-3">
                <form @submit.prevent="sendMessage" class="flex items-end gap-2">
                    <textarea
                        v-model="form.body"
                        rows="1"
                        placeholder="Scrivi un messaggio..."
                        class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm resize-none focus:border-primary-500 focus:ring-primary-500 max-h-32"
                        @keydown.enter.exact.prevent="sendMessage"
                    ></textarea>
                    <button
                        type="submit"
                        :disabled="form.processing || !form.body.trim()"
                        class="rounded-xl bg-primary-500 p-2.5 text-white hover:bg-primary-600 transition disabled:opacity-40 flex-shrink-0"
                    >
                        <Send class="h-4 w-4" />
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
