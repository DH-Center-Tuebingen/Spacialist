<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        :lock-scroll="false"
        name="delete-bibliograpy-item-modal"
    >
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ t('main.bibliography.modal.delete.title') }}
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body">
                <alert
                    :class="{'mb-0': !state.needsAlert}"
                    :message="t('global.delete_name.desc', {name: state.title})"
                    :type="'info'"
                    :noicon="true"
                />
                <alert
                    v-if="state.needsAlert"
                    :message="t('main.bibliography.modal.delete.alert', {
                        name: state.title,
                        cnt: state.count,
                    }, state.count)"
                    :type="'warning'"
                    :noicon="false"
                    :icontext="t('global.note')"
                />
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-outline-danger"
                    @click="confirmDelete()"
                >
                    <i class="fas fa-fw fa-trash" /> {{ t('global.delete') }}
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    @click="closeModal()"
                >
                    <i class="fas fa-fw fa-ban" /> {{ t('global.cancel') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            data: {
                required: true,
                type: Object
            },
        },
        emits: ['delete', 'closing'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                data,
            } = toRefs(props);

            // FUNCTIONS
            const confirmDelete = _ => {
                context.emit('delete', false);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                title: computed(_ => data.value.title),
                count: computed(_ => data.value.count),
                needsAlert: computed(_ => state.count > 0),
            });

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                // LOCAL
                confirmDelete,
                closeModal,
                //STATE
                state,
            }
        },
    }
</script>
