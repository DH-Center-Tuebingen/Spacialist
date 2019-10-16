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
        @blur="blur"
        @input="debounce"
        @keydown.down="down"
        @keydown.enter="hit"
        @keydown.esc="clearItem"
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

        <div class="dropdown-menu d-flex flex-column search-result-list" v-if="hasItems">
            <a href="#" class="dropdown-item" v-for="(item, $item) in items" :class="activeClass($item)" @mousedown="hit" @mousemove="setActive($item)">
                <span>{{item.name}}</span>
                <ol class="breadcrumb mb-0 p-0 pb-1 bg-none small">
                    <li class="breadcrumb-item" v-for="a in item.ancestors">
                        <span>{{ a }}</span>
                    </li>
              </ol>
            </a>
        </div>
    </div>
</template>

<script>
    import TypeaheadSearch from './TypeaheadSearch.vue';

    export default {
        extends: TypeaheadSearch,
        data () {
            return {
                src: 'search/entity',
            }
        },
        computed: {
        },
        methods: {
            onHit(item) {
                const vm = this;
                this.$router.push({
                    name: 'entitydetail',
                    params: {
                        id: item.id
                    },
                    query: this.$route.query
                });
                this.reset();
            },
            hit() {
                if(this.current !== -1) {
                    this.onHit(this.items[this.current]);
                } else {
                    if(this.onMultiselect) {
                        this.onMultiselect(this.items);
                        this.items = [];
                    }
                }
            },
        }
    }
</script>
