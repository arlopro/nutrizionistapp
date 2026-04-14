<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowLeft, FileText, CheckCircle, Send } from 'lucide-vue-next';

const props = defineProps<{
    submission: any;
}>();

const questions = props.submission.template?.questions || [];
const isCompleted = props.submission.status === 'completed';

// Build initial answers from existing data or empty
const initialAnswers: Record<string, any> = {};
for (const q of questions) {
    if (isCompleted && props.submission.answers) {
        initialAnswers[q.id] = props.submission.answers[q.id] ?? '';
    } else {
        initialAnswers[q.id] = q.type === 'checkbox' ? [] : '';
    }
}

const form = useForm({
    answers: initialAnswers,
});

function submitForm() {
    form.post(route('client.anamnesis.submit', props.submission.id));
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
}
</script>

<template>
    <Head :title="submission.template?.name || 'Questionario'" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('client.anamnesis.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">{{ submission.template?.name || 'Questionario' }}</h1>
                    <p v-if="submission.template?.description" class="text-sm text-gray-500 mt-0.5">{{ submission.template.description }}</p>
                </div>
            </div>
        </template>

        <!-- Already completed banner -->
        <div v-if="isCompleted" class="rounded-xl bg-green-50 border border-green-200 p-4 mb-6 flex items-center gap-3">
            <CheckCircle class="h-5 w-5 text-green-600 flex-shrink-0" />
            <div>
                <p class="text-sm font-medium text-green-800">Questionario compilato il {{ formatDate(submission.completed_at) }}</p>
                <p class="text-xs text-green-600">Le tue risposte sono state inviate al nutrizionista.</p>
            </div>
        </div>

        <form v-if="!isCompleted" @submit.prevent="submitForm" class="max-w-2xl space-y-6">
            <div
                v-for="(question, index) in questions"
                :key="question.id"
                class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6"
            >
                <label class="block text-sm font-medium text-gray-900 mb-2">
                    <span class="text-gray-400 mr-1">{{ index + 1 }}.</span>
                    {{ question.label }}
                    <span v-if="question.required" class="text-red-400 ml-0.5">*</span>
                </label>

                <!-- Text -->
                <input
                    v-if="question.type === 'text'"
                    v-model="form.answers[question.id]"
                    type="text"
                    :required="question.required"
                    class="block w-full rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                />

                <!-- Textarea -->
                <textarea
                    v-else-if="question.type === 'textarea'"
                    v-model="form.answers[question.id]"
                    rows="3"
                    :required="question.required"
                    class="block w-full rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500 resize-none"
                ></textarea>

                <!-- Number -->
                <input
                    v-else-if="question.type === 'number'"
                    v-model.number="form.answers[question.id]"
                    type="number"
                    :required="question.required"
                    class="block w-full max-w-xs rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                />

                <!-- Radio -->
                <div v-else-if="question.type === 'radio'" class="space-y-2 mt-1">
                    <label
                        v-for="option in question.options"
                        :key="option"
                        class="flex items-center gap-2 cursor-pointer"
                    >
                        <input
                            type="radio"
                            v-model="form.answers[question.id]"
                            :value="option"
                            :name="`q_${question.id}`"
                            class="text-primary-500 focus:ring-primary-500"
                        />
                        <span class="text-sm text-gray-700">{{ option }}</span>
                    </label>
                </div>

                <!-- Checkbox -->
                <div v-else-if="question.type === 'checkbox'" class="space-y-2 mt-1">
                    <label
                        v-for="option in question.options"
                        :key="option"
                        class="flex items-center gap-2 cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            v-model="form.answers[question.id]"
                            :value="option"
                            class="rounded text-primary-500 focus:ring-primary-500"
                        />
                        <span class="text-sm text-gray-700">{{ option }}</span>
                    </label>
                </div>

                <!-- Scale (1-10) -->
                <div v-else-if="question.type === 'scale'" class="mt-2">
                    <div class="flex items-center gap-1">
                        <button
                            v-for="n in 10"
                            :key="n"
                            type="button"
                            @click="form.answers[question.id] = n"
                            :class="[
                                'w-9 h-9 rounded-lg text-sm font-medium transition',
                                form.answers[question.id] === n
                                    ? 'bg-primary-500 text-white shadow-sm'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                            ]"
                        >
                            {{ n }}
                        </button>
                    </div>
                </div>

                <p v-if="form.errors[`answers.${question.id}`]" class="text-xs text-red-500 mt-1">
                    Campo obbligatorio
                </p>
            </div>

            <div class="flex justify-end pt-2 pb-8">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-3 text-sm font-medium text-white hover:from-primary-600 hover:to-primary-700 transition shadow-sm disabled:opacity-50"
                >
                    <Send class="h-4 w-4" /> Invia risposte
                </button>
            </div>
        </form>

        <!-- Read-only view for completed -->
        <div v-else class="max-w-2xl space-y-4">
            <div
                v-for="(question, index) in questions"
                :key="question.id"
                class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6"
            >
                <p class="text-sm font-medium text-gray-900 mb-1">
                    <span class="text-gray-400 mr-1">{{ index + 1 }}.</span>
                    {{ question.label }}
                </p>
                <div class="text-sm text-gray-700">
                    <template v-if="Array.isArray(submission.answers?.[question.id])">
                        <span v-for="(val, i) in submission.answers[question.id]" :key="i" class="inline-block rounded-full bg-primary-50 text-primary-700 px-2 py-0.5 text-xs font-medium mr-1 mb-1">{{ val }}</span>
                    </template>
                    <template v-else>
                        {{ submission.answers?.[question.id] || '—' }}
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
