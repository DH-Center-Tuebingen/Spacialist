<template>
    <div class="container">
        <tree-view :model="contexts" :category="category" :selection="selection" :onSelect="onSelect" :strategies="strategies" :display="display" :openerOpts="openerOpts" :css="css"></tree-view>
    </div>
</template>

<script>
    import { TreeView } from '@bosket/vue';

    export default {
        components: {
            'tree-view': TreeView
        },
        props: ['contexts', 'selectionCallback'],
        mounted() {},
        methods: {
            onSelect(newSelection) {
                this.selection = newSelection
                // this.selectionCallback(newSelection[0]); // TODO breaks selection
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
                category: "child_contexts",
                display: (item, inputs) => {
                    return item.name;
                }
            }
        }
    }
</script>
