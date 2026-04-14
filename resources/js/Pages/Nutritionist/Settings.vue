<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Camera, Trash2, Save, Globe, Instagram, MapPin, Phone, User, Briefcase, Plus, X, Bell, Mail, CalendarCheck, ClipboardList } from 'lucide-vue-next';

const props = defineProps<{
    profile: {
        id: number;
        specialization?: string;
        bio?: string;
        city?: string;
        address?: string;
        website?: string;
        instagram?: string;
        logo?: string;
    } | null;
    logoUrl: string | null;
    userPhone: string | null;
    locations: string[];
    notificationSettings: {
        appointment_reminder: boolean;
        appointment_reminder_hours: number;
        checkin_reminder: boolean;
        checkin_reminder_day: string;
        plan_delivered: boolean;
    };
}>();

const page = usePage();
const authUser = computed(() => (page.props as any).auth?.user);

const form = useForm({
    name: authUser.value?.name ?? '',
    specialization: props.profile?.specialization ?? '',
    phone: props.userPhone ?? '',
    bio: props.profile?.bio ?? '',
    city: props.profile?.city ?? '',
    address: props.profile?.address ?? '',
    website: props.profile?.website ?? '',
    instagram: props.profile?.instagram ?? '',
    logo: null as File | null,
    avatar: null as File | null,
});

const logoPreview = ref<string | null>(props.logoUrl);
const fileInput = ref<HTMLInputElement | null>(null);
const avatarPreview = ref<string | null>(authUser.value?.avatarUrl ?? null);
const avatarInput = ref<HTMLInputElement | null>(null);

function onLogoChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    form.logo = file;
    const reader = new FileReader();
    reader.onload = (ev) => { logoPreview.value = ev.target?.result as string; };
    reader.readAsDataURL(file);
}

function removeLogo() {
    if (props.logoUrl) {
        router.delete(route('nutritionist.settings.logo.delete'), { preserveScroll: true });
    }
    logoPreview.value = null;
    form.logo = null;
    if (fileInput.value) fileInput.value.value = '';
}

function onAvatarChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    form.avatar = file;
    const reader = new FileReader();
    reader.onload = (ev) => { avatarPreview.value = ev.target?.result as string; };
    reader.readAsDataURL(file);
}

function removeAvatar() {
    if (authUser.value?.avatarUrl) {
        router.delete(route('nutritionist.settings.avatar.delete'), { preserveScroll: true });
    }
    avatarPreview.value = null;
    form.avatar = null;
    if (avatarInput.value) avatarInput.value.value = '';
}

