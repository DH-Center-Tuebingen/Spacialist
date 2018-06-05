<template>
    <div class="input-group">
        <!-- <div class="input-group-prepend">
            <i class="fas fa-fw fa-times" v-show="isDirty" @click="reset"></i>
        </div> -->
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
                default: 'Search...'
            },
            onSelect: {
                type: Function,
                required: false
            },
            value: {
                type: String,
                required: false
            }
        },
        data () {
            return {
                src: '/api/search/context',
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
                this.onSelect(item);
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
