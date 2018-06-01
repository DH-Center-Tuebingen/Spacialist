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

        <vue-context ref="contextMenu" class="context-menu-wrapper">
            <ul class="list-group list-group-vue-context" slot-scope="itemScope" v-if="itemScope.data">
                <li class="list-group-item list-group-item-vue-context disabled">
                    {{ itemScope.data.item.name }}
                </li>
                <li class="list-group-item list-group-item-vue-context" @click="onContextMenuAdd(itemScope.data.item)">
                    <i class="fas fa-fw fa-plus text-success"></i> Add new Sub-Entity
                </li>
                <li class="list-group-item list-group-item-vue-context" @click="onContextMenuDuplicate(itemScope.data.item)">
                    <i class="fas fa-fw fa-copy text-info"></i> Duplicate <i>{{ itemScope.data.item.name }}</i>
                </li>
                <li class="list-group-item list-group-item-vue-context" @click="onContextMenuDelete(itemScope.data.item)">
                    <i class="fas fa-fw fa-trash text-danger"></i> Delete <i>{{ itemScope.data.item.name }}</i>
                </li>
            </ul>
        </vue-context>
    </div>
</template>

<script>
    import { TreeView } from '@bosket/vue';
    import { VueContext } from 'vue-context';
    import { transliterate as tr, slugify } from 'transliteration';

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
            'tree-view': TreeView,
            VueContext
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
            },
            openContextMenu(e, item) {
                this.$refs.contextMenu.open(e, { item: item });
                e.preventDefault();
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
                search: input => {
                    input = tr(input);
                    return item => tr(item.name).match(new RegExp(`.*${ input }.*`, "gi"))
                },
                display: (item, inputs) =>
                    <span onContextmenu={($event) => this.openContextMenu($event, item)}>
                        {item.name}
                        <span class="pl-2 font-italic mb-0">
                            {this.concepts[this.contextTypes[item.context_type_id].thesaurus_url].label}
                        </span>
                        <span class="pl-2">
                            {item.children_count > 0 ? `(${item.children_count})` : ""}
                        </span>
                    </span>
            }
        }
    }
</script>
