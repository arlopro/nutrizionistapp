<script setup lang="ts">
import { ref, computed, nextTick, onMounted, onUnmounted, watch } from 'vue';
import { X, ArrowRight, ArrowUp, ArrowDown, Check, ChevronRight, CornerDownLeft, Pencil, Send } from 'lucide-vue-next';

export interface TFQuestion {
    id: string;
    type: 'text' | 'textarea' | 'radio' | 'checkbox' | 'number' | 'scale';
    label: string;
    required: boolean;
    options?: string[];
}

const props = defineProps<{
    questions: TFQuestion[];
    answers: Record<string, any>;
    title: string;
    description?: string | null;
    previewMode?: boolean;
    exitLabel?: string;
}>();

const emit = defineEmits<{
    'update:answers': [value: Record<string, any>];
    'submit': [];
    'close': [];
}>();

// -1 = welcome, 0..n-1 = questions, n = recap, submitted = done
const step = ref(-1);
const submitted = ref(false);
const direction = ref<'forward' | 'backward'>('forward');
const errorMsg = ref('');
const inputRef = ref<HTMLElement | null>(null);

const total = computed(() => props.questions.length);
const isRecap = computed(() => step.value >= total.value && !submitted.value);
const isDone = computed(() => submitted.value);
const progress = computed(() => {
    if (step.value <= 0) return 0;
    if (isRecap.value || isDone.value) return 100;
    return Math.round((step.value / total.value) * 100);
});
const currentQ = computed<TFQuestion | null>(() =>
    step.value >= 0 && step.value < total.value ? props.questions[step.value] : null
);

function getVal(qId: string) {
    return props.answers[qId];
}

function setVal(qId: string, value: any) {
    emit('update:answers', { ...props.answers, [qId]: value });
}

function validate(): boolean {
    const q = currentQ.value;
    if (!q || !q.required) return true;
    const val = getVal(q.id);
    if (q.type === 'checkbox') return Array.isArray(val) && val.length > 0;
    return val !== '' && val !== null && val !== undefined;
}

function advance() {
    if (step.value >= 0 && step.value < total.value && !validate()) {
        errorMsg.value = 'Questo campo è obbligatorio.';
        return;
    }
    errorMsg.value = '';
    direction.value = 'forward';
    step.value++;
}

function confirmSubmit() {
    submitted.value = true;
    emit('submit');
}

function editQuestion(index: number) {
    direction.value = 'backward';
    step.value = index;
}

function formatAnswer(q: TFQuestion): string {
    const val = getVal(q.id);
    if (!val && val !== 0) return '—';
    if (Array.isArray(val)) return val.length ? val.join(', ') : '—';
    return String(val);
}

function retreat() {
    if (step.value <= -1) return;
    errorMsg.value = '';
    direction.value = 'backward';
    step.value--;
}

function handleKeydown(e: KeyboardEvent) {
    if (isDone.value) return;
    if (isRecap.value) return;
    if (e.key === 'Enter') {
        if (currentQ.value?.type === 'textarea') return;
        e.preventDefault();
        advance();
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
    document.body.style.overflow = 'hidden';
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});

watch(step, async () => {
    errorMsg.value = '';
    await nextTick();
    inputRef.value?.focus();
});

function radioSelect(qId: string, option: string) {
    setVal(qId, option);
    setTimeout(advance, 380);
}

function scaleSelect(qId: string, n: number) {
    setVal(qId, n);
    setTimeout(advance, 320);
}

function toggleCheckbox(qId: string, option: string) {
    const current: string[] = getVal(qId) || [];
    const next = current.includes(option)
        ? current.filter((o) => o !== option)
        : [...current, option];
    setVal(qId, next);
}

const transitionName = computed(() =>
    direction.value === 'forward' ? 'tf-up' : 'tf-down'
);

function optionLetter(i: number) {
    return String.fromCharCode(65 + i);
}
</script>

