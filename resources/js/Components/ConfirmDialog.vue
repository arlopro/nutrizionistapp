<script setup lang="ts">
import { useConfirm } from '@/Composables/useConfirm';
import { AlertTriangle, Trash2 } from 'lucide-vue-next';

const { state, accept, dismiss } = useConfirm();
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="state.show" class="fixed inset-0 z-50 flex items-center justify-center px-4">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" @click="dismiss" />

                <!-- Dialog -->
                <Transition
                    enter-active-class="ease-out duration-200"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="ease-in duration-150"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-if="state.show" class="relative w-full max-w-sm rounded-2xl bg-white shadow-xl border border-gray-100 p-6">
                        <div class="flex items-start gap-4">
                            <div :class="['flex-shrink-0 rounded-full p-2.5', state.danger ? 'bg-red-50' : 'bg-amber-50']">
                                <AlertTriangle v-if="!state.danger" class="h-5 w-5 text-amber-500" />
                                <Trash2 v-else class="h-5 w-5 text-red-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-semibold text-gray-900">{{ state.title }}</h3>
                                <p class="mt-1 text-sm text-gray-500 leading-relaxed">{{ state.message }}</p>
                            </div>
                        </div>

                        <div class="mt-5 flex justify-end gap-2.5">
                            <button
                                type="button"
                                @click="dismiss"
                                class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                            >
                                {{ state.cancelLabel }}
                            </button>
                            <button
                                type="button"
                                @click="accept"
                                :class="[
                                    'rounded-lg px-4 py-2 text-sm font-semibold text-white transition',
                                    state.danger
                                        ? 'bg-red-600 hover:bg-red-700'
                                        : 'bg-primary-600 hover:bg-primary-700'
                                ]"
                            >
                                {{ state.confirmLabel }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
