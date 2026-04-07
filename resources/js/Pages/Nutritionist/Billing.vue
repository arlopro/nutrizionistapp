<script setup lang="ts">
import { computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Check, Zap, Star, Building2, Users, CreditCard, ExternalLink, ArrowUpCircle } from 'lucide-vue-next';

interface Plan {
    name: string;
    price: number;
    client_limit: number | null;
    stripe_price_id: string | null;
    features: string[];
}

interface PlanData {
    key: string;
    name: string;
    price: number;
    client_limit: number | null;
    active_clients: number;
    can_add_client: boolean;
    subscription: any;
}

const props = defineProps<{
    planData: PlanData;
    plans: Record<string, Plan>;
}>();

const planKeys = ['free', 'starter', 'pro', 'business'] as const;

const planIcons: Record<string, any> = {
    free: Users,
    starter: Zap,
    pro: Star,
    business: Building2,
};

const planColors: Record<string, string> = {
    free: 'gray',
    starter: 'blue',
    pro: 'primary',
    business: 'purple',
};

function colorClasses(plan: string, type: 'badge' | 'button' | 'check' | 'border') {
    const c = planColors[plan];
    const map: Record<string, Record<string, string>> = {
        badge: {
            gray: 'bg-gray-100 text-gray-700',
            blue: 'bg-blue-100 text-blue-700',
            primary: 'bg-primary-100 text-primary-700',
            purple: 'bg-purple-100 text-purple-700',
        },
        button: {
            gray: 'bg-gray-600 text-white hover:bg-gray-700',
            blue: 'bg-blue-600 text-white hover:bg-blue-700',
            primary: 'bg-primary-600 text-white hover:bg-primary-700',
            purple: 'bg-purple-600 text-white hover:bg-purple-700',
        },
        check: {
            gray: 'text-gray-500',
            blue: 'text-blue-500',
            primary: 'text-primary-500',
            purple: 'text-purple-500',
        },
        border: {
            gray: 'border-gray-200',
            blue: 'border-blue-200',
            primary: 'border-primary-300 ring-2 ring-primary-200',
            purple: 'border-purple-200',
        },
    };
    return map[type][c] ?? '';
}

function isCurrentPlan(key: string) {
    return props.planData.key === key;
}

function isUpgrade(key: string) {
    const order = ['free', 'starter', 'pro', 'business'];
    return order.indexOf(key) > order.indexOf(props.planData.key);
}

function checkout(plan: string) {
    router.post(route('nutritionist.billing.checkout'), { plan });
}

function openPortal() {
    router.post(route('nutritionist.billing.portal'));
}

const usagePercent = computed(() => {
    if (!props.planData.client_limit) return 0;
    return Math.min(100, Math.round((props.planData.active_clients / props.planData.client_limit) * 100));
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Abbonamento</h1>
        </template>

        <div class="max-w-5xl space-y-8">
            <!-- Current plan card -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Piano attuale</p>
                        <div class="flex items-center gap-3">
                            <h2 class="text-2xl font-bold text-gray-900">{{ planData.name }}</h2>
                            <span
                                class="rounded-full px-3 py-0.5 text-xs font-semibold"
                                :class="colorClasses(planData.key, 'badge')"
                            >
                                {{ planData.key === 'free' ? 'Gratuito' : 'Attivo' }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            <Users class="inline h-3.5 w-3.5 mr-1" />
                            {{ planData.active_clients }}
                            {{ planData.client_limit ? '/ ' + planData.client_limit : '' }}
                            clienti attivi
                            {{ !planData.client_limit ? '(illimitati)' : '' }}
                        </p>
                    </div>
                    <div class="flex gap-3" v-if="planData.key !== 'free'">
                        <button
                            @click="openPortal"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                        >
                            <CreditCard class="h-4 w-4" />
                            Gestisci pagamento
                            <ExternalLink class="h-3.5 w-3.5 text-gray-400" />
                        </button>
                    </div>
                </div>

                <!-- Usage bar -->
                <div v-if="planData.client_limit" class="mt-5">
                    <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                        <span>Clienti attivi</span>
                        <span>{{ planData.active_clients }} / {{ planData.client_limit }}</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-gray-100">
                        <div
                            class="h-2 rounded-full transition-all"
                            :class="usagePercent >= 90 ? 'bg-red-500' : usagePercent >= 70 ? 'bg-yellow-500' : 'bg-primary-500'"
                            :style="{ width: usagePercent + '%' }"
                        />
                    </div>
                    <p v-if="usagePercent >= 90" class="mt-1.5 text-xs text-red-600">
                        Stai per raggiungere il limite del tuo piano. Valuta di effettuare un upgrade.
                    </p>
                </div>
            </div>

            <!-- Flash messages -->
            <div v-if="$page.props.flash?.success" class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                {{ $page.props.flash.success }}
            </div>

            <!-- Plans grid -->
            <div>
                <h2 class="text-base font-semibold text-gray-900 mb-4">Tutti i piani</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        v-for="key in planKeys"
                        :key="key"
                        class="relative rounded-xl border bg-white p-5 flex flex-col"
                        :class="[
                            colorClasses(key, 'border'),
                            isCurrentPlan(key) ? 'shadow-md' : ''
                        ]"
                    >
                        <!-- Current badge -->
                        <div
                            v-if="isCurrentPlan(key)"
                            class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full px-3 py-0.5 text-xs font-semibold bg-primary-600 text-white whitespace-nowrap"
                        >
                            Piano attuale
                        </div>

                        <!-- Icon + Name -->
                        <div class="flex items-center gap-2 mb-3">
                            <component :is="planIcons[key]" class="h-5 w-5" :class="colorClasses(key, 'check')" />
                            <span class="font-semibold text-gray-900">{{ plans[key].name }}</span>
                        </div>

                        <!-- Price -->
                        <div class="mb-4">
                            <span class="text-2xl font-bold text-gray-900">
                                {{ plans[key].price === 0 ? 'Gratis' : '€' + plans[key].price }}
                            </span>
                            <span v-if="plans[key].price > 0" class="text-sm text-gray-400">/mese</span>
                        </div>

                        <!-- Features -->
                        <ul class="flex-1 space-y-1.5 mb-5">
                            <li
                                v-for="feature in plans[key].features"
                                :key="feature"
                                class="flex items-start gap-1.5 text-xs text-gray-600"
                            >
                                <Check class="h-3.5 w-3.5 flex-shrink-0 mt-0.5" :class="colorClasses(key, 'check')" />
                                {{ feature }}
                            </li>
                        </ul>

                        <!-- CTA -->
                        <div>
                            <div
                                v-if="isCurrentPlan(key)"
                                class="rounded-lg border border-gray-200 px-4 py-2 text-center text-xs font-medium text-gray-400"
                            >
                                Piano corrente
                            </div>
                            <button
                                v-else-if="isUpgrade(key) && plans[key].stripe_price_id"
                                @click="checkout(key)"
                                class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg px-4 py-2 text-xs font-semibold transition"
                                :class="colorClasses(key, 'button')"
                            >
                                <ArrowUpCircle class="h-3.5 w-3.5" />
                                Upgrade a {{ plans[key].name }}
                            </button>
                            <div
                                v-else-if="!plans[key].stripe_price_id && key !== 'free'"
                                class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-2 text-center text-xs text-gray-400"
                            >
                                Non disponibile
                            </div>
                            <div
                                v-else-if="key === 'free' && !isCurrentPlan(key)"
                                class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-2 text-center text-xs text-gray-400"
                            >
                                Downgrade via portale
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
