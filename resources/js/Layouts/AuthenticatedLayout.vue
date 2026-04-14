<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Users,
    UtensilsCrossed,
    Apple,
    CalendarDays,
    ClipboardCheck,
    User,
    LogOut,
    Menu,
    ChevronDown,
    CookingPot,
    Settings,
    CreditCard,
    ShieldAlert,
    FileText,
    FlaskConical,
    MessageSquare,
} from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const roles = computed(() => (user.value as any)?.roles || []);
const isNutritionist = computed(() => roles.value.includes('nutritionist'));

const sidebarOpen = ref(false);
const userMenuOpen = ref(false);
const impersonating = computed(() => (page.props as any).impersonating);

function stopImpersonating() {
    router.post(route('impersonate.stop'));
}

interface NavItem {
    name: string;
    href: string;
    icon: any;
}

interface NavGroup {
    label?: string;
    items: NavItem[];
}

const nutritionistNavGroups: NavGroup[] = [
    {
        items: [
            { name: 'Dashboard', href: 'nutritionist.dashboard', icon: LayoutDashboard },
        ],
    },
    {
        label: 'Clienti',
        items: [
            { name: 'Clienti', href: 'nutritionist.clients.index', icon: Users },
            { name: 'Messaggi', href: 'nutritionist.messages.index', icon: MessageSquare },
            { name: 'Appuntamenti', href: 'nutritionist.appointments.index', icon: CalendarDays },
            { name: 'Monitoraggio', href: 'nutritionist.check-ins.index', icon: ClipboardCheck },
            { name: 'Anamnesi', href: 'nutritionist.anamnesis.index', icon: FileText },
            { name: 'Esami', href: 'nutritionist.lab-results.index', icon: FlaskConical },
        ],
    },
    {
        label: 'Libreria',
        items: [
            { name: 'Alimenti', href: 'nutritionist.foods.index', icon: Apple },
            { name: 'Ricette', href: 'nutritionist.recipes.index', icon: CookingPot },
            { name: 'Piani', href: 'nutritionist.plans.index', icon: UtensilsCrossed },
        ],
    },
];

const clientNavGroups: NavGroup[] = [
    {
        items: [
            { name: 'Dashboard', href: 'client.dashboard', icon: LayoutDashboard },
        ],
    },
    {
        label: 'La mia salute',
        items: [
            { name: 'Il mio Piano', href: 'client.plan', icon: UtensilsCrossed },
            { name: 'Monitoraggio', href: 'client.check-ins.index', icon: ClipboardCheck },
            { name: 'Questionari', href: 'client.anamnesis.index', icon: FileText },
            { name: 'Appuntamenti', href: 'client.appointments', icon: CalendarDays },
            { name: 'Messaggi', href: 'client.messages.index', icon: MessageSquare },
        ],
    },
];

const navGroups = computed(() => isNutritionist.value ? nutritionistNavGroups : clientNavGroups);

const nutritionistProfileMenu = [
    { name: 'Profilo', href: 'profile.edit', icon: User },
    { name: 'Impostazioni', href: 'nutritionist.settings', icon: Settings },
    { name: 'Abbonamento', href: 'nutritionist.billing', icon: CreditCard },
];

const clientProfileMenu = [
    { name: 'Profilo', href: 'profile.edit', icon: User },
    { name: 'Impostazioni', href: 'client.settings', icon: Settings },
];

const profileMenu = computed(() => isNutritionist.value ? nutritionistProfileMenu : clientProfileMenu);

function isRouteAvailable(name: string): boolean {
    try {
        route(name);
        return true;
    } catch {
        return false;
    }
}

