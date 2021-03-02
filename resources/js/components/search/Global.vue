<template>
    <multiselect
        v-model="state.entry"
        name="global-search"
        class="multiselect-search"
        :object="false"
        :label="'id'"
        :track-by="'id'"
        :valueProp="'id'"
        :mode="'single'"
        :options="query => search(query)"
        :filterResults="false"
        :resolveOnLoad="false"
        :clearOnSearch="true"
        :clearOnSelect="true"
        :caret="false"
        :minChars="0"
        :searchable="true"
        :delay="delay"
        :limit="limit"
        :placeholder="t('global.search')">
            <template v-slot:singlelabel="{ value }">
                <div class="multiselect-single-label">
                    #{{ value.id }}: {{ value.name }}
                </div>
            </template>
            <template v-slot:option="{ option }">
                #{{ option.id }}: {{ option.name }}
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
        _debounce,
    } from '../../helpers/helpers.js';

    import {
        searchGlobal,
    } from '../../api.js';

    export default {
        props: {
            delay: {
                type: Number,
                required: false,
                default: 500,
            },
            limit: {
                type: Number,
                required: false,
                default: 10,
            },
        },
        setup(props, context) {
            const { t } = useI18n();
            const {
                delay,
                limit,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS
            const search = async (query) => {
                return await searchGlobal(query);
            };

            // DATA
            const state = reactive({
                entry: null,
            });

            // RETURN
            return {
                t,
                // HELPER
                // LOCAL
                search,
                // PROPS
                delay,
                limit,
                // STATE
                state,
            };
        },
    }
</script>
