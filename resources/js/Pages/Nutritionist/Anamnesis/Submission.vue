<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, FileText, CheckCircle, Clock, User } from 'lucide-vue-next';

const props = defineProps<{
    submission: any;
}>();

const questions = props.submission.template?.questions || [];

function formatDate(d: string) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head :title="`Anamnesi: ${submission.template?.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('nutritionist.clients.show', submission.client?.id)" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <div class="flex items-center gap-2">
                        <FileText class="h-5 w-5 text-primary-500" />
                        <h1 class="text-xl font-semibold text-gray-900">{{ submission.template?.name }}</h1>
                        <span v-if="submission.status === 'completed'" class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">
                            <CheckCircle class="h-3.5 w-3.5" /> Compilato
                        </span>
                        <span v-else class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">
                            <Clock class="h-3.5 w-3.5" /> In attesa
                        </span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-500 mt-0.5">
                        <span class="flex items-center gap-1"><User class="h-3.5 w-3.5" /> {{ submission.client?.user?.name }}</span>
                        <span>Inviato: {{ formatDate(submission.sent_at) }}</span>
                        <span v-if="submission.completed_at">Compilato: {{ formatDate(submission.completed_at) }}</span>
                    </div>
                </div>
            </div>
        </template>

        <div v-if="submission.status === 'pending'" class="rounded-xl bg-amber-50 border border-amber-200 p-4 mb-6">
            <p class="text-sm text-amber-800">Il paziente non ha ancora compilato questo questionario.</p>
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

                <div v-if="submission.status === 'completed' && submission.answers" class="text-sm text-gray-700">
                    <template v-if="Array.isArray(submission.answers[question.id])">
                        <span
                            v-for="(val, i) in submission.answers[question.id]"
                            :key="i"
                            class="inline-block rounded-full bg-primary-50 text-primary-700 px-2.5 py-0.5 text-xs font-medium mr-1 mb-1"
                        >{{ val }}</span>
                        <span v-if="submission.answers[question.id].length === 0" class="text-gray-400 italic">Nessuna selezione</span>
                    </template>
                    <template v-else-if="question.type === 'scale' && submission.answers[question.id]">
                        <div class="flex items-center gap-1">
                            <div
                                v-for="n in 10"
                                :key="n"
                                :class="[
                                    'w-8 h-8 rounded-lg text-sm font-medium flex items-center justify-center',
                                    submission.answers[question.id] === n
                                        ? 'bg-primary-500 text-white'
                                        : 'bg-gray-100 text-gray-400'
                                ]"
                            >{{ n }}</div>
                        </div>
                    </template>
                    <template v-else>
                        <p>{{ submission.answers[question.id] || '—' }}</p>
                    </template>
                </div>
                <div v-else class="text-sm text-gray-400 italic">
                    In attesa di risposta
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
