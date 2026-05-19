<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import TypeformForm from '@/Components/TypeformForm.vue';

const props = defineProps<{
    template: {
        id: number;
        name: string;
        description: string | null;
        questions: any[];
    };
}>();

const answers = ref<Record<string, any>>({});
for (const q of props.template.questions) {
    answers.value[q.id] = q.type === 'checkbox' ? [] : '';
}

function handleClose() {
    router.visit(route('nutritionist.anamnesis.index'));
}
</script>

<template>
    <Head :title="`Anteprima: ${template.name}`" />
    <AuthenticatedLayout>
        <template #header></template>

        <TypeformForm
            :questions="template.questions"
            v-model:answers="answers"
            :title="template.name"
            :description="template.description"
            :preview-mode="true"
            exit-label="Esci dall'anteprima"
            @close="handleClose"
            @submit="handleClose"
        />
    </AuthenticatedLayout>
</template>
