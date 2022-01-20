<template>
    <multiselect
        v-model="state.entry"
        class="multiselect"
        :name="state.id"
        :id="state.id"
        :object="true"
        :label="'id'"
        :track-by="'id'"
        :valueProp="'id'"
        :mode="mode"
        :options="query => search(query)"
        :hideSelected="false"
        :filterResults="false"
        :resolveOnLoad="false"
        :clearOnSearch="true"
        :clearOnSelect="true"
        :caret="false"
        :minChars="0"
        :searchable="true"
        :delay="delay"
        :limit="limit"
        :placeholder="t('global.search')"
        @change="handleSelection">
            <template v-slot:singlelabel="{ value }">
                <div class="multiselect-single-label">
                    {{ displayResult(value) }}
                </div>
            </template>
            <template v-slot:tag="{ option, handleTagRemove, disabled }">
                <div class="multiselect-tag">
                    <span @click.prevent="handleTagClick(option)">
                        {{ displayResult(option) }}
                    </span>
                    <span v-if="!disabled" class="multiselect-tag-remove" @click.prevent @mousedown.prevent.stop="handleTagRemove(option, $event)">
                        <span class="multiselect-tag-remove-icon"></span>
                    </span>
                </div>
            </template>
            <template v-slot:option="{ option }">
                {{ displayResult(option) }}
            </template>
            <template v-slot:nooptions="">
                <div class="p-2" v-if="!!state.query" v-html="t('global.search_no_results_for', {term: state.query})">
                </div>
                <div class="p-1 text-muted" v-else>
                    {{ t('global.search_no_term_info') }}
                </div>
            </template>
    </multiselect>
</template>

<script>
    import {
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        getTs
    } from '../../helpers/helpers.js';

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
            },
            keyText: {
                type: String,
                required: false,
            },
            keyFn: {
                type: Function,
                required: false,
            },
            mode: {
                type: String,
                required: false,
                default: 'single',
            },
            defaultValue: {
                type: Object,
                required: false,
                default: null,
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
                mode,
                defaultValue,
            } = toRefs(props);
            if(!keyText && !keyFn) {
                throw new Error('You have to either provide a key or key function for your search component!');
            }
            // FETCH

            // FUNCTIONS
            const search = async (query) => {
                state.query = query;
                if(!query) {
                    return await new Promise(r => r([]));
                }
                const results = await endpoint.value(query);
                if(!!filterFn && !!filterFn.value) {
                    return filterFn.value(results, query);
                } else {
                    return results;
                }
            };
            const displayResult = result => {
                if(!!keyText && !!keyText.value) {
                    return result[keyText.value];
                } else if(!!keyFn && !!keyFn.value) {
                    return keyFn.value(result);
                } else {
                    // Should never happen ;) :P
                    throw new Error('Can not display search result!');
                }
            };
            const handleSelection = option => {
                let data = {}
                if(!!option) {
                    if(mode.value == 'single') {
                        data = {
                            ...option,
                            added: true,
                        };
                    } else {
                        data = {
                            values: option,
                            added: true,
                        }
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

            // DATA
            const state = reactive({
                id: `multiselect-search-${getTs()}`,
                entry: defaultValue,
                query: '',
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
                // PROPS
                delay,
                limit,
                mode,
                // STATE
                state,
            };
        },
    }
</script>
