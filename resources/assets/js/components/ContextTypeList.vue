<template>
    <div>
        <ul class="ml-3 list-unstyled">
            <li v-for="(d, i) in data" class="pb-1 d-flex align-items-center justify-content-between" @mouseenter="onEnter(i)" @mouseleave="onLeave(i)">
                <i class="fas fa-fw fa-seedling"></i>
                <a class="p-2" href="#" :class="{ 'font-weight-bold': d.id == selectedElement.id }" @click="select(d)">
                    {{ concepts[d.thesaurus_url].label }}
                </a>
                <span class="ml-auto" v-if="onDelete">
                    <button class="btn btn-danger btn-fab rounded-circle" v-show="hoverState[i]" @click="onDelete(d)">
                        <i class="fas fa-fw fa-xs fa-trash" style="vertical-align: 0;"></i>
                    </button>
                </span>
            </li>
            <li v-if="onAdd">
                <i class="fas fa-fw fa-plus"></i>
                <a href="#" v-on:click="onAdd()" class="text-secondary">New Context-Type...</a>
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        props: {
            data: {
                type: Array,
                required: true
            },
            concepts: {
                type: Object,
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
            select(contextType) {
                this.selectedElement = Object.assign({}, contextType);
                this.onSelect(contextType);
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
            }
        }
    }
</script>
