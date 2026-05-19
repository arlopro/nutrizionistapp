<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    messages: any[];
    clientId: number;
    nutritionistId: number;
}>();

const recent = computed(() => [...props.messages].reverse().slice(0, 5));
</script>

<template>
    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-semibold text-gray-900">Conversazione recente</p>
            <Link
                :href="route('nutritionist.messages.show', clientId)"
                class="text-xs font-medium text-primary-600 hover:text-primary-700 transition"
            >
                Apri →
            </Link>
        </div>

        <div v-if="!messages.length" class="text-center py-6 text-sm text-gray-400">
            Nessun messaggio
        </div>
        <div v-else class="space-y-2">
            <div
                v-for="msg in recent"
                :key="msg.id"
                :class="['flex', msg.sender_id !== nutritionistId ? 'justify-start' : 'justify-end']"
            >
                <div :class="[
                    'max-w-[80%] rounded-2xl px-3 py-2 text-sm',
                    msg.sender_id !== nutritionistId
                        ? 'bg-gray-100 text-gray-700 rounded-tl-sm'
                        : 'bg-primary-600 text-white rounded-tr-sm',
                ]">
                    <p>{{ msg.body }}</p>
                    <p :class="['text-[10px] mt-0.5 text-right', msg.sender_id !== nutritionistId ? 'text-gray-400' : 'text-primary-200']">
                        {{ new Date(msg.created_at).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' }) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
