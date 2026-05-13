<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { AlertTriangle, Lock, ServerCrash, Clock, Zap, Wrench } from 'lucide-vue-next';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps<{ status: number }>();

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);

const errors: Record<number, { icon: any; title: string; description: string }> = {
    403: { icon: Lock,         title: 'Accesso negato',      description: 'Non hai i permessi per visualizzare questa risorsa.' },
    404: { icon: AlertTriangle, title: 'Pagina non trovata', description: 'La pagina che cerchi non esiste o è stata spostata.' },
    419: { icon: Clock,        title: 'Sessione scaduta',    description: 'La tua sessione è scaduta. Ricarica la pagina e riprova.' },
    429: { icon: Zap,          title: 'Troppe richieste',    description: 'Stai effettuando troppe richieste. Attendi qualche istante e riprova.' },
    500: { icon: ServerCrash,  title: 'Errore del server',   description: 'Qualcosa è andato storto. Stiamo lavorando per risolvere il problema.' },
    503: { icon: Wrench,       title: 'In manutenzione',     description: 'Stiamo eseguendo manutenzione. Torna tra qualche minuto.' },
};

const current = computed(() => errors[props.status] ?? {
    icon: AlertTriangle,
    title: 'Errore imprevisto',
    description: 'Si è verificato un errore. Prova a ricaricare la pagina.',
});

const homeRoute = computed(() => {
    const roles: string[] = user.value?.roles ?? [];
    if (roles.includes('dev')) return route('dev.dashboard');
    if (roles.includes('nutritionist')) return route('nutritionist.dashboard');
    if (roles.includes('client')) return route('client.dashboard');
    return '/';
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center px-4">
        <div class="mb-8">
            <ApplicationLogo class="h-12 w-12 mx-auto" />
        </div>

        <div class="w-full max-w-md bg-white rounded-2xl border border-gray-100 shadow-sm px-8 py-10 text-center">
            <div class="flex justify-center mb-5">
                <div class="rounded-full bg-gray-100 p-4">
                    <component :is="current.icon" class="h-8 w-8 text-gray-500" />
                </div>
            </div>

            <span class="inline-block rounded-full bg-gray-100 px-3 py-0.5 text-xs font-semibold text-gray-500 mb-3">
                Errore {{ status }}
            </span>

            <h1 class="text-xl font-bold text-gray-900 mb-2">{{ current.title }}</h1>
            <p class="text-sm text-gray-500 mb-8">{{ current.description }}</p>

            <Link
                :href="homeRoute"
                class="inline-flex items-center gap-2 rounded-lg bg-primary-600 hover:bg-primary-700 px-5 py-2.5 text-sm font-semibold text-white transition"
            >
                Torna alla home
            </Link>
        </div>
    </div>
</template>
