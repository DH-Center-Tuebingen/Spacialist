<template>
    <div class="container">
        <tree-view
            :category="category"
            :css="css"
            :display="display"
            :model="roots"
            :onSelect="onSelect"
            :openerOpts="openerOpts"
            :search="search"
            :selection="selection"
            :strategies="strategies">
        </tree-view>
    </div>
</template>

<script>
    import { TreeView } from '@bosket/vue';
    import * as VueMenu from '@hscmap/vue-menu';

    Vue.use(VueMenu);

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
            onContextMenuAdd: {
                required: false,
                type: Function
            },
            onContextMenuDuplicate: {
                required: false,
                type: Function
            },
            onContextMenuDelete: {
                required: false,
                type: Function
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
                    opener: 'opener mr-2 text-info',
                    search: 'form-control'
                },
                category: "children",
                search: input => item => item.name.match(new RegExp(`.*${ input }.*`, "gi")),
                display: (item, inputs) =>
                    <span>
                        <hsc-menu-style-white class="d-inline-block">
                            <hsc-menu-context-menu class="d-inline-block">
                                {item.name}
                                <i class="pl-2">{this.concepts[this.contextTypes[item.context_type_id].thesaurus_url].label}</i>
                                <template slot="contextmenu">
                                    <hsc-menu-item disabled>
                                        <div slot="body">
                                            <a href="#" class="dropdown-item">
                                                {item.name}
                                            </a>
                                        </div>
                                    </hsc-menu-item>
                                    <hsc-menu-separator />
                                    <hsc-menu-item>
                                        <div slot="body">
                                            <a href="#" class="dropdown-item" onClick={() => this.onContextMenuAdd(item)}>
                                                <i class="fas fa-fw fa-plus text-success"></i> Add new Sub-Entity
                                            </a>
                                        </div>
                                    </hsc-menu-item>
                                    <hsc-menu-item>
                                        <div slot="body">
                                            <a href="#" class="dropdown-item" onClick={() => this.onContextMenuDuplicate(item)}>
                                                <i class="fas fa-fw fa-copy text-info"></i> Duplicate <i>{item.name}</i>
                                            </a>
                                        </div>
                                    </hsc-menu-item>
                                    <hsc-menu-item>
                                        <div slot="body">
                                            <a href="#" class="dropdown-item" onClick={() => this.onContextMenuDelete(item)}>
                                                <i class="fas fa-fw fa-trash text-danger"></i> Delete <i>{item.name}</i>
                                            </a>
                                        </div>
                                    </hsc-menu-item>
                                </template>
                            </hsc-menu-context-menu>
                        </hsc-menu-style-white>
                    </span>
            }
        }
    }
</script>
