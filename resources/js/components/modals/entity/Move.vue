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
                        v-if="state.hasParent"
                        class="form-check form-switch"
                    >
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

    export default {
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
                return results.filter(r => {
                    const onSame = r.id == entity.value.root_entity_id ||
                        r.id == entity.value.id;
                    if(onSame) return false;
                    return isAllowedSubEntityType(r.entity_type_id, entity.value.entity_type_id);
                });
            };
            const move = _ => {
                if(state.dataMissing) {
                    return;
                }
                context.emit('confirm', state.moveToRoot ? null : state.parent.id);
            };
            const closeModal = _ => {
                context.emit('cancel', false);
            };

            // DATA
            const state = reactive({
                moveToRoot: false,
                parent: null,
                hasParent: computed(_ => !!entity.value.root_entity_id),
                dataMissing: computed(_ => {
                    return !state.moveToRoot && (!state.parent || !state.parent.id);
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
                move,
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>