<script setup lang="ts">
import Form from './Form.vue';

defineProps<{
    clients: any[];
    selectedClientId?: number | null;
}>();
</script>

<template>
    <Form :clients="clients" :selected-client-id="selectedClientId" />
</template>
