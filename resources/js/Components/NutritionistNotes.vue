<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Trash2, Edit3 } from 'lucide-vue-next';

const props = defineProps<{
    notes: { date: string; text: string }[] | null;
    clientId: number;
}>();

const form = useForm({ text: '' });
const showForm = ref(false);

function addNote() {
    form.post(route('nutritionist.clients.notes.store', props.clientId), {
        preserveScroll: true,
        onSuccess: () => { form.reset(); showForm.value = false; },
    });
}

function deleteNote(index: number) {
    router.delete(route('nutritionist.clients.notes.destroy', [props.clientId, index]), {
        preserveScroll: true,
    });
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: 'numeric' });
}

const sortedNotes = () => [...(props.notes ?? [])].reverse();
</script>

<template>
    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <Edit3 class="h-4 w-4 text-gray-400" />
                <p class="text-sm font-semibold text-gray-900">Note del nutrizionista</p>
            </div>
            <button
                @click="showForm = !showForm"
                class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 hover:text-primary-700 transition"
            >
                <Plus class="h-3.5 w-3.5" /> Aggiungi nota
            </button>
        </div>

        <!-- Add form -->
        <div v-if="showForm" class="mb-4 rounded-xl bg-gray-50 p-3 space-y-2">
            <textarea
                v-model="form.text"
                rows="3"
                placeholder="Scrivi una nota clinica..."
                class="block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500 resize-none"
            ></textarea>
            <div class="flex justify-end gap-2">
                <button @click="showForm = false; form.reset()" class="rounded-lg px-3 py-1.5 text-sm text-gray-500 hover:bg-gray-200 transition">Annulla</button>
                <button @click="addNote" :disabled="!form.text || form.processing" class="rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-700 transition disabled:opacity-50">
                    Salva nota
                </button>
            </div>
        </div>

        <!-- Notes list -->
        <div v-if="!sortedNotes().length" class="text-center py-6 text-sm text-gray-400">
            Nessuna nota
        </div>
        <div v-else class="space-y-3">
            <div v-for="(note, idx) in sortedNotes()" :key="idx" class="group flex gap-3">
                <div class="flex-shrink-0 mt-0.5">
                    <div class="h-7 w-7 rounded-full bg-gray-100 flex items-center justify-center">
                        <Edit3 class="h-3 w-3 text-gray-400" />
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-medium text-primary-600 mb-0.5">{{ formatDate(note.date) }}</p>
                    <p class="text-sm text-gray-700">{{ note.text }}</p>
                </div>
                <button
                    @click="deleteNote((notes?.length ?? 0) - 1 - idx)"
                    class="opacity-0 group-hover:opacity-100 transition rounded-lg p-1 text-gray-300 hover:text-red-400 hover:bg-red-50"
                >
                    <Trash2 class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>
    </div>
</template>
