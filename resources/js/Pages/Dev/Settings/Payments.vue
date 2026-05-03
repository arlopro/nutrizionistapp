<script setup lang="ts">
import DevLayout from '@/Layouts/DevLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { CheckCircle, XCircle, AlertCircle, Eye, EyeOff, RefreshCw, Save, Copy, Check } from 'lucide-vue-next';

interface FieldMeta {
    label: string;
    is_secret: boolean;
    filled: boolean;
    masked: string;
}

interface ProviderData {
    verified_at: string | null;
    last_verified_error: string | null;
    fields: Record<string, FieldMeta>;
}

const props = defineProps<{
    stripe: ProviderData;
    paypal: ProviderData;
    webhookUrls: { stripe: string; paypal: string };
}>();

const page = usePage();
const flash = computed(() => (page.props as any).flash ?? {});

// Show/hide toggling for secret fields
const revealed = ref<Record<string, boolean>>({});
function toggleReveal(key: string) {
    revealed.value[key] = !revealed.value[key];
}

// Stripe form
const stripeForm = useForm(
    Object.fromEntries(
        Object.keys(props.stripe.fields).map(k => [k, ''])
    )
);

// PayPal form
const paypalForm = useForm({
    ...Object.fromEntries(
        Object.keys(props.paypal.fields).filter(k => k !== 'mode').map(k => [k, ''])
    ),
    mode: props.paypal.fields.mode?.masked || 'sandbox',
});

function submitStripe() {
    stripeForm.post(route('dev.settings.payments.update', 'stripe'), {
        onSuccess: () => stripeForm.reset(),
    });
}

function submitPaypal() {
    paypalForm.post(route('dev.settings.payments.update', 'paypal'), {
        onSuccess: () => {
            const mode = paypalForm.mode;
            paypalForm.reset();
            paypalForm.mode = mode;
        },
    });
}

function testStripe() {
    useForm({}).post(route('dev.settings.payments.test', 'stripe'));
}

function testPaypal() {
    useForm({}).post(route('dev.settings.payments.test', 'paypal'));
}

