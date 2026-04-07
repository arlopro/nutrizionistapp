<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { Camera, Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
    avatarUrl: string | null;
}>();

const page = usePage();
const authUser = computed(() => (page.props as any).auth?.user);

const avatarPreview = ref<string | null>(props.avatarUrl);
const avatarInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    avatar: null as File | null,
});

function onAvatarChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    form.avatar = file;
    const reader = new FileReader();
    reader.onload = (ev) => { avatarPreview.value = ev.target?.result as string; };
    reader.readAsDataURL(file);
}

function removeAvatar() {
    if (props.avatarUrl) {
        router.delete(route('client.settings.avatar.delete'), { preserveScroll: true });
    }
    avatarPreview.value = null;
    form.avatar = null;
    if (avatarInput.value) avatarInput.value.value = '';
}

function submit() {
    if (!form.avatar) return;
    form.post(route('client.settings.avatar.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Impostazioni" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-900">Impostazioni</h1>
        </template>

        <div class="max-w-lg">
            <div
                v-if="$page.props.flash?.success"
                class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700"
            >
                {{ $page.props.flash.success }}
            </div>

            <form @submit.prevent="submit" enctype="multipart/form-data">
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Foto profilo</h2>
                    <div class="flex items-center gap-5">
                        <div class="flex-shrink-0">
                            <img
                                v-if="avatarPreview"
                                :src="avatarPreview"
                                alt="Avatar"
                                class="h-20 w-20 rounded-full object-cover border border-gray-200"
                            />
                            <div
                                v-else
                                class="h-20 w-20 rounded-full border-2 border-dashed border-gray-300 flex items-center justify-center bg-gradient-to-br from-primary-100 to-primary-200"
                            >
                                <span class="text-2xl font-bold text-primary-500">{{ authUser?.name?.charAt(0)?.toUpperCase() }}</span>
                            </div>
                        </div>
                        <div class="flex-1 space-y-2">
                            <p class="text-sm text-gray-600">La foto viene mostrata nella barra laterale della tua area personale.</p>
                            <p class="text-xs text-gray-400">JPG, PNG, WEBP — max 2 MB</p>
                            <div class="flex gap-2 pt-1">
                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white hover:bg-primary-700 transition">
                                    <Camera class="h-4 w-4" />
                                    {{ avatarPreview ? 'Cambia foto' : 'Carica foto' }}
                                    <input
                                        ref="avatarInput"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="sr-only"
                                        @change="onAvatarChange"
                                    />
                                </label>
                                <button
                                    v-if="avatarPreview"
                                    type="button"
                                    @click="removeAvatar"
                                    class="inline-flex items-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition"
                                >
                                    <Trash2 class="h-4 w-4" />
                                    Rimuovi
                                </button>
                            </div>
                            <p v-if="form.errors.avatar" class="text-sm text-red-600">{{ form.errors.avatar }}</p>
                        </div>
                    </div>

                    <div v-if="form.avatar" class="mt-4 pt-4 border-t border-gray-100">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-600 transition disabled:opacity-40"
                        >
                            Salva foto
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
