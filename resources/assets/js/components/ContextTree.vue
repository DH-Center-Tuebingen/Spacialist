<template>
    <div class="container">
        <tree-view
            :category="category"
            :css="css"
            :display="display"
            :model="tree"
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

    class Node {
        constructor(data) {
            Object.assign(this, data)
            if(this.children_count > 0) {
                this.children = () => this.fetchChildren(this.id)
            }
        }

        fetchChildren(id) {
            return $http.get('/api/context/byParent/'+id)
            .then(response => {
                const result = response.data.map(n => new Node(n));
                return result;
            });
        }
    }

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
        mounted() {
            this.init()
        },
        methods: {
            init() {
                this.tree = this.roots.map(n => new Node(n))
            },
            onSelect(newSelection) {
                this.selection = newSelection
                this.selectionCallback(newSelection[0]);
            }
        },
        data() {
            return {
                tree: [],
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
                                <span class="pl-2 font-italic mb-0">
                                    {this.concepts[this.contextTypes[item.context_type_id].thesaurus_url].label}
                                </span>
                                <span class="pl-2">
                                    {item.children_count > 0 ? `(${item.children_count})` : ""}
                                </span>
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