<template>
    <Teleport to="body">
        <div class="tf-root" role="dialog" aria-modal="true">

            <!-- Background -->
            <div class="tf-bg">
                <div class="tf-grid"></div>
                <div class="tf-glow tf-glow-1"></div>
                <div class="tf-glow tf-glow-2"></div>
            </div>

            <!-- Progress bar -->
            <div class="tf-progress-track">
                <div class="tf-progress-fill" :style="{ width: progress + '%' }"></div>
            </div>

            <!-- Top bar -->
            <div class="tf-topbar">
                <div class="tf-preview-badge" v-if="previewMode">
                    <span class="tf-badge-dot"></span>
                    <span>Anteprima</span>
                </div>
                <div v-else></div>

                <div class="tf-topbar-right">
                    <span v-if="step >= 0 && !isDone" class="tf-step-counter">
                        {{ step + 1 }}<span class="tf-step-total">/{{ total }}</span>
                    </span>
                    <button class="tf-exit-btn" @click="emit('close')">
                        <X :size="15" />
                        <span>{{ exitLabel || 'Esci' }}</span>
                    </button>
                </div>
            </div>

            <!-- Main content -->
            <div class="tf-stage">
                <div class="tf-content">
                    <Transition :name="transitionName" mode="out-in">

                        <!-- Welcome -->
                        <div v-if="step === -1" key="welcome" class="tf-screen">
                            <p class="tf-eyebrow">Questionario</p>
                            <h1 class="tf-hero-title">{{ title }}</h1>
                            <p v-if="description" class="tf-hero-desc">{{ description }}</p>
                            <div class="tf-meta">
                                <span>{{ total }} domand{{ total === 1 ? 'a' : 'e' }}</span>
                                <span class="tf-meta-dot"></span>
                                <span>~{{ Math.ceil(total * 0.5) }} min</span>
                            </div>
                            <button class="tf-cta" @click="advance">
                                <span>Inizia</span>
                                <ChevronRight :size="16" />
                            </button>
                            <p class="tf-keyboard-hint">
                                oppure premi <kbd>Enter ↵</kbd>
                            </p>
                        </div>

                        <!-- Question -->
                        <div v-else-if="!isRecap && !isDone" :key="step" class="tf-screen">
                            <div class="tf-q-number">
                                <span>{{ step + 1 }}</span>
                                <ArrowRight :size="13" />
                            </div>

                            <h2 class="tf-q-label">
                                {{ currentQ?.label }}
                                <span v-if="currentQ?.required" class="tf-required">*</span>
                            </h2>

                            <!-- text -->
                            <div v-if="currentQ?.type === 'text'" class="tf-field">
                                <input
                                    ref="inputRef"
                                    :value="getVal(currentQ.id)"
                                    @input="setVal(currentQ.id, ($event.target as HTMLInputElement).value)"
                                    type="text"
                                    class="tf-input"
                                    :placeholder="currentQ.required ? 'Scrivi la tua risposta...' : 'Risposta facoltativa...'"
                                />
                                <div class="tf-underline"></div>
                            </div>

                            <!-- textarea -->
                            <div v-else-if="currentQ?.type === 'textarea'" class="tf-field">
                                <textarea
                                    ref="inputRef"
                                    :value="getVal(currentQ.id)"
                                    @input="setVal(currentQ.id, ($event.target as HTMLTextAreaElement).value)"
                                    class="tf-input tf-textarea"
                                    :placeholder="currentQ.required ? 'Scrivi la tua risposta...' : 'Risposta facoltativa...'"
                                    rows="4"
                                ></textarea>
                                <div class="tf-underline"></div>
                            </div>

                            <!-- number -->
                            <div v-else-if="currentQ?.type === 'number'" class="tf-field tf-field-narrow">
                                <input
                                    ref="inputRef"
                                    :value="getVal(currentQ.id)"
                                    @input="setVal(currentQ.id, Number(($event.target as HTMLInputElement).value))"
                                    type="number"
                                    class="tf-input"
                                    placeholder="0"
                                />
                                <div class="tf-underline"></div>
                            </div>

                            <!-- radio -->
                            <div v-else-if="currentQ?.type === 'radio'" class="tf-options">
                                <button
                                    v-for="(option, i) in currentQ.options"
                                    :key="option"
                                    type="button"
                                    :class="['tf-option', getVal(currentQ.id) === option ? 'tf-option--selected' : '']"
                                    @click="radioSelect(currentQ.id, option)"
                                >
                                    <span class="tf-option-key">{{ optionLetter(i) }}</span>
                                    <span class="tf-option-text">{{ option }}</span>
                                    <Check v-if="getVal(currentQ.id) === option" :size="15" class="tf-option-check" />
                                </button>
                            </div>

                            <!-- checkbox -->
                            <div v-else-if="currentQ?.type === 'checkbox'" class="tf-options">
                                <button
                                    v-for="(option, i) in currentQ.options"
                                    :key="option"
                                    type="button"
                                    :class="['tf-option', (getVal(currentQ.id) || []).includes(option) ? 'tf-option--selected' : '']"
                                    @click="toggleCheckbox(currentQ.id, option)"
                                >
                                    <span class="tf-option-key">{{ optionLetter(i) }}</span>
                                    <span class="tf-option-text">{{ option }}</span>
                                    <Check v-if="(getVal(currentQ.id) || []).includes(option)" :size="15" class="tf-option-check" />
                                </button>
                                <p class="tf-multi-hint">Puoi selezionare più risposte</p>
                            </div>

                            <!-- scale -->
                            <div v-else-if="currentQ?.type === 'scale'" class="tf-scale-wrap">
                                <div class="tf-scale-row">
                                    <button
                                        v-for="n in 10"
                                        :key="n"
                                        type="button"
                                        :class="['tf-scale-btn', getVal(currentQ.id) === n ? 'tf-scale-btn--selected' : '']"
                                        @click="scaleSelect(currentQ.id, n)"
                                    >{{ n }}</button>
                                </div>
                                <div class="tf-scale-labels">
                                    <span>Per niente</span>
                                    <span>Moltissimo</span>
                                </div>
                            </div>

                            <!-- error -->
                            <p v-if="errorMsg" class="tf-error">
                                <span class="tf-error-icon">!</span>
                                {{ errorMsg }}
                            </p>

                            <!-- CTA row (not for radio/scale — they auto-advance) -->
                            <div
                                v-if="currentQ?.type !== 'radio' && currentQ?.type !== 'scale'"
                                class="tf-cta-row"
                            >
                                <button class="tf-cta" @click="advance">
                                    <span>OK</span>
                                    <CornerDownLeft :size="14" />
                                </button>
                                <span class="tf-keyboard-hint">
                                    premi <kbd>Enter ↵</kbd>
                                </span>
                            </div>
                            <div v-else-if="currentQ?.type === 'checkbox'" class="tf-cta-row tf-cta-row--mt">
                                <button class="tf-cta" @click="advance">
                                    <span>Avanti</span>
                                    <ChevronRight :size="15" />
                                </button>
                                <span class="tf-keyboard-hint">premi <kbd>Enter ↵</kbd></span>
                            </div>
                        </div>

                        <!-- Recap -->
                        <div v-else-if="isRecap" key="recap" class="tf-screen tf-screen--recap">
                            <p class="tf-eyebrow">Riepilogo</p>
                            <h2 class="tf-hero-title">Controlla le tue risposte</h2>
                            <p class="tf-hero-desc">Puoi modificare qualsiasi risposta prima di inviare.</p>

                            <div class="tf-recap-list">
                                <div
                                    v-for="(q, index) in questions"
                                    :key="q.id"
                                    class="tf-recap-item"
                                >
                                    <div class="tf-recap-meta">
                                        <span class="tf-recap-num">{{ index + 1 }}</span>
                                        <p class="tf-recap-question">{{ q.label }}</p>
                                        <button class="tf-recap-edit" @click="editQuestion(index)" title="Modifica">
                                            <Pencil :size="13" />
                                        </button>
                                    </div>
                                    <div class="tf-recap-answer">
                                        <template v-if="Array.isArray(getVal(q.id)) && getVal(q.id).length">
                                            <span
                                                v-for="(val, i) in getVal(q.id)"
                                                :key="i"
                                                class="tf-recap-tag"
                                            >{{ val }}</span>
                                        </template>
                                        <span v-else :class="['tf-recap-val', !getVal(q.id) && getVal(q.id) !== 0 ? 'tf-recap-empty' : '']">
                                            {{ formatAnswer(q) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="tf-cta-row" style="margin-top: 2rem;">
                                <button class="tf-cta tf-cta--submit" @click="confirmSubmit">
                                    <span>Invia risposte</span>
                                    <Send :size="14" />
                                </button>
                                <button class="tf-exit-btn" style="margin-left: 0;" @click="retreat">
                                    <ArrowUp :size="13" />
                                    <span>Torna indietro</span>
                                </button>
                            </div>
                        </div>

                        <!-- Done -->
                        <div v-else key="done" class="tf-screen tf-screen--done">
                            <div class="tf-done-icon">
                                <Check :size="32" />
                            </div>
                            <h2 class="tf-hero-title">
                                {{ previewMode ? 'Anteprima completata' : 'Risposte inviate!' }}
                            </h2>
                            <p class="tf-hero-desc">
                                {{ previewMode
                                    ? 'Questo è come i tuoi clienti vedranno il questionario. Le risposte non sono state salvate.'
                                    : 'Le tue risposte sono state ricevute dal nutrizionista.' }}
                            </p>
                            <button class="tf-cta" @click="emit('close')">
                                <span>{{ previewMode ? 'Torna ai template' : 'Chiudi' }}</span>
                                <ChevronRight :size="15" />
                            </button>
                        </div>

                    </Transition>
                </div>
            </div>

            <!-- Nav arrows (bottom-right) -->
            <div v-if="step >= 0 && !isDone && !isRecap" class="tf-nav">
                <button v-if="step > 0" class="tf-nav-btn" title="Precedente" @click="retreat">
                    <ArrowUp :size="15" />
                </button>
                <button class="tf-nav-btn tf-nav-btn--next" title="Successivo" @click="advance">
                    <ArrowDown :size="15" />
                </button>
            </div>

        </div>
    </Teleport>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&display=swap');

/* ── Root ── */
.tf-root {
    position: fixed;
    inset: 0;
    z-index: 500;
    overflow: hidden;
    font-family: 'DM Sans', system-ui, sans-serif;
    color: #f1f5f9;
}

/* ── Background ── */
.tf-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(160deg, #080d18 0%, #0e1525 55%, #090e1b 100%);
}
.tf-grid {
    position: absolute;
    inset: 0;
    opacity: 0.035;
    background-image: radial-gradient(circle, #fff 1px, transparent 1px);
    background-size: 28px 28px;
}
.tf-glow {
    position: absolute;
    border-radius: 50%;
    filter: blur(120px);
    pointer-events: none;
}
.tf-glow-1 {
    width: 500px;
    height: 500px;
    top: -150px;
    right: -100px;
    background: rgba(34, 197, 94, 0.07);
}
.tf-glow-2 {
    width: 400px;
    height: 400px;
    bottom: -100px;
    left: -50px;
    background: rgba(59, 130, 246, 0.05);
}

/* ── Progress ── */
.tf-progress-track {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: rgba(255, 255, 255, 0.05);
    z-index: 10;
}
.tf-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #4ade80, #22c55e);
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 0 8px rgba(74, 222, 128, 0.5);
}

/* ── Top bar ── */
.tf-topbar {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    z-index: 10;
}
.tf-topbar-right {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}
.tf-preview-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    border: 1px solid rgba(74, 222, 128, 0.25);
    background: rgba(74, 222, 128, 0.08);
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #4ade80;
}
.tf-badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #4ade80;
    animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
.tf-step-counter {
    font-size: 0.8rem;
    font-weight: 500;
    color: #64748b;
    letter-spacing: 0.02em;
}
.tf-step-total {
    color: #334155;
}
.tf-exit-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    border: 1px solid rgba(255, 255, 255, 0.06);
    background: transparent;
    color: #475569;
    font-family: inherit;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.15s ease;
}
.tf-exit-btn:hover {
    border-color: rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.04);
    color: #94a3b8;
}

