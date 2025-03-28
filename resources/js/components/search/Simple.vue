<template>
    <multiselect
        :id="state.id"
        :value="value"
        class="multiselect"
        :name="state.id"
        :object="true"
        :label="'id'"
        :loading="state.loading"
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
        :append-to-body="appendToBody"
        @search-change="search"
        @select="onSelected"
        @change="onChanged"
    >
        <template #singlelabel="{ value }">
            <div class="multiselect-single-label">
                {{ displayResult(value) }}
            </div>
        </template>
        <template #tag="{ option, handleTagRemove, disabled: tagDisabled }">
            <div
                class="multiselect-tag"
                :class="{ 'pe-2': tagDisabled }"
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
            <div class="d-flex flex-column">
                <span>
                    {{ displayResult(option) }}
                </span>
                <slot
                    v-if="state.enableChain"
                    name="chain"
                    :option="option"
                >
                    <Chain :chain="getChain(option)" />
                </slot>
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
            v-if="infinite && canFetchMore && hasResults"
            #afterlist=""
        >
            <div class="d-flex">
                <!--
                It happened that the click listener occasionally closed the select list.
                Using the mousedown event prevents this from happening.
                We need to prevent the click event and stop the propagation of the link navigation.
                -->
                <a
                    class="list-hover text-decoration-none d-flex align-items-center justify-content-between p-2 flex-fill"
                    href="#"
                    tabindex="-1"
                    draggable="false"
                    @click.stop.prevent
                    @mousedown.stop.prevent="loadMore()"
                >
                    <span>
                        <i class="fas fa-fw fa-arrows-rotate" />
                        {{ t('global.search_load_n_more', { n: limit }) }}
                    </span>

                    <span
                        v-if="state.loading"
                        class="spinner-border spinner-border-sm ms-2"
                        role="status"
                        aria-hidden="true"
                    />
                </a>
            </div>
        </template>
    </multiselect>
</template>

<script>
    import {
        computed,
        reactive,
        ref,
        toRefs,
        watch,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        _debounce,
        getTs,
    } from '@/helpers/helpers.js';
    import Chain from '@/components/chain/Chain.vue';

    export default {
        components: {
            Chain,
        },
        props: {
            appendToBody:{
                type: Boolean,
                required: false,
                default: false,
            },
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
            chainFn: {
                type: Function,
                required: false,
                default: null,
            },
            mode: {
                required: false,
                default: 'single',
                validator: (val) => ['single', 'multiple'].includes(val),
            },
            value: {
                required: false,
                default: null,
                validator: (val, props) => {
                    if(props.mode == 'single') {
                        return val == null || typeof val == 'object';
                    } else {
                        return val == null || Array.isArray(val);
                    }
                },
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


            if(!props.keyText && !props.keyFn) {
                throw new Error('You have to either provide a key or key function for your search component!');
            }
            // FETCH

            // FUNCTIONS

            // Used to check for a race condition
            const searchExecutionCounter = ref(0);
            /**
             * Called when the search is changed
             * so the query always has a different value.
             */
            const requestSearchEndpoint = async query => {
                // When selected a null value is set and triggers the
                // search again, resulting in an error. This is a workaround.
                if(!query) return;

                searchExecutionCounter.value++;
                const round = searchExecutionCounter.value;
                state.loading = true;
                const results = await props.endpoint(query);

                if(round !== searchExecutionCounter.value) {
                    // If there was a newer query executed in the meantime,
                    // we do not want to apply the results.
                    return;
                }

                let filteredResults;
                if(!!props.filterFn) {
                    filteredResults = props.filterFn(results, query, state.searchResults);
                } else {
                    filteredResults = results;
                }

                // Only apply if the query did not change in the meantime.
                if(state.query === query) {
                    state.searchResults = [
                        ...state.searchResults,
                        ...filteredResults,
                    ];
                }
                state.loading = false;
            };

            const debouncedSearchRequest = _debounce(requestSearchEndpoint, props.delay);
            const search = async query => {
                if(!query) query = '';
                resetSearch(query);
                // As long as the query is typed we debounce the search
                debouncedSearchRequest(query);
            };
            const resetSearch = (query = '') => {
                state.query = query;
                state.searchResults = [];
            };
            const loadMore = async _ => {
                if(state.loading) return;
                await requestSearchEndpoint(state.query);
            };

            const displayResult = obj => {
                if(props.keyText) {
                    return obj[props.keyText];
                } else if(props.keyFn) {
                    return props.keyFn(obj);
                } else {
                    // Should never happen
                    throw new Error('No key provided!');
                }
            };

            const getBaseValue = _ => {
                return props.mode == 'single' ? {} : [];
            };

            const onSelected = value => {
                context.emit('selected', value);
            };
            
            const onChanged = value => {
                context.emit('change', value);
            };

            const handleTagClick = option => {
                context.emit('entry-click', option);
            };

            // DATA
            const state = reactive({
                id: `multiselect-search-${getTs()}`,
                loading: false,
                query: 'base',
                isSimpleChain: computed(_ => props.chain && props.chain.length > 0),
                isFnChain: computed(_ => !!props.chainFn),
                enableChain: computed(_ => state.isSimpleChain || state.isFnChain || context.slots.chain),
                searchResults: [],
            });

            watch(_ => props.value, (newValue, oldValue) => {
                if(!newValue || newValue.reset) {
                    state.entry = getBaseValue();
                } else {
                    state.entry = newValue;
                }
            });

            const getChain = option => {
                if(state.isFnChain) {
                    return props.chainFn(option);
                } else {
                    return option[props.chain];
                }
            };

            const hasResults = computed(_ => state.searchResults.length > 0);

            // RETURN
            return {
                t,
                // HELPER
                // LOCAL
                hasResults,
                search,
                getChain,
                loadMore,
                displayResult,
                // handleChange,
                handleTagClick,
                onChanged,
                onSelected,
                // STATE
                state,
            };
        },
    };
</script>
