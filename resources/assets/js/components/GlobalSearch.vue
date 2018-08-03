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
            <a href="#" class="dropdown-item" v-for="(item, k) in items" :class="activeClass(k)" @mousedown="hit" @mousemove="setActive(k)">
                <span v-if="item.group == 'bibliography'">
                    <i class="fas fa-fw fa-book"></i>
                    {{ item.title }} - {{ item.author }}
                </span>
                <span v-else-if="item.group == 'entities'">
                    <i class="fas fa-fw fa-monument"></i>
                    {{ item.name }}
                </span>
                <span v-else-if="item.group == 'files'">
                    <i class="fas fa-fw fa-file"></i>
                    {{ item.name }} - {{ item.mime_type }}
                </span>
                <span v-else-if="item.group == 'geodata'">
                    <i class="fas fa-fw fa-map-marker-alt"></i>
                    {{ item }}: {{ item.group }}
                </span>
                <span v-else>
                    Unknown group: {{ item.group }}
                </span>
            </a>
        </div>
    </div>
</template>

<script>
    import VueTypeahead from 'vue-typeahead';
    import debounce from 'debounce';

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
                        for(let k in data) {
                            if(this.limit) {
                                data[k] = data[k].slice(0, this.limit).map(d => {
                                    d.group = k;
                                    return d;
                                });
                            }
                        }
                        this.items = Object.values(data).flat();
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
                limit: 5,
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
                console.log(this.query);
                if(!this.query.length) {
                    return false;
                }
                return !!this.query.match(/^!\w\s/);
            }
        }
    }
</script>
