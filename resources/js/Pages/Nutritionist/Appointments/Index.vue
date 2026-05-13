<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, reactive, nextTick, onMounted } from 'vue';
import { useConfirm } from '@/Composables/useConfirm';

const { confirm: confirmDialog } = useConfirm();
import {
    Plus, Calendar, Clock, MapPin, User, X, Trash2, Pencil,
    ChevronLeft, ChevronRight, LayoutList, CalendarDays,
    UserPlus, RefreshCw, Video, MoreHorizontal, Check, ChevronRight as Next,
} from 'lucide-vue-next';

const props = defineProps<{
    appointments: any;
    filters: { client_id?: string; status?: string; month?: string };
    clients: any[];
    types: { value: string; label: string }[];
    statuses: { value: string; label: string }[];
    sessionDurations: Record<string, number>;
    locations: string[];
}>();

// ─── View mode ───────────────────────────────────────────────────────────────
const viewMode   = ref<'month' | 'week' | 'day' | 'list'>('month');
const showWeekend = ref(true);

const visibleWeekDayLabels = computed(() => {
    const all = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];
    return showWeekend.value ? all : all.slice(0, 5);
});

// ─── Navigation ──────────────────────────────────────────────────────────────
const currentDate = ref((() => {
    const m = props.filters.month;
    return m ? new Date(m + '-01') : new Date();
})());

const currentYear  = computed(() => currentDate.value.getFullYear());
const currentMonth = computed(() => currentDate.value.getMonth());
const monthLabel   = computed(() => currentDate.value.toLocaleDateString('it-IT', { month: 'long', year: 'numeric' }));

const weekStart = computed(() => {
    const d = new Date(currentDate.value);
    const day = d.getDay();
    d.setDate(d.getDate() + (day === 0 ? -6 : 1 - day));
    d.setHours(0, 0, 0, 0);
    return d;
});

const weekDaysWithDates = computed(() => {
    const days: Date[] = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(weekStart.value);
        d.setDate(d.getDate() + i);
        days.push(d);
    }
    return showWeekend.value ? days : days.slice(0, 5);
});

const weekLabel = computed(() => {
    const start = weekStart.value;
    const end   = weekDaysWithDates.value[weekDaysWithDates.value.length - 1];
    const fmt   = (d: Date) => d.toLocaleDateString('it-IT', { day: '2-digit', month: 'short' });
    return `${fmt(start)} – ${fmt(end)} ${start.getFullYear()}`;
});

