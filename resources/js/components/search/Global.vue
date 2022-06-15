<template>
    <multiselect
        v-model="state.entry"
        name="global-search"
        :id="state.id"
        :object="true"
        :label="'id'"
        :track-by="'id'"
        :valueProp="'id'"
        :mode="'single'"
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
        @select="optionSelected">
            <template v-slot:singlelabel="{ value }">
                <div class="multiselect-single-label">
                    {{ value.name }}
                </div>
            </template>
            <template v-slot:option="{ option }">
                <div class="col-3 text-center">
                    <img :src="option.thumb_url" :alt="option.name" class="w-100" v-if="isImage(option)" />
                    <span v-else-if="isFile(option, true)">
                        <i class="fas fa-fw fa-file"></i>
                    </span>
                    <span v-else-if="isEntity(option)">
                        <i class="fas fa-fw fa-monument"></i>
                    </span>
                    <span v-else-if="isBibliography(option)">
                        <i class="fas fa-fw fa-book"></i>
                    </span>
                    <span v-else-if="isGeodata(option)">
                        <i class="fas fa-fw fa-map-marked-alt"></i>
                    </span>
                </div>
                <div class="col-9 ps-2">
                    <span v-if="isFile(option)">
                        {{ option.name }}
                    </span>
                    <span v-if="isBibliography(option)">
                        {{ option.citekey }}
                        {{ option.author }}
                        {{ option.title }}
                    </span>
                    <span v-if="isEntity(option)">
                        {{ option.name }}
                    </span>
                    <span v-if="isGeodata(option)">
                        {{ option.id }}
                    </span>
                </div>
            </template>
            <template v-slot:nooptions="">
                <div class="p-2" v-if="!!state.query" v-html="t('global.search_no_results_for', {term: state.query})">
                </div>
                <div class="p-1 text-muted" v-else>
                    {{ t('global.search_no_term_info_global') }}
                </div>
            </template>
    </multiselect>
</template>

<script>
    import {
        reactive,
        onMounted,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        searchGlobal,
    } from '@/api.js';

    import {
        getTs
    } from '@/helpers/helpers.js';
    import {
        routeToBibliography,
        routeToEntity,
        routeToFile,
        routeToGeodata,
    } from '@/helpers/routing.js';

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
                state.query = query;
                if(!query) {
                    return await new Promise(r => r([]));
                }
                return await searchGlobal(query);
            };
            const isFile = (searchRes, withoutImages = false) => {
                let ret = searchRes.group == 'files';
                if(!ret) return false;

                if(withoutImages) {
                    ret = !searchRes.mime_type.startsWith('image/');
                }
                return ret;
            };
            const isImage = searchRes => {
                return searchRes.group == 'files' && searchRes.mime_type.startsWith('image/');
            };
            const isEntity = searchRes => {
                return searchRes.group == 'entities';
            };
            const isBibliography = searchRes => {
                return searchRes.group == 'bibliography';
            };
            const isGeodata = searchRes => {
                return searchRes.group == 'geodata';
            };
            const optionSelected = option => {
                if(isEntity(option)) {
                    routeToEntity(option.id);
                }
                if(isFile(option)) {
                    routeToFile(option.id);
                }
                if(isBibliography(option)) {
                    routeToBibliography(option.id);
                }
                if(isGeodata(option)) {
                    routeToGeodata(option.id);
                }
                state.entry = {};
            };

            // DATA
            const state = reactive({
                id: `multiselect-global-search-${getTs()}`,
                entry: {},
                query: '',
            });

            // RETURN
            return {
                t,
                // HELPER
                // LOCAL
                search,
                isFile,
                isImage,
                isEntity,
                isBibliography,
                isGeodata,
                optionSelected,
                // PROPS
                delay,
                limit,
                // STATE
                state,
            };
        },
    }
</script>
