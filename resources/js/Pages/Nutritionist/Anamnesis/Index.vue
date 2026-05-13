<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, FileText, Pencil, Trash2, Star } from 'lucide-vue-next';
import { useConfirm } from '@/Composables/useConfirm';

const { confirm: confirmDialog } = useConfirm();

defineProps<{
    templates: {
        id: number;
        name: string;
        description: string | null;
        is_default: boolean;
        questions: any[];
        created_at: string;
    }[];
}>();

async function deleteTemplate(id: number) {
    const ok = await confirmDialog('Il template anamnesi verrà eliminato definitivamente.', {
        title: 'Elimina template anamnesi',
        confirmLabel: 'Elimina',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('nutritionist.anamnesis.destroy', id), { preserveScroll: true });
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
}
</script>

<template>
    <Head title="Template Anamnesi" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Template Anamnesi</h1>
                <Link
                    :href="route('nutritionist.anamnesis.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 px-4 py-2 text-sm font-medium text-white hover:from-primary-600 hover:to-primary-700 transition shadow-sm"
                >
                    <Plus class="h-4 w-4" /> Nuovo template
                </Link>
            </div>
        </template>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ $page.props.flash.success }}
        </div>

        <!-- Empty -->
        <div v-if="templates.length === 0" class="rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm">
            <FileText class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun template</h3>
            <p class="text-sm text-gray-500 mb-6">
                Crea un template di anamnesi con le domande che fai ai tuoi clienti al primo appuntamento.
            </p>
            <Link
                :href="route('nutritionist.anamnesis.create')"
                class="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-600 transition"
            >
                <Plus class="h-4 w-4" /> Crea il primo template
            </Link>
        </div>

        <!-- List -->
        <div v-else class="space-y-3">
            <div
                v-for="tmpl in templates"
                :key="tmpl.id"
                class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-semibold text-gray-900">{{ tmpl.name }}</h3>
                            <span
                                v-if="tmpl.is_default"
                                class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700"
                            >
                                <Star class="h-3 w-3" /> Predefinito
                            </span>
                        </div>
                        <p v-if="tmpl.description" class="text-sm text-gray-500 mb-2 line-clamp-2">{{ tmpl.description }}</p>
                        <div class="flex items-center gap-3 text-xs text-gray-400">
                            <span>{{ tmpl.questions.length }} domand{{ tmpl.questions.length === 1 ? 'a' : 'e' }}</span>
                            <span>·</span>
                            <span>Creato {{ formatDate(tmpl.created_at) }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <Link
                            :href="route('nutritionist.anamnesis.edit', tmpl.id)"
                            class="p-1.5 text-gray-400 hover:text-primary-600 transition rounded-lg hover:bg-gray-50"
                        >
                            <Pencil class="h-4 w-4" />
                        </Link>
                        <button
                            @click="deleteTemplate(tmpl.id)"
                            class="p-1.5 text-gray-400 hover:text-red-500 transition rounded-lg hover:bg-gray-50"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