const dayLabel = computed(() => {
    return currentDate.value.toLocaleDateString('it-IT', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
});

function prevPeriod() {
    const d = new Date(currentDate.value);
    if (viewMode.value === 'day') d.setDate(d.getDate() - 1);
    else if (viewMode.value === 'week') d.setDate(d.getDate() - 7);
    else d.setMonth(d.getMonth() - 1);
    currentDate.value = d;
    fetchMonth();
}

function nextPeriod() {
    const d = new Date(currentDate.value);
    if (viewMode.value === 'day') d.setDate(d.getDate() + 1);
    else if (viewMode.value === 'week') d.setDate(d.getDate() + 7);
    else d.setMonth(d.getMonth() + 1);
    currentDate.value = d;
    fetchMonth();
}

function fetchMonth() {
    const m = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}`;
    router.get(route('nutritionist.appointments.index'), {
        month:     m,
        client_id: filterClientId.value || undefined,
        status:    filterStatus.value   || undefined,
    }, { preserveState: true, replace: true });
}

function goToToday() {
    currentDate.value = new Date();
    fetchMonth();
    if (viewMode.value === 'week' || viewMode.value === 'day') nextTick(() => scrollToCurrentTime());
}

// ─── Filters ─────────────────────────────────────────────────────────────────
const filterClientId = ref(props.filters.client_id || '');
const filterStatus   = ref(props.filters.status    || '');

// ─── Month grid ──────────────────────────────────────────────────────────────
const calendarDays = computed(() => {
    const year = currentYear.value, month = currentMonth.value;
    const firstDay = new Date(year, month, 1);
    const lastDay  = new Date(year, month + 1, 0);
    let startDow   = firstDay.getDay() - 1;
    if (startDow < 0) startDow = 6;
    const days: (Date | null)[] = [];
    for (let i = 0; i < startDow; i++) days.push(null);
    for (let d = 1; d <= lastDay.getDate(); d++) days.push(new Date(year, month, d));
    while (days.length % 7 !== 0) days.push(null);
    return days;
});

const calendarDaysWeekdaysOnly = computed(() => {
    const year = currentYear.value, month = currentMonth.value;
    const firstDay = new Date(year, month, 1);
    const lastDay  = new Date(year, month + 1, 0);
    let startDow   = firstDay.getDay() - 1;
    if (startDow < 0) startDow = 6;
    const padStart = startDow > 4 ? 0 : startDow;
    const days: (Date | null)[] = [];
    for (let i = 0; i < padStart; i++) days.push(null);
    for (let d = 1; d <= lastDay.getDate(); d++) {
        const date = new Date(year, month, d);
        if (date.getDay() !== 0 && date.getDay() !== 6) days.push(date);
    }
    while (days.length % 5 !== 0) days.push(null);
    return days;
});

// ─── Time grid constants (full day) ──────────────────────────────────────────
const HOUR_HEIGHT = 64; // px per hour
const START_HOUR  = 0;
const END_HOUR    = 24;
const TOTAL_PX    = (END_HOUR - START_HOUR) * HOUR_HEIGHT;

const hours = computed(() => {
    const h: number[] = [];
    for (let i = START_HOUR; i <= END_HOUR; i++) h.push(i);
    return h;
});

const timeSlots = computed(() => {
    const slots: { key: string; hour: number; minute: number; timeStr: string }[] = [];
    for (let h = START_HOUR; h < END_HOUR; h++) {
        for (let m = 0; m < 60; m += 15) {
            slots.push({
                key:     `${h}-${m}`,
                hour:    h,
                minute:  m,
                timeStr: `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`,
            });
        }
    }
    return slots;
});

const currentTimeTopPx = computed(() => {
    const now = new Date();
    return now.getHours() * HOUR_HEIGHT + (now.getMinutes() / 60) * HOUR_HEIGHT;
});

// ─── Time grid helpers ────────────────────────────────────────────────────────
function aptTopPx(apt: any): number {
    const d = new Date(apt.starts_at);
    return d.getHours() * HOUR_HEIGHT + (d.getMinutes() / 60) * HOUR_HEIGHT;
}

function aptHeightPx(apt: any): number {
    const start = new Date(apt.starts_at);
    const end   = new Date(apt.ends_at);
    const mins  = Math.max((end.getTime() - start.getTime()) / 60000, 20);
    return (mins / 60) * HOUR_HEIGHT;
}

function aptBgColor(status: string): string {
    const map: Record<string, string> = {
        scheduled: 'bg-blue-100  border-l-[3px] border-blue-500  text-blue-900',
        confirmed: 'bg-green-100 border-l-[3px] border-green-500 text-green-900',
        completed: 'bg-gray-100  border-l-[3px] border-gray-400  text-gray-600',
        cancelled: 'bg-red-50    border-l-[3px] border-red-400   text-red-700',
        no_show:   'bg-orange-50 border-l-[3px] border-orange-400 text-orange-700',
    };
    return map[status] || 'bg-gray-100 border-l-[3px] border-gray-400 text-gray-700';
}

// ─── Scroll to 8am (or current time) ─────────────────────────────────────────
const timeGridRef = ref<HTMLElement | null>(null);

function scrollToCurrentTime() {
    if (!timeGridRef.value) return;
    const now          = new Date();
    const targetHour   = Math.max(7, now.getHours() - 1);
    timeGridRef.value.scrollTop = targetHour * HOUR_HEIGHT;
}

onMounted(() => {
    if (viewMode.value === 'week' || viewMode.value === 'day') scrollToCurrentTime();
});

// ─── Overlap layout (side-by-side for overlapping appointments) ─────────────
interface LayoutSlot { apt: any; col: number; totalCols: number }

function layoutAppointments(apts: any[]): LayoutSlot[] {
    if (!apts.length) return [];
    const sorted = [...apts].sort((a, b) => new Date(a.starts_at).getTime() - new Date(b.starts_at).getTime());
    const groups: any[][] = [];
    let groupEnd = -Infinity;
    let currentGroup: any[] = [];

    for (const apt of sorted) {
        const s = new Date(apt.starts_at).getTime();
        const e = new Date(apt.ends_at).getTime();
        if (s < groupEnd) {
            currentGroup.push(apt);
            groupEnd = Math.max(groupEnd, e);
        } else {
            if (currentGroup.length) groups.push(currentGroup);
            currentGroup = [apt];
            groupEnd = e;
        }
    }
    if (currentGroup.length) groups.push(currentGroup);

    const result: LayoutSlot[] = [];
    for (const group of groups) {
        const cols: number[] = [];
        for (const apt of group) {
            const s = new Date(apt.starts_at).getTime();
            let col = 0;
            while (cols[col] !== undefined && cols[col] > s) col++;
            cols[col] = new Date(apt.ends_at).getTime();
            result.push({ apt, col, totalCols: 0 });
        }
        const totalCols = cols.length;
        for (const r of result) {
            if (group.includes(r.apt)) r.totalCols = totalCols;
        }
    }
    return result;
}

function dayAppointmentLayouts(date: Date | null): LayoutSlot[] {
    return layoutAppointments(appointmentsForDay(date));
}

// ─── Drag & Drop ─────────────────────────────────────────────────────────────
interface PendingMove {
    apt:         any;
    newStartsAt: string;
    newEndsAt:   string;
    dayLabel:    string;
    timeLabel:   string;
}

const draggingApt       = ref<any>(null);
const dragOffsetMinutes = ref(0);
const hoveredSlotId     = ref<string | null>(null);
const pendingMove       = ref<PendingMove | null>(null);
const confirmingMove    = ref(false);

function toLocalDateTimeStr(d: Date): string {
    const p = (n: number) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
}

function onDragStart(e: DragEvent, apt: any) {
    draggingApt.value = apt;
    // Memorizza quanti minuti dal bordo superiore dell'evento è stato afferrato,
    // in modo da correggere il drop target e mostrare l'ora di inizio reale.
    dragOffsetMinutes.value = Math.round((e.offsetY / HOUR_HEIGHT) * 60 / 15) * 15;
    if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(apt.id));
    }
}

function onDragEnd() {
    draggingApt.value   = null;
    hoveredSlotId.value = null;
}

function slotId(day: Date, slot: { hour: number; minute: number }): string {
    return `${day.toISOString().slice(0, 10)}-${slot.hour}-${slot.minute}`;
}

function onSlotDragOver(e: DragEvent, day: Date, slot: { hour: number; minute: number; timeStr: string }) {
    e.preventDefault();
    if (e.dataTransfer) e.dataTransfer.dropEffect = 'move';
    hoveredSlotId.value = slotId(day, slot);
}

function onSlotDrop(e: DragEvent, day: Date, slot: { hour: number; minute: number; timeStr: string }) {
    e.preventDefault();
    const apt = draggingApt.value;
    if (!apt) return;

    // Correggi il punto di drop sottraendo l'offset di cattura (dove l'utente ha afferrato l'evento)
    const rawMinutes     = slot.hour * 60 + slot.minute - dragOffsetMinutes.value;
    const clampedMinutes = Math.max(0, Math.min(23 * 60 + 45, Math.round(rawMinutes / 15) * 15));
    const startHour      = Math.floor(clampedMinutes / 60);
    const startMin       = clampedMinutes % 60;
    const p              = (n: number) => String(n).padStart(2, '0');

    const ds           = `${day.getFullYear()}-${p(day.getMonth() + 1)}-${p(day.getDate())}`;
    const timeLabel    = `${p(startHour)}:${p(startMin)}`;
    const newStartsAt  = `${ds}T${timeLabel}`;
    const origDuration = new Date(apt.ends_at).getTime() - new Date(apt.starts_at).getTime();
    const newEnd       = new Date(new Date(newStartsAt).getTime() + origDuration);
    const newEndsAt    = toLocalDateTimeStr(newEnd);
    const dayLabel     = day.toLocaleDateString('it-IT', { weekday: 'long', day: 'numeric', month: 'short' });

    pendingMove.value   = { apt, newStartsAt, newEndsAt, dayLabel, timeLabel };
    draggingApt.value   = null;
    hoveredSlotId.value = null;
}

function confirmMove() {
    if (!pendingMove.value || confirmingMove.value) return;
    const { apt, newStartsAt, newEndsAt } = pendingMove.value;
    confirmingMove.value = true;
    router.put(route('nutritionist.appointments.update', apt.id), {
        client_id:   apt.client_id   || null,
        description: apt.description || null,
        starts_at:   newStartsAt,
        ends_at:     newEndsAt,
        location:    apt.location    || null,
        type:        apt.type,
        status:      apt.status,
        notes:       apt.notes       || null,
    }, {
        preserveScroll: true,
        onSuccess: () => { pendingMove.value = null; confirmingMove.value = false; },
        onError:   () => { confirmingMove.value = false; },
    });
}

function cancelMove() {
    pendingMove.value = null;
}

// ─── Calendar helpers ─────────────────────────────────────────────────────────
function appointmentsForDay(date: Date | null): any[] {
    if (!date) return [];
    const ds = date.toISOString().slice(0, 10);
    return props.appointments.data.filter((a: any) => a.starts_at.slice(0, 10) === ds);
}

function isToday(date: Date | null): boolean {
    if (!date) return false;
    return date.toDateString() === new Date().toDateString();
}

// ─── Edit modal ───────────────────────────────────────────────────────────────
const showModal = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    client_id:   '',
    description: '',
    starts_at:   '',
    ends_at:     '',
    location:    '',
    type:        'follow_up',
    status:      'confirmed',
    notes:       '',
});

function openEdit(apt: any) {
    editingId.value  = apt.id;
    form.client_id   = apt.client_id   || '';
    form.description = apt.description || '';
    form.starts_at   = apt.starts_at?.slice(0, 16) || '';
    form.ends_at     = apt.ends_at?.slice(0, 16)   || '';
    form.location    = apt.location || '';
    form.type        = apt.type;
    form.status      = apt.status;
    form.notes       = apt.notes || '';
    showModal.value  = true;
}

function submitEdit() {
    form.put(route('nutritionist.appointments.update', editingId.value!), {
        preserveScroll: true,
        onSuccess: () => { showModal.value = false; },
    });
}

async function deleteAppointment(id: number) {
    const ok = await confirmDialog('L\'appuntamento verrà eliminato definitivamente.', {
        title: 'Elimina appuntamento',
        confirmLabel: 'Elimina',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('nutritionist.appointments.destroy', id), { preserveScroll: true });
}

// ─── Wizard (create flow) ─────────────────────────────────────────────────────
const showWizard      = ref(false);
const wizardStep      = ref<1 | 2 | 3>(1);
const wizardProcessing = ref(false);

const wizard = reactive({
    type:              '',
    client_id:         '' as string | number,
    client_label:      '',
    new_client:        false,
    new_client_name:   '',
    new_client_email:  '',
    new_client_phone:  '',
    date:              '',
    start_time:        '09:00',
    end_time:          '10:00',
    location:          '',
    location_custom:   '',
    description:       '',
    notes:             '',
});

const clientSearch        = ref('');
const clientDropdownOpen  = ref(false);

const filteredClients = computed(() => {
    const q = clientSearch.value.toLowerCase();
    if (!q) return props.clients.slice(0, 20);
    return props.clients.filter((c: any) =>
        (c.user.name + ' ' + (c.user.last_name || '')).toLowerCase().includes(q)
    ).slice(0, 20);
});

function selectClient(c: any) {
    wizard.client_id    = c.id;
    wizard.client_label = c.user.name + (c.user.last_name ? ' ' + c.user.last_name : '');
    wizard.new_client   = false;
    clientSearch.value  = wizard.client_label;
    clientDropdownOpen.value = false;
}

function selectNoClient() {
    wizard.client_id    = '';
    wizard.client_label = 'Nessun cliente';
    wizard.new_client   = false;
    clientSearch.value  = 'Nessun cliente';
    clientDropdownOpen.value = false;
}

function startNewClient() {
    wizard.client_id        = '';
    wizard.client_label     = '';
    wizard.new_client       = true;
    wizard.new_client_name  = '';
    wizard.new_client_email = '';
    clientSearch.value      = '';
    clientDropdownOpen.value = false;
}

function closeClientDropdown() {
    setTimeout(() => { clientDropdownOpen.value = false; }, 180);
}

function durationForType(type: string): number {
    return props.sessionDurations[type] ?? 60;
}

function calcEndTime(type: string, startTime: string): string {
    const [h, m] = startTime.split(':').map(Number);
    const totalMin = h * 60 + m + durationForType(type);
    return `${String(Math.floor(totalMin / 60) % 24).padStart(2, '0')}:${String(totalMin % 60).padStart(2, '0')}`;
}

function onWizardTypeSelect(type: string) {
    wizard.type     = type;
    wizard.end_time = calcEndTime(type, wizard.start_time);
    // don't auto-advance — let user click "Continua"
}

function confirmTypeAndAdvance() {
    if (!wizard.type) return;
    wizardStep.value = 2;
}

function onWizardStartTimeChange() {
    if (wizard.type) wizard.end_time = calcEndTime(wizard.type, wizard.start_time);
}

function openWizard(prefillDate?: Date, prefillTime?: string) {
    wizardStep.value        = 1;
    wizard.type             = '';
    wizard.client_id        = '';
    wizard.client_label     = '';
    wizard.new_client       = false;
    wizard.new_client_name  = '';
    wizard.new_client_email = '';
    wizard.new_client_phone = '';
    wizard.location         = '';
    wizard.location_custom  = '';
    wizard.description      = '';
    wizard.notes            = '';
    wizard.date             = prefillDate
        ? prefillDate.toISOString().slice(0, 10)
        : new Date().toISOString().slice(0, 10);
    wizard.start_time = prefillTime ?? '09:00';
    wizard.end_time   = '10:00';
    clientSearch.value      = '';
    clientDropdownOpen.value = false;
    wizardProcessing.value  = false;
    showWizard.value = true;
}

function onSlotClick(day: Date, slot: { timeStr: string }) {
    openWizard(day, slot.timeStr);
}

function submitWizard() {
    wizardProcessing.value = true;
    const effectiveLoc = wizard.location === '__custom__' ? wizard.location_custom : wizard.location;
    const data: Record<string, any> = {
        type:        wizard.type,
        client_id:   wizard.new_client ? '' : (wizard.client_id || ''),
        starts_at:   `${wizard.date}T${wizard.start_time}`,
        ends_at:     `${wizard.date}T${wizard.end_time}`,
        location:    effectiveLoc,
        description: wizard.description,
        notes:       wizard.notes,
    };
    if (wizard.new_client && wizard.new_client_name) {
        data.new_client_name  = wizard.new_client_name;
        data.new_client_email = wizard.new_client_email;
        data.new_client_phone = wizard.new_client_phone;
    }
    router.post(route('nutritionist.appointments.store'), data, {
        preserveScroll: true,
        onSuccess: () => { showWizard.value = false; wizardProcessing.value = false; },
        onError:   () => { wizardProcessing.value = false; },
    });
}

// Type metadata for wizard
const typeIconMap: Record<string, any> = {
    first_visit: UserPlus,
    follow_up:   RefreshCw,
    online:      Video,
    other:       MoreHorizontal,
};

const typeColorMap: Record<string, { bg: string; border: string; icon: string; selBg: string; selBorder: string }> = {
    first_visit: { bg: 'bg-emerald-50', border: 'border-emerald-200', icon: 'text-emerald-600', selBg: 'bg-emerald-50', selBorder: 'border-emerald-500' },
    follow_up:   { bg: 'bg-blue-50',    border: 'border-blue-200',    icon: 'text-blue-600',    selBg: 'bg-blue-50',    selBorder: 'border-blue-500'    },
    online:      { bg: 'bg-violet-50',  border: 'border-violet-200',  icon: 'text-violet-600',  selBg: 'bg-violet-50',  selBorder: 'border-violet-500'  },
    other:       { bg: 'bg-gray-50',    border: 'border-gray-200',    icon: 'text-gray-500',    selBg: 'bg-gray-50',    selBorder: 'border-gray-500'    },
};

// ─── Formatting helpers ───────────────────────────────────────────────────────
function formatTime(d: string) {
    return new Date(d).toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
}
function formatDateTime(d: string) {
    return new Date(d).toLocaleString('it-IT', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
}
function statusColor(s: string) {
    const map: Record<string, string> = {
        scheduled: 'bg-blue-100 text-blue-700',    confirmed: 'bg-green-100 text-green-700',
        completed: 'bg-gray-100 text-gray-600',    cancelled: 'bg-red-100 text-red-600',
        no_show:   'bg-orange-100 text-orange-600',
    };
    return map[s] || 'bg-gray-100 text-gray-700';
}
function statusDot(s: string) {
    const map: Record<string, string> = {
        scheduled: 'bg-blue-400',  confirmed: 'bg-green-400',
        completed: 'bg-gray-400',  cancelled: 'bg-red-400',  no_show: 'bg-orange-400',
    };
    return map[s] || 'bg-gray-400';
}
function statusLabel(s: string) { return props.statuses.find(st => st.value === s)?.label || s; }
function typeLabel(t: string)   { return props.types.find(tp => tp.value === t)?.label   || t; }

const gridStyle = (cols: number) => ({ display: 'grid', gridTemplateColumns: `repeat(${cols}, minmax(0, 1fr))` });

function goToDay(date: Date) {
    currentDate.value = new Date(date);
    viewMode.value = 'day';
    fetchMonth();
    nextTick(() => scrollToCurrentTime());
}
</script>

<template>
    <Head title="Appuntamenti" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap gap-2">
                <h1 class="text-xl font-semibold text-gray-900">Appuntamenti</h1>
                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Toggle weekend -->
                    <button
                        v-if="viewMode !== 'list'"
                        @click="showWeekend = !showWeekend"
                        :class="['rounded-lg px-3 py-1.5 text-xs font-medium border transition',
                            showWeekend ? 'border-primary-300 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-500 hover:bg-gray-50']"
                    >
                        {{ showWeekend ? 'Nascondi weekend' : 'Mostra weekend' }}
                    </button>
                    <!-- Toggle vista -->
                    <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                        <button @click="viewMode = 'month'" :class="['flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium transition', viewMode === 'month' ? 'bg-primary-500 text-white' : 'text-gray-600 hover:bg-gray-50']">
                            <Calendar class="h-4 w-4" /> Mese
                        </button>
                        <button @click="viewMode = 'week'; nextTick(scrollToCurrentTime)" :class="['flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium border-l border-gray-200 transition', viewMode === 'week' ? 'bg-primary-500 text-white' : 'text-gray-600 hover:bg-gray-50']">
                            <CalendarDays class="h-4 w-4" /> Settimana
                        </button>
                        <button @click="viewMode = 'day'; nextTick(scrollToCurrentTime)" :class="['flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium border-l border-gray-200 transition', viewMode === 'day' ? 'bg-primary-500 text-white' : 'text-gray-600 hover:bg-gray-50']">
                            <Clock class="h-4 w-4" /> Giorno
                        </button>
                        <button @click="viewMode = 'list'" :class="['flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium border-l border-gray-200 transition', viewMode === 'list' ? 'bg-primary-500 text-white' : 'text-gray-600 hover:bg-gray-50']">
                            <LayoutList class="h-4 w-4" /> Lista
                        </button>
                    </div>
                    <button @click="openWizard()" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 px-4 py-2 text-sm font-medium text-white hover:from-primary-600 hover:to-primary-700 transition shadow-sm">
                        <Plus class="h-4 w-4" /> Nuovo
                    </button>
                </div>
            </div>
        </template>

        <!-- ═══════════════════════ CALENDARIO MESE ═══════════════════════ -->
        <template v-if="viewMode === 'month'">
            <div class="flex items-center justify-between mb-4">
                <button @click="prevPeriod" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 transition"><ChevronLeft class="h-5 w-5" /></button>
                <div class="flex items-center gap-3">
                    <button @click="goToToday" class="rounded-lg px-3 py-1.5 text-sm font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Oggi</button>
                    <h2 class="text-base font-semibold text-gray-900 capitalize">{{ monthLabel }}</h2>
                </div>
                <button @click="nextPeriod" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 transition"><ChevronRight class="h-5 w-5" /></button>
            </div>

            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden">
                <div :style="gridStyle(showWeekend ? 7 : 5)" class="border-b border-gray-100">
                    <div v-for="day in visibleWeekDayLabels" :key="day" class="py-2 text-center text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ day }}</div>
                </div>
                <div :style="gridStyle(showWeekend ? 7 : 5)">
                    <div
                        v-for="(day, idx) in (showWeekend ? calendarDays : calendarDaysWeekdaysOnly)"
                        :key="idx"
                        :class="['min-h-[90px] border-b border-r border-gray-50 p-1.5',
                            (showWeekend ? (idx + 1) % 7 === 0 : (idx + 1) % 5 === 0) ? 'border-r-0' : '',
                            !day ? 'bg-gray-50/50' : 'cursor-pointer hover:bg-gray-50/60 transition-colors']"
                        @click="day && openWizard(day)"
                    >
                        <span v-if="day" :class="['inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium mb-1', isToday(day) ? 'bg-primary-500 text-white' : 'text-gray-700']">{{ day.getDate() }}</span>
                        <div class="space-y-0.5">
                            <button v-for="apt in appointmentsForDay(day).slice(0, 3)" :key="apt.id" type="button" @click.stop="openEdit(apt)"
                                :class="['w-full text-left rounded px-1.5 py-0.5 text-xs font-medium truncate flex items-center gap-1', statusColor(apt.status)]" :title="apt.title">
                                <span :class="['h-1.5 w-1.5 rounded-full flex-shrink-0', statusDot(apt.status)]"></span>
                                <span class="truncate">{{ formatTime(apt.starts_at) }} {{ apt.title }}</span>
                            </button>
                            <span v-if="appointmentsForDay(day).length > 3" class="text-xs text-gray-400 pl-1">+{{ appointmentsForDay(day).length - 3 }} altri</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap gap-3 text-xs text-gray-500">
                <span v-for="s in statuses" :key="s.value" class="flex items-center gap-1.5">
                    <span :class="['h-2 w-2 rounded-full', statusDot(s.value)]"></span> {{ s.label }}
                </span>
            </div>
        </template>

        <!-- ═══════════════════ CALENDARIO SETTIMANA (TIME GRID) ══════════════ -->
        <template v-else-if="viewMode === 'week'">
            <div class="flex items-center justify-between mb-4">
                <button @click="prevPeriod" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 transition"><ChevronLeft class="h-5 w-5" /></button>
                <div class="flex items-center gap-3">
                    <button @click="goToToday" class="rounded-lg px-3 py-1.5 text-sm font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Oggi</button>
                    <h2 class="text-base font-semibold text-gray-900">{{ weekLabel }}</h2>
                </div>
                <button @click="nextPeriod" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 transition"><ChevronRight class="h-5 w-5" /></button>
            </div>

            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <!-- Sticky header: day names + dates -->
                <div class="flex border-b border-gray-100 bg-white z-10">
                    <div class="w-14 flex-shrink-0 border-r border-gray-100"></div>
                    <div class="flex-1" :style="gridStyle(weekDaysWithDates.length)">
                        <div
                            v-for="d in weekDaysWithDates"
                            :key="d.toISOString()"
                            :class="['py-3 text-center border-l border-gray-100 first:border-l-0 cursor-pointer hover:bg-gray-50 transition', isToday(d) ? 'bg-primary-50' : '']"
                            @click="goToDay(d)"
                        >
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide capitalize">
                                {{ d.toLocaleDateString('it-IT', { weekday: 'short' }) }}
                            </p>
                            <span :class="['inline-flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold mt-0.5',
                                isToday(d) ? 'bg-primary-500 text-white' : 'text-gray-800']">
                                {{ d.getDate() }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Scrollable time grid: full 24h, default scrolled to 8am -->
                <div ref="timeGridRef" class="overflow-y-auto" style="max-height: 68vh">
                    <div class="flex" :style="`height: ${TOTAL_PX}px`">

                        <!-- Time labels column -->
                        <div class="w-14 flex-shrink-0 relative border-r border-gray-100 select-none">
                            <div
                                v-for="h in hours"
                                :key="h"
                                class="absolute right-2 text-[11px] text-gray-400 font-medium -translate-y-2"
                                :style="`top: ${h * HOUR_HEIGHT}px`"
                            >
                                {{ h < END_HOUR ? `${String(h).padStart(2,'0')}:00` : '' }}
                            </div>
                        </div>

                        <!-- Day columns -->
                        <div class="flex-1" :style="gridStyle(weekDaysWithDates.length)">
                            <div
                                v-for="day in weekDaysWithDates"
                                :key="day.toISOString()"
                                class="relative border-l border-gray-100 first:border-l-0"
                            >
                                <!-- 15-min slot divs -->
                                <div
                                    v-for="slot in timeSlots"
                                    :key="slot.key"
                                    :class="[
                                        'absolute left-0 right-0 cursor-pointer transition-colors',
                                        slot.minute === 0 ? 'border-t border-gray-200' : 'border-t border-gray-100/60',
                                        hoveredSlotId === slotId(day, slot) && draggingApt ? 'bg-primary-50/80' : 'hover:bg-gray-50/60',
                                    ]"
                                    :style="`top: ${slot.hour * HOUR_HEIGHT + (slot.minute / 60) * HOUR_HEIGHT}px; height: ${HOUR_HEIGHT / 4}px`"
                                    @dragover.prevent="onSlotDragOver($event, day, slot)"
                                    @dragleave="hoveredSlotId = null"
                                    @drop.prevent="onSlotDrop($event, day, slot)"
                                    @click="onSlotClick(day, slot)"
                                ></div>

                                <!-- Current time indicator (today only) -->
                                <div
                                    v-if="isToday(day)"
                                    class="absolute left-0 right-0 z-20 pointer-events-none flex items-center"
                                    :style="`top: ${currentTimeTopPx}px`"
                                >
                                    <div class="h-2.5 w-2.5 rounded-full bg-red-500 -ml-1.5 flex-shrink-0"></div>
                                    <div class="flex-1 border-t-2 border-red-500"></div>
                                </div>

                                <!-- Appointment blocks (with overlap layout) -->
                                <div
                                    v-for="slot in dayAppointmentLayouts(day)"
                                    :key="slot.apt.id"
                                    class="absolute rounded-lg overflow-hidden z-10 cursor-grab active:cursor-grabbing shadow-sm select-none group"
                                    :class="[aptBgColor(slot.apt.status), draggingApt?.id === slot.apt.id ? 'opacity-40' : '']"
                                    :style="`top: ${aptTopPx(slot.apt)}px; height: ${aptHeightPx(slot.apt)}px; min-height: 22px; left: calc(${(slot.col / slot.totalCols) * 100}% + 2px); width: calc(${100 / slot.totalCols}% - 4px)`"
                                    draggable="true"
                                    @dragstart="onDragStart($event, slot.apt)"
                                    @dragend="onDragEnd"
                                    @click.stop="openEdit(slot.apt)"
                                >
                                    <div class="px-2 py-1 h-full overflow-hidden">
                                        <p class="text-[11px] font-semibold leading-tight truncate">{{ slot.apt.title }}</p>
                                        <p class="text-[10px] opacity-70 leading-tight mt-0.5">{{ formatTime(slot.apt.starts_at) }}–{{ formatTime(slot.apt.ends_at) }}</p>
                                    </div>
                                    <button
                                        type="button"
                                        @click.stop="openEdit(slot.apt)"
                                        class="absolute top-0.5 right-0.5 p-0.5 rounded opacity-0 group-hover:opacity-60 hover:!opacity-100 transition bg-white/40"
                                    >
                                        <Pencil class="h-2.5 w-2.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap gap-3 text-xs text-gray-500">
                <span v-for="s in statuses" :key="s.value" class="flex items-center gap-1.5">
                    <span :class="['h-2 w-2 rounded-full', statusDot(s.value)]"></span> {{ s.label }}
                </span>
            </div>
        </template>

        <!-- ═══════════════════ CALENDARIO GIORNO (SINGLE DAY TIME GRID) ══════════════ -->
        <template v-else-if="viewMode === 'day'">
            <div class="flex items-center justify-between mb-4">
                <button @click="prevPeriod" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 transition"><ChevronLeft class="h-5 w-5" /></button>
                <div class="flex items-center gap-3">
                    <button @click="goToToday" class="rounded-lg px-3 py-1.5 text-sm font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Oggi</button>
                    <h2 class="text-base font-semibold text-gray-900 capitalize">{{ dayLabel }}</h2>
                </div>
                <button @click="nextPeriod" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 transition"><ChevronRight class="h-5 w-5" /></button>
            </div>

            <div class="rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <!-- Day header -->
                <div class="flex border-b border-gray-100 bg-white z-10">
                    <div class="w-14 flex-shrink-0 border-r border-gray-100"></div>
                    <div class="flex-1">
                        <div :class="['py-3 text-center', isToday(currentDate) ? 'bg-primary-50' : '']">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide capitalize">
                                {{ currentDate.toLocaleDateString('it-IT', { weekday: 'long' }) }}
                            </p>
                            <span :class="['inline-flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold mt-0.5',
                                isToday(currentDate) ? 'bg-primary-500 text-white' : 'text-gray-800']">
                                {{ currentDate.getDate() }}
                            </span>
                            <p class="text-xs text-gray-400 mt-0.5">{{ appointmentsForDay(currentDate).length }} appuntament{{ appointmentsForDay(currentDate).length === 1 ? 'o' : 'i' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Scrollable time grid -->
                <div ref="timeGridRef" class="overflow-y-auto" style="max-height: 72vh">
                    <div class="flex" :style="`height: ${TOTAL_PX}px`">
                        <!-- Time labels column -->
                        <div class="w-14 flex-shrink-0 relative border-r border-gray-100 select-none">
                            <div
                                v-for="h in hours"
                                :key="h"
                                class="absolute right-2 text-[11px] text-gray-400 font-medium -translate-y-2"
                                :style="`top: ${h * HOUR_HEIGHT}px`"
                            >
                                {{ h < END_HOUR ? `${String(h).padStart(2,'0')}:00` : '' }}
                            </div>
                        </div>

                        <!-- Single day column -->
                        <div class="flex-1 relative">
                            <!-- 15-min slot divs -->
                            <div
                                v-for="slot in timeSlots"
                                :key="slot.key"
                                :class="[
                                    'absolute left-0 right-0 cursor-pointer transition-colors',
                                    slot.minute === 0 ? 'border-t border-gray-200' : 'border-t border-gray-100/60',
                                    hoveredSlotId === slotId(currentDate, slot) && draggingApt ? 'bg-primary-50/80' : 'hover:bg-gray-50/60',
                                ]"
                                :style="`top: ${slot.hour * HOUR_HEIGHT + (slot.minute / 60) * HOUR_HEIGHT}px; height: ${HOUR_HEIGHT / 4}px`"
                                @dragover.prevent="onSlotDragOver($event, currentDate, slot)"
                                @dragleave="hoveredSlotId = null"
                                @drop.prevent="onSlotDrop($event, currentDate, slot)"
                                @click="onSlotClick(currentDate, slot)"
                            ></div>

                            <!-- Current time indicator -->
                            <div
                                v-if="isToday(currentDate)"
                                class="absolute left-0 right-0 z-20 pointer-events-none flex items-center"
                                :style="`top: ${currentTimeTopPx}px`"
                            >
                                <div class="h-2.5 w-2.5 rounded-full bg-red-500 -ml-1.5 flex-shrink-0"></div>
                                <div class="flex-1 border-t-2 border-red-500"></div>
                            </div>

                            <!-- Appointment blocks (with overlap layout) -->
                            <div
                                v-for="slot in dayAppointmentLayouts(currentDate)"
                                :key="slot.apt.id"
                                class="absolute rounded-lg overflow-hidden z-10 cursor-grab active:cursor-grabbing shadow-sm select-none group"
                                :class="[aptBgColor(slot.apt.status), draggingApt?.id === slot.apt.id ? 'opacity-40' : '']"
                                :style="`top: ${aptTopPx(slot.apt)}px; height: ${aptHeightPx(slot.apt)}px; min-height: 28px; left: calc(${(slot.col / slot.totalCols) * 100}% + 4px); width: calc(${100 / slot.totalCols}% - 8px)`"
                                draggable="true"
                                @dragstart="onDragStart($event, slot.apt)"
                                @dragend="onDragEnd"
                                @click.stop="openEdit(slot.apt)"
                            >
                                <div class="px-3 py-1.5 h-full overflow-hidden">
                                    <p class="text-sm font-semibold leading-tight truncate">{{ slot.apt.title }}</p>
                                    <p class="text-xs opacity-70 leading-tight mt-0.5">{{ formatTime(slot.apt.starts_at) }} – {{ formatTime(slot.apt.ends_at) }}</p>
                                    <p v-if="slot.apt.client?.user?.name" class="text-xs opacity-60 leading-tight mt-0.5 flex items-center gap-1">
                                        <User class="h-3 w-3" /> {{ slot.apt.client.user.name }}
                                    </p>
                                    <p v-if="slot.apt.location" class="text-xs opacity-60 leading-tight mt-0.5 flex items-center gap-1">
                                        <MapPin class="h-3 w-3" /> {{ slot.apt.location }}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click.stop="openEdit(slot.apt)"
                                    class="absolute top-1 right-1 p-1 rounded opacity-0 group-hover:opacity-60 hover:!opacity-100 transition bg-white/40"
                                >
                                    <Pencil class="h-3 w-3" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap gap-3 text-xs text-gray-500">
                <span v-for="s in statuses" :key="s.value" class="flex items-center gap-1.5">
                    <span :class="['h-2 w-2 rounded-full', statusDot(s.value)]"></span> {{ s.label }}
                </span>
            </div>
        </template>

        <!-- ═══════════════════════════ LISTA ═══════════════════════════ -->
        <template v-else>
            <div class="mb-6 flex flex-col sm:flex-row gap-3">
                <input
                    type="month"
                    :value="props.filters.month || new Date().toISOString().slice(0,7)"
                    class="rounded-lg border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500"
                    @change="($event: any) => { router.get(route('nutritionist.appointments.index'), { month: $event.target.value, client_id: filterClientId || undefined, status: filterStatus || undefined }, { preserveState: true, replace: true }) }"
                />
                <select v-model="filterClientId" @change="fetchMonth" class="rounded-lg border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Tutti i clienti</option>
                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.user.name }}</option>
                </select>
                <select v-model="filterStatus" @change="fetchMonth" class="rounded-lg border-gray-200 py-2.5 text-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Tutti gli stati</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
            </div>

            <div v-if="appointments.data.length === 0" class="rounded-2xl bg-white border border-gray-100 p-12 text-center shadow-sm">
                <Calendar class="mx-auto h-12 w-12 text-gray-300 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun appuntamento</h3>
                <p class="text-sm text-gray-500">Nessun appuntamento per il periodo selezionato.</p>
            </div>
            <div v-else class="space-y-3">
                <div v-for="apt in appointments.data" :key="apt.id" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <h3 class="font-semibold text-gray-900">{{ apt.title }}</h3>
                                <span :class="['rounded-full px-2 py-0.5 text-xs font-medium', statusColor(apt.status)]">{{ statusLabel(apt.status) }}</span>
                                <span class="rounded-full bg-gray-50 text-gray-500 px-2 py-0.5 text-xs">{{ typeLabel(apt.type) }}</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                                <span v-if="apt.client" class="flex items-center gap-1"><User class="h-3.5 w-3.5" /> {{ apt.client.user?.name }}</span>
                                <span class="flex items-center gap-1"><Calendar class="h-3.5 w-3.5" /> {{ formatDateTime(apt.starts_at) }}</span>
                                <span class="flex items-center gap-1"><Clock class="h-3.5 w-3.5" /> {{ formatTime(apt.starts_at) }} - {{ formatTime(apt.ends_at) }}</span>
                                <span v-if="apt.location" class="flex items-center gap-1"><MapPin class="h-3.5 w-3.5" /> {{ apt.location }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 ml-4">
                            <button @click="openEdit(apt)" class="p-1.5 text-gray-400 hover:text-primary-600 transition"><Pencil class="h-4 w-4" /></button>
                            <button @click="deleteAppointment(apt.id)" class="p-1.5 text-gray-400 hover:text-red-500 transition"><Trash2 class="h-4 w-4" /></button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- ═══════════════ EDIT MODAL ═══════════════ -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showModal = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-semibold text-gray-900">Modifica Appuntamento</h3>
                    <button @click="showModal = false" class="p-1 text-gray-400 hover:text-gray-600"><X class="h-5 w-5" /></button>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                        <select v-model="form.client_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                            <option value="">Nessun cliente</option>
                            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.user.name }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Inizio *</label>
                            <input v-model="form.starts_at" type="datetime-local" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                            <p v-if="form.errors.starts_at" class="mt-1 text-xs text-red-600">{{ form.errors.starts_at }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fine *</label>
                            <input v-model="form.ends_at" type="datetime-local" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                            <p v-if="form.errors.ends_at" class="mt-1 text-xs text-red-600">{{ form.errors.ends_at }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select v-model="form.type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stato</label>
                            <select v-model="form.status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Luogo</label>
                        <input v-model="form.location" type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                        <textarea v-model="form.notes" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"></textarea>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <button type="button" @click="deleteAppointment(editingId!); showModal = false" class="text-sm text-red-500 hover:text-red-700 flex items-center gap-1">
                            <Trash2 class="h-3.5 w-3.5" /> Elimina
                        </button>
                        <div class="flex gap-2">
                            <button type="button" @click="showModal = false" class="rounded-lg px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 transition">Annulla</button>
                            <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white hover:bg-primary-600 transition disabled:opacity-40">Salva</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ═══════════════ WIZARD CREATE ═══════════════ -->
        <div v-if="showWizard" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showWizard = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
                <!-- Wizard header -->
                <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <button v-if="wizardStep > 1" type="button" @click="wizardStep--" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 transition">
                            <ChevronLeft class="h-4 w-4" />
                        </button>
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Passo {{ wizardStep }} di 3</p>
                            <h3 class="text-base font-semibold text-gray-900">
                                {{ wizardStep === 1 ? 'Tipo di appuntamento' : wizardStep === 2 ? 'Cliente' : 'Data, orario e luogo' }}
                            </h3>
                        </div>
                    </div>
                    <button @click="showWizard = false" class="p-1 text-gray-400 hover:text-gray-600"><X class="h-5 w-5" /></button>
                </div>

                <!-- Step progress bar -->
                <div class="flex gap-1.5 px-6 pt-3 pb-1">
                    <div v-for="s in 3" :key="s" :class="['h-1 flex-1 rounded-full transition-colors duration-300', s <= wizardStep ? 'bg-green-500' : 'bg-gray-200']"></div>
                </div>

                <!-- Step 1: Tipo -->
                <div v-if="wizardStep === 1" class="p-6">
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        <button
                            v-for="t in types"
                            :key="t.value"
                            type="button"
                            @click="onWizardTypeSelect(t.value)"
                            :class="[
                                'flex flex-col items-center gap-3 p-5 rounded-xl border-2 text-center transition-all duration-200 cursor-pointer',
                                wizard.type === t.value
                                    ? 'border-green-500 bg-green-50 shadow-sm'
                                    : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50',
                            ]"
                        >
                            <div :class="[
                                'h-11 w-11 rounded-xl flex items-center justify-center transition-colors duration-200',
                                wizard.type === t.value ? 'bg-green-100' : 'bg-gray-100',
                            ]">
                                <component
                                    :is="typeIconMap[t.value] ?? MoreHorizontal"
                                    :class="['h-5 w-5 transition-colors duration-200', wizard.type === t.value ? 'text-green-600' : 'text-gray-400']"
                                />
                            </div>
                            <span :class="['text-sm font-semibold transition-colors duration-200', wizard.type === t.value ? 'text-green-800' : 'text-gray-500']">
                                {{ t.label }}
                            </span>
                        </button>
                    </div>

                    <!-- "Continua" button slides in when type selected -->
                    <Transition name="slide-up-btn">
                        <button
                            v-if="wizard.type"
                            type="button"
                            @click="confirmTypeAndAdvance"
                            class="w-full rounded-xl bg-green-500 px-5 py-3 text-sm font-semibold text-white hover:bg-green-600 transition-colors shadow-sm flex items-center justify-center gap-2"
                        >
                            Continua <ChevronRight class="h-4 w-4" />
                        </button>
                    </Transition>
                </div>

                <!-- Step 2: Cliente -->
                <div v-else-if="wizardStep === 2" class="px-6 pb-6 pt-4 space-y-4">
                    <!-- Searchable client inline list -->
                    <div v-if="!wizard.new_client">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cliente <span class="text-red-500">*</span></label>
                        <input
                            v-model="clientSearch"
                            type="text"
                            placeholder="Cerca per nome..."
                            autocomplete="off"
                            :class="['block w-full text-sm border-gray-300 focus:border-green-500 focus:ring-green-500',
                                clientDropdownOpen ? 'rounded-t-lg border-b-0' : 'rounded-lg']"
                            @focus="clientDropdownOpen = true"
                            @blur="closeClientDropdown"
                        />
                        <!-- Inline list — no absolute, no overflow clipping -->
                        <div v-if="clientDropdownOpen" class="w-full bg-white rounded-b-xl border border-t-0 border-gray-300 max-h-52 overflow-y-auto shadow-md">
                            <button
                                v-for="c in filteredClients"
                                :key="c.id"
                                type="button"
                                @click="selectClient(c)"
                                class="w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-green-50 hover:text-green-800 flex items-center gap-2"
                            >
                                <User class="h-4 w-4 text-gray-400 flex-shrink-0" />
                                {{ c.user.name }}{{ c.user.last_name ? ' ' + c.user.last_name : '' }}
                            </button>
                            <button type="button" @click="startNewClient"
                                class="w-full px-4 py-2.5 text-left text-sm text-green-600 hover:bg-green-50 flex items-center gap-2 border-t border-gray-100 font-medium">
                                <Plus class="h-4 w-4 flex-shrink-0" /> Nuovo cliente
                            </button>
                        </div>
                    </div>

                    <!-- Quick new client form -->
                    <div v-if="wizard.new_client" class="rounded-xl border border-green-200 bg-green-50 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-green-800">Nuovo cliente</p>
                            <button type="button" @click="wizard.new_client = false; clientSearch = ''" class="text-green-400 hover:text-green-600">
                                <X class="h-4 w-4" />
                            </button>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Nome *</label>
                            <input v-model="wizard.new_client_name" type="text" placeholder="Mario Rossi" class="block w-full rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-green-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Email *</label>
                            <input v-model="wizard.new_client_email" type="email" placeholder="mario@email.com" class="block w-full rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-green-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Telefono <span class="text-gray-400 font-normal">(facoltativo)</span></label>
                            <input v-model="wizard.new_client_phone" type="tel" placeholder="+39 333 000 0000" class="block w-full rounded-lg border-gray-300 text-sm focus:border-green-500 focus:ring-green-500" />
                        </div>
                        <p class="text-xs text-gray-500">Il cliente verrà creato e potrai inviare l'invito di accesso dalla sua scheda.</p>
                    </div>

                    <div class="flex justify-end pt-1">
                        <button
                            type="button"
                            @click="wizardStep = 3"
                            :disabled="(!wizard.client_id && !wizard.new_client) || (wizard.new_client && !wizard.new_client_name)"
                            class="w-full rounded-xl bg-green-500 px-5 py-3 text-sm font-semibold text-white hover:bg-green-600 transition disabled:opacity-40 flex items-center justify-center gap-2"
                        >
                            Continua <ChevronRight class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <!-- Step 3: Data, ora e luogo -->
                <div v-else-if="wizardStep === 3" class="p-6 space-y-4">
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data *</label>
                            <input v-model="wizard.date" type="date" required class="block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500" />
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Inizio *</label>
                            <input v-model="wizard.start_time" type="time" required @change="onWizardStartTimeChange" class="block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500" />
                        </div>
                        <div class="col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fine *</label>
                            <input v-model="wizard.end_time" type="time" required class="block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500" />
                        </div>
                    </div>

                    <!-- Location / video link -->
                    <div v-if="wizard.type === 'online'">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Link videochiamata <span class="text-gray-400 font-normal">(facoltativo)</span>
                        </label>
                        <input
                            v-model="wizard.location"
                            type="url"
                            placeholder="https://meet.google.com/..."
                            class="block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>
                    <div v-else>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Luogo <span class="text-gray-400 font-normal">(facoltativo)</span></label>
                        <select v-if="locations.length > 0" v-model="wizard.location" class="block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Nessun luogo</option>
                            <option v-for="loc in locations" :key="loc" :value="loc">{{ loc }}</option>
                            <option value="__custom__">Altro (specifica sotto)</option>
                        </select>
                        <input v-else v-model="wizard.location" type="text" placeholder="Studio, domicilio, ecc." class="block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500" />
                        <input
                            v-if="wizard.location === '__custom__'"
                            v-model="wizard.location_custom"
                            type="text"
                            placeholder="Inserisci il luogo..."
                            class="mt-2 block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Note <span class="text-gray-400 font-normal">(opzionale)</span></label>
                        <textarea v-model="wizard.notes" rows="2" placeholder="Note sull'appuntamento..." class="block w-full rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="showWizard = false" class="rounded-lg px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 transition">Annulla</button>
                        <button
                            type="button"
                            @click="submitWizard"
                            :disabled="wizardProcessing || !wizard.date || !wizard.start_time || !wizard.end_time"
                            class="rounded-lg bg-primary-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-600 transition disabled:opacity-40 flex items-center gap-2"
                        >
                            <Check class="h-4 w-4" />
                            {{ wizardProcessing ? 'Creazione...' : 'Crea appuntamento' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drag-move confirmation bar -->
        <Transition name="slide-up">
            <div
                v-if="pendingMove"
                class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 bg-gray-900 text-white rounded-2xl px-5 py-4 shadow-2xl flex items-center gap-4 min-w-max max-w-[calc(100vw-2rem)]"
            >
                <div class="text-sm leading-snug">
                    Sposta <span class="font-semibold">{{ pendingMove.apt.title }}</span>
                    <span class="text-gray-400 mx-1">→</span>
                    <span class="text-primary-300 font-medium capitalize">{{ pendingMove.dayLabel }}</span>
                    <span class="text-gray-400 mx-1">alle</span>
                    <span class="font-medium">{{ pendingMove.timeLabel }}</span>
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <button
                        @click="confirmMove"
                        :disabled="confirmingMove"
                        class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium hover:bg-primary-400 transition disabled:opacity-50"
                    >
                        {{ confirmingMove ? '...' : 'Conferma' }}
                    </button>
                    <button @click="cancelMove" class="rounded-lg bg-white/10 px-4 py-2 text-sm hover:bg-white/20 transition">
                        Annulla
                    </button>
                </div>
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Drag-move toast */
.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.25s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
    opacity: 0;
    transform: translate(-50%, 16px);
}
.slide-up-enter-to,
.slide-up-leave-from {
    opacity: 1;
    transform: translate(-50%, 0);
}

/* "Continua" button in wizard step 1 */
.slide-up-btn-enter-active {
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.slide-up-btn-leave-active {
    transition: all 0.15s ease;
}
.slide-up-btn-enter-from {
    opacity: 0;
    transform: translateY(10px);
}
.slide-up-btn-enter-to {
    opacity: 1;
    transform: translateY(0);
}
.slide-up-btn-leave-to {
    opacity: 0;
}
</style>
