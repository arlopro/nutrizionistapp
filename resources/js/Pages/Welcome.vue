<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import {
    ArrowRight, Check, Users, UtensilsCrossed, CalendarDays, ClipboardCheck,
    BarChart3, FileDown, Apple, Zap, Star, Building2,
    ChevronDown, Menu, X, TrendingUp, ShieldCheck, Smartphone, Clock,
    Target, Sparkles, Play,
} from 'lucide-vue-next';

const mobileMenuOpen = ref(false);
const openFaq = ref<number | null>(null);

// ── Scroll-reveal (Intersection Observer) ──────────────────────────
function useReveal() {
    onMounted(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12 }
        );
        document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));
    });
}
useReveal();

// ── Counter animation ──────────────────────────────────────────────
const statsVisible = ref(false);
const counters = ref([0, 0, 0, 0]);
const statsTargets = [500, 12000, 98, 4.9];
const statsLabels = ['Nutrizionisti attivi', 'Pazienti gestiti', 'Tasso soddisfazione %', 'Valutazione media'];
const statsDisplay = ['500+', '12k+', '98%', '4.9★'];

onMounted(() => {
    const el = document.querySelector('#stats-section');
    if (!el) return;
    const obs = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !statsVisible.value) {
            statsVisible.value = true;
            statsTargets.forEach((target, i) => {
                const duration = 1600;
                const step = 16;
                const steps = duration / step;
                let current = 0;
                const increment = target / steps;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) { current = target; clearInterval(timer); }
                    counters.value[i] = i === 3 ? parseFloat(current.toFixed(1)) : Math.floor(current);
                }, step);
            });
        }
    }, { threshold: 0.4 });
    obs.observe(el);
});

function formatCounter(val: number, i: number) {
    if (i === 0) return val >= 500 ? '500+' : val.toString();
    if (i === 1) return val >= 12000 ? '12k+' : val >= 1000 ? Math.floor(val / 1000) + 'k+' : val.toString();
    if (i === 2) return val + '%';
    return val.toFixed(1) + '★';
}

// ── Data ──────────────────────────────────────────────────────────
const features = [
    { icon: Users, color: 'blue', title: 'CRM Pazienti Completo', description: 'Anagrafica dettagliata con BMI calcolato, allergie, intolleranze, obiettivi, livello attività e storico misurazioni. Tutto in un profilo.', items: ['Scheda cliente completa', 'Calcolo BMI automatico', 'Storico peso e misurazioni', 'Grafico andamento nel tempo'] },
    { icon: UtensilsCrossed, color: 'green', title: 'Piani Nutrizionali Pro', description: 'Crea piani settimanali strutturati o in testo libero. Calcolo macro automatico, template riutilizzabili, export PDF con il tuo logo.', items: ['Calcolo calorie e macro', 'Modalità libera e strutturata', 'Template riutilizzabili', 'Export PDF professionale'] },
    { icon: CalendarDays, color: 'purple', title: 'Calendario Appuntamenti', description: 'Vista calendario mensile con gestione visite, controlli e consulti online. Filtra per cliente, stato e tipologia.', items: ['Vista calendario mensile', 'Prima visita, follow-up, online', 'Gestione stati (confermato, annullato)', 'Filtri avanzati'] },
    { icon: ClipboardCheck, color: 'orange', title: 'Monitoraggio & Progressi', description: 'I tuoi clienti registrano peso, misurazioni e foto. Tu monitori energia, umore, sonno e adesione al piano.', items: ['Misurazioni corporee', 'Upload foto progresso', 'Energia, umore e sonno', '% adesione al piano'] },
    { icon: Apple, color: 'red', title: 'Libreria Alimenti', description: 'Database integrato con oltre 60 alimenti italiani e la possibilità di creare alimenti e ricette personalizzati.', items: ['60+ alimenti italiani', 'Alimenti custom', 'Gestione ricette', 'Macro per porzione'] },
    { icon: BarChart3, color: 'indigo', title: 'Area Cliente Dedicata', description: 'I tuoi pazienti accedono con le proprie credenziali, vedono il piano, spuntano i pasti e registrano i monitoraggi.', items: ['Piano del giorno', 'Spunta pasti completati', 'Dashboard progressi', 'Storico monitoraggi'] },
];

const colorMap: Record<string, string> = {
    blue: 'bg-blue-50 text-blue-600',
    green: 'bg-green-50 text-green-600',
    purple: 'bg-purple-50 text-purple-600',
    orange: 'bg-orange-50 text-orange-600',
    red: 'bg-red-50 text-red-600',
    indigo: 'bg-indigo-50 text-indigo-600',
};

