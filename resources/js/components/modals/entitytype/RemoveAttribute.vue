<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        name="remove-attribute-from-entity-type-modal">
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span v-if="state.attribute.is_system">
                        {{ t('global.remove_name.title', {name: t(`global.attributes.${state.attribute.datatype}`)}) }}
                    </span>
                    <span v-else>
                        {{ t('global.remove_name.title', {name: translateConcept(state.attribute.thesaurus_url)}) }}
                    </span>
                </h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
                </button>
            </div>
            <div class="modal-body">
                <template v-if="state.attribute.is_system">
                    <alert
                        class="mb-0"
                        :message="t('global.remove_name.desc', {name: t(`global.attributes.${state.attribute.datatype}`)})"
                        :type="'info'"
                        :noicon="true" />
                </template>
                <template v-else>
                    <alert
                        :class="{'mb-0': !state.needsAlert}"
                        :message="t('global.remove_name.desc', {name: translateConcept(state.attribute.thesaurus_url)})"
                        :type="'info'"
                        :noicon="true" />
                    <alert
                        v-if="state.needsAlert"
                        :message="t('main.datamodel.attribute.modal.delete.alert', {
                                name: translateConcept(state.attribute.thesaurus_url),
                                cnt: state.count
                            }, state.count)"
                        :type="'warning'"
                        :noicon="false"
                        :icontext="t('global.note')" />
                </template>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" @click="confirmRemove()">
                    <i class="fas fa-fw fa-check"></i> {{ t('global.remove') }}
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

    import {
        getAttribute,
        translateConcept,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            attributeId: {
                required: true,
                type: Number,
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
                attributeId,
                metadata,
            } = toRefs(props);

            // FUNCTIONS
            const confirmRemove = _ => {
                context.emit('confirm', false);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                attribute: computed(_ => getAttribute(attributeId.value)),
                count: computed(_ => metadata.value.entityCount),
                needsAlert: computed(_ => state.count > 0),
            });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                // PROPS
                // LOCAL
                confirmRemove,
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>