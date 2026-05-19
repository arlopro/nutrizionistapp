<script setup lang="ts">
import { computed, ref } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps<{
    appointments: any[];
}>();

const today = new Date();
const viewDate = ref(new Date(today.getFullYear(), today.getMonth(), 1));

function prev() { viewDate.value = new Date(viewDate.value.getFullYear(), viewDate.value.getMonth() - 1, 1); }
function next() { viewDate.value = new Date(viewDate.value.getFullYear(), viewDate.value.getMonth() + 1, 1); }

const monthLabel = computed(() =>
    viewDate.value.toLocaleDateString('it-IT', { month: 'long', year: 'numeric' }).replace(/^\w/, c => c.toUpperCase())
);

const days = computed(() => {
    const year = viewDate.value.getFullYear();
    const month = viewDate.value.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    // Monday-first: getDay() returns 0=Sun, fix offset
    const startOffset = (firstDay.getDay() + 6) % 7;
    const result: (null | number)[] = Array(startOffset).fill(null);
    for (let d = 1; d <= lastDay.getDate(); d++) result.push(d);
    while (result.length % 7 !== 0) result.push(null);
    return result;
});

function appointmentsOnDay(day: number) {
    const year = viewDate.value.getFullYear();
    const month = viewDate.value.getMonth();
    return props.appointments.filter(a => {
        const d = new Date(a.starts_at);
        return d.getFullYear() === year && d.getMonth() === month && d.getDate() === day;
    });
}

function isToday(day: number) {
    return today.getFullYear() === viewDate.value.getFullYear()
        && today.getMonth() === viewDate.value.getMonth()
        && today.getDate() === day;
}

function dotColor(apt: any) {
    const map: Record<string, string> = { confirmed: 'bg-green-500', scheduled: 'bg-amber-400', completed: 'bg-gray-400', cancelled: 'bg-red-400' };
    return map[apt.status] ?? 'bg-gray-400';
}
</script>

<template>
    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <button @click="prev" class="p-1 rounded-lg text-gray-400 hover:bg-gray-100 transition"><ChevronLeft class="h-4 w-4" /></button>
            <p class="text-sm font-semibold text-gray-900">{{ monthLabel }}</p>
            <button @click="next" class="p-1 rounded-lg text-gray-400 hover:bg-gray-100 transition"><ChevronRight class="h-4 w-4" /></button>
        </div>

        <!-- Day names -->
        <div class="grid grid-cols-7 mb-1">
            <p v-for="d in ['L','M','M','G','V','S','D']" :key="d" class="text-center text-[10px] font-medium text-gray-400 py-1">{{ d }}</p>
        </div>

        <!-- Days -->
        <div class="grid grid-cols-7 gap-y-1">
            <div v-for="(day, i) in days" :key="i" class="flex flex-col items-center py-1">
                <div v-if="day" :class="[
                    'flex h-7 w-7 items-center justify-center rounded-full text-xs font-medium transition',
                    isToday(day) ? 'bg-gray-900 text-white font-bold' :
                    appointmentsOnDay(day).length > 0 ? 'text-green-700 font-semibold' : 'text-gray-700',
                ]">
                    {{ day }}
                </div>
                <div v-if="day && appointmentsOnDay(day).length > 0" class="flex gap-0.5 mt-0.5">
                    <div v-for="apt in appointmentsOnDay(day).slice(0, 3)" :key="apt.id" :class="['h-1 w-1 rounded-full', dotColor(apt)]"></div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-3 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500 border-t border-gray-100 pt-3">
            <div class="flex items-center gap-1.5"><div class="h-2 w-2 rounded-full bg-green-500"></div> Confermato</div>
            <div class="flex items-center gap-1.5"><div class="h-2 w-2 rounded-full bg-amber-400"></div> Da confermare</div>
        </div>
    </div>
</template>
