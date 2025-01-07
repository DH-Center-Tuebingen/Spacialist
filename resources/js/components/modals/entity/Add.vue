<template>
    <ConfirmModal
        :title="t('main.entity.modals.add.title')"
        name="add-entity-modal"
        :disabled="state.dataMissing"
        :loading="loading"
        @confirm="add()"
        @close="closeModal"
    >
        <form
            id="newEntityForm"
            name="newEntityForm"
            role="form"
        >
            <div class="mb-3">
                <label
                    class="col-form-label col-md-3"
                    for="name"
                >
                    {{ t('global.name') }}
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
                    {{ t('global.type') }}
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
    </ConfirmModal>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import {
        getEntityType,
        getEntityTypes,
        translateConcept,
        multiselectResetClasslist,
    } from '@/helpers/helpers.js';
    import ConfirmModal from '../ConfirmModal.vue';

    export default {
        components: {
            ConfirmModal,
        },
        props: {
            parent: {
                required: false,
                type: Object,
                default: null,
            },
            loading: {
                type: Boolean,
                default: false,
            },
        },
        emits: ['close', 'confirm'],
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
                context.emit('confirm', state.entity);
            };
            const closeModal = _ => {
                console.log('closeModal');
                context.emit('close', false);
            };

            // DATA
            const state = reactive({
                entity: {
                    name: '',
                    type: {},
                    parent_id: null,
                },
                hasParent: computed(_ => !!parent.value),
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