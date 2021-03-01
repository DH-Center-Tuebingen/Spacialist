<template>
    <multiselect
        v-model="state.entry"
        name="global-search"
        :object="false"
        :label="'id'"
        :track-by="'id'"
        :valueProp="'id'"
        :mode="'single'"
        :options="query => search(query)"
        :filterResults="false"
        :resolveOnLoad="false"
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
    // import TypeaheadSearch from './TypeaheadSearch.vue';

    // import SearchResultBibliography from './SearchResultBibliography.vue';
    // import SearchResultEntity from './SearchResultEntity.vue';
    // import SearchResultFile from './SearchResultFile.vue';
    // import SearchResultGeodata from './SearchResultGeodata.vue';
    import {
        computed,
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
        // methods: {
        //     update() {
        //         this.cancel();

        //         if(!this.query) {
        //             return this.reset();
        //         }

        //         if(this.minChars) {
        //             if(this.query.length < this.minChars) {
        //                 return;
        //             }
        //             if(this.hasShebang && this.query.length - this.shebangLength < this.minChars) {
        //                 return;
        //             }
        //         }

        //         this.loading = true;

        //         this.fetch().then((response) => {
        //             if(response && this.query) {
        //                 let data = response.data;
        //                 data = this.prepareResponseData ? this.prepareResponseData(data) : data;
        //                 this.items = data;
        //                 this.current = -1;
        //                 this.loading = false;

        //                 if(this.selectFirst) {
        //                     this.down();
        //                 }
        //             }
        //         });
        //     },
        //     onHit(item) {
        //         if(item) {
        //             this.query = item.name;
        //             switch (item.group) {
        //                 case 'entities':
        //                     this.$router.push({
        //                         name: 'entitydetail',
        //                         params: {
        //                             id: item.id
        //                         }
        //                     });
        //                     break;
        //                 case 'files':
        //                     const currRoute = this.$route;
        //                     const query = {
        //                         tab: 'files',
        //                         f: item.id
        //                     };
        //                     // Only append, if current route is one of the
        //                     // MainView routes
        //                     const append = currRoute.name == 'home' || currRoute.name == 'entitydetail';
        //                     this.$router.push({
        //                         append: append,
        //                         query: query
        //                     });
        //                     break;
        //                 case 'bibliography':
        //                     this.$router.push({
        //                         name: 'bibedit',
        //                         params: {
        //                             id: item.id
        //                         }
        //                     });
        //                     break;
        //                 case 'geodata':
        //                     //TODO
        //                     break;
        //                 default:
        //                     this.$throwError({message: `Action is not yet implemented for items of type ${item.group}.`});
        //             }
        //         } else {
        //             this.query = '';
        //         }
        //         this.closeSelect();
        //     },
        //     clearItem() {
        //         this.onHit();
        //     },
        //     closeSelect() {
        //         this.items = [];
        //         this.loading = false;
        //     }
        // }
    }
</script>
