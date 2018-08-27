<template>
    <div class="input-group">
        <!-- <div class="input-group-prepend">
            <i class="fas fa-fw fa-times" v-show="isDirty" @click="reset"></i>
        </div> -->
        <input type="text"
        autocomplete="off"
        class="form-control"
        v-model="query"
        :placeholder="$t(placeholder)"
        @blur="closeSelect"
        @input="debounce"
        @keydown.down="down"
        @keydown.enter="hit"
        @keydown.esc="reset"
        @keydown.up="up"/>
        <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="button" @click="clearItem">
                <i class="fas fa-fw fa-times"></i>
            </button>
            <span class="input-group-text multiselect-search">
                <i class="fas fa-spinner fa-spin" v-if="loading"></i>
                <template v-else>
                    <i class="fas fa-fw fa-search"></i>
                </template>
            </span>
        </div>

        <div class="dropdown-menu" style="display: flex; flex-direction: column;" v-show="hasItems">
            <a href="#" class="dropdown-item" v-for="(item, $item) in items" :class="activeClass($item)" @mousedown="hit" @mousemove="setActive($item)">
                <span v-text="item.name"></span>
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
                default: 'global.search'
            },
            onSelect: {
                type: Function,
                required: false
            },
            filters: {
                required: false,
                type: Array
            },
            resetInput: {
                required: false,
                type: Boolean
            },
            value: {
                type: String,
                required: false
            }
        },
        data () {
            return {
                src: 'search/context',
                limit: 5,
                minChars: 3,
                selectFirst: false
            }
        },
        mounted() {
            this.query = this.value;
        },
        computed: {
            debounce () {
                return debounce(this.update, 250)
            }
        },
        methods: {
            onHit(item) {
                this.query = item ? item.name : undefined;
                if(this.onSelect) this.onSelect(item);
                this.closeSelect();
                if(this.resetInput) this.reset();
            },
            prepareResponseData(data) {
                if(this.filters.length) {
                    // remove all results with id in filters
                    // from result items
                    return data.filter(i => {
                        return this.filters.indexOf(i.id) == -1;
                    });
                } else {
                    return data;
                }
            },
            clearItem() {
                this.reset();
            },
            closeSelect() {
                this.items = [];
                this.loading = false;
            }
        }
    }
</script>
