<script setup lang="ts">
import DevLayout from '@/Layouts/DevLayout.vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ArrowLeft, UserCog, Users, UtensilsCrossed, CalendarDays, Activity, CreditCard, Gift, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    nutritionist: any;
    plans: any[];
    appointments: any[];
    recentActivity: any[];
    giftedPlans: any[];
}>();

const page = usePage();
const flash = computed(() => (page.props as any).flash ?? {});

const activeTab = ref<'plans' | 'appointments' | 'activity' | 'billing'>('plans');

function impersonate() {
    if (!confirm('Vuoi impersonificare questo nutrizionista?')) return;
    router.post(route('dev.impersonate', props.nutritionist.id));
}

function formatDate(d: string | null) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function formatMoney(cents: number) {
    return new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(cents / 100);
}

function planBadge(plan: string) {
    const map: Record<string, string> = {
        free: 'bg-gray-700 text-gray-300',
        starter: 'bg-blue-900/40 text-blue-300',
        pro: 'bg-emerald-900/40 text-emerald-300',
        business: 'bg-violet-900/40 text-violet-300',
    };
    return map[plan] ?? 'bg-gray-700 text-gray-300';
}

// Gift form
const giftForm = useForm({
    plan_key: 'starter',
    months: '1',
    note: '',
});

function submitGift() {
    giftForm.post(route('dev.nutritionists.gift', props.nutritionist.id), {
        onSuccess: () => giftForm.reset('note'),
    });
}

function removeGift(giftId: number) {
    if (!confirm('Rimuovere questo regalo?')) return;
    router.delete(route('dev.nutritionists.gift.destroy', { user: props.nutritionist.id, gift: giftId }));
}
</script>

