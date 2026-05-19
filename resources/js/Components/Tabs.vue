<script setup lang="ts">
defineProps<{
    tabs: { key: string; label: string; icon?: any }[];
    active: string;
}>();

const emit = defineEmits<{ change: [key: string] }>();
</script>

<template>
    <div class="border-b border-gray-200 bg-white">
        <nav class="-mb-px flex gap-0 overflow-x-auto px-1" aria-label="Tabs">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                @click="emit('change', tab.key)"
                :class="[
                    'flex items-center gap-2 whitespace-nowrap border-b-2 px-4 py-3.5 text-sm font-medium transition-colors',
                    active === tab.key
                        ? 'border-primary-600 text-primary-700'
                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                ]"
            >
                <component :is="tab.icon" v-if="tab.icon" class="h-4 w-4 flex-shrink-0" />
                {{ tab.label }}
            </button>
        </nav>
    </div>
</template>
