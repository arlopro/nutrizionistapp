<script setup lang="ts">
import { computed, ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Check, Zap, Star, Building2, Users, CreditCard, ExternalLink,
    ArrowUpCircle, AlertCircle, Receipt, ChevronDown, ChevronUp,
    FileText, Calendar,
} from 'lucide-vue-next';

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

interface Invoice {
    id: string;
    number: string;
    date: string | null;
    total: string;
    status: string;
    pdf_url: string | null;
    hosted_url: string | null;
}

const props = defineProps<{
    planData: PlanData;
    plans: Record<string, Plan>;
    paymentProviders: { stripe: boolean; paypal: boolean };
    invoices: Invoice[];
    nextBillingDate: string | null;
    billingInfo: Record<string, string>;
}>();

const planKeys = ['free', 'starter', 'pro', 'business'] as const;

const planIcons: Record<string, any> = {
    free: Users, starter: Zap, pro: Star, business: Building2,
};

const planColors: Record<string, string> = {
    free: 'gray', starter: 'blue', pro: 'primary', business: 'purple',
};

const anyProviderConfigured = computed(() => props.paymentProviders.stripe || props.paymentProviders.paypal);

function colorClasses(plan: string, type: 'badge' | 'button' | 'check' | 'border') {
    const c = planColors[plan];
    const map: Record<string, Record<string, string>> = {
        badge:  { gray: 'bg-gray-100 text-gray-700', blue: 'bg-blue-100 text-blue-700', primary: 'bg-primary-100 text-primary-700', purple: 'bg-purple-100 text-purple-700' },
        button: { gray: 'bg-gray-600 text-white hover:bg-gray-700', blue: 'bg-blue-600 text-white hover:bg-blue-700', primary: 'bg-primary-600 text-white hover:bg-primary-700', purple: 'bg-purple-600 text-white hover:bg-purple-700' },
        check:  { gray: 'text-gray-500', blue: 'text-blue-500', primary: 'text-primary-500', purple: 'text-purple-500' },
        border: { gray: 'border-gray-200', blue: 'border-blue-200', primary: 'border-primary-300 ring-2 ring-primary-200', purple: 'border-purple-200' },
    };
    return map[type][c] ?? '';
}

function isCurrentPlan(key: string) { return props.planData.key === key; }
function isUpgrade(key: string) {
    const order = ['free', 'starter', 'pro', 'business'];
    return order.indexOf(key) > order.indexOf(props.planData.key);
}

function checkout(plan: string, provider: 'stripe' | 'paypal') {
    router.post(route('nutritionist.billing.checkout'), { plan, provider });
}
function openPortal() { router.post(route('nutritionist.billing.portal')); }

const usagePercent = computed(() => {
    if (!props.planData.client_limit) return 0;
    return Math.min(100, Math.round((props.planData.active_clients / props.planData.client_limit) * 100));
});

function formatDate(d: string | null) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'long', year: 'numeric' });
}

// Billing info form
const billingForm = useForm({
    business_name: props.billingInfo.business_name ?? '',
    tax_id:        props.billingInfo.tax_id ?? '',
    address_line1: props.billingInfo.address_line1 ?? '',
    city:          props.billingInfo.city ?? '',
    postal_code:   props.billingInfo.postal_code ?? '',
    country:       props.billingInfo.country ?? 'IT',
    sdi_code:      props.billingInfo.sdi_code ?? '',
    pec:           props.billingInfo.pec ?? '',
});

function saveBillingInfo() {
    billingForm.post(route('nutritionist.billing.info.update'));
}