const plans = [
    { key: 'free', name: 'Free', price: 0, description: 'Per chi inizia', icon: Users, color: 'gray', features: ['Fino a 5 clienti attivi', 'Piani nutrizionali', 'Monitoraggio clienti', 'Appuntamenti', 'Area cliente'], cta: 'Inizia gratis', highlighted: false },
    { key: 'starter', name: 'Starter', price: 12, description: 'Per studi in crescita', icon: Zap, color: 'blue', features: ['Fino a 20 clienti attivi', 'Tutte le funzionalità core', 'Template piani illimitati', 'Export PDF', 'Supporto email'], cta: 'Inizia ora', highlighted: false },
    { key: 'pro', name: 'Pro', price: 24, description: 'Il più scelto', icon: Star, color: 'primary', features: ['Fino a 100 clienti attivi', 'Tutto di Starter', 'Tracking avanzato', 'Statistiche', 'Supporto prioritario'], cta: 'Prova Pro', highlighted: true },
    { key: 'business', name: 'Business', price: 49, description: 'Per grandi studi', icon: Building2, color: 'purple', features: ['Clienti illimitati', 'Tutto di Pro', 'Multi-nutrizionista (soon)', 'Statistiche avanzate (soon)', 'Supporto dedicato'], cta: 'Contattaci', highlighted: false },
];

const testimonials = [
    { name: 'Giulia Ferretti', role: 'Biologa Nutrizionista, Milano', avatar: 'GF', color: 'from-pink-400 to-pink-600', text: 'Ho triplicato il numero di clienti senza aumentare le ore di lavoro. I piani si creano in metà del tempo e i clienti adorano l\'area personale.' },
    { name: 'Marco Santini', role: 'Dietista, Roma', avatar: 'MS', color: 'from-blue-400 to-blue-600', text: 'L\'export PDF con il mio logo è un dettaglio che fa la differenza. I pazienti percepiscono subito la professionalità.' },
    { name: 'Sara Conti', role: 'Nutrizionista, Bologna', avatar: 'SC', color: 'from-purple-400 to-purple-600', text: 'I monitoraggi automatici mi permettono di seguire 50 clienti senza impazzire. Prima ci impiegavo ore, ora sono aggiornata in tempo reale.' },
];

const faqs = [
    { q: 'Posso provare l\'app prima di pagare?', a: 'Sì, il piano Free è gratuito per sempre e include fino a 5 clienti attivi. Non è richiesta carta di credito.' },
    { q: 'Posso cambiare piano in qualsiasi momento?', a: 'Assolutamente. Puoi fare upgrade o downgrade quando vuoi. Il cambio è immediato e la fatturazione viene ricalcolata proporzionalmente.' },
    { q: 'I dati dei miei clienti sono al sicuro?', a: 'I dati sono salvati su server sicuri con crittografia HTTPS. Non condividiamo nulla con terze parti. Siamo conformi al GDPR.' },
    { q: 'I clienti devono pagare qualcosa?', a: 'No. L\'accesso all\'area cliente è completamente gratuito per i tuoi pazienti. Paghi solo tu come nutrizionista.' },
    { q: 'Posso esportare i dati?', a: 'Sì. I piani nutrizionali si esportano in PDF con il tuo logo. Stiamo lavorando a ulteriori formati di export.' },
];

const steps = [
    { n: '01', title: 'Crea il tuo account', desc: 'Registrazione gratuita in meno di un minuto. Nessuna carta richiesta.' },
    { n: '02', title: 'Aggiungi i clienti', desc: 'Inserisci l\'anagrafica, obiettivi e dati fisici del paziente. Il sistema crea automaticamente le credenziali di accesso.' },
    { n: '03', title: 'Costruisci il piano', desc: 'Crea piani nutrizionali con calcolo macro automatico. Scegli tra inserimento strutturato o testo libero per ogni pasto.' },
    { n: '04', title: 'Monitora i progressi', desc: 'I clienti accedono alla loro area personale, registrano i monitoraggi e tu segui tutto in tempo reale.' },
];
</script>

