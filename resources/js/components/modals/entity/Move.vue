<template>
    <vue-final-modal
        classes="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        v-model="state.show"
        name="move-entity-modal">
        <div class="modal-header">
            <h5 class="modal-title">
                {{ t('main.entity.modals.move.title') }}
                <small>
                    {{ entity.name }}
                </small>
            </h5>
            <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body nonscrollable">
            <form name="moveEntityForm" id="moveEntityForm" role="form" @submit.prevent="move()">
                <div class="form-check form-switch" v-if="state.hasParent">
                    <input class="form-check-input" type="checkbox" id="move-to-root" v-model="state.moveToRoot">
                    <label class="form-check-label" for="move-to-root">
                        {{ t('main.entity.modals.move.to_root') }}
                    </label>
                </div>
                <div v-show="!state.moveToRoot">
                    <label class="col-form-label col-md-3" for="parent-entity">
                        {{ t('global.parent_entity') }}:
                    </label>
                    <div class="col-md-9">
                        <simple-search
                            id="parent-entity"
                            :endpoint="searchEntity"
                            :filter-fn="filterEntityResults"
                            :key-text="'name'"
                            @selected="e => entitySelected(e)" />
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success" form="moveEntityForm" :disabled="state.dataMissing">
                <i class="fas fa-fw fa-plus"></i> {{ t('global.move') }}
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
                state.show = false;
                context.emit('confirm', state.moveToRoot ? null : state.parent.id);
            };
            const closeModal = _ => {
                state.show = false;
                context.emit('cancel', false);
            };

            // DATA
            const state = reactive({
                show: false,
                moveToRoot: false,
                parent: null,
                hasParent: computed(_ => !!entity.value.root_entity_id),
                dataMissing: computed(_ => {
                    return !state.moveToRoot && (!state.parent || !state.parent.id);
                }),
            });

            // ON MOUNTED
            onMounted(_ => {
                state.show = true;
            });

            // RETURN
            return {
                t,
                // HELPERS
                searchEntity,
                // PROPS
                entity,
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