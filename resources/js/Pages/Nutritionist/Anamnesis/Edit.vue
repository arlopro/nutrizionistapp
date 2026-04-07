<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Trash2, ChevronUp, ChevronDown } from 'lucide-vue-next';

type QuestionType = 'text' | 'textarea' | 'checkbox' | 'radio' | 'number' | 'scale';

interface Question {
    id: string;
    type: QuestionType;
    label: string;
    required: boolean;
    options: string[];
}

const props = defineProps<{
    template: {
        id: number;
        name: string;
        description: string | null;
        is_default: boolean;
        questions: Question[];
    };
}>();

const questionTypes: { value: QuestionType; label: string }[] = [
    { value: 'text',     label: 'Testo breve' },
    { value: 'textarea', label: 'Testo lungo' },
    { value: 'number',   label: 'Numero' },
    { value: 'scale',    label: 'Scala 1-10' },
    { value: 'radio',    label: 'Scelta singola' },
    { value: 'checkbox', label: 'Scelta multipla' },
];

const form = useForm({
    name:        props.template.name,
    description: props.template.description ?? '',
    is_default:  props.template.is_default,
    questions:   props.template.questions.map(q => ({ ...q, options: q.options ?? [] })) as Question[],
});

function newId() { return Math.random().toString(36).slice(2, 9); }

function addQuestion() {
    form.questions.push({ id: newId(), type: 'text', label: '', required: false, options: [] });
}

function removeQuestion(idx: number) { form.questions.splice(idx, 1); }

function moveUp(idx: number) {
    if (idx === 0) return;
    [form.questions[idx - 1], form.questions[idx]] = [form.questions[idx], form.questions[idx - 1]];
}

function moveDown(idx: number) {
    if (idx === form.questions.length - 1) return;
    [form.questions[idx], form.questions[idx + 1]] = [form.questions[idx + 1], form.questions[idx]];
}

function addOption(q: Question) { q.options.push(''); }
function removeOption(q: Question, i: number) { q.options.splice(i, 1); }

function onTypeChange(q: Question) {
    if (q.type !== 'radio' && q.type !== 'checkbox') {
        q.options = [];
    } else if (q.options.length === 0) {
        q.options = ['', ''];
    }
}

function submit() {
    form.put(route('nutritionist.anamnesis.update', props.template.id));
}
</script>

<template>
    <Head :title="`Modifica — ${template.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('nutritionist.anamnesis.index')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <h1 class="text-xl font-semibold text-gray-900">Modifica template</h1>
            </div>
        </template>

        <form @submit.prevent="submit" class="max-w-3xl space-y-6">
            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-4">Informazioni template</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome template *</label>
                        <input v-model="form.name" type="text" required class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500" />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrizione</label>
                        <textarea v-model="form.description" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input v-model="form.is_default" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                        <span class="text-sm text-gray-700">Imposta come template predefinito</span>
                    </label>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-gray-900">Domande</h2>
                    <span class="text-sm text-gray-400">{{ form.questions.length }} domand{{ form.questions.length === 1 ? 'a' : 'e' }}</span>
                </div>

                <div v-if="form.questions.length === 0" class="text-center py-8 text-gray-400 border-2 border-dashed border-gray-200 rounded-xl mb-4">
                    <p class="text-sm">Nessuna domanda. Aggiungine una!</p>
                </div>

                <div v-else class="space-y-4 mb-4">
                    <div v-for="(q, idx) in form.questions" :key="q.id" class="rounded-xl border border-gray-200 p-4">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="flex flex-col gap-0.5 pt-1">
                                <button type="button" @click="moveUp(idx)" :disabled="idx === 0" class="p-0.5 text-gray-300 hover:text-gray-500 disabled:opacity-30">
                                    <ChevronUp class="h-3.5 w-3.5" />
                                </button>
                                <button type="button" @click="moveDown(idx)" :disabled="idx === form.questions.length - 1" class="p-0.5 text-gray-300 hover:text-gray-500 disabled:opacity-30">
                                    <ChevronDown class="h-3.5 w-3.5" />
                                </button>
                            </div>
                            <div class="flex-1 space-y-3">
                                <input
                                    v-model="q.label"
                                    type="text"
                                    :placeholder="`Domanda ${idx + 1}...`"
                                    required
                                    class="w-full rounded-lg border-gray-300 text-sm font-medium focus:border-primary-500 focus:ring-primary-500"
                                />
                                <div class="flex flex-wrap items-center gap-3">
                                    <select v-model="q.type" @change="onTypeChange(q)" class="rounded-lg border-gray-200 text-xs py-1.5 focus:border-primary-500 focus:ring-primary-500">
                                        <option v-for="t in questionTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                                    </select>
                                    <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer">
                                        <input v-model="q.required" type="checkbox" class="rounded border-gray-300 text-primary-600" />
                                        Obbligatoria
                                    </label>
                                </div>
                                <div v-if="q.type === 'radio' || q.type === 'checkbox'" class="space-y-2">
                                    <p class="text-xs text-gray-500 font-medium">Opzioni:</p>
                                    <div v-for="(opt, oi) in q.options" :key="oi" class="flex items-center gap-2">
                                        <div :class="['h-3.5 w-3.5 flex-shrink-0 border border-gray-300', q.type === 'radio' ? 'rounded-full' : 'rounded']"></div>
                                        <input v-model="q.options[oi]" type="text" :placeholder="`Opzione ${oi + 1}`" class="flex-1 rounded-lg border-gray-200 text-xs py-1.5 focus:border-primary-500 focus:ring-primary-500" />
                                        <button type="button" @click="removeOption(q, oi)" class="text-gray-300 hover:text-red-400"><Trash2 class="h-3.5 w-3.5" /></button>
                                    </div>
                                    <button type="button" @click="addOption(q)" class="text-xs text-primary-600 hover:text-primary-800 font-medium">+ Aggiungi opzione</button>
                                </div>
                                <div v-if="q.type === 'scale'" class="flex gap-1">
                                    <span v-for="n in 10" :key="n" class="flex h-7 w-7 items-center justify-center rounded-lg border border-gray-200 text-xs text-gray-400">{{ n }}</span>
                                </div>
                            </div>
                            <button type="button" @click="removeQuestion(idx)" class="p-1.5 text-gray-300 hover:text-red-500 transition flex-shrink-0">
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    @click="addQuestion"
                    class="inline-flex items-center gap-2 rounded-lg border border-primary-200 bg-primary-50 px-4 py-2 text-sm font-medium text-primary-700 hover:bg-primary-100 transition w-full justify-center"
                >
                    <Plus class="h-4 w-4" /> Aggiungi domanda
                </button>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-primary-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-600 transition disabled:opacity-40"
                >
                    Salva modifiche
                </button>
                <Link :href="route('nutritionist.anamnesis.index')" class="text-sm text-gray-600 hover:text-gray-900">Annulla</Link>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
