<template>
    <div class="input-group">
        <!-- <div class="input-group-prepend">
            <i class="fas fa-fw fa-times" v-show="isDirty" @click="reset"></i>
        </div> -->
        <input type="text"
        class="form-control"
        :placeholder="placeholder"
        autocomplete="off"
        v-model="query"
        @keydown.down="down"
        @keydown.up="up"
        @keydown.enter="hit"
        @keydown.esc="reset"
        @blur="reset"
        @input="debounce"/>
        <div class="input-group-append">
            <span class="input-group-text multiselect-search">
                <i class="fas fa-spinner fa-spin" v-if="loading"></i>
                <template v-else>
                    <i class="fas fa-fw fa-search" v-show="isEmpty"></i>
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
            }
        },
        data () {
            return {
                src: '/api/context/search',
                limit: 5,
                minChars: 3,
                selectFirst: false
            }
        },
        computed: {
            debounce () {
                return debounce(this.update, 250)
            }
        },
        methods: {
            onHit(item) {
                console.log(item);
                this.query = item;
            }
        }
    }
</script>