/* ── Stage ── */
.tf-stage {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 5rem 1.5rem 5rem;
}
@media (min-width: 1024px) {
    .tf-stage { padding-left: 5rem; padding-right: 5rem; }
}
.tf-content {
    width: 100%;
    max-width: 660px;
}

/* ── Screens ── */
.tf-screen {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}
.tf-screen--done {
    align-items: flex-start;
}

/* ── Typography ── */
.tf-eyebrow {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: #22c55e;
    margin-bottom: 0.75rem;
}
.tf-hero-title {
    font-family: 'Instrument Serif', Georgia, serif;
    font-size: clamp(2rem, 4.5vw, 3.25rem);
    font-weight: 400;
    line-height: 1.15;
    color: #f1f5f9;
    margin-bottom: 1rem;
}
.tf-hero-desc {
    font-size: 1.05rem;
    line-height: 1.65;
    color: #64748b;
    margin-bottom: 2rem;
    max-width: 520px;
}
.tf-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.85rem;
    color: #475569;
    margin-bottom: 2.5rem;
}
.tf-meta-dot {
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: #334155;
}

/* ── Question ── */
.tf-q-number {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #22c55e;
    margin-bottom: 1rem;
    letter-spacing: 0.01em;
}
.tf-q-label {
    font-family: 'Instrument Serif', Georgia, serif;
    font-size: clamp(1.4rem, 2.8vw, 2.1rem);
    font-weight: 400;
    line-height: 1.3;
    color: #f1f5f9;
    margin-bottom: 2rem;
    max-width: 580px;
}
.tf-required {
    color: #22c55e;
    margin-left: 0.2rem;
    font-size: 0.85em;
}

