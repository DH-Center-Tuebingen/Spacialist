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
            <button class="btn btn-outline-secondary" type="button" @click="clearItem">
                <i class="fas fa-fw fa-times"></i>
            </button>
            <span class="input-group-text">
                <i class="fas fa-spinner fa-spin" v-if="loading"></i>
                <template v-else>
                    <i class="fas fa-fw fa-search"></i>
                </template>
            </span>
        </div>

        <div class="dropdown-menu" style="display: flex; flex-direction: column;" v-show="hasItems">
            <a href="#" class="dropdown-item" v-for="(item, $item) in items" :class="activeClass($item)" @mousedown="hit" @mousemove="setActive($item)">
                <span v-text="$translateConcept(item.thesaurus_url)"></span>
            </a>
        </div>
    </div>
</template>

<script>
    import TypeaheadSearch from '@/TypeaheadSearch.vue';

    export default {
        extends: TypeaheadSearch,
        props: {
            onSelect: {
                type: Function,
                required: false
            },
            resetInput: {
                required: false,
                type: Boolean
            },
        },
        data () {
            return {
                src: 'search/entity-type',
                limit: 5,
                minChars: 3,
                selectFirst: false
            }
        },
        mounted() {
        },
        computed: {
        },
        methods: {
            onHit(item) {
                this.query = item ? item.name : undefined;
                if(this.onSelect) this.onSelect(item);
                this.closeSelect();
                if(this.resetInput) this.reset();
            },
            clearItem() {
                if(this.onSelect) this.onSelect();
                this.reset();
            },
            closeSelect() {
                this.items = [];
                this.loading = false;
            }
        },
        watch: {
            value(newValue, oldValue) {
                this.query = newValue;
            }
        }
    }
</script>
