<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    LayoutDashboard, Users, UtensilsCrossed, UserCog,
    LogOut, Menu, X, ChevronDown, Terminal, ShieldAlert, Activity, Settings,
} from 'lucide-vue-next';

const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
const impersonating = computed(() => (page.props as any).impersonating);

const sidebarOpen = ref(false);
const userMenuOpen = ref(false);

const nav = [
    { name: 'Dashboard', href: 'dev.dashboard', icon: LayoutDashboard },
    { name: 'Nutrizionisti', href: 'dev.nutritionists', icon: Users },
    { name: 'Piani', href: 'dev.plans', icon: UtensilsCrossed },
    { name: 'Utenti', href: 'dev.users', icon: UserCog },
    { name: 'Activity Log', href: 'dev.activity', icon: Activity },
    { name: 'Impostazioni', href: 'dev.settings.payments', icon: Settings },
];

function isActive(name: string) {
    try { return route().current(name) || false; } catch { return false; }
}

function stopImpersonating() {
    router.post(route('impersonate.stop'));
}
</script>

<template>
    <div class="min-h-screen bg-gray-950">

        <!-- Impersonation banner -->
        <div v-if="impersonating" class="fixed top-0 inset-x-0 z-[100] bg-amber-500 text-amber-950 px-4 py-2 flex items-center justify-between text-sm font-semibold">
            <div class="flex items-center gap-2">
                <ShieldAlert class="h-4 w-4" />
                Stai impersonificando <strong>{{ user?.name }} {{ user?.last_name }}</strong> ({{ user?.roles?.[0] }})
            </div>
            <button @click="stopImpersonating" class="rounded-lg bg-amber-950/20 hover:bg-amber-950/30 px-3 py-1 text-xs font-bold transition">
                ← Torna alla sessione dev
            </button>
        </div>

        <!-- Sidebar -->
        <div :class="['fixed inset-y-0 left-0 z-50 flex w-60 flex-col bg-gray-900 border-r border-gray-800 transition-transform duration-300 lg:translate-x-0', impersonating ? 'top-10' : '', sidebarOpen ? 'translate-x-0' : '-translate-x-full']">
            <!-- Logo -->
            <div class="flex h-14 items-center gap-2.5 px-5 border-b border-gray-800">
                <div class="h-7 w-7 rounded-lg bg-gradient-to-br from-violet-500 to-violet-700 flex items-center justify-center">
                    <Terminal class="h-4 w-4 text-white" />
                </div>
                <span class="font-extrabold text-white tracking-tight">Dev Panel</span>
                <span class="ml-auto rounded-full bg-violet-500/20 px-1.5 py-0.5 text-xs font-bold text-violet-400">DEV</span>
            </div>

            <!-- Nav -->
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
                <Link
                    v-for="item in nav"
                    :key="item.name"
                    :href="route(item.href)"
                    :class="[
                        'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all',
                        isActive(item.href)
                            ? 'bg-violet-600/20 text-violet-300'
                            : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100'
                    ]"
                >
                    <component :is="item.icon" class="h-4.5 w-4.5 flex-shrink-0" style="width:18px;height:18px" />
                    {{ item.name }}
                </Link>
            </nav>

            <!-- User -->
            <div class="border-t border-gray-800 p-3">
                <div class="relative">
                    <button @click="userMenuOpen = !userMenuOpen" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm hover:bg-gray-800 transition">
                        <div class="h-7 w-7 rounded-full bg-gradient-to-br from-violet-400 to-violet-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            {{ user?.name?.charAt(0) }}
                        </div>
                        <div class="flex-1 text-left min-w-0">
                            <p class="text-sm font-medium text-gray-200 truncate">{{ user?.name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ user?.email }}</p>
                        </div>
                        <ChevronDown class="h-3.5 w-3.5 text-gray-500" />
                    </button>
                    <div v-if="userMenuOpen" class="absolute bottom-full left-0 mb-1 w-full rounded-lg border border-gray-700 bg-gray-800 py-1 shadow-xl">
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-400 hover:bg-red-500/10"
                        >
                            <LogOut class="h-4 w-4" />
                            Esci
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main -->
        <div :class="['lg:pl-60 transition-all', impersonating ? 'pt-10' : '']">
            <!-- Mobile header -->
            <header class="lg:hidden sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-gray-800 bg-gray-900 px-4">
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-gray-100">
                    <Menu v-if="!sidebarOpen" class="h-5 w-5" />
                    <X v-else class="h-5 w-5" />
                </button>
                <span class="font-bold text-white">Dev Panel</span>
            </header>

            <main class="p-4 lg:p-8">
                <slot />
            </main>
        </div>

        <!-- Mobile overlay -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-black/60 lg:hidden" @click="sidebarOpen = false" />
    </div>
</template>
