<script setup lang="ts">
defineProps<{
    tabs: { key: string; label: string; icon?: any }[];
    active: string;
}>();

const emit = defineEmits<{ change: [key: string] }>();
</script>

<template>
    <div class="flex flex-wrap gap-1">
        <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="emit('change', tab.key)"
            :class="[
                'flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition-colors',
                active === tab.key
                    ? 'bg-gray-900 text-white'
                    : 'text-gray-600 hover:bg-gray-100',
            ]"
        >
            <component :is="tab.icon" v-if="tab.icon" class="h-3.5 w-3.5" />
            {{ tab.label }}
        </button>
    </div>
</template>
