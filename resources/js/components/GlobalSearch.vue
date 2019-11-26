<template>
    <div class="input-group">
        <input type="text"
        autocomplete="off"
        class="form-control"
        v-model="query"
        :placeholder="$t(placeholder)"
        @blur="closeSelect"
        @input="defaultUpdate"
        @keydown.down="down"
        @keydown.enter="hit"
        @keydown.esc="reset"
        @keydown.up="up"/>
        <div class="input-group-append">
            <span class="input-group-text clickable" @click="clearItem">
                <i class="fas fa-fw fa-times"></i>
            </span>
            <span class="input-group-text multiselect-search">
                <i class="fas fa-spinner fa-spin" v-if="loading"></i>
                <template v-else>
                    <i class="fas fa-fw fa-search"></i>
                </template>
            </span>
        </div>

        <div class="dropdown-menu d-flex flex-column search-result-list" style="max-height: 50vh;" v-if="hasItems">
            <a href="#" class="dropdown-item px-1" v-for="(item, k) in items" :class="activeClass(k)" @mousedown="hit" @mousemove="setActive(k)">
                <component :is="'search-result-'+item.group" :data="item"></component>
            </a>
        </div>
    </div>
</template>

<script>
    import TypeaheadSearch from './TypeaheadSearch.vue';

    import SearchResultBibliography from './SearchResultBibliography.vue';
    import SearchResultEntity from './SearchResultEntity.vue';
    import SearchResultFile from './SearchResultFile.vue';
    import SearchResultGeodata from './SearchResultGeodata.vue';

    export default {
        extends: TypeaheadSearch,
        components: {
            'search-result-bibliography': SearchResultBibliography,
            'search-result-entities': SearchResultEntity,
            'search-result-files': SearchResultFile,
            'search-result-geodata': SearchResultGeodata
        },
        data () {
            return {
                src: 'search',
                shebangLength: 3, // is always '!' + letter + space
            }
        },
        mounted() {
        },
        computed: {
            hasShebang() {
                if(!this.query.length) {
                    return false;
                }
                return !!this.query.match(/^!\w\s/);
            }
        },
        methods: {
            update() {
                this.cancel();

                if(!this.query) {
                    return this.reset();
                }

                if(this.minChars) {
                    if(this.query.length < this.minChars) {
                        return;
                    }
                    if(this.hasShebang && this.query.length - this.shebangLength < this.minChars) {
                        return;
                    }
                }

                this.loading = true;

                this.fetch().then((response) => {
                    if(response && this.query) {
                        let data = response.data;
                        data = this.prepareResponseData ? this.prepareResponseData(data) : data;
                        this.items = data;
                        this.current = -1;
                        this.loading = false;

                        if(this.selectFirst) {
                            this.down();
                        }
                    }
                });
            },
            onHit(item) {
                if(item) {
                    this.query = item.name;
                    switch (item.group) {
                        case 'entities':
                            this.$router.push({
                                name: 'entitydetail',
                                params: {
                                    id: item.id
                                }
                            });
                            break;
                        case 'files':
                            const currRoute = this.$route;
                            const query = {
                                tab: 'files',
                                f: item.id
                            };
                            // Only append, if current route is one of the
                            // MainView routes
                            const append = currRoute.name == 'home' || currRoute.name == 'entitydetail';
                            this.$router.push({
                                append: append,
                                query: query
                            });
                            break;
                        case 'bibliography':
                            this.$router.push({
                                name: 'bibedit',
                                params: {
                                    id: item.id
                                }
                            });
                            break;
                        case 'geodata':
                            //TODO
                            break;
                        default:
                            this.$throwError({message: `Action is not yet implemented for items of type ${item.group}.`});
                    }
                } else {
                    this.query = '';
                }
                this.closeSelect();
            },
            clearItem() {
                this.onHit();
            },
            closeSelect() {
                this.items = [];
                this.loading = false;
            }
        }
    }
</script>
