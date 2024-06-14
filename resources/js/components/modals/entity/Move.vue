<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        name="move-entity-modal"
    >
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ t('main.entity.modals.move.title') }}
                    <small>
                        {{ entity.name }}
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
                    id="moveEntityForm"
                    name="moveEntityForm"
                    role="form"
                    @submit.prevent="move()"
                >
                    <div
                        v-if="isMoveModeMulti"
                        class="alert alert-warning"
                    >
                        {{ t('main.entity.tree.multiedit.warning') }}
                    </div>

                    <Alert
                        v-if="invalidSelection"
                        type="error"
                        :message="t('main.entity.tree.move.invalid_selection')"
                    />

                    <div class="form-check form-switch">
                        <input
                            id="move-to-root"
                            v-model="state.moveToRoot"
                            class="form-check-input"
                            type="checkbox"
                        >
                        <label
                            class="form-check-label"
                            for="move-to-root"
                        >
                            {{ t('main.entity.modals.move.to_root') }}
                        </label>
                    </div>
                    <div v-show="!state.moveToRoot">
                        <label
                            class="col-form-label col-md-3"
                            for="parent-entity"
                        >
                            {{ t('global.parent_entity') }}:
                        </label>
                        <div class="col-md-9">
                            <simple-search
                                id="parent-entity"
                                :endpoint="searchEntity"
                                :filter-fn="filterEntityResults"
                                :key-text="'name'"
                                :chain="'ancestors'"
                                @selected="e => entitySelected(e)"
                            />
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button
                    type="submit"
                    class="btn btn-outline-success"
                    form="moveEntityForm"
                    :disabled="state.dataMissing"
                >
                    <i class="fas fa-fw fa-long-arrow-alt-right" /> {{ t('global.move') }}
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
        searchEntity,
    } from '@/api.js';

    import {
        isAllowedSubEntityType,
    } from '@/helpers/helpers.js';

    import {
        store
    } from '@/bootstrap/store.js';

    import Alert from '@/components/Alert.vue';

    export default {
        components: {
            Alert,
        },
        props: {
            entity: {
                required: true,
                type: Object,
            },
        },
        emits: ['cancel', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                entity,
            } = toRefs(props);

            const isMoveModeMulti = computed(() => {
                return Object.keys(store.getters.treeSelection).length > 1;
            });

            // We only allow moving entities of the same type
            const invalidSelection = computed(() => {
                if(isMoveModeMulti.value) {
                    let type = null;
                    const selection = store.getters.treeSelection;
                    for(let index in selection) {
                        const item = selection[index];
                        const entityTypeId = item?.entity_type_id;
                        if(type == null) {
                            type = entityTypeId;
                        } else if(type !== entityTypeId) {
                            return true;
                        }
                    }

                    return false;
                } else return false;
            });

            // FUNCTIONS
            const entitySelected = e => {
                const {
                    added,
                    removed,
                    ...entity
                } = e;
                if(removed) {
                    state.parent = null;
                } else if(added) {
                    state.parent = entity;
                }
            };
            const filterEntityResults = results => {
                if(isMoveModeMulti.value) {
                    if(invalidSelection.value) {
                        return [];
                    } else {
                        // As we reqire the same entity type, we can just take the first one
                        const entity = Object.values(store.getters.treeSelection)[0];
                        const entities = Object.keys(store.getters.treeSelection).map(id => parseInt(id));
                        const entityTypeId = entity.entity_type_id;

                        return results.filter(result => {
                            const onSameEntity = entities.includes(parseInt(result.id));
                            if(onSameEntity) return false;
                            return isAllowedSubEntityType(result.entity_type_id, entityTypeId);
                        });
                    }
                } else {
                    return results.filter(r => {
                        const onSame = r.id == entity.value.root_entity_id ||
                            r.id == entity.value.id;
                        if(onSame) return false;
                        return isAllowedSubEntityType(r.entity_type_id, entity.value.entity_type_id);
                    });
                }
            };
            const move = _ => {
                if(state.dataMissing) {
                    return;
                }

                let targets = isMoveModeMulti.value ? Object.keys(store.getters.treeSelection).map(id => parseInt(id)) : [];
                context.emit('confirm', state.moveToRoot ? null : state.parent.id, targets);
            };
            const closeModal = _ => {
                context.emit('cancel', false);
            };

            // DATA
            const state = reactive({
                moveToRoot: false,
                parent: null,
                dataMissing: computed(_ => {
                    return !state.moveToRoot && (!state.parent || !state.parent.id) && !invalidSelection.value;
                }),
            });

            // RETURN
            return {
                t,
                // HELPERS
                searchEntity,
                // LOCAL
                entitySelected,
                filterEntityResults,
                invalidSelection,
                move,
                isMoveModeMulti,
                closeModal,
                // STATE
                state,
            };
        },
    };
</script>