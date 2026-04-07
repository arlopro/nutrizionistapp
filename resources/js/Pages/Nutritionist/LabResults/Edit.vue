<script setup lang="ts">
import Form from './Form.vue';

defineProps<{
    labResult: any;
    clients: any[];
}>();
</script>

<template>
    <Form :lab-result="labResult" :clients="clients" is-edit />
</template>
