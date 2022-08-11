<template>
    <multiselect
        v-model="state.entry"
        name="global-search"
        class="multiselect-wide"
        :id="state.id"
        :object="true"
        :label="'idx'"
        :track-by="'idx'"
        :valueProp="'idx'"
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
                {{ value.title }}
            </div>
        </template>
        <template v-slot:option="{ option }">
            <div class="col-3 text-center">
                <img :src="option.thumb_url" :alt="option.title" class="w-100" v-if="isImage(option)" />
                <span v-else-if="isFile(option, true)">
                    <i class="fas fa-fw fa-file"></i>
                </span>
                <span v-else-if="isEntity(option) || isEntityAttribute(option)">
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
                    {{ truncate(option.searchable.name, 50) }}
                </span>
                <span v-if="isBibliography(option)">
                    <div>
                        <div>
                            <span class="fw-medium">{{ truncate(option.searchable.title, 50) }}</span>
                        </div>
                        <cite class="small">
                            {{ truncate(option.searchable.author, 40) }} ({{ option.searchable.year }})
                        </cite>
                    </div>
                </span>
                <span v-if="isEntity(option)">
                    {{ truncate(option.searchable.name, 50) }}
                </span>
                <span v-if="isEntityAttribute(option)">
                    {{ truncate(option.title, 50) }}
                </span>
                <span v-if="isGeodata(option)">
                    {{ option.title }}
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
        truncate,
    } from '@/helpers/filters.js';
    import {
        getTs,
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
                const results = await searchGlobal(query);
                return results.map((r, i) => {
                    return {
                        ...r,
                        idx: i,
                    };
                });
            };
            const isFile = (searchRes, withoutImages = false) => {
                let ret = searchRes.type == 'files';
                if(!ret) return false;

                if(withoutImages) {
                    ret = !searchRes.searchable.mime_type.startsWith('image/');
                }
                return ret;
            };
            const isImage = searchRes => {
                return searchRes.type == 'files' && searchRes.searchable.mime_type.startsWith('image/');
            };
            const isEntity = searchRes => {
                return searchRes.type == 'entities';
            };
            const isEntityAttribute = searchRes => {
                return searchRes.type == 'entity_attribute';
            };
            const isBibliography = searchRes => {
                return searchRes.type == 'bibliography';
            };
            const isGeodata = searchRes => {
                return searchRes.type == 'geodata';
            };
            const optionSelected = option => {
                const obj = option.searchable;
                if(isEntity(option)) {
                    routeToEntity(obj.id);
                } else if(isEntityAttribute(option)) {
                    routeToEntity(obj.entity_id);
                } else if(isFile(option)) {
                    routeToFile(obj.id);
                } else if(isBibliography(option)) {
                    routeToBibliography(obj.id);
                } else if(isGeodata(option)) {
                    routeToGeodata(obj.id);
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
                truncate,
                // LOCAL
                search,
                isFile,
                isImage,
                isEntity,
                isEntityAttribute,
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
