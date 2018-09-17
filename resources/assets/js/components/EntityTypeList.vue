<template>
    <ul class="ml-3 list-unstyled mb-0">
        <li v-for="(d, i) in entries" class="pb-1 d-flex align-items-center justify-content-between" @mouseenter="onEnter(i)" @mouseleave="onLeave(i)">
            <i class="fas fa-fw fa-monument"></i>
            <a class="p-2" href="#" :class="{ 'font-weight-bold': d.id == selectedElement.id }" @click.prevent="select(d)">
                {{ $translateConcept(d.thesaurus_url) }}
            </a>
            <span class="ml-auto" v-if="onDelete">
                <button class="btn btn-danger btn-fab rounded-circle" v-show="hoverState[i]" @click="onDelete(d)">
                    <i class="fas fa-fw fa-xs fa-trash" style="vertical-align: 0;"></i>
                </button>
            </span>
        </li>
        <li v-if="onAdd">
            <i class="fas fa-fw fa-plus"></i>
            <a href="#" @click.prevent="onAdd()" class="text-secondary">
                {{ $t('main.datamodel.entity.add-button') }}
            </a>
        </li>
    </ul>
</template>

<script>
    export default {
        props: {
            data: {
                type: Array,
                required: true
            },
            onAdd: {
                type: Function,
                required: false
            },
            onDelete: {
                type: Function,
                required: false
            },
            onSelect: {
                type: Function,
                required: false
            }
        },
        mounted() {},
        methods: {
            onEnter(i) {
                Vue.set(this.hovered, i, true);
            },
            onLeave(i) {
                Vue.set(this.hovered, i, false);
            },
            select(entityType) {
                this.selectedElement = Object.assign({}, entityType);
                this.onSelect(entityType);
            }
        },
        data() {
            return {
                hovered: [],
                selectedElement: {}
            }
        },
        created() {
            for(let i=0; i<this.data.length; i++) {
                this.hovered.push(false);
            }
        },
        computed: {
            hoverState: function() {
                return this.hovered;
            },
            entries: function() {
                return this.data.slice();
            }
        }
    }
</script>
