<template>
    <vue-final-modal
        classes="modal-container"
        content-class="sp-modal-content sp-modal-content-sm"
        v-model="state.show"
        name="delete-entity-type-modal">
        <div class="modal-header">
            <h5 class="modal-title">
                {{ t('global.delete-name.title', {name: translateConcept(entityType.thesaurus_url)}) }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body">
            <div class="alert alert-info" role="alert" v-html="t('global.delete-name.desc', {name: translateConcept(entityType.thesaurus_url)})">
            </div>
            <div class="alert alert-danger" role="alert">
                {{
                    t('main.datamodel.entity.modal.delete.alert', {
                        name: translateConcept(entityType.thesaurus_url),
                        cnt: state.count
                    }, state.count)
                }}
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger" @click="confirmDelete()">
                    <i class="fas fa-fw fa-check"></i> {{ t('global.delete') }}
                </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" @click="closeModal()">
                <i class="fas fa-fw fa-times"></i> {{ t('global.cancel') }}
            </button>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import {
        translateConcept,
    } from '../../../helpers/helpers.js';

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
                state.show = false;
                context.emit('confirm', false);
            };
            const closeModal = _ => {
                state.show = false;
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                show: false,
                count: computed(_ => metadata.value.entityCount),
            });

            // ON MOUNTED
            onMounted(_ => {
                state.show = true;
            });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                // PROPS
                entityType,
                // LOCAL
                confirmDelete,
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>