/* ── Field / Input ── */
.tf-field {
    width: 100%;
    margin-bottom: 0.5rem;
}
.tf-field-narrow {
    max-width: 220px;
}
.tf-input {
    width: 100%;
    background: transparent;
    border: none;
    outline: none;
    font-family: 'DM Sans', sans-serif;
    font-size: 1.15rem;
    font-weight: 300;
    color: #e2e8f0;
    padding: 0.5rem 0;
    caret-color: #4ade80;
    line-height: 1.5;
}
.tf-input::placeholder {
    color: #1e293b;
}
.tf-textarea {
    resize: none;
}
.tf-underline {
    height: 2px;
    background: #1e293b;
    position: relative;
    overflow: hidden;
    margin-top: 2px;
    border-radius: 1px;
}
.tf-underline::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, #4ade80, #22c55e);
    transform: translateX(-101%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.tf-field:focus-within .tf-underline::after {
    transform: translateX(0);
}

/* ── Options ── */
.tf-options {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    width: 100%;
    margin-bottom: 0.5rem;
}
.tf-option {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    width: 100%;
    text-align: left;
    padding: 0.75rem 0.875rem;
    border-radius: 0.5rem;
    border: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(255, 255, 255, 0.02);
    color: #64748b;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.15s ease;
}
.tf-option:hover {
    border-color: rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.05);
    color: #cbd5e1;
}
.tf-option--selected {
    border-color: rgba(74, 222, 128, 0.3) !important;
    background: rgba(74, 222, 128, 0.07) !important;
    color: #dcfce7 !important;
}
.tf-option-key {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.375rem;
    height: 1.375rem;
    border-radius: 0.25rem;
    border: 1px solid currentColor;
    font-size: 0.65rem;
    font-weight: 700;
    opacity: 0.5;
}
.tf-option-text {
    flex: 1;
}
.tf-option-check {
    flex-shrink: 0;
    color: #4ade80;
}
.tf-multi-hint {
    font-size: 0.75rem;
    color: #334155;
    margin-top: 0.5rem;
    padding-left: 0.25rem;
}

