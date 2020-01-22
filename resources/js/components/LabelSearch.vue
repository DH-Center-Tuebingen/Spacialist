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
            @input="defaultUpdate"
            @keydown.down="down"
            @keydown.enter="hit"
            @keydown.esc="reset"
            @keydown.up="up"/>
        <div class="input-group-append">
            <span class="input-group-text multiselect-search">
                <i class="fas fa-spinner fa-spin" v-if="loading"></i>
                <template v-else>
                    <i class="fas fa-fw fa-search" v-show="isEmpty"></i>
                </template>
            </span>
        </div>

        <div class="dropdown-menu d-flex flex-column search-result-list" v-if="hasItems">
            <a href="#" class="dropdown-item" v-for="(item, $item) in items" :class="activeClass($item)" @mousedown="hit" @mousemove="setActive($item)">
                <span v-text="item.labels[0].label"></span>
            </a>
        </div>
    </div>
</template>

<script>
    import TypeaheadSearch from './TypeaheadSearch.vue';

    export default {
        extends: TypeaheadSearch,
        props: {
            onSelect: {
                type: Function,
                required: false
            }
        },
        data () {
            return {
                src: 'search/label',
            }
        },
        computed: {
        },
        methods: {
            onHit(item) {
                this.query = item.labels[0].label;
                if(this.onSelect) this.onSelect(item.labels[0]);
                this.closeSelect();
            },
            closeSelect() {
                this.items = [];
                this.loading = false;
            }
        }
    }
</script>
