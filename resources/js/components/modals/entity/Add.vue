<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        name="add-entity-modal"
    >
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ t('main.entity.modals.add.title') }}
                    <small v-if="state.hasParent">
                        {{ parent.name }}
                    </small>
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body nonscrollable">
                <form
                    id="newEntityForm"
                    name="newEntityForm"
                    role="form"
                    @submit.prevent="add()"
                >
                    <div class="mb-3">
                        <label
                            class="col-form-label col-md-3"
                            for="name"
                        >
                            {{ t('global.name') }}:
                        </label>
                        <div class="col-md-9">
                            <input
                                id="name"
                                v-model="state.entity.name"
                                type="text"
                                class="form-control"
                                required
                            >
                        </div>
                    </div>
                    <div class="mb-3">
                        <label
                            class="col-form-label col-md-3"
                            for="type"
                        >
                            {{ t('global.type') }}:
                        </label>
                        <div class="col-md-9">
                            <multiselect
                                id="type"
                                v-model="state.entity.type"
                                name="type"
                                :classes="multiselectResetClasslist"
                                :object="true"
                                :label="'thesaurus_url'"
                                :track-by="'id'"
                                :value-prop="'id'"
                                :mode="'single'"
                                :options="state.entityTypes"
                                :placeholder="t('global.select.placeholder')"
                            >
                                <template #option="{ option }">
                                    {{ translateConcept(option.thesaurus_url) }}
                                </template>
                                <template #singlelabel="{ value }">
                                    <div class="multiselect-single-label">
                                        {{ translateConcept(value.thesaurus_url) }}
                                    </div>
                                </template>
                            </multiselect>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">                
                <LoadingButton
                    :loading="state.saving"
                    class="btn btn-outline-success"
                    form="newEntityForm"
                    :disabled="state.dataMissing"
                >
                    <template #icon>
                        <i class="fas fa-fw fa-plus" />
                    </template>
                    {{ t('global.add') }}
                </LoadingButton>
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

    import useEntityStore from '@/bootstrap/stores/entity.js';

    import {
        translateConcept,
        multiselectResetClasslist,
    } from '@/helpers/helpers.js';
    import LoadingButton from '@/components/forms/button/LoadingButton.vue';

    export default {
        components: {
            LoadingButton
        },
        props: {
            parent: {
                required: false,
                type: Object,
                default: null,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();
            const entityStore = useEntityStore();
            const {
                parent,
            } = toRefs(props);

            // FUNCTIONS
            const add = _ => {
                if(state.dataMissing) {
                    return;
                }
                state.saving = true;
                context.emit('confirm', state.entity);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                saving: false,
                entity: {
                    name: '',
                    type: {},
                    parent_id: null,
                },
                hasParent: computed(_ => !!parent.value),
                entityTypes: computed(_ => {
                    if(parent.value && parent.value.entity_type_id) {
                        return entityStore.getEntityType(parent.value.entity_type_id).sub_entity_types;
                    } else {
                        return Object.values(entityStore.entityTypes).filter(type => type.is_root);
                    }
                }),
                dataMissing: computed(_ => {
                    return !state.entity.name || (!state.entity.type || !state.entity.type.id);
                }),
            });

            if(state.entityTypes.length == 1) {
                state.entity.type = state.entityTypes[0];
            }
            if(parent.value && parent.value.id) {
                state.entity.parent_id = parent.value.id;
            }

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                multiselectResetClasslist,
                // LOCAL
                add,
                closeModal,
                // STATE
                state,
            };
        },
    };
</script>