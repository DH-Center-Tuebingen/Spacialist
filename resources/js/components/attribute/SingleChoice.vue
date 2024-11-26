<template>
    <multiselect
        ref="multiselect"
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
        @keydown.tab="handleTab"
        @keydown="clearInputOnDelete"
        @select="value => v.handleChange(value)"
        @deselect="v.handleChange(null)"
        @search-change="setSearchQuery"
    >
        <template #option="{ option }">
            {{ translateConcept(option.concept_url) }}
            <span
                v-if="isTabOption(option)"
                class="position-absolute end-0 me-2 badge rounded-pill border border-1 border-secondary text-secondary py-1 fs-xs"
            >Tab</span>
        </template>
        <template #singlelabel="{ value: singlelabelValue }">
            <div class="multiselect-single-label">
                {{ translateConcept(singlelabelValue.concept_url) }}
            </div>
        </template>
        <template #clear="{clear}">
            <span
                aria-hidden="true"
                role="button"
                data-clear=""
                aria-roledescription="❎"
                class="multiselect-clear multiselect-clear-reset"
                tabindex="-1"
                @mousedown.prevent.stop="clear"
            >
                <span class="multiselect-clear-icon" />
            </span>
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
        ref,
        toRefs,
        watch,
    } from 'vue';

    import { useField } from 'vee-validate';

    import * as yup from 'yup';

    import { useI18n } from 'vue-i18n';

    import useAttributeStore from '@/bootstrap/stores/attribute.js';
    import useSystemStore from '@/bootstrap/stores/system.js';

    import {
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
            const attributeStore = useAttributeStore();
            const systemStore = useSystemStore();
            const {
                name,
                disabled,
                value,
                selections,
                selectionFrom,
                selectionFromValue,
            } = toRefs(props);

            const multiselect = ref(null);

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
            const getAttributeName = attributeId => {
                return attributeStore.getAttributeName(attributeId);
            };

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

            const handleSelectionUpdate = async conceptId => {
                if(!state.hasRootAttribute) return;

                if(!conceptId) {
                    state.localSelection = [];
                    updateCurrentValue();
                    return;
                }

                const cachedSelection = await systemStore.fetchCachedConceptSelection(conceptId);
                state.localSelection = cachedSelection;
                updateCurrentValue();
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

            const handleTab = event => {
                const value = event.target?.value?.toLowerCase() ?? '';
                if(isOnlyChoice(value)) {
                    return formatAndHandleChange(state.filteredSelections[0]);
                }

                const match = state.filteredSelections.find(concept => {
                    const label = translateConcept(concept.concept_url);
                    return checkPerfectMatch(value, label);
                });
                if(match) {
                    return formatAndHandleChange(match);
                }
            };

            const isOnlyChoice = value => {
                return value && value.length > 0 && state.filteredSelections && state.filteredSelections.length == 1;
            };

            const isPerfectMatch = label => {
                if(!state.query) return false;
                return checkPerfectMatch(state.query, label);
            };

            const checkPerfectMatch = (search, label) => {
                return search.toLowerCase() === label.toLowerCase();
            };

            const isTabOption = option => {
                const concept = translateConcept(option.concept_url);
                return isOnlyChoice(concept) || isPerfectMatch(concept);
            };

            const clearInputOnDelete = e => {
                if(e.key === 'Delete' || e.code === 'Delete' || e.which === 46 || e.keyCode === 46) {
                    state.query = '';
                    v.handleChange(null);
                    multiselect.value.clearSearch();
                }
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

            return {
                t,
                // HELPERS
                multiselectResetClasslist,
                translateConcept,
                // LOCAL
                getAttributeName,
                clearInputOnDelete,
                handleTab,
                isTabOption,
                multiselect,
                resetFieldState,
                setSearchQuery,
                undirtyField,
                // STATE
                state,
                v,
            };
        },
    };
</script>
