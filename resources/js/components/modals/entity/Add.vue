<template>
    <vue-final-modal
        classes="modal-container"
        content-class="sp-modal-content sp-modal-content-sm"
        v-model="state.show"
        name="add-entity-modal">
        <div class="modal-header">
            <h5 class="modal-title">
                {{ t('main.entity.modals.add.title') }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body nonscrollable">
            <form name="newEntityForm" id="newEntityForm" role="form" @submit.prevent="add()">
                <div class="mb-3">
                    <label class="col-form-label col-md-3" for="name">
                        {{ t('global.name') }}:
                    </label>
                    <div class="col-md-9">
                        <input type="text" id="name" class="form-control" required v-model="state.entity.name" />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="col-form-label col-md-3" for="type">
                        {{ t('global.type') }}:
                    </label>
                    <div class="col-md-9">
                        <multiselect
                            name="type"
                            id="type"
                            v-model="state.entity.type"
                            :object="true"
                            :label="'thesaurus_url'"
                            :track-by="'id'"
                            :valueProp="'id'"
                            :mode="'single'"
                            :options="state.entityTypes"
                            :placeholder="t('global.select.placeholder')">
                            <template v-slot:option="{ option }">
                                    {{ translateConcept(option.thesaurus_url) }}
                                </template>
                                <template v-slot:singlelabel="{ value }">
                                    <div class="px-2">
                                        {{ translateConcept(value.thesaurus_url) }}
                                    </div>
                                </template>
                        </multiselect>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success" form="newEntityForm" :disabled="state.dataMissing">
                    <i class="fas fa-fw fa-plus"></i> {{ t('global.add') }}
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
        getEntityType,
        getEntityTypes,
        translateConcept,
    } from '../../../helpers/helpers.js';

    export default {
        props: {
            parent: {
                required: false,
                type: Object,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                parent,
            } = toRefs(props);

            // FUNCTIONS
            const add = _ => {
                if(state.dataMissing) {
                    return;
                }
                state.show = false;
                context.emit('confirm', state.entity);
            };
            const closeModal = _ => {
                state.show = false;
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                show: false,
                entity: {
                    name: '',
                    type: {},
                    parent_id: null,
                },
                entityTypes: computed(_ => {
                    if(parent.value && parent.value.entity_type_id) {
                        return getEntityType(parent.value.entity_type_id).sub_entity_types;
                    } else {
                        return Object.values(getEntityTypes()).filter(type => type.is_root);
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
                // LOCAL
                add,
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>