<template>
    <div class="container">
        <tree-view
            :category="category"
            :css="css"
            :display="display"
            :model="roots"
            :onSelect="onSelect"
            :openerOpts="openerOpts"
            :selection="selection"
            :strategies="strategies">
        </tree-view>
    </div>
</template>

<script>
    import { TreeView } from '@bosket/vue';

    export default {
        components: {
            'tree-view': TreeView
        },
        props: {
            concepts: {
                required: false, // TODO required?
                type: Object
            },
            contextTypes: {
                required: false, // TODO required?
                type: Object
            },
            roots: {
                required: true,
                type: Array
            },
            selectionCallback: {
                required: false,
                type: Function
            }
        },
        mounted() {},
        methods: {
            onSelect(newSelection) {
                this.selection = newSelection
                this.selectionCallback(newSelection[0]);
            }
        },
        data() {
            return {
                selection: [],
                strategies: {
                    selection: ["single"],
                    click: ["select"],
                    fold: ["opener-control"]
                },
                openerOpts: {
                    position: 'left'
                },
                css: {
                    opener: 'opener mr-2 text-info'
                },
                category: "children",
                display: (item, inputs) =>
                    <span>
                        {item.name}
                        <i class="pl-2">{this.concepts[this.contextTypes[item.context_type_id].thesaurus_url].label}</i>
                    </span>
            }
        }
    }
</script>