<template>
    <Head title="NutrizionistApp — La piattaforma per nutrizionisti professionisti" />

    <div class="min-h-screen bg-white font-sans antialiased">

        <!-- ── NAVBAR ── -->
        <nav class="fixed top-0 inset-x-0 z-50 border-b border-white/10 bg-white/80 backdrop-blur-xl transition-all duration-300">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center animate-logo-spin-once">
                            <UtensilsCrossed class="text-white" style="width:18px;height:18px" />
                        </div>
                        <span class="text-lg font-extrabold tracking-tight text-gray-900">
                            Nutrizionist<span class="text-primary-600">App</span>
                        </span>
                    </div>
                    <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
                        <a href="#features" class="hover:text-gray-900 transition-colors duration-200">Funzionalità</a>
                        <a href="#how" class="hover:text-gray-900 transition-colors duration-200">Come funziona</a>
                        <a href="#pricing" class="hover:text-gray-900 transition-colors duration-200">Prezzi</a>
                        <a href="#faq" class="hover:text-gray-900 transition-colors duration-200">FAQ</a>
                    </div>
                    <div class="hidden md:flex items-center gap-3">
                        <Link :href="route('login')" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition">Accedi</Link>
                        <Link :href="route('register')" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 transition shadow-sm shadow-primary-500/30 hover:shadow-md hover:shadow-primary-500/40 hover:-translate-y-px active:translate-y-0 duration-150">
                            Inizia gratis →
                        </Link>
                    </div>
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden rounded-lg p-2 text-gray-500 hover:bg-gray-100 transition">
                        <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
                        <X v-else class="h-5 w-5" />
                    </button>
                </div>
            </div>
            <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-100 bg-white px-4 py-4 space-y-2 animate-slide-down">
                <a href="#features" class="block py-2 text-sm font-medium text-gray-700" @click="mobileMenuOpen=false">Funzionalità</a>
                <a href="#how" class="block py-2 text-sm font-medium text-gray-700" @click="mobileMenuOpen=false">Come funziona</a>
                <a href="#pricing" class="block py-2 text-sm font-medium text-gray-700" @click="mobileMenuOpen=false">Prezzi</a>
                <a href="#faq" class="block py-2 text-sm font-medium text-gray-700" @click="mobileMenuOpen=false">FAQ</a>
                <div class="pt-2 flex flex-col gap-2">
                    <Link :href="route('login')" class="block text-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700">Accedi</Link>
                    <Link :href="route('register')" class="block text-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white">Inizia gratis</Link>
                </div>
            </div>
        </nav>

        <!-- ── HERO ── -->
        <section class="relative pt-16 overflow-hidden">
            <!-- Background -->
            <div class="absolute inset-0 bg-gradient-to-b from-primary-950 via-primary-900 to-primary-800" />

            <!-- Animated blobs -->
            <div class="absolute top-20 left-1/4 w-96 h-96 rounded-full bg-primary-500/20 blur-3xl animate-blob" />
            <div class="absolute top-40 right-1/4 w-80 h-80 rounded-full bg-emerald-400/15 blur-3xl animate-blob" style="animation-delay: 2s" />
            <div class="absolute bottom-20 left-1/3 w-64 h-64 rounded-full bg-primary-400/15 blur-3xl animate-blob" style="animation-delay: 4s" />

            <!-- Grid -->
            <div class="absolute inset-0 opacity-10" style="background-image: linear-gradient(rgba(255,255,255,0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.08) 1px, transparent 1px); background-size: 60px 60px" />

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-20 pb-0">
                <!-- Badge -->
                <div class="flex justify-center mb-6 animate-fade-down" style="animation-delay:0.1s">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 backdrop-blur-sm">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary-400 animate-pulse" />
                        <span class="text-xs font-medium text-primary-200">Piattaforma SaaS per nutrizionisti · Made in Italy</span>
                    </div>
                </div>

                <!-- Headline -->
                <div class="text-center max-w-4xl mx-auto">
                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-white leading-[1.05] animate-fade-up" style="animation-delay:0.2s">
                        Il software che i<br/>
                        <span class="animate-gradient-text bg-gradient-to-r from-primary-300 via-emerald-300 to-primary-400 bg-clip-text text-transparent bg-[length:200%_auto]">
                            nutrizionisti
                        </span>
                        <br/>stavano aspettando
                    </h1>
                    <p class="mt-6 text-lg sm:text-xl text-primary-200 max-w-2xl mx-auto leading-relaxed animate-fade-up" style="animation-delay:0.35s">
                        Piani nutrizionali, CRM clienti, calendario appuntamenti e area paziente dedicata.<br class="hidden sm:block"/>
                        Tutto in un'unica piattaforma professionale.
                    </p>

                    <!-- CTAs -->
                    <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 animate-fade-up" style="animation-delay:0.5s">
                        <Link
                            :href="route('register')"
                            class="group inline-flex items-center gap-2.5 rounded-xl bg-white px-7 py-3.5 text-base font-bold text-primary-700 shadow-2xl shadow-black/20 hover:bg-primary-50 hover:shadow-primary-500/20 hover:-translate-y-1 active:translate-y-0 transition-all duration-200"
                        >
                            Inizia gratis oggi
                            <ArrowRight class="h-5 w-5 transition-transform duration-200 group-hover:translate-x-1" />
                        </Link>
                        <a
                            href="#features"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-7 py-3.5 text-base font-semibold text-white backdrop-blur-sm hover:bg-white/20 hover:-translate-y-1 transition-all duration-200"
                        >
                            <Play class="h-4 w-4 fill-white" />
                            Scopri le funzionalità
                        </a>
                    </div>

                    <!-- Social proof -->
                    <div class="mt-12 flex flex-wrap items-center justify-center gap-6 text-primary-300 animate-fade-up" style="animation-delay:0.65s">
                        <div class="flex items-center gap-2 hover:text-primary-100 transition-colors">
                            <ShieldCheck class="h-4 w-4" />
                            <span class="text-sm">GDPR compliant</span>
                        </div>
                        <div class="h-4 w-px bg-white/20" />
                        <div class="flex items-center gap-2 hover:text-primary-100 transition-colors">
                            <Smartphone class="h-4 w-4" />
                            <span class="text-sm">Mobile ready</span>
                        </div>
                        <div class="h-4 w-px bg-white/20" />
                        <div class="flex items-center gap-2 hover:text-primary-100 transition-colors">
                            <Clock class="h-4 w-4" />
                            <span class="text-sm">Setup in 2 minuti</span>
                        </div>
                        <div class="h-4 w-px bg-white/20" />
                        <div class="flex items-center gap-2 hover:text-primary-100 transition-colors">
                            <Target class="h-4 w-4" />
                            <span class="text-sm">Nessuna carta richiesta</span>
                        </div>
                    </div>
                </div>

                <!-- Dashboard mockup -->
                <div class="mt-16 relative max-w-5xl mx-auto animate-fade-up" style="animation-delay:0.8s">
                    <div class="absolute -inset-4 rounded-3xl bg-primary-400/20 blur-3xl animate-pulse-slow" />
                    <div class="relative rounded-2xl overflow-hidden border border-white/15 shadow-2xl shadow-black/40 animate-float">
                        <!-- Browser bar -->
                        <div class="bg-gray-900 px-4 py-3 flex items-center gap-2">
                            <div class="flex gap-1.5">
                                <div class="h-3 w-3 rounded-full bg-red-500/80" />
                                <div class="h-3 w-3 rounded-full bg-yellow-500/80" />
                                <div class="h-3 w-3 rounded-full bg-green-500/80" />
                            </div>
                            <div class="flex-1 mx-4">
                                <div class="rounded-md bg-gray-800 px-3 py-1 text-xs text-gray-400 text-center">
                                    app.nutrizionistapp.it/nutritionist/dashboard
                                </div>
                            </div>
                        </div>
                        <!-- App mockup -->
                        <div class="bg-gray-50 flex" style="min-height: 360px">
                            <!-- Sidebar -->
                            <div class="w-48 bg-white border-r border-gray-200 p-3 flex flex-col gap-1 flex-shrink-0">
                                <div class="flex items-center gap-2 px-2 py-2 mb-2">
                                    <div class="h-6 w-6 rounded-md bg-gradient-to-br from-primary-500 to-primary-700" />
                                    <span class="text-xs font-bold text-gray-900">NutrizionistApp</span>
                                </div>
                                <div v-for="(item, i) in ['Dashboard','Clienti','Alimenti','Ricette','Piani','Appuntamenti','Monitoraggio']" :key="i"
                                    :class="['flex items-center gap-2 rounded-lg px-2.5 py-2 text-xs transition-colors', i === 0 ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-gray-500 hover:bg-gray-50']">
                                    <div :class="['h-3 w-3 rounded-sm', i === 0 ? 'bg-primary-400' : 'bg-gray-200']" />
                                    {{ item }}
                                </div>
                            </div>
                            <!-- Main -->
                            <div class="flex-1 p-5 overflow-hidden">
                                <div class="text-sm font-bold text-gray-900 mb-4">Buongiorno, Dott.ssa Ferretti 👋</div>
                                <div class="grid grid-cols-4 gap-3 mb-4">
                                    <div v-for="(stat, i) in [{label:'Clienti attivi',val:'24',color:'text-primary-600'},{label:'Piani creati',val:'47',color:'text-blue-600'},{label:'Appuntamenti',val:'8',color:'text-purple-600'},{label:'Monitoraggi oggi',val:'3',color:'text-orange-600'}]" :key="i"
                                        class="rounded-xl bg-white border border-gray-100 p-3 shadow-sm hover:shadow-md transition-shadow">
                                        <div :class="['text-lg font-extrabold', stat.color]">{{ stat.val }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ stat.label }}</div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="rounded-xl bg-white border border-gray-100 p-3 shadow-sm">
                                        <div class="text-xs font-semibold text-gray-700 mb-2">Clienti recenti</div>
                                        <div v-for="(c, i) in [{name:'Francesca B.',kg:'68kg',bmi:'23.1'},{name:'Luca M.',kg:'85kg',bmi:'26.4'},{name:'Anna P.',kg:'58kg',bmi:'21.8'}]" :key="i"
                                            class="flex items-center gap-2 py-1.5 border-b border-gray-50 last:border-0">
                                            <div class="h-6 w-6 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">{{ c.name.charAt(0) }}</div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xs font-medium text-gray-800 truncate">{{ c.name }}</div>
                                                <div class="text-xs text-gray-400">{{ c.kg }} · BMI {{ c.bmi }}</div>
                                            </div>
                                            <div class="h-1.5 w-1.5 rounded-full bg-green-400 animate-pulse" />
                                        </div>
                                    </div>
                                    <div class="rounded-xl bg-white border border-gray-100 p-3 shadow-sm">
                                        <div class="text-xs font-semibold text-gray-700 mb-2">Andamento peso — Francesca</div>
                                        <div class="flex items-end gap-1 h-16">
                                            <div v-for="(h, i) in [55,60,52,65,58,70,62,75,68,72,66,78]" :key="i"
                                                class="flex-1 rounded-t bg-primary-400 opacity-80 animate-bar-grow"
                                                :style="{height: (h/78*100)+'%', animationDelay: i * 60 + 'ms'}" />
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-400 mt-1">
                                            <span>Gen</span><span>Giu</span><span>Dic</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wave -->
            <div class="relative -mb-1">
                <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                    <path d="M0 80V40C240 0 480 80 720 40C960 0 1200 80 1440 40V80H0Z" fill="white"/>
                </svg>
            </div>
        </section>

        <!-- ── STATS ── -->
        <section id="stats-section" class="py-16 bg-white">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div v-for="(label, i) in statsLabels" :key="label" class="reveal fade-up" :style="{transitionDelay: i * 100 + 'ms'}">
                        <div class="text-4xl font-extrabold text-gray-900 tabular-nums transition-all duration-300">
                            {{ statsVisible ? formatCounter(counters[i], i) : '0' }}
                        </div>
                        <div class="mt-1 text-sm text-gray-500">{{ label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── FEATURES ── -->
        <section id="features" class="py-24 bg-gray-50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 reveal fade-up">
                    <div class="inline-flex items-center gap-2 rounded-full bg-primary-100 px-3 py-1 text-xs font-semibold text-primary-700 mb-4">
                        <Sparkles class="h-3.5 w-3.5" />
                        Funzionalità complete
                    </div>
                    <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-tight">
                        Tutto ciò che serve<br/>al tuo studio
                    </h2>
                    <p class="mt-4 text-lg text-gray-500 max-w-2xl mx-auto">
                        Progettato da zero per i professionisti della nutrizione. Niente di superfluo, tutto quello che conta.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="(feat, i) in features"
                        :key="feat.title"
                        class="group relative rounded-2xl bg-white border border-gray-200 p-7 hover:border-primary-200 hover:shadow-xl hover:shadow-primary-500/5 hover:-translate-y-1 transition-all duration-300 reveal fade-up"
                        :style="{transitionDelay: (i % 3) * 80 + 'ms'}"
                    >
                        <!-- Hover glow -->
                        <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-primary-50/0 to-primary-100/0 group-hover:from-primary-50/50 group-hover:to-primary-100/20 transition-all duration-300 pointer-events-none" />
                        <div :class="['inline-flex rounded-xl p-3 mb-4 transition-transform duration-300 group-hover:scale-110', colorMap[feat.color]]">
                            <component :is="feat.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ feat.title }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed mb-4">{{ feat.description }}</p>
                        <ul class="space-y-1.5">
                            <li v-for="item in feat.items" :key="item" class="flex items-center gap-2 text-xs text-gray-600">
                                <Check class="h-3.5 w-3.5 text-primary-500 flex-shrink-0" />
                                {{ item }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── HOW IT WORKS ── -->
        <section id="how" class="py-24 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16 reveal fade-up">
                    <div class="inline-flex items-center gap-2 rounded-full bg-primary-100 px-3 py-1 text-xs font-semibold text-primary-700 mb-4">
                        <TrendingUp class="h-3.5 w-3.5" />
                        Semplice da usare
                    </div>
                    <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-tight">Come funziona</h2>
                    <p class="mt-4 text-lg text-gray-500">Quattro passi per trasformare il tuo studio</p>
                </div>

                <div class="relative">
                    <div class="absolute top-8 left-1/2 -translate-x-1/2 hidden lg:block w-3/4 h-0.5 bg-gradient-to-r from-transparent via-primary-200 to-transparent" />
                    <div class="grid gap-8 lg:grid-cols-4">
                        <div v-for="(step, i) in steps" :key="step.n"
                            class="relative text-center reveal fade-up"
                            :style="{transitionDelay: i * 120 + 'ms'}">
                            <div class="relative inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 text-2xl font-extrabold text-white shadow-lg shadow-primary-500/30 mb-5 hover:scale-110 hover:shadow-xl hover:shadow-primary-500/40 transition-all duration-200 cursor-default">
                                {{ step.n }}
                                <!-- Ping ring on first step -->
                                <span v-if="i === 0" class="absolute -inset-1 rounded-2xl border-2 border-primary-400 animate-ping-slow opacity-60" />
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ step.title }}</h3>
                            <p class="text-sm text-gray-500 leading-relaxed">{{ step.desc }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-16 text-center reveal fade-up">
                    <Link
                        :href="route('register')"
                        class="group inline-flex items-center gap-2 rounded-xl bg-primary-600 px-7 py-3.5 text-base font-bold text-white hover:bg-primary-700 hover:-translate-y-1 hover:shadow-lg hover:shadow-primary-500/30 transition-all duration-200"
                    >
                        Crea il tuo account gratuito
                        <ArrowRight class="h-5 w-5 transition-transform duration-200 group-hover:translate-x-1" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- ── PDF HIGHLIGHT ── -->
        <section class="py-24 bg-gradient-to-br from-primary-950 to-primary-900 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-96 h-96 rounded-full bg-primary-500/10 blur-3xl animate-blob" style="animation-delay:1s" />
            <div class="absolute bottom-0 left-0 w-80 h-80 rounded-full bg-emerald-400/10 blur-3xl animate-blob" style="animation-delay:3s" />
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div class="reveal fade-right">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-medium text-primary-200 mb-6">
                            <FileDown class="h-3.5 w-3.5" />
                            Export PDF professionale
                        </div>
                        <h2 class="text-4xl sm:text-5xl font-extrabold text-white leading-tight mb-5">
                            Piani che fanno la<br/>
                            <span class="text-primary-300 animate-gradient-text bg-gradient-to-r from-primary-300 via-emerald-300 to-primary-400 bg-clip-text text-transparent bg-[length:200%_auto]">differenza visiva</span>
                        </h2>
                        <p class="text-primary-200 text-lg leading-relaxed mb-8">
                            Esporta i piani nutrizionali in PDF con il tuo logo, la tua specializzazione e i dettagli del paziente. Un documento professionale che rafforza la tua identità.
                        </p>
                        <ul class="space-y-3 mb-8">
                            <li v-for="(item, i) in ['Logo studio personalizzato','Barra macro calorie/proteine/carboidrati/grassi','Dettaglio alimenti per ogni pasto','Note e istruzioni del nutrizionista']" :key="item"
                                class="flex items-center gap-3 text-primary-100 reveal fade-right"
                                :style="{transitionDelay: i * 80 + 'ms'}">
                                <div class="h-5 w-5 rounded-full bg-primary-500/30 flex items-center justify-center flex-shrink-0">
                                    <Check class="h-3 w-3 text-primary-300" />
                                </div>
                                {{ item }}
                            </li>
                        </ul>
                    </div>
                    <!-- PDF mockup -->
                    <div class="relative reveal fade-left">
                        <div class="absolute -inset-4 rounded-3xl bg-primary-400/10 blur-2xl" />
                        <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-white/10 hover:-translate-y-2 hover:shadow-3xl transition-all duration-300">
                            <div class="bg-gradient-to-r from-primary-600 to-primary-500 px-6 py-5 flex items-center justify-between">
                                <div>
                                    <div class="text-white font-extrabold text-lg">Dott.ssa Giulia Ferretti</div>
                                    <div class="text-primary-200 text-sm">Biologa Nutrizionista · Milano</div>
                                </div>
                                <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center text-white font-extrabold text-lg">GF</div>
                            </div>
                            <div class="bg-white p-5">
                                <div class="text-base font-bold text-gray-900 mb-1">Piano Ipocalorico — Francesca B.</div>
                                <div class="text-xs text-gray-400 mb-4">Dal 01/04/2026 al 30/06/2026 · Attivo</div>
                                <div class="grid grid-cols-4 gap-2 mb-4">
                                    <div v-for="macro in [{v:'1800',l:'kcal',c:'text-yellow-500'},{v:'140g',l:'Prot.',c:'text-blue-500'},{v:'180g',l:'Carb.',c:'text-orange-500'},{v:'60g',l:'Grassi',c:'text-purple-500'}]" :key="macro.l"
                                        class="rounded-lg border border-gray-100 p-2 text-center hover:border-primary-200 transition-colors">
                                        <div :class="['text-sm font-extrabold', macro.c]">{{ macro.v }}</div>
                                        <div class="text-xs text-gray-400">{{ macro.l }}</div>
                                    </div>
                                </div>
                                <div class="rounded-xl overflow-hidden border border-gray-200">
                                    <div class="bg-primary-600 text-white text-xs font-bold px-3 py-2 uppercase tracking-wider">Lunedì</div>
                                    <div v-for="(meal, i) in ['Colazione','Spuntino','Pranzo','Cena']" :key="i" class="flex items-center justify-between px-3 py-2 border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
                                        <span class="text-xs font-semibold text-primary-700">{{ meal }}</span>
                                        <div class="flex gap-1">
                                            <div class="h-1.5 rounded-full bg-gray-200" :style="{width: (20+i*15)+'px'}" />
                                            <div class="h-1.5 rounded-full bg-gray-200" :style="{width: (35-i*5)+'px'}" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── TESTIMONIALS ── -->
        <section class="py-24 bg-gray-50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14 reveal fade-up">
                    <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-tight">
                        Cosa dicono i professionisti
                    </h2>
                    <p class="mt-4 text-lg text-gray-500">Nutrizionisti reali, risultati reali</p>
                </div>
                <div class="grid gap-6 md:grid-cols-3">
                    <div
                        v-for="(t, i) in testimonials"
                        :key="t.name"
                        class="relative rounded-2xl bg-white border border-gray-200 p-7 shadow-sm hover:shadow-xl hover:shadow-gray-200/80 hover:-translate-y-2 transition-all duration-300 reveal fade-up"
                        :style="{transitionDelay: i * 100 + 'ms'}"
                    >
                        <div class="absolute top-5 right-6 text-5xl font-serif text-gray-100 select-none leading-none">"</div>
                        <div class="flex gap-0.5 mb-4">
                            <Star v-for="n in 5" :key="n" class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                        </div>
                        <p class="text-gray-700 text-sm leading-relaxed mb-6 relative">"{{ t.text }}"</p>
                        <div class="flex items-center gap-3">
                            <div :class="['h-10 w-10 rounded-full bg-gradient-to-br flex items-center justify-center text-white text-sm font-bold flex-shrink-0', t.color]">
                                {{ t.avatar }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ t.name }}</div>
                                <div class="text-xs text-gray-500">{{ t.role }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── PRICING ── -->
        <section id="pricing" class="py-24 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14 reveal fade-up">
                    <div class="inline-flex items-center gap-2 rounded-full bg-primary-100 px-3 py-1 text-xs font-semibold text-primary-700 mb-4">
                        Prezzi trasparenti
                    </div>
                    <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-tight">
                        Scegli il piano giusto
                    </h2>
                    <p class="mt-4 text-lg text-gray-500">Parti gratis, scala quando sei pronto. Cancella quando vuoi.</p>
                </div>

                <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4 items-start">
                    <div
                        v-for="(plan, i) in plans"
                        :key="plan.key"
                        :class="[
                            'relative rounded-2xl border p-6 flex flex-col transition-all duration-300 reveal fade-up',
                            plan.highlighted
                                ? 'border-primary-400 bg-gradient-to-b from-primary-600 to-primary-700 text-white shadow-2xl shadow-primary-500/30 scale-105 lg:scale-110 z-10 hover:scale-[1.07] lg:hover:scale-[1.13]'
                                : 'border-gray-200 bg-white hover:border-primary-200 hover:shadow-xl hover:shadow-primary-500/5 hover:-translate-y-1'
                        ]"
                        :style="{transitionDelay: i * 80 + 'ms'}"
                    >
                        <div v-if="plan.highlighted" class="absolute -top-3.5 inset-x-0 flex justify-center">
                            <div class="rounded-full bg-amber-400 px-4 py-0.5 text-xs font-bold text-amber-900 animate-bounce-gentle">
                                ⭐ Il più scelto
                            </div>
                        </div>
                        <div class="flex items-center gap-3 mb-4">
                            <component :is="plan.icon" :class="['h-5 w-5', plan.highlighted ? 'text-primary-200' : 'text-gray-400']" />
                            <span :class="['text-sm font-semibold', plan.highlighted ? 'text-primary-200' : 'text-gray-500']">{{ plan.description }}</span>
                        </div>
                        <div :class="['text-2xl font-extrabold mb-1', plan.highlighted ? 'text-white' : 'text-gray-900']">{{ plan.name }}</div>
                        <div class="flex items-end gap-1 mb-5">
                            <span :class="['text-5xl font-extrabold leading-none', plan.highlighted ? 'text-white' : 'text-gray-900']">
                                {{ plan.price === 0 ? '0' : plan.price }}
                            </span>
                            <div :class="['mb-1', plan.highlighted ? 'text-primary-200' : 'text-gray-400']">
                                <div class="text-sm font-medium">€</div>
                                <div class="text-xs">/mese</div>
                            </div>
                        </div>
                        <ul class="flex-1 space-y-2.5 mb-7">
                            <li v-for="feat in plan.features" :key="feat" :class="['flex items-start gap-2 text-sm', plan.highlighted ? 'text-primary-100' : 'text-gray-600']">
                                <Check :class="['h-4 w-4 flex-shrink-0 mt-0.5', plan.highlighted ? 'text-primary-300' : 'text-primary-500']" />
                                {{ feat }}
                            </li>
                        </ul>
                        <Link
                            :href="route('register')"
                            :class="[
                                'block text-center rounded-xl px-4 py-2.5 text-sm font-bold transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0',
                                plan.highlighted
                                    ? 'bg-white text-primary-700 hover:bg-primary-50 hover:shadow-md'
                                    : 'bg-primary-600 text-white hover:bg-primary-700 hover:shadow-md hover:shadow-primary-500/30'
                            ]"
                        >
                            {{ plan.cta }}
                        </Link>
                    </div>
                </div>

                <p class="text-center text-sm text-gray-400 mt-10 reveal fade-up">
                    Tutti i piani includono SSL, backup giornaliero e supporto. Nessun contratto vincolante.
                </p>
            </div>
        </section>

        <!-- ── FAQ ── -->
        <section id="faq" class="py-24 bg-gray-50">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14 reveal fade-up">
                    <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-tight">Domande frequenti</h2>
                </div>
                <div class="space-y-3">
                    <div
                        v-for="(faq, i) in faqs"
                        :key="i"
                        class="rounded-2xl border border-gray-200 bg-white overflow-hidden hover:border-primary-200 transition-colors duration-200 reveal fade-up"
                        :style="{transitionDelay: i * 60 + 'ms'}"
                    >
                        <button
                            @click="openFaq = openFaq === i ? null : i"
                            class="w-full flex items-center justify-between px-6 py-5 text-left group"
                        >
                            <span class="font-semibold text-gray-900 text-sm sm:text-base group-hover:text-primary-700 transition-colors">{{ faq.q }}</span>
                            <ChevronDown :class="['h-5 w-5 text-gray-400 flex-shrink-0 ml-4 transition-transform duration-300', openFaq === i ? 'rotate-180 text-primary-500' : '']" />
                        </button>
                        <Transition
                            enter-active-class="transition-all duration-300 ease-out"
                            enter-from-class="opacity-0 max-h-0"
                            enter-to-class="opacity-100 max-h-40"
                            leave-active-class="transition-all duration-200 ease-in"
                            leave-from-class="opacity-100 max-h-40"
                            leave-to-class="opacity-0 max-h-0"
                        >
                            <div v-if="openFaq === i" class="overflow-hidden">
                                <div class="px-6 pb-5 text-sm text-gray-600 leading-relaxed -mt-1">{{ faq.a }}</div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── FINAL CTA ── -->
        <section class="py-24 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-700 to-primary-900" />
            <div class="absolute top-0 left-1/3 w-96 h-96 rounded-full bg-primary-500/20 blur-3xl animate-blob" />
            <div class="absolute bottom-0 right-1/4 w-80 h-80 rounded-full bg-emerald-400/10 blur-3xl animate-blob" style="animation-delay:2s" />

            <div class="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center reveal fade-up">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-medium text-primary-200 mb-6">
                    <span class="h-1.5 w-1.5 rounded-full bg-primary-400 animate-pulse" />
                    Nessuna carta di credito richiesta
                </div>
                <h2 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-tight mb-6">
                    Pronto a trasformare<br/>il tuo studio?
                </h2>
                <p class="text-xl text-primary-200 mb-10 max-w-2xl mx-auto">
                    Unisciti a centinaia di nutrizionisti che hanno già scelto NutrizionistApp per gestire il loro studio in modo professionale.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link
                        :href="route('register')"
                        class="group inline-flex items-center gap-2.5 rounded-xl bg-white px-8 py-4 text-lg font-extrabold text-primary-700 shadow-2xl shadow-black/20 hover:bg-primary-50 hover:-translate-y-1 hover:shadow-3xl transition-all duration-200"
                    >
                        Inizia gratis oggi
                        <ArrowRight class="h-5 w-5 transition-transform duration-200 group-hover:translate-x-1" />
                    </Link>
                    <Link
                        :href="route('login')"
                        class="inline-flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 px-8 py-4 text-lg font-semibold text-white backdrop-blur-sm hover:bg-white/20 hover:-translate-y-1 transition-all duration-200"
                    >
                        Ho già un account
                    </Link>
                </div>
                <p class="mt-6 text-sm text-primary-300">
                    Piano Free per sempre · Upgrade quando vuoi · Cancella in qualsiasi momento
                </p>
            </div>
        </section>

        <!-- ── FOOTER ── -->
        <footer class="bg-gray-950 text-gray-400">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-10">
                    <div class="col-span-2 md:col-span-1">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="h-7 w-7 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                                <UtensilsCrossed class="text-white" style="width:14px;height:14px" />
                            </div>
                            <span class="font-extrabold text-white">NutrizionistApp</span>
                        </div>
                        <p class="text-sm leading-relaxed">La piattaforma SaaS per nutrizionisti professionisti italiani.</p>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Prodotto</div>
                        <div class="space-y-2 text-sm">
                            <a href="#features" class="block hover:text-white transition-colors">Funzionalità</a>
                            <a href="#pricing" class="block hover:text-white transition-colors">Prezzi</a>
                            <a href="#how" class="block hover:text-white transition-colors">Come funziona</a>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Account</div>
                        <div class="space-y-2 text-sm">
                            <Link :href="route('register')" class="block hover:text-white transition-colors">Registrati</Link>
                            <Link :href="route('login')" class="block hover:text-white transition-colors">Accedi</Link>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Legale</div>
                        <div class="space-y-2 text-sm">
                            <a href="#" class="block hover:text-white transition-colors">Privacy Policy</a>
                            <a href="#" class="block hover:text-white transition-colors">Termini di servizio</a>
                            <a href="#" class="block hover:text-white transition-colors">Cookie Policy</a>
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-800 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <span>© {{ new Date().getFullYear() }} NutrizionistApp. Tutti i diritti riservati.</span>
                    <div class="flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse" />
                        <span>Tutti i sistemi operativi</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* ── Scroll reveal ────────────────────────────────── */
