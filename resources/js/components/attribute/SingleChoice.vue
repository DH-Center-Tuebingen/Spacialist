<template>
    <multiselect
        v-model="v.value"
        :classes="multiselectResetClasslist"
        :value-prop="'id'"
        :track-by="'concept_url'"
        :object="true"
        :mode="'single'"
        :disabled="disabled"
        :options="state.filteredSelections"
        :name="name"
        :searchable="true"
        :infinite="true"
        :limit="15"
        :filter-results="false"
        :placeholder="t('global.select.placeholder')"
        @select="value => v.handleChange(value)"
        @deselect="v.handleChange(null)"
        @search-change="setSearchQuery"
    >
        <template #option="{ option }">
            {{ translateConcept(option.concept_url) }}
        </template>
        <template #singlelabel="{ value: singlelabelValue }">
            <div class="multiselect-single-label">
                {{ translateConcept(singlelabelValue.concept_url) }}
            </div>
        </template>
    </multiselect>
    <div
        v-if="selectionFrom"
        :title="t('main.entity.attributes.root_attribute_info', { name: getAttributeName(selectionFrom) })"
        class="badge border border-secondary text-reset cursor-help user-select-none mt-1"
    >
        <i class="fas fa-fw fa-link" />
        {{
            getAttributeName(selectionFrom)
        }}
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
        watch,
    } from 'vue';

    import { useField } from 'vee-validate';

    import * as yup from 'yup';

    import { useI18n } from 'vue-i18n';

    import store from '@/bootstrap/store.js';

    import {
        searchConceptSelection,
    } from '@/api.js';

    import {
        getAttributeName,
        translateConcept,
        multiselectResetClasslist,
        only,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            name: {
                type: String,
                required: true,
            },
            disabled: {
                type: Boolean,
                required: false,
                default: false,
            },
            value: {
                type: Object,
                required: true,
                default: _ => ({}),
            },
            selections: {
                type: Array,
                required: true,
            },
            selectionFrom: {
                type: Number,
                required: false,
                default: 0,
            },
            selectionFromValue: {
                type: Number,
                required: false,
                default: -1,
            },
        },
        emits: ['change', 'update-selection'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                name,
                disabled,
                value,
                selections,
                selectionFrom,
                selectionFromValue,
            } = toRefs(props);

            const {
                handleChange: veeHandleChange,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`sc_${name.value}`, yup.mixed().nullable(), {
                initialValue: value.value,
            });
            // FETCH

            // FUNCTIONS
            const handleUpdateForSelections = value => {
                context.emit('update-selection', value?.id);
                formatAndHandleChange(value);
            };

            const updateCurrentValue = _ => {
                if(!v.value || Object.keys(v.value).length == 0) return;

                // check if current selected value is part of available selection...
                const idx = state.localSelection.findIndex(s => s.id == v.value.id);
                if(idx == -1) {
                    // ...otherwise unset value
                    handleUpdateForSelections(null);
                }
            };

            const handleSelectionUpdate = conceptId => {
                if(!state.hasRootAttribute) return;

                if(!conceptId) {
                    state.localSelection = [];
                    updateCurrentValue();
                    return;
                }

                const cachedSelection = store.getters.cachedConceptSelection(conceptId);
                if(!cachedSelection) {
                    searchConceptSelection(conceptId).then(selection => {
                        store.dispatch('setCachedConceptSelection', {
                            id: conceptId,
                            selection: selection,
                        });

                        state.localSelection = selection;
                        updateCurrentValue();
                    });
                } else {
                    state.localSelection = cachedSelection;
                    updateCurrentValue();
                }
            };

            const resetFieldState = _ => {
                v.resetField({
                    value: value.value
                });
            };
            const undirtyField = _ => {
                v.resetField({
                    value: v.value,
                });
            };

            const formatValue = value => {
                if(!value) return null;
                return only(value, ['id', 'concept_url']);
            };

            const formatAndHandleChange = value => {
                if(value != null) {
                    value = formatValue(value);
                }
                return veeHandleChange(value);
            };

            const setSearchQuery = query => {
                state.query = query ? query.toLowerCase().trim() : null;
            };

            // DATA
            const state = reactive({
                hasRootAttribute: computed(_ => {
                    return !!selectionFrom.value;
                }),
                localSelection: [],
                compSelection: computed(_ => {
                    if(state.hasRootAttribute) {
                        return state.localSelection;
                    } else {
                        return selections.value;
                    }
                }),
                query: null,
                filteredSelections: computed(_ => {
                    const sel = state.compSelection;
                    if(!state.query) return sel;

                    return sel.filter(concept => {
                        return concept.concept_url.toLowerCase().indexOf(state.query) !== -1 || translateConcept(concept.concept_url).toLowerCase().indexOf(state.query) !== -1;
                    });
                }),
            });
            const v = reactive({
                value: fieldValue,
                handleChange: handleUpdateForSelections,
                meta,
                resetField,
            });


            watch(_ => value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(_ => v.value, (newValue, oldValue) => {
                // only emit @change event if field is validated (required because Entity.vue components)
                // trigger this watcher several times even if another component is updated/validated
                if(!v.meta.validated) return;
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: formatValue(v.value),
                });
            });
            if(state.hasRootAttribute) {
                watch(selectionFromValue, (newValue, oldValue) => {
                    if(typeof newValue == 'object' || newValue == -1) {
                        newValue = null;
                    }
                    handleSelectionUpdate(newValue);
                });
            }

            // RETURN
            return {
                t,
                // HELPERS
                getAttributeName,
                translateConcept,
                multiselectResetClasslist,
                // LOCAL
                resetFieldState,
                undirtyField,
                setSearchQuery,
                // STATE
                state,
                v,
            };
        },
    };
</script>