<template>
    <DevLayout>
        <div class="space-y-6">
            <!-- Flash -->
            <div v-if="flash.success" class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ flash.success }}
            </div>

            <!-- Header -->
            <div class="flex items-start gap-4">
                <Link :href="route('dev.nutritionists')" class="mt-1 text-gray-500 hover:text-gray-200 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-2xl font-extrabold text-white">{{ nutritionist.name }} {{ nutritionist.last_name }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ nutritionist.email }}</p>
                </div>
                <button @click="impersonate" class="inline-flex items-center gap-2 rounded-lg bg-violet-600/20 border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-300 hover:bg-violet-600/30 transition">
                    <UserCog class="h-4 w-4" />
                    Impersona
                </button>
            </div>

            <!-- Info cards -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="rounded-xl bg-gray-900 border border-gray-800 p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Piano</p>
                    <span :class="['mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-sm font-bold', planBadge(nutritionist.plan)]">
                        {{ nutritionist.plan?.toUpperCase() }}
                    </span>
                    <p v-if="nutritionist.subscription_status" class="text-xs text-gray-500 mt-1">{{ nutritionist.subscription_status }}</p>
                </div>
                <div class="rounded-xl bg-gray-900 border border-gray-800 p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Clienti</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ nutritionist.clients_count }}</p>
                </div>
                <div class="rounded-xl bg-gray-900 border border-gray-800 p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Rate pagate</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ nutritionist.invoice_count }}</p>
                </div>
                <div class="rounded-xl bg-gray-900 border border-gray-800 p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Totale pagato</p>
                    <p class="text-lg font-bold text-emerald-400 mt-1">{{ formatMoney(nutritionist.total_paid_cents) }}</p>
                </div>
                <div class="rounded-xl bg-gray-900 border border-gray-800 p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Ultimo login</p>
                    <p class="text-sm text-gray-200 mt-1">{{ formatDate(nutritionist.last_login_at) }}</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-1 border-b border-gray-800 overflow-x-auto">
                <button v-for="tab in [
                    { key: 'plans', label: 'Piani', icon: UtensilsCrossed },
                    { key: 'appointments', label: 'Appuntamenti', icon: CalendarDays },
                    { key: 'activity', label: 'Activity', icon: Activity },
                    { key: 'billing', label: 'Pagamenti & Regali', icon: CreditCard },
                ]"
                    :key="tab.key"
                    @click="activeTab = tab.key as any"
                    :class="['flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium transition border-b-2 -mb-px whitespace-nowrap', activeTab === tab.key ? 'border-violet-500 text-violet-300' : 'border-transparent text-gray-500 hover:text-gray-300']">
                    <component :is="tab.icon" class="h-4 w-4" />
                    {{ tab.label }}
                </button>
            </div>

            <!-- Piani -->
            <div v-if="activeTab === 'plans'" class="rounded-xl bg-gray-900 border border-gray-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Titolo</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Cliente</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Stato</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Creato</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <tr v-for="p in plans" :key="p.id" class="hover:bg-gray-800/40">
                            <td class="px-5 py-3 text-gray-200 font-medium">{{ p.title }}</td>
                            <td class="px-4 py-3 text-gray-400 hidden md:table-cell">{{ p.client?.user?.name }} {{ p.client?.user?.last_name }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold', p.status === 'active' ? 'bg-emerald-900/40 text-emerald-300' : 'bg-gray-700 text-gray-400']">{{ p.status }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs hidden lg:table-cell">{{ formatDate(p.created_at) }}</td>
                        </tr>
                        <tr v-if="!plans.length"><td colspan="4" class="px-5 py-8 text-center text-gray-500">Nessun piano</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Appuntamenti -->
            <div v-if="activeTab === 'appointments'" class="rounded-xl bg-gray-900 border border-gray-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Cliente</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Data/ora</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Stato</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <tr v-for="a in appointments" :key="a.id" class="hover:bg-gray-800/40">
                            <td class="px-5 py-3 text-gray-200">{{ a.client?.user?.name }} {{ a.client?.user?.last_name }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ formatDate(a.starts_at) }}</td>
                            <td class="px-4 py-3">
                                <span :class="['inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold', a.status === 'confirmed' ? 'bg-emerald-900/40 text-emerald-300' : 'bg-gray-700 text-gray-400']">{{ a.status }}</span>
                            </td>
                        </tr>
                        <tr v-if="!appointments.length"><td colspan="3" class="px-5 py-8 text-center text-gray-500">Nessun appuntamento</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Activity -->
            <div v-if="activeTab === 'activity'" class="rounded-xl bg-gray-900 border border-gray-800 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Evento</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Soggetto</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <tr v-for="a in recentActivity" :key="a.id" class="hover:bg-gray-800/40">
                            <td class="px-5 py-3 font-mono text-xs text-violet-300">{{ a.event || a.description }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs hidden md:table-cell">{{ a.subject_type?.split('\\').pop() }} #{{ a.subject_id }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ formatDate(a.created_at) }}</td>
                        </tr>
                        <tr v-if="!recentActivity.length"><td colspan="3" class="px-5 py-8 text-center text-gray-500">Nessuna attività registrata</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Billing & Gifts tab -->
            <div v-if="activeTab === 'billing'" class="space-y-5">

                <!-- Regalo piano -->
                <div class="rounded-xl bg-gray-900 border border-gray-800 overflow-hidden">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-800">
                        <Gift class="h-4.5 w-4.5 text-violet-400" style="width:18px;height:18px" />
                        <h3 class="text-sm font-bold text-white">Regala un piano</h3>
                    </div>
                    <form @submit.prevent="submitGift" class="p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1.5">Piano</label>
                                <select v-model="giftForm.plan_key" class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-200 focus:border-violet-500 focus:outline-none">
                                    <option value="starter">Starter</option>
                                    <option value="pro">Pro</option>
                                    <option value="business">Business</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1.5">Durata</label>
                                <select v-model="giftForm.months" class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-200 focus:border-violet-500 focus:outline-none">
                                    <option value="1">1 mese</option>
                                    <option value="3">3 mesi</option>
                                    <option value="6">6 mesi</option>
                                    <option value="12">12 mesi</option>
                                    <option value="unlimited">Illimitato</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1.5">Nota (opzionale)</label>
                                <input v-model="giftForm.note" type="text" placeholder="es. beta tester"
                                    class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-gray-200 placeholder-gray-600 focus:border-violet-500 focus:outline-none" />
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" :disabled="giftForm.processing"
                                class="inline-flex items-center gap-2 rounded-lg bg-violet-600 hover:bg-violet-700 disabled:opacity-50 px-4 py-2 text-sm font-semibold text-white transition">
                                <Gift class="h-4 w-4" />
                                Regala piano
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Regali attivi/storici -->
                <div class="rounded-xl bg-gray-900 border border-gray-800 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-800">
                        <h3 class="text-sm font-bold text-white">Regali assegnati</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-800">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Piano</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Scade</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Nota</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Stato</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            <tr v-for="g in giftedPlans" :key="g.id" class="hover:bg-gray-800/30">
                                <td class="px-6 py-3">
                                    <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold', planBadge(g.plan_key)]">
                                        {{ g.plan_key.toUpperCase() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400">{{ g.expires_at ? formatDate(g.expires_at) : '∞ Illimitato' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500 hidden md:table-cell">{{ g.note || '—' }}</td>
                                <td class="px-4 py-3">
                                    <span :class="['inline-flex rounded-full px-2 py-0.5 text-xs font-semibold', g.is_active ? 'bg-emerald-900/40 text-emerald-300' : 'bg-gray-700 text-gray-500']">
                                        {{ g.is_active ? 'Attivo' : 'Scaduto' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button v-if="g.is_active" @click="removeGift(g.id)" class="text-red-500/60 hover:text-red-400 transition">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!giftedPlans.length">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-600">Nessun regalo assegnato</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Payment stats card -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Rate pagate (Stripe)</p>
                        <p class="text-3xl font-bold text-white">{{ nutritionist.invoice_count }}</p>
                    </div>
                    <div class="rounded-xl bg-gray-900 border border-gray-800 p-5">
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Totale incassato</p>
                        <p class="text-3xl font-bold text-emerald-400">{{ formatMoney(nutritionist.total_paid_cents) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </DevLayout>
</template>
