<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowLeft, CheckCircle } from 'lucide-vue-next';
import TypeformForm from '@/Components/TypeformForm.vue';

const props = defineProps<{
    submission: {
        id: number;
        status: string;
        completed_at: string;
        answers: Record<string, any> | null;
        template: {
            name: string;
            description: string | null;
            questions: any[];
        } | null;
    };
}>();

const questions = props.submission.template?.questions || [];
const isCompleted = props.submission.status === 'completed';

const initialAnswers: Record<string, any> = {};
for (const q of questions) {
    if (isCompleted && props.submission.answers) {
        initialAnswers[q.id] = props.submission.answers[q.id] ?? (q.type === 'checkbox' ? [] : '');
    } else {
        initialAnswers[q.id] = q.type === 'checkbox' ? [] : '';
    }
}

const answers = ref<Record<string, any>>(initialAnswers);
const form = useForm<{ answers: Record<string, any> }>({ answers: {} });

function handleSubmit() {
    form.answers = answers.value;
    form.post(route('client.anamnesis.submit', props.submission.id));
}

function handleClose() {
    window.location.href = route('client.anamnesis.index');
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
}
</script>

<template>
    <Head :title="submission.template?.name || 'Questionario'" />
    <AuthenticatedLayout>
        <template #header>
            <div v-if="isCompleted" class="flex items-center gap-3">
                <Link :href="route('client.anamnesis.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">{{ submission.template?.name || 'Questionario' }}</h1>
            </div>
        </template>

        <!-- Already completed: static read-only view within layout -->
        <template v-if="isCompleted">
            <div class="mb-6 rounded-xl bg-green-50 border border-green-200 p-4 flex items-center gap-3">
                <CheckCircle class="h-5 w-5 text-green-600 flex-shrink-0" />
                <div>
                    <p class="text-sm font-medium text-green-800">Questionario compilato il {{ formatDate(submission.completed_at) }}</p>
                    <p class="text-xs text-green-600">Le tue risposte sono state inviate al nutrizionista.</p>
                </div>
            </div>

            <div class="max-w-2xl space-y-4">
                <div
                    v-for="(question, index) in questions"
                    :key="question.id"
                    class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6"
                >
                    <p class="text-sm font-medium text-gray-900 mb-2">
                        <span class="text-gray-400 mr-1">{{ index + 1 }}.</span>
                        {{ question.label }}
                    </p>
                    <div class="text-sm text-gray-700">
                        <template v-if="Array.isArray(submission.answers?.[question.id])">
                            <span
                                v-for="(val, i) in submission.answers[question.id]"
                                :key="i"
                                class="inline-block rounded-full bg-primary-50 text-primary-700 px-2 py-0.5 text-xs font-medium mr-1 mb-1"
                            >{{ val }}</span>
                        </template>
                        <template v-else>
                            {{ submission.answers?.[question.id] || '—' }}
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <!-- Not yet completed: full Typeform experience -->
        <template v-else>
            <TypeformForm
                :questions="questions"
                v-model:answers="answers"
                :title="submission.template?.name || 'Questionario'"
                :description="submission.template?.description"
                :preview-mode="false"
                exit-label="Torna ai questionari"
                @close="handleClose"
                @submit="handleSubmit"
            />
        </template>
    </AuthenticatedLayout>
</template>
