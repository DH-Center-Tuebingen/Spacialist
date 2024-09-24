<template>
    <multiselect
        :id="state.id"
        v-model="state.entry"
        class="multiselect"
        :name="state.id"
        :object="true"
        :label="'id'"
        :track-by="'id'"
        :value-prop="'id'"
        :mode="mode"
        :options="state.searchResults"
        :hide-selected="false"
        :filterResults="false"
        :resolve-on-load="false"
        :clear-on-search="true"
        :clear-on-select="true"
        :caret="false"
        :min-chars="0"
        :searchable="true"
        :placeholder="t('global.search')"
        :disabled="disabled"
        @change="handleSelection"
        @search-change="search"
    >
        <template #singlelabel="{ value }">
            <div class="multiselect-single-label">
                {{ displayResult(value) }}
            </div>
        </template>
        <template #tag="{ option, handleTagRemove, disabled: tagDisabled }">
            <div
                class="multiselect-tag"
                :class="{'pe-2': tagDisabled}"
            >
                <span @click.prevent="handleTagClick(option)">
                    {{ displayResult(option) }}
                </span>
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
        <template #option="{ option }">
            <span v-if="!state.enableChain">
                {{ displayResult(option) }}
            </span>
            <div
                v-else
                class="d-flex flex-column"
            >
                <span>
                    {{ displayResult(option) }}
                </span>
                <ol class="breadcrumb m-0 p-0 bg-none small">
                    <li
                        v-for="anc in option[chain]"
                        :key="`search-result-multiselect-${state.id}-${anc}`"
                        class="breadcrumb-item text-muted small"
                    >
                        <span>
                            {{ anc }}
                        </span>
                    </li>
                </ol>
            </div>
        </template>
        <template #nooptions="">
            <div
                v-if="!!state.query"
                class="p-2"
            >
                <span>
                    {{ t('global.search_no_results_for') }}
                </span>
                <span class="fst-italic fw-bold">
                    {{ state.query }}
                </span>
            </div>
            <div
                v-else
                class="p-1 text-muted"
            >
                {{ t('global.search_no_term_info') }}
            </div>
        </template>
        <template
            v-if="infinite && canFetchMore && state.hasResults"
            #afterlist=""
        >
            <div class="p-2">
                <a
                    class="text-decoration-none text-reset"
                    href="#"
                    @click.prevent="search(state.query)"
                >
                    {{ t('global.search_load_n_more', {n: limit}) }}
                </a>
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

    import { useI18n } from 'vue-i18n';

    import {
        _debounce,
        getTs,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            delay: {
                type: Number,
                required: false,
                default: 300,
            },
            limit: {
                type: Number,
                required: false,
                default: 10,
            },
            endpoint: {
                type: Function,
                required: true,
            },
            filterFn: {
                type: Function,
                required: false,
                default: (val) => val,
            },
            keyText: {
                type: String,
                required: false,
                default: null,
            },
            keyFn: {
                type: Function,
                required: false,
                default: null,
            },
            chain: {
                type: String,
                required: false,
                default: null,
            },
            mode: {
                type: String,
                required: false,
                default: 'single',
            },
            defaultValue: {
                type: Object, // TODO should be Array for Entity-MC
                required: false,
                default: null,
            },
            disabled: {
                type: Boolean,
                required: false,
                default: false,
            },
            infinite: {
                type: Boolean,
                required: false,
                default: false,
            },
            canFetchMore: {
                type: Boolean,
                required: false,
                default: false,
            },
        },
        emits: ['selected', 'entry-click'],
        setup(props, context) {
            const { t } = useI18n();

            const {
                delay,
                limit,
                endpoint,
                filterFn,
                keyText,
                keyFn,
                chain,
                mode,
                defaultValue,
                disabled,
                infinite,
            } = toRefs(props);

            if(!keyText.value && !keyFn.value) {
                throw new Error('You have to either provide a key or key function for your search component!');
            }
            // FETCH

            // FUNCTIONS
            const search = _debounce(async query => {
                const queryChanged = state.query != query;
                state.query = query;
                if(!query) {
                    state.hasResults = false;
                    state.searchResults = [];
                    return;
                }
                const results = await endpoint.value(query);

                let filteredResults;
                if(!!filterFn.value) {
                    filteredResults = filterFn.value(results, query);
                } else {
                    filteredResults = results;
                }
                state.hasResults = results.length > 0;
                if(queryChanged) {
                    state.searchResults = filteredResults;
                } else {
                    state.searchResults = [
                        ...state.searchResults,
                        ...filteredResults
                    ];
                }
            }, 250);
            const displayResult = result => {
                if(!!keyText.value) {
                    return result[keyText.value];
                } else if(!!keyFn.value) {
                    return keyFn.value(result);
                } else {
                    // Should never happen ;) :P
                    throw new Error('Can not display search result!');
                }
            };
            const handleSelection = option => {
                let data = {};
                if(option) {
                    if(mode.value == 'single') {
                        data = {
                            ...option,
                            added: true,
                        };
                    } else {
                        data = {
                            values: option,
                            added: true,
                        };
                    }
                } else {
                    data.removed = true;
                }
                state.entry = option;
                context.emit('selected', data);
            };
            const handleTagClick = option => {
                context.emit('entry-click', option);
            };

            const getBaseValue = _ => {
                    return mode.value == 'single' ? {} : [];
            };

            const getDefaultValue = _ => {
                if(defaultValue.value)
                    return defaultValue.value;
                else
                    return getBaseValue();
            };

            // DATA
            const state = reactive({
                id: `multiselect-search-${getTs()}`,
                entry: getDefaultValue(),
                query: '',
                searchResults: [],
                hasResults: false,
                enableChain: computed(_ => chain.value && chain.value.length > 0),
            });

            watch(_ => defaultValue.value, (newValue, oldValue) => {
                if(!newValue || newValue.reset) {
                    state.entry = getBaseValue();
                } else {
                    state.entry = newValue;
                }
            });

            // RETURN
            return {
                t,
                // HELPER
                // LOCAL
                search,
                displayResult,
                handleSelection,
                handleTagClick,
                // STATE
                state,
            };
        },
    }
</script>