function formatDate(d: string | null) {
    if (!d) return null;
    return new Date(d).toLocaleString('it-IT', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

const copied = ref<string | null>(null);
async function copyUrl(key: string, url: string) {
    await navigator.clipboard.writeText(url);
    copied.value = key;
    setTimeout(() => { copied.value = null; }, 2000);
}
</script>

<template>
    <DevLayout>
        <div class="space-y-6 max-w-4xl">
            <div>
                <h1 class="text-2xl font-extrabold text-white">Impostazioni Pagamenti</h1>
                <p class="text-sm text-gray-500 mt-0.5">Configura le credenziali Stripe e PayPal. I valori sono cifrati nel database.</p>
            </div>

            <!-- Flash messages -->
            <div v-if="flash.success" class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300 flex items-center gap-2">
                <CheckCircle class="h-4 w-4 flex-shrink-0" />
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300 flex items-center gap-2">
                <XCircle class="h-4 w-4 flex-shrink-0" />
                {{ flash.error }}
            </div>

            <!-- Stripe card -->
            <div class="rounded-2xl border border-gray-800 bg-gray-900 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-xl bg-[#635BFF]/20 flex items-center justify-center">
                            <span class="text-[#635BFF] font-bold text-xs">S</span>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-white">Stripe</h2>
                            <p class="text-xs text-gray-500">Abbonamenti ricorrenti con carta di credito</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span v-if="stripe.verified_at" class="flex items-center gap-1.5 text-xs text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-full px-2.5 py-1">
                            <CheckCircle class="h-3 w-3" />
                            Verificato {{ formatDate(stripe.verified_at) }}
                        </span>
                        <span v-else-if="stripe.last_verified_error" class="flex items-center gap-1.5 text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-full px-2.5 py-1">
                            <XCircle class="h-3 w-3" />
                            Errore
                        </span>
                        <span v-else class="flex items-center gap-1.5 text-xs text-gray-500 bg-gray-800 border border-gray-700 rounded-full px-2.5 py-1">
                            <AlertCircle class="h-3 w-3" />
                            Non configurato
                        </span>
                        <button @click="testStripe" type="button"
                            class="flex items-center gap-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 border border-gray-700 px-3 py-1.5 text-xs font-medium text-gray-300 transition">
                            <RefreshCw class="h-3.5 w-3.5" />
                            Testa
                        </button>
                    </div>
                </div>

                <div v-if="stripe.last_verified_error" class="mx-6 mt-4 rounded-lg bg-red-500/10 border border-red-500/20 px-3 py-2 text-xs text-red-400">
                    {{ stripe.last_verified_error }}
                </div>

                <form @submit.prevent="submitStripe" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div v-for="(meta, key) in stripe.fields" :key="key">
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                {{ meta.label }}
                                <span v-if="meta.filled" class="ml-1.5 text-emerald-500">✓ impostato</span>
                            </label>
                            <div class="relative">
                                <input
                                    v-model="(stripeForm as any)[key]"
                                    :type="meta.is_secret && !revealed[`stripe_${key}`] ? 'password' : 'text'"
                                    :placeholder="meta.filled ? meta.masked : `Inserisci ${meta.label.toLowerCase()}`"
                                    class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-200 placeholder-gray-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500/30 pr-9"
                                />
                                <button v-if="meta.is_secret" type="button"
                                    @click="toggleReveal(`stripe_${key}`)"
                                    class="absolute right-2.5 top-2.5 text-gray-500 hover:text-gray-300">
                                    <Eye v-if="!revealed[`stripe_${key}`]" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Stripe webhook URL hint -->
                    <div class="rounded-lg border border-gray-700 bg-gray-800/50 p-4 space-y-2">
                        <p class="text-xs font-semibold text-gray-300">URL Webhook da registrare su Stripe</p>
                        <div class="flex items-center gap-2">
                            <code class="flex-1 rounded bg-gray-900 px-3 py-2 text-xs text-violet-300 font-mono break-all">{{ webhookUrls.stripe }}</code>
                            <button type="button" @click="copyUrl('stripe', webhookUrls.stripe)"
                                class="flex-shrink-0 flex items-center gap-1.5 rounded-lg bg-gray-700 hover:bg-gray-600 px-3 py-2 text-xs font-medium text-gray-300 transition">
                                <Check v-if="copied === 'stripe'" class="h-3.5 w-3.5 text-emerald-400" />
                                <Copy v-else class="h-3.5 w-3.5" />
                                {{ copied === 'stripe' ? 'Copiato' : 'Copia' }}
                            </button>
                        </div>
                        <div class="text-xs text-gray-500 space-y-1 pt-1">
                            <p>1. Vai su <strong class="text-gray-400">dashboard.stripe.com → Developers → Webhooks → Add endpoint</strong></p>
                            <p>2. Incolla l'URL qui sopra come endpoint URL</p>
                            <p>3. Seleziona gli eventi: <code class="text-gray-400">customer.subscription.*</code>, <code class="text-gray-400">invoice.payment_succeeded</code>, <code class="text-gray-400">invoice.payment_failed</code></p>
                            <p>4. Dopo la creazione clicca sul webhook → <strong class="text-gray-400">Signing secret → Reveal</strong> → copia il <code class="text-violet-400">whsec_...</code> qui sopra nel campo "Webhook Secret"</p>
                            <p class="text-amber-500/80">⚠ In locale usa invece: <code class="text-amber-400">stripe listen --forward-to {{ webhookUrls.stripe }}</code></p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" :disabled="stripeForm.processing"
                            class="flex items-center gap-2 rounded-lg bg-violet-600 hover:bg-violet-700 disabled:opacity-50 px-4 py-2 text-sm font-semibold text-white transition">
                            <Save class="h-4 w-4" />
                            Salva Stripe
                        </button>
                    </div>
                </form>
            </div>

            <!-- PayPal card -->
            <div class="rounded-2xl border border-gray-800 bg-gray-900 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-xl bg-[#003087]/30 flex items-center justify-center">
                            <span class="text-[#009cde] font-bold text-xs">PP</span>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-white">PayPal</h2>
                            <p class="text-xs text-gray-500">Abbonamenti ricorrenti via PayPal Subscriptions API</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span v-if="paypal.verified_at" class="flex items-center gap-1.5 text-xs text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-full px-2.5 py-1">
                            <CheckCircle class="h-3 w-3" />
                            Verificato {{ formatDate(paypal.verified_at) }}
                        </span>
                        <span v-else-if="paypal.last_verified_error" class="flex items-center gap-1.5 text-xs text-red-400 bg-red-500/10 border border-red-500/20 rounded-full px-2.5 py-1">
                            <XCircle class="h-3 w-3" />
                            Errore
                        </span>
                        <span v-else class="flex items-center gap-1.5 text-xs text-gray-500 bg-gray-800 border border-gray-700 rounded-full px-2.5 py-1">
                            <AlertCircle class="h-3 w-3" />
                            Non configurato
                        </span>
                        <button @click="testPaypal" type="button"
                            class="flex items-center gap-1.5 rounded-lg bg-gray-800 hover:bg-gray-700 border border-gray-700 px-3 py-1.5 text-xs font-medium text-gray-300 transition">
                            <RefreshCw class="h-3.5 w-3.5" />
                            Testa
                        </button>
                    </div>
                </div>

                <div v-if="paypal.last_verified_error" class="mx-6 mt-4 rounded-lg bg-red-500/10 border border-red-500/20 px-3 py-2 text-xs text-red-400">
                    {{ paypal.last_verified_error }}
                </div>

                <form @submit.prevent="submitPaypal" class="p-6 space-y-4">
                    <!-- Mode selector -->
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Modalità</label>
                        <select v-model="paypalForm.mode"
                            class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-200 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500/30">
                            <option value="sandbox">Sandbox (test)</option>
                            <option value="live">Live (produzione)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <template v-for="(meta, key) in paypal.fields" :key="key">
                            <div v-if="key !== 'mode'">
                                <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                    {{ meta.label }}
                                    <span v-if="meta.filled" class="ml-1.5 text-emerald-500">✓ impostato</span>
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="(paypalForm as any)[key]"
                                        :type="meta.is_secret && !revealed[`paypal_${String(key)}`] ? 'password' : 'text'"
                                        :placeholder="meta.filled ? meta.masked : `Inserisci ${meta.label.toLowerCase()}`"
                                        class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-200 placeholder-gray-600 focus:border-violet-500 focus:outline-none focus:ring-1 focus:ring-violet-500/30 pr-9"
                                    />
                                    <button v-if="meta.is_secret" type="button"
                                        @click="toggleReveal(`paypal_${String(key)}`)"
                                        class="absolute right-2.5 top-2.5 text-gray-500 hover:text-gray-300">
                                        <Eye v-if="!revealed[`paypal_${String(key)}`]" class="h-4 w-4" />
                                        <EyeOff v-else class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="rounded-lg bg-gray-800/60 border border-gray-700 px-4 py-3 text-xs text-gray-400 space-y-1">
                        <p class="font-medium text-gray-300">Come ottenere i Plan ID PayPal:</p>
                        <p>1. Accedi a developer.paypal.com → Sandbox (o Live) → My Apps</p>
                        <p>2. Crea i prodotti e i piani di abbonamento per Starter, Pro e Business</p>
                        <p>3. Copia i <code class="text-violet-400">P-XXXXXXXX</code> corrispondenti</p>
                    </div>

                    <!-- PayPal webhook URL hint -->
                    <div class="rounded-lg border border-gray-700 bg-gray-800/50 p-4 space-y-2">
                        <p class="text-xs font-semibold text-gray-300">URL Webhook da registrare su PayPal</p>
                        <div class="flex items-center gap-2">
                            <code class="flex-1 rounded bg-gray-900 px-3 py-2 text-xs text-violet-300 font-mono break-all">{{ webhookUrls.paypal }}</code>
                            <button type="button" @click="copyUrl('paypal', webhookUrls.paypal)"
                                class="flex-shrink-0 flex items-center gap-1.5 rounded-lg bg-gray-700 hover:bg-gray-600 px-3 py-2 text-xs font-medium text-gray-300 transition">
                                <Check v-if="copied === 'paypal'" class="h-3.5 w-3.5 text-emerald-400" />
                                <Copy v-else class="h-3.5 w-3.5" />
                                {{ copied === 'paypal' ? 'Copiato' : 'Copia' }}
                            </button>
                        </div>
                        <div class="text-xs text-gray-500 space-y-1 pt-1">
                            <p>1. Vai su <strong class="text-gray-400">developer.paypal.com → My Apps & Credentials → seleziona la tua app</strong></p>
                            <p>2. Scorri fino a <strong class="text-gray-400">Webhooks → Add Webhook</strong></p>
                            <p>3. Incolla l'URL qui sopra e seleziona gli eventi:</p>
                            <p class="pl-3 text-gray-600"><code class="text-gray-400">BILLING.SUBSCRIPTION.ACTIVATED</code>, <code class="text-gray-400">BILLING.SUBSCRIPTION.CANCELLED</code>, <code class="text-gray-400">BILLING.SUBSCRIPTION.EXPIRED</code>, <code class="text-gray-400">BILLING.SUBSCRIPTION.SUSPENDED</code>, <code class="text-gray-400">PAYMENT.SALE.COMPLETED</code></p>
                            <p>4. Copia il <strong class="text-gray-400">Webhook ID</strong> generato e incollalo nel campo "Webhook ID" qui sopra</p>
                            <p class="text-amber-500/80">⚠ I webhook PayPal richiedono un URL pubblico — non funzionano in locale senza un tunnel (es. ngrok).</p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" :disabled="paypalForm.processing"
                            class="flex items-center gap-2 rounded-lg bg-violet-600 hover:bg-violet-700 disabled:opacity-50 px-4 py-2 text-sm font-semibold text-white transition">
                            <Save class="h-4 w-4" />
                            Salva PayPal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DevLayout>
</template>
