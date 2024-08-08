<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        name="delete-entity-modal"
    >
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <slot name="header" />
                <template v-if="!$slots.header">
                    <h5 class="modal-title">
                        {{ title }}
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        aria-label="Close"
                        data-bs-dismiss="modal"
                        @click="close()"
                    />
                </template>
            </div>
            <div class="modal-body">
                <slot />
            </div>
            <div class="modal-footer">
                <slot name="footer" />
                <template v-if="!$slots.footer">
                    <button
                        type="button"
                        class="btn btn-outline-danger"
                        @click="confirm()"
                    >
                        <i class="fas fa-fw fa-check" /> {{ computedConfirmText }}
                    </button>
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal"
                        @click="close()"
                    >
                        <i class="fas fa-fw fa-times" /> {{ t('global.cancel') }}
                    </button>
                </template>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import { useI18n } from 'vue-i18n';
    import { computed } from 'vue';

    export default {
        props: {
            title: {
                type: String,
                required: true,
            },
            confirmText: {
                type: String,
                default: null,
            },
        },
        emits: ['close', 'confirm'],
        setup(props, { emit }) {

            const t = useI18n().t;

            const confirm = _ => {
                emit('confirm');
            };

            const close = _ => {
                emit('close');
            };

            const computedConfirmText = computed(_ => props.confirmText ?? t('global.confirm'));

            return {
                t,
                close,
                confirm,
                computedConfirmText,
            };
        },
    };

</script>
