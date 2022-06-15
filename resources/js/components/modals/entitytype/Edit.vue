<template>
    <vue-final-modal
        classes="modal-container"
        content-class="sp-modal-content sp-modal-content-sm"
        v-model="state.show"
        name="edit-entity-type-modal">
        <div class="modal-header">
            <h5 class="modal-title">
                {{
                    t('main.entity.modals.edit.title_type', {
                        name: translateConcept(entityType.thesaurus_url)
                    })
                }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body nonscrollable">
            <form id="edit-entity-type-form" name="edit-entity-type-form" role="form" @submit.prevent="confirmEdit()">
                <div class="row">
                    <label class="col-form-label col-3" for="label">
                        {{ t('global.label') }}:
                    </label>
                    <div class="col-9">
                        <simple-search
                            :endpoint="searchLabel"
                            :key-fn="getConceptLabel"
                            :default-value="state.defaultConcept"
                            @selected="labelSelected" />
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success" form="edit-entity-type-form" :disabled="!state.isValid">
                <i class="fas fa-fw fa-plus"></i> {{ t('global.save') }}
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
        watch,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import {
        searchLabel,
    } from '@/api.js';

    import {
        translateConcept,
        getConceptLabel,
        getConcept,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            entityType: {
                required: true,
                type: Object,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                entityType,
            } = toRefs(props);

            // FUNCTIONS
            const confirmEdit = _ => {
                if(!state.isValid) return;
                state.show = false;
                context.emit('confirm', state.editedProps);
            };
            const closeModal = _ => {
                state.show = false;
                context.emit('closing', false);
            };
            const labelSelected = e => {
                const {
                    added,
                    removed,
                    ...label
                } = e;
                if(removed) {
                    // state.defaultConcept.label = null;
                    state.editedProps.thesaurus_url = null;
                } else if(added) {
                    // state.defaultConcept.label = label;
                    state.editedProps.thesaurus_url = label.concept_url;
                }
            };
            const getDefaultConcept = url => {
                const concept = {
                    ...getConcept(url)
                };
                if(!concept.labels) {
                    concept.labels = [{
                        label: concept.label,
                    }];
                }
                return concept;
            }

            // DATA
            const state = reactive({
                show: false,
                defaultConcept: getDefaultConcept(entityType.value.thesaurus_url),
                editedProps: {
                    thesaurus_url: null,
                },
                isValid: computed(_ => {
                    for(let k in state.editedProps) {
                        if(!state.editedProps[k]) {
                            return false;
                        }
                    }
                    return true;
                }),
            });

            // ON MOUNTED
            onMounted(_ => {
                state.show = true;
            });

            // WATCHER
            watch(entityType.value, (newValue, oldValue) => {
                state.editedProps.thesaurus_url = null;
                state.defaultConcept = getDefaultConcept(newValue.thesaurus_url);
            });
            // watch(state.defaultConcept, (newValue, oldValue) => {
            //     if(!newValue) {
            //     } else {
            //     }
            // });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                getConceptLabel,
                searchLabel,
                // PROPS
                // LOCAL
                confirmEdit,
                closeModal,
                labelSelected,
                // STATE
                state,
            }
        },
    }
</script>