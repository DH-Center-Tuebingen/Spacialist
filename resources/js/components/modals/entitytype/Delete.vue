<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        name="delete-entity-type-modal"
    >
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ t('global.delete_name.title', {name: translateConcept(entityType.thesaurus_url)}) }}
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
                <alert
                    :class="{'mb-0': !state.needsAlert}"
                    :message="t('global.delete_name.desc', {name: translateConcept(entityType.thesaurus_url)})"
                    :type="'info'"
                    :noicon="true"
                />
                <alert
                    v-if="state.needsAlert"
                    :message="t('main.datamodel.entity.modal.delete.alert', {
                        name: translateConcept(entityType.thesaurus_url),
                        cnt: state.count
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
                    <i class="fas fa-fw fa-check" /> {{ t('global.delete') }}
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
        computed,
        reactive,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import {
        translateConcept,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            entityType: {
                required: true,
                type: Object,
            },
            metadata: {
                required: true,
                type: Object,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                entityType,
                metadata,
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
                count: computed(_ => metadata.value.entityCount),
                needsAlert: computed(_ => state.count > 0),
            });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                // LOCAL
                confirmDelete,
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>