/* ── Scale ── */
.tf-scale-wrap {
    width: 100%;
    margin-bottom: 0.5rem;
}
.tf-scale-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}
.tf-scale-btn {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 0.5rem;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.03);
    color: #475569;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s ease;
}
.tf-scale-btn:hover {
    border-color: rgba(74, 222, 128, 0.25);
    background: rgba(74, 222, 128, 0.07);
    color: #86efac;
    transform: translateY(-1px);
}
.tf-scale-btn--selected {
    border-color: #4ade80 !important;
    background: #4ade80 !important;
    color: #052e16 !important;
    font-weight: 700;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(74, 222, 128, 0.3);
}
.tf-scale-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.7rem;
    color: #334155;
    letter-spacing: 0.02em;
}

/* ── Error ── */
.tf-error {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #f87171;
    margin-top: 0.75rem;
}
.tf-error-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background: rgba(248, 113, 113, 0.15);
    font-size: 0.65rem;
    font-weight: 700;
    flex-shrink: 0;
}

/* ── CTA ── */
.tf-cta-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1.75rem;
}
.tf-cta-row--mt {
    margin-top: 1rem;
}
.tf-cta {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    border: none;
    background: #22c55e;
    color: #052e16;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    letter-spacing: 0.01em;
}
.tf-cta:hover {
    background: #4ade80;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(34, 197, 94, 0.3);
}
.tf-cta:active {
    transform: translateY(0);
}
.tf-keyboard-hint {
    font-size: 0.75rem;
    color: #334155;
}
.tf-keyboard-hint kbd {
    display: inline-block;
    padding: 0.15rem 0.4rem;
    border-radius: 0.25rem;
    border: 1px solid #1e293b;
    background: #0f172a;
    color: #475569;
    font-family: inherit;
    font-size: 0.7rem;
}

