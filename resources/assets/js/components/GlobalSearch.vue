<template>
    <div class="input-group">
        <input type="text"
        autocomplete="off"
        class="form-control"
        v-model="query"
        :placeholder="placeholder"
        @blur="closeSelect"
        @input="debounce"
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

        <div class="dropdown-menu" style="display: flex; flex-direction: column; max-height: 50vh; overflow-y: auto;" v-show="hasItems">
            <a href="#" class="dropdown-item px-1" v-for="(item, k) in items" :class="activeClass(k)" @mousedown="hit" @mousemove="setActive(k)">
                <component :is="'search-result-'+item.group" :data="item"></component>
            </a>
        </div>
    </div>
</template>

<script>
    import VueTypeahead from 'vue-typeahead';
    import debounce from 'debounce';

    Vue.component('search-result-bibliography', require('./SearchResultBibliography.vue'));
    Vue.component('search-result-entities', require('./SearchResultEntity.vue'));
    Vue.component('search-result-files', require('./SearchResultFile.vue'));
    Vue.component('search-result-geodata', require('./SearchResultGeodata.vue'));

    export default {
        extends: VueTypeahead,
        props: {
            placeholder: {
                type: String,
                default: 'Search...'
            },
            value: {
                type: String,
                required: false
            }
        },
        mounted() {
            this.query = this.value;
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
                            this.$router.push({name: 'contextdetail', params: {id: item.id}});
                            break;
                        case 'files':
                            this.$router.push({name: 'files', params: {id: item.id}});
                        case 'bibliography':
                            //TODO
                        case 'geodata':
                            //TODO
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
        },
        data () {
            return {
                src: 'search',
                minChars: 3,
                shebangLength: 3, // is always '!' + letter + space
                selectFirst: false
            }
        },
        computed: {
            debounce() {
                return debounce(this.update, 250)
            },
            hasShebang() {
                if(!this.query.length) {
                    return false;
                }
                return !!this.query.match(/^!\w\s/);
            }
        }
    }
</script>
