<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-xs"
        name="deactivate-user-modal"
    >
        <div class="sp-modal-content sp-modal-content-xs">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{
                        t('global.deactivate_name.title', {name: user.name})
                    }}
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body">
                <div
                    class="alert alert-info"
                    role="alert"
                >
                    {{ t('global.deactivate_name.info') }}
                </div>
                <div
                    class="alert alert-danger"
                    role="alert"
                >
                    {{ t('global.deactivate_name.desc', {name: user.name}) }}
                </div>
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-outline-success"
                    @click="confirmDeactivate()"
                >
                    <i class="fas fa-fw fa-check" /> {{ t('global.deactivate') }}
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                >
                    <i class="fas fa-fw fa-times" /> {{ t('global.cancel') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            user: {
                required: true,
                type: Object,
            },
        },
        emits: ['deactivate', 'cancel'],
        setup(props, context) {
            const { t } = useI18n();

            const {
                user,
            } = toRefs(props);

            // FUNCTIONS
            const closeModal = _ => {
                context.emit('cancel', false);
            };
            const confirmDeactivate = _ => {
                context.emit('deactivate', false);
            };

            // DATA
            const state = reactive({
            });

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                user,
                // LOCAL
                closeModal,
                confirmDeactivate,
                // STATE
                state,
            }
        },
    }
</script>
