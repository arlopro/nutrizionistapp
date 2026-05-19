<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Upload, FileText, AlertTriangle, CheckCircle, ExternalLink } from 'lucide-vue-next';

const props = defineProps<{
    labResults: any[];
    clientId: number;
}>();

const NORMAL_RANGES: Record<string, [number, number]> = {
    glucose: [70, 100],
    hba1c: [4, 5.7],
    total_cholesterol: [0, 200],
    hdl_cholesterol: [40, 999],
    ldl_cholesterol: [0, 130],
    triglycerides: [0, 150],
    tsh: [0.4, 4.0],
    creatinine: [0.6, 1.2],
    crp: [0, 5],
};

function outOfRange(result: any) {
    return Object.entries(NORMAL_RANGES).filter(([key, [min, max]]) => {
        const val = result[key];
        return val != null && (Number(val) < min || Number(val) > max);
    }).length;
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: 'numeric', month: 'short', year: 'numeric' });
}

// Quick upload form
const dropzoneActive = ref(false);
const uploadFile = ref<File | null>(null);

function handleDrop(e: DragEvent) {
    dropzoneActive.value = false;
    const files = e.dataTransfer?.files;
    if (files?.[0]) uploadFile.value = files[0];
}

function handleFileInput(e: Event) {
    const input = e.target as HTMLInputElement;
    if (input.files?.[0]) uploadFile.value = input.files[0];
}

const form = useForm({
    client_id: props.clientId,
    date: new Date().toISOString().split('T')[0],
    lab_name: '',
    notes: '',
    file: null as File | null,
});

function submitUpload() {
    if (!uploadFile.value) return;
    form.file = uploadFile.value;
    form.post(route('nutritionist.lab-results.store'), {
        forceFormData: true,
        onSuccess: () => { uploadFile.value = null; form.reset(); },
    });
}
</script>

<template>
    <div class="space-y-4">
        <!-- List -->
        <div class="space-y-2">
            <div v-if="!labResults.length" class="text-center py-6 text-sm text-gray-400">
                Nessun esame caricato
            </div>
            <Link
                v-for="result in labResults"
                :key="result.id"
                :href="route('nutritionist.lab-results.show', result.id)"
                class="flex items-center gap-3 rounded-xl border border-gray-100 p-3.5 hover:bg-gray-50 transition"
            >
                <div class="flex-shrink-0 h-9 w-9 rounded-lg bg-indigo-50 flex items-center justify-center">
                    <FileText class="h-4 w-4 text-indigo-500" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ result.lab_name || 'Esame clinico' }}</p>
                    <p class="text-xs text-gray-400">{{ formatDate(result.date) }}</p>
                </div>
                <div class="flex-shrink-0">
                    <span v-if="outOfRange(result) > 0" class="inline-flex items-center gap-1 rounded-full bg-amber-50 border border-amber-200 px-2 py-0.5 text-xs font-medium text-amber-700">
                        <AlertTriangle class="h-3 w-3" />
                        {{ outOfRange(result) }} fuori range
                    </span>
                    <span v-else class="inline-flex items-center gap-1 rounded-full bg-green-50 border border-green-200 px-2 py-0.5 text-xs font-medium text-green-700">
                        <CheckCircle class="h-3 w-3" />
                        Nella norma
                    </span>
                </div>
                <ExternalLink class="h-3.5 w-3.5 text-gray-300 flex-shrink-0" />
            </Link>
        </div>

        <!-- Quick PDF upload -->
        <div class="rounded-xl border-2 border-dashed border-gray-200 p-5"
            :class="{ 'border-primary-400 bg-primary-50': dropzoneActive }"
            @dragover.prevent="dropzoneActive = true"
            @dragleave="dropzoneActive = false"
            @drop.prevent="handleDrop"
        >
            <div v-if="!uploadFile" class="text-center">
                <Upload class="mx-auto h-7 w-7 text-gray-300 mb-2" />
                <p class="text-sm font-medium text-gray-500">Trascina un PDF qui</p>
                <p class="text-xs text-gray-400 mb-3">Oppure clicca per selezionare</p>
                <label class="cursor-pointer inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition">
                    <Upload class="h-3.5 w-3.5" /> Sfoglia
                    <input type="file" accept=".pdf" class="hidden" @change="handleFileInput" />
                </label>
            </div>
            <div v-else class="space-y-3">
                <p class="text-sm font-medium text-gray-700">{{ uploadFile.name }}</p>
                <div class="grid grid-cols-2 gap-2">
                    <input v-model="form.date" type="date" class="rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500" />
                    <input v-model="form.lab_name" type="text" placeholder="Nome laboratorio" class="rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500" />
                </div>
                <div class="flex gap-2">
                    <button @click="uploadFile = null" class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition">Annulla</button>
                    <button @click="submitUpload" :disabled="form.processing" class="rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-700 transition disabled:opacity-50">
                        Carica esame
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
