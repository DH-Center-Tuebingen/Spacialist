<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        name="delete-entity-modal">
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ t('global.delete_name.title', {name: state.entity.name}) }}
                </h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
                </button>
            </div>
            <div class="modal-body">
                <alert
                    :class="{'mb-0': !state.needsAlert}"
                    :message="t('global.delete_name.desc', {name: state.entity.name})"
                    :type="'info'"
                    :noicon="true" />
                <alert
                    v-if="state.needsAlert"
                    :message="t('main.entity.modals.delete.alert', {
                            name: state.entity.name,
                            cnt: state.count
                        }, state.count)"
                    :type="'warning'"
                    :noicon="false"
                    :icontext="t('global.note')" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" @click="confirmDelete()">
                    <i class="fas fa-fw fa-check"></i> {{ t('global.delete') }}
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" @click="closeModal()">
                    <i class="fas fa-fw fa-times"></i> {{ t('global.cancel') }}
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

    import store from '@/bootstrap/store.js';

    export default {
        props: {
            entityId: {
                type: Number,
                required: true,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                entityId,
            } = toRefs(props);

            // FUNCTIONS
            const confirmDelete = _ => {
                context.emit('confirm', false);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                entity: computed(_ => store.getters.entities[entityId.value] || {}),
                count: computed(_ => state.entity.children ? state.entity.children.length : 0),
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
                // STATE
                state,
            }
        },
    }
</script>