/* ── Recap ── */
.tf-screen--recap {
    width: 100%;
}
.tf-recap-list {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    max-height: 55vh;
    overflow-y: auto;
    padding-right: 0.25rem;
    scrollbar-width: thin;
    scrollbar-color: #1e293b transparent;
}
.tf-recap-list::-webkit-scrollbar {
    width: 4px;
}
.tf-recap-list::-webkit-scrollbar-thumb {
    background: #1e293b;
    border-radius: 2px;
}
.tf-recap-item {
    padding: 0.875rem 1rem;
    border-radius: 0.625rem;
    border: 1px solid rgba(255, 255, 255, 0.05);
    background: rgba(255, 255, 255, 0.02);
    transition: border-color 0.15s;
}
.tf-recap-item:hover {
    border-color: rgba(255, 255, 255, 0.09);
}
.tf-recap-meta {
    display: flex;
    align-items: flex-start;
    gap: 0.625rem;
    margin-bottom: 0.5rem;
}
.tf-recap-num {
    flex-shrink: 0;
    font-size: 0.7rem;
    font-weight: 700;
    color: #22c55e;
    margin-top: 0.1rem;
    letter-spacing: 0.02em;
}
.tf-recap-question {
    flex: 1;
    font-size: 0.8rem;
    font-weight: 500;
    color: #64748b;
    line-height: 1.4;
}
.tf-recap-edit {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 0.375rem;
    border: 1px solid rgba(255, 255, 255, 0.06);
    background: transparent;
    color: #334155;
    cursor: pointer;
    transition: all 0.15s;
}
.tf-recap-edit:hover {
    border-color: rgba(74, 222, 128, 0.25);
    color: #4ade80;
    background: rgba(74, 222, 128, 0.07);
}
.tf-recap-answer {
    padding-left: 1.125rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
    align-items: center;
}
.tf-recap-val {
    font-size: 0.9rem;
    color: #e2e8f0;
    font-weight: 300;
    line-height: 1.4;
}
.tf-recap-empty {
    color: #334155;
    font-style: italic;
}
.tf-recap-tag {
    display: inline-block;
    padding: 0.2rem 0.625rem;
    border-radius: 9999px;
    border: 1px solid rgba(74, 222, 128, 0.2);
    background: rgba(74, 222, 128, 0.07);
    color: #86efac;
    font-size: 0.8rem;
    font-weight: 500;
}
.tf-cta--submit {
    background: #16a34a;
    gap: 0.5rem;
}
.tf-cta--submit:hover {
    background: #22c55e;
}

/* ── Done icon ── */
.tf-done-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 5rem;
    height: 5rem;
    border-radius: 50%;
    border: 1px solid rgba(74, 222, 128, 0.2);
    background: rgba(74, 222, 128, 0.08);
    color: #4ade80;
    margin-bottom: 2rem;
    animation: pop-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes pop-in {
    from { transform: scale(0.5); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

/* ── Nav arrows ── */
.tf-nav {
    position: absolute;
    bottom: 2rem;
    right: 2rem;
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
    z-index: 10;
}
.tf-nav-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.375rem;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.03);
    color: #475569;
    cursor: pointer;
    transition: all 0.15s ease;
}
.tf-nav-btn:hover {
    border-color: rgba(255, 255, 255, 0.15);
    background: rgba(255, 255, 255, 0.07);
    color: #94a3b8;
}
.tf-nav-btn--next {
    border-color: rgba(74, 222, 128, 0.15);
    background: rgba(74, 222, 128, 0.05);
    color: #4ade80;
}
.tf-nav-btn--next:hover {
    border-color: rgba(74, 222, 128, 0.35);
    background: rgba(74, 222, 128, 0.12);
}

/* ── Transitions ── */
.tf-up-enter-active,
.tf-up-leave-active,
.tf-down-enter-active,
.tf-down-leave-active {
    transition: opacity 0.3s ease, transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.tf-up-enter-from { opacity: 0; transform: translateY(48px); }
.tf-up-leave-to   { opacity: 0; transform: translateY(-48px); }
.tf-down-enter-from { opacity: 0; transform: translateY(-48px); }
.tf-down-leave-to   { opacity: 0; transform: translateY(48px); }
</style>
