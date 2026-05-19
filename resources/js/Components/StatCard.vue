<script setup lang="ts">
defineProps<{
    label: string;
    value: string | number | null;
    unit?: string;
    sub?: string;
    delta?: string | null;
    deltaPositiveIsGood?: boolean;
    accent?: 'green' | 'indigo' | 'amber' | 'default';
    icon?: any;
}>();
</script>

<template>
    <div :class="[
        'rounded-xl border p-4 flex flex-col gap-1',
        accent === 'green' ? 'border-green-100 bg-green-50' :
        accent === 'indigo' ? 'border-indigo-100 bg-indigo-50' :
        accent === 'amber' ? 'border-amber-100 bg-amber-50' :
        'border-gray-100 bg-white',
    ]">
        <div class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-gray-400">
            <component :is="icon" v-if="icon" class="h-3.5 w-3.5" />
            {{ label }}
        </div>
        <div class="flex items-baseline gap-1">
            <span class="text-2xl font-bold text-gray-900 leading-none">{{ value ?? '—' }}</span>
            <span v-if="unit" class="text-sm text-gray-500">{{ unit }}</span>
        </div>
        <div v-if="sub || delta" class="flex items-center gap-2 text-xs">
            <span v-if="delta" :class="[
                'font-medium',
                deltaPositiveIsGood === false
                    ? (parseFloat(delta) < 0 ? 'text-green-600' : parseFloat(delta) > 0 ? 'text-red-500' : 'text-gray-500')
                    : (parseFloat(delta) > 0 ? 'text-green-600' : parseFloat(delta) < 0 ? 'text-red-500' : 'text-gray-500'),
            ]">
                {{ parseFloat(delta) > 0 ? '+' : '' }}{{ delta }}
            </span>
            <span v-if="sub" class="text-gray-400">{{ sub }}</span>
        </div>
    </div>
</template>