function submit() {
    form.post(route('nutritionist.settings.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
}

// ─── Locations ───────────────────────────────────────────────────────────────
const locationsList = ref<string[]>([...(props.locations ?? [])]);
const newLocation   = ref('');
const savingLocations = ref(false);

function addLocation() {
    const val = newLocation.value.trim();
    if (!val || locationsList.value.includes(val)) return;
    locationsList.value.push(val);
    newLocation.value = '';
}

function removeLocation(i: number) {
    locationsList.value.splice(i, 1);
}

function saveLocations() {
    savingLocations.value = true;
    router.patch(route('nutritionist.settings.locations.update'), {
        locations: locationsList.value,
    }, {
        preserveScroll: true,
        onFinish: () => { savingLocations.value = false; },
    });
}

// ─── Notification settings ──────────────────────────────────────────────────
const notifForm = useForm({
    appointment_reminder:       props.notificationSettings.appointment_reminder,
    appointment_reminder_hours: props.notificationSettings.appointment_reminder_hours,
    checkin_reminder:           props.notificationSettings.checkin_reminder,
    checkin_reminder_day:       props.notificationSettings.checkin_reminder_day,
    plan_delivered:             props.notificationSettings.plan_delivered,
});

function saveNotifications() {
    notifForm.patch(route('nutritionist.settings.notifications.update'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900">Impostazioni</h1>
        </template>

        <div class="max-w-3xl space-y-6">
            <!-- Success flash -->
            <div
                v-if="$page.props.flash?.success"
                class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700"
            >
                {{ $page.props.flash.success }}
            </div>

            <form @submit.prevent="submit" enctype="multipart/form-data">
                <!-- Logo section -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Logo studio</h2>
                    <div class="flex items-center gap-5">
                        <!-- Logo preview -->
                        <div class="relative h-24 w-24 flex-shrink-0">
                            <img
                                v-if="logoPreview"
                                :src="logoPreview"
                                alt="Logo"
                                class="h-24 w-24 rounded-xl object-contain border border-gray-200 bg-gray-50 p-1"
                            />
                            <div
                                v-else
                                class="h-24 w-24 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50"
                            >
                                <Camera class="h-8 w-8 text-gray-300" />
                            </div>
                        </div>
                        <div class="flex-1 space-y-2">
                            <p class="text-sm text-gray-600">
                                Carica il logo del tuo studio. Verrà utilizzato nei PDF dei piani nutrizionali e nelle email.
                            </p>
                            <p class="text-xs text-gray-400">JPG, PNG, WEBP — max 2 MB</p>
                            <div class="flex gap-2 pt-1">
                                <label
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white hover:bg-primary-700 transition"
                                >
                                    <Camera class="h-4 w-4" />
                                    {{ logoPreview ? 'Cambia logo' : 'Carica logo' }}
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="sr-only"
                                        @change="onLogoChange"
                                    />
                                </label>
                                <button
                                    v-if="logoPreview"
                                    type="button"
                                    @click="removeLogo"
                                    class="inline-flex items-center gap-2 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition"
                                >
                                    <Trash2 class="h-4 w-4" />
                                    Rimuovi
                                </button>
                            </div>
                            <p v-if="form.errors.logo" class="text-sm text-red-600">{{ form.errors.logo }}</p>
                        </div>
                    </div>
                </div>

                <!-- Avatar section -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Foto profilo</h2>
                    <div class="flex items-center gap-5">
                        <div class="relative h-20 w-20 flex-shrink-0">
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
                            <p class="text-sm text-gray-600">La foto viene mostrata nella barra laterale e nelle comunicazioni con i clienti.</p>
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
                </div>

                <!-- Personal info -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Informazioni profilo</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Name -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <User class="inline h-4 w-4 mr-1 text-gray-400" />Nome e cognome *
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Specialization -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <Briefcase class="inline h-4 w-4 mr-1 text-gray-400" />Specializzazione
                            </label>
                            <input
                                v-model="form.specialization"
                                type="text"
                                placeholder="es. Biologo nutrizionista"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                            />
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <Phone class="inline h-4 w-4 mr-1 text-gray-400" />Telefono
                            </label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                placeholder="+39 333 000 0000"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                            />
                        </div>

                        <!-- City -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <MapPin class="inline h-4 w-4 mr-1 text-gray-400" />Città
                            </label>
                            <input
                                v-model="form.city"
                                type="text"
                                placeholder="Milano"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                            />
                        </div>

                        <!-- Address -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Indirizzo studio</label>
                            <input
                                v-model="form.address"
                                type="text"
                                placeholder="Via Roma 1"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                            />
                        </div>

                        <!-- Bio -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bio / Presentazione</label>
                            <textarea
                                v-model="form.bio"
                                rows="3"
                                placeholder="Breve presentazione professionale..."
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none resize-none"
                            />
                        </div>
                    </div>
                </div>

                <!-- Social / Web -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Contatti online</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <Globe class="inline h-4 w-4 mr-1 text-gray-400" />Sito web
                            </label>
                            <input
                                v-model="form.website"
                                type="url"
                                placeholder="https://tuosito.it"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                                :class="{ 'border-red-500': form.errors.website }"
                            />
                            <p v-if="form.errors.website" class="mt-1 text-sm text-red-600">{{ form.errors.website }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <Instagram class="inline h-4 w-4 mr-1 text-gray-400" />Instagram
                            </label>
                            <div class="flex items-center rounded-lg border border-gray-300 overflow-hidden focus-within:border-primary-500 focus-within:ring-1 focus-within:ring-primary-500">
                                <span class="px-3 py-2 text-sm text-gray-400 bg-gray-50 border-r border-gray-300">@</span>
                                <input
                                    v-model="form.instagram"
                                    type="text"
                                    placeholder="tuoprofilo"
                                    class="flex-1 px-3 py-2 text-sm outline-none"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50 transition"
                    >
                        <Save class="h-4 w-4" />
                        {{ form.processing ? 'Salvataggio...' : 'Salva impostazioni' }}
                    </button>
                </div>
            </form>

            <!-- Luoghi -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-900 mb-1">Luoghi in cui operi</h2>
                <p class="text-sm text-gray-400 mb-4">Questi luoghi saranno disponibili come opzioni rapide durante la creazione degli appuntamenti.</p>

                <div class="space-y-2 mb-4">
                    <div v-if="locationsList.length === 0" class="text-sm text-gray-400 italic">Nessun luogo aggiunto.</div>
                    <div v-for="(loc, i) in locationsList" :key="i" class="flex items-center gap-2">
                        <MapPin class="h-4 w-4 text-gray-400 flex-shrink-0" />
                        <span class="flex-1 text-sm text-gray-800">{{ loc }}</span>
                        <button type="button" @click="removeLocation(i)" class="p-1 text-gray-300 hover:text-red-500 transition">
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <div class="flex gap-2">
                    <input
                        v-model="newLocation"
                        type="text"
                        placeholder="es. Studio via Roma 1, Online (Zoom)..."
                        class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none"
                        @keydown.enter.prevent="addLocation"
                    />
                    <button type="button" @click="addLocation" class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 transition">
                        <Plus class="h-4 w-4" />
                    </button>
                </div>

                <div class="flex justify-end mt-4">
                    <button
                        type="button"
                        @click="saveLocations"
                        :disabled="savingLocations"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50 transition"
                    >
                        <Save class="h-4 w-4" />
                        {{ savingLocations ? 'Salvataggio...' : 'Salva luoghi' }}
                    </button>
                </div>
            </div>
            <!-- Notifiche email -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-center gap-2 mb-1">
                    <Bell class="h-5 w-5 text-primary-500" />
                    <h2 class="text-base font-semibold text-gray-900">Notifiche email ai pazienti</h2>
                </div>
                <p class="text-sm text-gray-400 mb-5">Configura le email automatiche inviate ai tuoi pazienti.</p>

                <div class="space-y-5">
                    <!-- Appointment reminder -->
                    <div class="flex items-start gap-3">
                        <label class="relative inline-flex items-center cursor-pointer mt-0.5">
                            <input type="checkbox" v-model="notifForm.appointment_reminder" class="sr-only peer" />
                            <div class="w-9 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-500"></div>
                        </label>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <CalendarCheck class="h-4 w-4 text-gray-400" />
                                <span class="text-sm font-medium text-gray-800">Promemoria appuntamento</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">Invia un'email al paziente prima dell'appuntamento.</p>
                            <div v-if="notifForm.appointment_reminder" class="mt-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Anticipo promemoria</label>
                                <select v-model="notifForm.appointment_reminder_hours" class="rounded-lg border-gray-300 text-sm py-1.5 focus:border-primary-500 focus:ring-primary-500">
                                    <option :value="1">1 ora prima</option>
                                    <option :value="2">2 ore prima</option>
                                    <option :value="4">4 ore prima</option>
                                    <option :value="12">12 ore prima</option>
                                    <option :value="24">24 ore prima (1 giorno)</option>
                                    <option :value="48">48 ore prima (2 giorni)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Check-in reminder -->
                    <div class="flex items-start gap-3 border-t border-gray-100 pt-5">
                        <label class="relative inline-flex items-center cursor-pointer mt-0.5">
                            <input type="checkbox" v-model="notifForm.checkin_reminder" class="sr-only peer" />
                            <div class="w-9 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-500"></div>
                        </label>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <ClipboardList class="h-4 w-4 text-gray-400" />
                                <span class="text-sm font-medium text-gray-800">Promemoria check-in settimanale</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">Ricorda ai pazienti attivi di compilare il check-in settimanale.</p>
                            <div v-if="notifForm.checkin_reminder" class="mt-2">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Giorno di invio</label>
                                <select v-model="notifForm.checkin_reminder_day" class="rounded-lg border-gray-300 text-sm py-1.5 focus:border-primary-500 focus:ring-primary-500">
                                    <option value="monday">Lunedì</option>
                                    <option value="tuesday">Martedì</option>
                                    <option value="wednesday">Mercoledì</option>
                                    <option value="thursday">Giovedì</option>
                                    <option value="friday">Venerdì</option>
                                    <option value="saturday">Sabato</option>
                                    <option value="sunday">Domenica</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Plan delivered -->
                    <div class="flex items-start gap-3 border-t border-gray-100 pt-5">
                        <label class="relative inline-flex items-center cursor-pointer mt-0.5">
                            <input type="checkbox" v-model="notifForm.plan_delivered" class="sr-only peer" />
                            <div class="w-9 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-500"></div>
                        </label>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <Mail class="h-4 w-4 text-gray-400" />
                                <span class="text-sm font-medium text-gray-800">Conferma ricezione piano</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">Invia un'email al paziente quando un nuovo piano nutrizionale viene creato o attivato.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-5 pt-4 border-t border-gray-100">
                    <button
                        type="button"
                        @click="saveNotifications"
                        :disabled="notifForm.processing"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50 transition"
                    >
                        <Save class="h-4 w-4" />
                        {{ notifForm.processing ? 'Salvataggio...' : 'Salva notifiche' }}
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