const billingOpen = ref(Object.values(props.billingInfo).some(v => !v));
const invoicesOpen = ref(false);
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Abbonamento</h1>
        </template>

        <div class="max-w-5xl space-y-6">
            <!-- No payment provider banner -->
            <div v-if="!anyProviderConfigured" class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 flex items-start gap-3">
                <AlertCircle class="h-5 w-5 text-amber-500 flex-shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-semibold text-amber-800">Pagamenti non ancora configurati</p>
                    <p class="text-xs text-amber-700 mt-0.5">Nessun provider di pagamento attivo. Contatta l'amministratore.</p>
                </div>
            </div>

            <!-- Flash messages -->
            <div v-if="$page.props.flash?.success" class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                {{ $page.props.flash.error }}
            </div>

            <!-- Current plan card -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Piano attuale</p>
                        <div class="flex items-center gap-3">
                            <h2 class="text-2xl font-bold text-gray-900">{{ planData.name }}</h2>
                            <span class="rounded-full px-3 py-0.5 text-xs font-semibold" :class="colorClasses(planData.key, 'badge')">
                                {{ planData.key === 'free' ? 'Gratuito' : 'Attivo' }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            <Users class="inline h-3.5 w-3.5 mr-1" />
                            {{ planData.active_clients }}{{ planData.client_limit ? ' / ' + planData.client_limit : '' }} clienti attivi{{ !planData.client_limit ? ' (illimitati)' : '' }}
                        </p>
                        <!-- Next billing date -->
                        <p v-if="nextBillingDate" class="mt-1.5 flex items-center gap-1.5 text-xs text-gray-500">
                            <Calendar class="h-3.5 w-3.5" />
                            Prossimo addebito: <strong class="text-gray-700">{{ formatDate(nextBillingDate) }}</strong>
                        </p>
                    </div>
                    <div v-if="planData.key !== 'free' && paymentProviders.stripe">
                        <button @click="openPortal" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
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
                        <div class="h-2 rounded-full transition-all" :class="usagePercent >= 90 ? 'bg-red-500' : usagePercent >= 70 ? 'bg-yellow-500' : 'bg-primary-500'" :style="{ width: usagePercent + '%' }" />
                    </div>
                    <p v-if="usagePercent >= 90" class="mt-1.5 text-xs text-red-600">Stai per raggiungere il limite del piano.</p>
                </div>
            </div>

            <!-- Billing info collapsible -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <button @click="billingOpen = !billingOpen" class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3">
                        <Receipt class="h-4.5 w-4.5 text-gray-400" style="width:18px;height:18px" />
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Dati di fatturazione</p>
                            <p class="text-xs text-gray-500">Nome/azienda, P.IVA, indirizzo, codice SDI</p>
                        </div>
                    </div>
                    <ChevronDown v-if="!billingOpen" class="h-4 w-4 text-gray-400" />
                    <ChevronUp v-else class="h-4 w-4 text-gray-400" />
                </button>

                <div v-if="billingOpen" class="border-t border-gray-100 px-6 py-5">
                    <form @submit.prevent="saveBillingInfo" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nome / Ragione sociale</label>
                                <input v-model="billingForm.business_name" type="text" placeholder="Mario Rossi / Studio Nutrizionistico Rossi"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500/30" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">P.IVA / Codice Fiscale</label>
                                <input v-model="billingForm.tax_id" type="text" placeholder="IT12345678901"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500/30" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Codice SDI</label>
                                <input v-model="billingForm.sdi_code" type="text" placeholder="ABCDEFG"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500/30" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">PEC</label>
                                <input v-model="billingForm.pec" type="email" placeholder="studio@pec.it"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500/30" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Indirizzo</label>
                                <input v-model="billingForm.address_line1" type="text" placeholder="Via Roma 1"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500/30" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Città</label>
                                <input v-model="billingForm.city" type="text" placeholder="Milano"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500/30" />
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">CAP</label>
                                    <input v-model="billingForm.postal_code" type="text" placeholder="20100"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500/30" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Paese</label>
                                    <select v-model="billingForm.country" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-primary-500 focus:outline-none">
                                        <option value="IT">Italia</option>
                                        <option value="CH">Svizzera</option>
                                        <option value="DE">Germania</option>
                                        <option value="FR">Francia</option>
                                        <option value="ES">Spagna</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" :disabled="billingForm.processing"
                                class="rounded-lg bg-primary-600 hover:bg-primary-700 disabled:opacity-50 px-4 py-2 text-sm font-semibold text-white transition">
                                Salva dati di fatturazione
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Plans grid -->
            <div>
                <h2 class="text-base font-semibold text-gray-900 mb-4">Tutti i piani</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="key in planKeys" :key="key"
                        class="relative rounded-xl border bg-white p-5 flex flex-col"
                        :class="[colorClasses(key, 'border'), isCurrentPlan(key) ? 'shadow-md' : '']">

                        <div v-if="isCurrentPlan(key)" class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full px-3 py-0.5 text-xs font-semibold bg-primary-600 text-white whitespace-nowrap">
                            Piano attuale
                        </div>

                        <div class="flex items-center gap-2 mb-3">
                            <component :is="planIcons[key]" class="h-5 w-5" :class="colorClasses(key, 'check')" />
                            <span class="font-semibold text-gray-900">{{ plans[key].name }}</span>
                        </div>

                        <div class="mb-4">
                            <span class="text-2xl font-bold text-gray-900">{{ plans[key].price === 0 ? 'Gratis' : '€' + plans[key].price }}</span>
                            <span v-if="plans[key].price > 0" class="text-sm text-gray-400">/mese</span>
                        </div>

                        <ul class="flex-1 space-y-1.5 mb-5">
                            <li v-for="feature in plans[key].features" :key="feature" class="flex items-start gap-1.5 text-xs text-gray-600">
                                <Check class="h-3.5 w-3.5 flex-shrink-0 mt-0.5" :class="colorClasses(key, 'check')" />
                                {{ feature }}
                            </li>
                        </ul>

                        <div class="space-y-2">
                            <div v-if="isCurrentPlan(key)" class="rounded-lg border border-gray-200 px-4 py-2 text-center text-xs font-medium text-gray-400">
                                Piano corrente
                            </div>
                            <template v-else-if="isUpgrade(key) && key !== 'free'">
                                <div v-if="!anyProviderConfigured" class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-2 text-center text-xs text-gray-400">
                                    Pagamenti non configurati
                                </div>
                                <template v-else>
                                    <button v-if="paymentProviders.stripe && plans[key].stripe_price_id"
                                        @click="checkout(key, 'stripe')"
                                        class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg px-4 py-2 text-xs font-semibold transition"
                                        :class="colorClasses(key, 'button')">
                                        <ArrowUpCircle class="h-3.5 w-3.5" />
                                        Fai upgrade a {{ plans[key].name }}
                                    </button>
                                    <button v-if="paymentProviders.paypal"
                                        @click="checkout(key, 'paypal')"
                                        class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg px-4 py-2 text-xs font-semibold transition bg-[#0070BA] text-white hover:bg-[#005EA6]">
                                        <ArrowUpCircle class="h-3.5 w-3.5" />
                                        PayPal — {{ plans[key].name }}
                                    </button>
                                </template>
                            </template>
                            <div v-else-if="key === 'free' && !isCurrentPlan(key)" class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-2 text-center text-xs text-gray-400">
                                Downgrade via portale
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction history collapsible -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <button @click="invoicesOpen = !invoicesOpen" class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3">
                        <FileText class="h-4.5 w-4.5 text-gray-400" style="width:18px;height:18px" />
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Storico transazioni</p>
                            <p class="text-xs text-gray-500">{{ invoices.length > 0 ? invoices.length + ' fatture' : 'Nessuna transazione ancora' }}</p>
                        </div>
                    </div>
                    <ChevronDown v-if="!invoicesOpen" class="h-4 w-4 text-gray-400" />
                    <ChevronUp v-else class="h-4 w-4 text-gray-400" />
                </button>

                <div v-if="invoicesOpen" class="border-t border-gray-100">
                    <div v-if="invoices.length === 0" class="px-6 py-8 text-center text-sm text-gray-400">
                        Nessuna transazione registrata.
                    </div>
                    <table v-else class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">N° fattura</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Data</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Importo</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Stato</th>
                                <th class="text-right px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="inv in invoices" :key="inv.id" class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-mono text-xs text-gray-700">{{ inv.number || inv.id }}</td>
                                <td class="px-4 py-3 text-xs text-gray-600">{{ formatDate(inv.date) }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ inv.total }}</td>
                                <td class="px-4 py-3">
                                    <span :class="['inline-flex rounded-full px-2 py-0.5 text-xs font-semibold', inv.status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600']">
                                        {{ inv.status === 'paid' ? 'Pagata' : inv.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <a v-if="inv.pdf_url" :href="inv.pdf_url" target="_blank" class="text-xs text-primary-600 hover:text-primary-700 font-medium">PDF</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