.reveal {
    opacity: 0;
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.reveal.revealed { opacity: 1; transform: none !important; }

.fade-up   { transform: translateY(36px); }
.fade-down { transform: translateY(-24px); }
.fade-left { transform: translateX(40px); }
.fade-right{ transform: translateX(-40px); }

/* ── Hero entrance animations ─────────────────────── */
.animate-fade-up {
    animation: fadeUp 0.8s cubic-bezier(0.22, 1, 0.36, 1) both;
}
.animate-fade-down {
    animation: fadeDown 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes fadeDown {
    from { opacity: 0; transform: translateY(-20px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Animated gradient text ───────────────────────── */
.animate-gradient-text {
    background-size: 200% auto;
    animation: gradientShift 4s linear infinite;
}
@keyframes gradientShift {
    0%   { background-position: 0% center; }
    100% { background-position: 200% center; }
}

/* ── Floating mockup ──────────────────────────────── */
.animate-float {
    animation: float 6s ease-in-out infinite;
}
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50%       { transform: translateY(-10px); }
}

/* ── Background blobs ─────────────────────────────── */
.animate-blob {
    animation: blob 10s ease-in-out infinite;
}
@keyframes blob {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33%       { transform: translate(20px, -20px) scale(1.1); }
    66%       { transform: translate(-15px, 10px) scale(0.95); }
}

/* ── Slow pulse (glow) ────────────────────────────── */
.animate-pulse-slow {
    animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* ── Chart bar grow-in ────────────────────────────── */
.animate-bar-grow {
    animation: barGrow 1s cubic-bezier(0.22, 1, 0.36, 1) both;
    transform-origin: bottom;
}
@keyframes barGrow {
    from { transform: scaleY(0); opacity: 0; }
    to   { transform: scaleY(1); opacity: 1; }
}

/* ── Ping ring (step 01) ──────────────────────────── */
.animate-ping-slow {
    animation: ping 2.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}
@keyframes ping {
    75%, 100% { transform: scale(1.5); opacity: 0; }
}

/* ── Badge gentle bounce ──────────────────────────── */
.animate-bounce-gentle {
    animation: bounceGentle 2s ease-in-out infinite;
}
@keyframes bounceGentle {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-4px); }
}

/* ── Mobile menu slide down ───────────────────────── */
.animate-slide-down {
    animation: slideDown 0.25s ease-out both;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
