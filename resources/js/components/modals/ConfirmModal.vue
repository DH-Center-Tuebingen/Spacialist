<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        :name="name"
    >
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5
                    v-if="title"
                    class="modal-title"
                >
                    {{ title }}
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="close()"
                />
            </div>
            <div class="modal-body nonscrollable">
                <slot />
            </div>
            <div class="modal-footer">
                <LoadingButton
                    type="submit"
                    class="btn btn-outline-success"
                    form="newEntityTypeForm"
                    :disabled="disabled"
                    :loading="loading"
                    @click="confirm()"
                >
                    <i class="fas fa-fw fa-plus" /> {{ t('global.add') }}
                </LoadingButton>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    @click.prevent="close()"
                >
                    <i class="fas fa-fw fa-times" /> {{ t('global.cancel') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import { useI18n } from 'vue-i18n';
    import LoadingButton from '../forms/button/LoadingButton.vue';

    export default {
        components: {
            LoadingButton,
        },
        props: {
            disabled: {
                type: Boolean,
                default: false,
            },
            loading: {
                type: Boolean,
                default: false,
            },
            title: {
                type: String,
                default: '',
            },
        },
        emits: ['close', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();

            // FUNCTIONS
            const confirm = _ => {
                context.emit('confirm');
            };
            const close = _ => {
                context.emit('close');
            };

            // RETURN
            return {
                t,
                // PROPS
                // LOCAL
                close,
                confirm,
            };
        },
    };
</script>