function isActive(name: string): boolean {
    try {
        return route().current(name) || false;
    } catch {
        return false;
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Impersonation banner -->
        <div v-if="impersonating" class="fixed top-0 inset-x-0 z-[100] bg-amber-400 text-amber-950 px-4 py-2 flex items-center justify-between text-sm font-semibold">
            <div class="flex items-center gap-2">
                <ShieldAlert class="h-4 w-4 flex-shrink-0" />
                Stai impersonificando <strong class="ml-1">{{ user?.name }} {{ (user as any)?.last_name }}</strong>
            </div>
            <button @click="stopImpersonating" class="rounded-lg bg-amber-950/20 hover:bg-amber-950/30 px-3 py-1 text-xs font-bold transition whitespace-nowrap">
                ← Torna alla sessione dev
            </button>
        </div>
        <!-- Mobile sidebar overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white border-r border-gray-200 transition-transform duration-300 lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                impersonating ? 'top-9' : ''
            ]"
        >
            <!-- Logo area -->
            <div class="flex h-16 items-center px-6 border-b border-gray-100">
                <img src="/images/logo-nutrizionistapp.png" alt="NutrizionistApp" class="h-8 w-auto" />
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-4">
                <div v-for="(group, gi) in navGroups" :key="gi">
                    <p v-if="group.label" class="mb-1 px-3 text-[10px] font-semibold uppercase tracking-widest text-gray-400">
                        {{ group.label }}
                    </p>
                    <div class="space-y-0.5">
                        <template v-for="item in group.items" :key="item.name">
                            <Link
                                v-if="isRouteAvailable(item.href)"
                                :href="route(item.href)"
                                :class="[
                                    'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150',
                                    isActive(item.href)
                                        ? 'bg-gradient-to-r from-primary-50 to-primary-100/50 text-primary-700'
                                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                                ]"
                            >
                                <component
                                    :is="item.icon"
                                    :class="[
                                        'h-5 w-5 flex-shrink-0',
                                        isActive(item.href) ? 'text-primary-600' : 'text-gray-400'
                                    ]"
                                />
                                {{ item.name }}
                                <div
                                    v-if="isActive(item.href)"
                                    class="ml-auto h-1.5 w-1.5 rounded-full bg-primary-500"
                                />
                            </Link>
                            <span
                                v-else
                                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-300 cursor-not-allowed"
                            >
                                <component :is="item.icon" class="h-5 w-5 flex-shrink-0" />
                                {{ item.name }}
                            </span>
                        </template>
                    </div>
                </div>
            </nav>

            <!-- User section at bottom -->
            <div class="border-t border-gray-100 p-3">
                <div class="relative">
                    <button
                        @click="userMenuOpen = !userMenuOpen"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition hover:bg-gray-50"
                    >
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-primary-400 to-primary-600 text-white text-xs font-bold overflow-hidden">
                            <img v-if="(user as any)?.avatarUrl" :src="(user as any).avatarUrl" class="h-full w-full object-cover" alt="" />
                            <span v-else>{{ user?.name?.charAt(0)?.toUpperCase() }}</span>
                        </div>
                        <div class="flex-1 text-left">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                        </div>
                        <ChevronDown class="h-4 w-4 text-gray-400" />
                    </button>

                    <!-- User dropdown -->
                    <div
                        v-if="userMenuOpen"
                        class="absolute bottom-full left-0 mb-1 w-full rounded-lg border border-gray-200 bg-white py-1 shadow-lg"
                    >
                        <template v-for="item in profileMenu" :key="item.name">
                            <Link
                                v-if="isRouteAvailable(item.href)"
                                :href="route(item.href)"
                                class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                @click="userMenuOpen = false"
                            >
                                <component :is="item.icon" class="h-4 w-4 text-gray-400" />
                                {{ item.name }}
                            </Link>
                        </template>
                        <div class="my-1 border-t border-gray-100" />
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50"
                        >
                            <LogOut class="h-4 w-4" />
                            Esci
                        </Link>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div :class="['lg:pl-64', impersonating ? 'pt-9' : '']">
            <!-- Top bar (mobile) -->
            <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-gray-200 bg-white/80 backdrop-blur-md px-4 lg:px-8">
                <button
                    @click="sidebarOpen = true"
                    class="lg:hidden -ml-1 rounded-lg p-2 text-gray-500 hover:bg-gray-100"
                >
                    <Menu class="h-5 w-5" />
                </button>

                <!-- Page title slot -->
                <div class="flex-1">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page content -->
            <main class="p-4 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
