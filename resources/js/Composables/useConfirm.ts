import { reactive } from 'vue';

export interface ConfirmOptions {
    title?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    danger?: boolean;
}

const state = reactive({
    show: false,
    message: '',
    title: 'Conferma',
    confirmLabel: 'Conferma',
    cancelLabel: 'Annulla',
    danger: false,
    resolve: null as ((v: boolean) => void) | null,
});

export function useConfirm() {
    function confirm(message: string, options: ConfirmOptions = {}): Promise<boolean> {
        state.message = message;
        state.title = options.title ?? 'Conferma';
        state.confirmLabel = options.confirmLabel ?? 'Conferma';
        state.cancelLabel = options.cancelLabel ?? 'Annulla';
        state.danger = options.danger ?? false;
        state.show = true;

        return new Promise(resolve => {
            state.resolve = resolve;
        });
    }

    function accept() {
        state.show = false;
        state.resolve?.(true);
        state.resolve = null;
    }

    function dismiss() {
        state.show = false;
        state.resolve?.(false);
        state.resolve = null;
    }

    return { state, confirm, accept, dismiss };
}
