<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { X, Camera, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    clientId: number;
}>();

const emit = defineEmits<{ close: [] }>();

const form = useForm({
    date: new Date().toISOString().split('T')[0],
    weight_kg: '',
    body_fat_percentage: '',
    lean_mass_kg: '',
    notes: '',
    measurements: [
        { type: 'waist', value: '' },
        { type: 'hips', value: '' },
        { type: 'chest', value: '' },
    ],
});

const measurementLabels: Record<string, string> = {
    waist: 'Vita (cm)',
    hips: 'Fianchi (cm)',
    chest: 'Petto (cm)',
};

const photoTypeLabels: Record<string, string> = {
    front: 'Frontale',
    side:  'Laterale',
    back:  'Posteriore',
    other: 'Altro',
};

interface PhotoItem {
    file: File;
    type: string;
    preview: string;
}

const photoItems = ref<PhotoItem[]>([]);
const processing = ref(false);

function onFileChange(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files) return;
    Array.from(input.files).forEach(file => {
        photoItems.value.push({ file, type: 'front', preview: URL.createObjectURL(file) });
    });
    input.value = '';
}

function removePhoto(index: number) {
    URL.revokeObjectURL(photoItems.value[index].preview);
    photoItems.value.splice(index, 1);
}

function submit() {
    processing.value = true;

    router.post(
        route('nutritionist.clients.check-ins.store', props.clientId),
        {
            date:                form.date,
            weight_kg:           form.weight_kg           || null,
            body_fat_percentage: form.body_fat_percentage || null,
            lean_mass_kg:        form.lean_mass_kg        || null,
            notes:               form.notes               || null,
            measurements: form.measurements
                .filter(m => m.value !== '')
                .map(m => ({ type: m.type, value: Number(m.value) })),
            photo_files: photoItems.value.map(p => p.file),
            photo_types: photoItems.value.map(p => p.type),
        },
        {
            preserveScroll: true,
            onSuccess: () => emit('close'),
            onFinish:  () => { processing.value = false; },
        }
    );
}
</script>

<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="emit('close')">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Nuova misurazione</h3>
                    <button @click="emit('close')" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 transition">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="p-5 space-y-4 overflow-y-auto max-h-[70vh]">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Data</label>
                        <input v-model="form.date" type="date" class="block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500" />
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Peso (kg)</label>
                            <input v-model="form.weight_kg" type="number" step="0.1" placeholder="—" class="block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">M. Grassa (%)</label>
                            <input v-model="form.body_fat_percentage" type="number" step="0.1" placeholder="—" class="block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">M. Magra (kg)</label>
                            <input v-model="form.lean_mass_kg" type="number" step="0.1" placeholder="—" class="block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500" />
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-2">Circonferenze</p>
                        <div class="grid grid-cols-3 gap-3">
                            <div v-for="m in form.measurements" :key="m.type">
                                <label class="block text-xs text-gray-400 mb-1">{{ measurementLabels[m.type] }}</label>
                                <input v-model="m.value" type="number" step="0.1" placeholder="—" class="block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Note</label>
                        <textarea v-model="form.notes" rows="2" placeholder="Osservazioni..." class="block w-full rounded-lg border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500 resize-none"></textarea>
                    </div>

                    <!-- Foto -->
                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-2">Foto <span class="text-gray-400 font-normal">(facoltativo)</span></p>

                        <div v-if="photoItems.length" class="grid grid-cols-3 gap-2 mb-2">
                            <div v-for="(p, i) in photoItems" :key="i" class="relative rounded-xl overflow-hidden bg-gray-100">
                                <div class="aspect-square">
                                    <img :src="p.preview" class="h-full w-full object-cover" />
                                </div>
                                <!-- Tipo selector -->
                                <div class="flex items-center justify-between gap-1 px-1.5 py-1 bg-gray-50 border-t border-gray-200">
                                    <select
                                        v-model="p.type"
                                        class="flex-1 text-[11px] rounded-md border-0 bg-transparent text-gray-600 py-0 pl-0 pr-4 focus:ring-0 cursor-pointer"
                                    >
                                        <option v-for="(label, val) in photoTypeLabels" :key="val" :value="val">{{ label }}</option>
                                    </select>
                                    <button type="button" @click="removePhoto(i)" class="text-gray-400 hover:text-red-500 transition flex-shrink-0">
                                        <Trash2 class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <label class="flex items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-200 px-4 py-3 cursor-pointer hover:border-primary-300 hover:bg-primary-50/40 transition">
                            <Camera class="h-4 w-4 text-gray-400" />
                            <span class="text-sm text-gray-500">Aggiungi foto</span>
                            <input type="file" accept="image/*" multiple class="hidden" @change="onFileChange" />
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 p-5 border-t border-gray-100">
                    <button @click="emit('close')" class="rounded-lg px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 transition">Annulla</button>
                    <button @click="submit" :disabled="processing" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 transition disabled:opacity-50">
                        {{ processing ? 'Salvataggio...' : 'Salva misurazione' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
