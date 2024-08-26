<template>
    <multiselect
        v-model="v.value"
        :value-prop="'id'"
        :label="'concept_url'"
        :track-by="'concept_url'"
        :object="true"
        :mode="'tags'"
        :disabled="disabled"
        :options="state.filteredSelections"
        :name="name"
        :searchable="true"
        :infinite="true"
        :limit="15"
        :filter-results="false"
        :close-on-select="false"
        :placeholder="t('global.select.placeholder')"
        @change="v.handleChange"
        @search-change="setSearchQuery"
    >
        <template #option="{ option }">
            {{ translateConcept(option.concept_url) }}
        </template>
        <template #tag="{ option, handleTagRemove, disabled: tagDisabled }">
            <div
                class="multiselect-tag"
                :class="{'pe-2': tagDisabled}"
            >
                {{ translateConcept(option.concept_url) }}
                <span
                    v-if="!tagDisabled"
                    class="multiselect-tag-remove"
                    @click.prevent
                    @mousedown.prevent.stop="handleTagRemove(option, $event)"
                >
                    <span class="multiselect-tag-remove-icon" />
                </span>
            </div>
        </template>
    </multiselect>
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

    import {
        translateConcept,
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
                type: Array,
                required: true,
                default: _ => [],
            },
            selections: {
                type: Array,
                required: true,
            },
        },
        emits: [
            'change',
        ],
        setup(props, context) {
            const { t } = useI18n();
            const {
                name,
                disabled,
                value,
                selections,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS
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

            const setSearchQuery = query => {
                state.query = query ? query.toLowerCase().trim() : null;
            };

            // DATA
            const {
                handleChange,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`mc_${name.value}`, yup.mixed(), {
                initialValue: value.value,
            });
            const state = reactive({
                query: null,
                filteredSelections: computed(_ => {
                    let selection = null;
                    if(!state.query) {
                        selection = selections.value;
                    } else {
                        selection = selections.value.filter(concept => {
                            return concept.concept_url.toLowerCase().indexOf(state.query) !== -1 || translateConcept(concept.concept_url).toLowerCase().indexOf(state.query) !== -1;
                        });
                    }

                    return selection.map(s => only(s, ['id', 'concept_url']));
                }),
            });
            const v = reactive({
                value: fieldValue,
                handleChange,
                meta,
                resetField,
            });

            watch(_ => value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(_ => v.value, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: v.value,
                });
            });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
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
