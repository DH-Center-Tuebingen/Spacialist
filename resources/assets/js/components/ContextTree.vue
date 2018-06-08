<template>
    <div class="container">
        <tree-view
            :category="category"
            :css="css"
            :display="display"
            :dragndrop="dragndrop"
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
    import { dragndrop } from "@bosket/core";
    import { array } from '@bosket/tools';
    import { VueContext } from 'vue-context';
    import { transliterate as tr, slugify } from 'transliteration';

    class Node {
        constructor(data) {
            Object.assign(this, data);
            if(this.children_count > 0) {
                this.children = () => this.fetchChildren(this.id);
            }
        }

        fetchChildren(id) {
            return $http.get('/api/context/byParent/'+id)
            .then(response => {
                const result = response.data.map(n => new Node({...n, parent: this}));
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
            this.init();
        },
        methods: {
            init() {
                this.tree = this.roots.map(n => new Node(n));
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
                dragndrop: {
                    ...dragndrop.selection(() => this.tree, m => this.tree = m),
                    over: (target, event, inputs) => {
                        // TODO open on hover
                    },
                    // Returns true if element is guarded (drop not allowed)
                    guard: (target, event, inputs) => {
                        const vm = this;
                        const dragElem = inputs.selection[0];
                        const dragContextType = vm.contextTypes[dragElem.context_type_id];
                        const dropContextType = vm.contextTypes[target.context_type_id];
                        // If currently dragged element is not allowed as root
                        // and dragged on element is a root element (no parent)
                        // do not allow drop
                        if(!dragContextType.is_root && (!target.parent /*&& folded*/)) {
                            return true;
                        }

                        // Check if currently dragged context type is allowed
                        // as subtype of current drop target
                        const index = dropContextType.sub_context_types.findIndex(ct => ct.id == dragElem.context_type_id);
                        if(index == -1) {
                            return true;
                        }

                        // In any other cases allow drop
                        return false;
                    },
                    drop: (target, event, inputs) => {
                        const vm = this;
                        const dragElem = inputs.selection[0];
                        let children;
                        let parentId;
                        let oldParentId;
                        if(target.children /*&& !folded*/) { // TODO
                            children = target.children;
                            parentId = target.id;
                        } else if(target.parent) {
                            children = target.parent.children;
                            parentId = target.parent.id;
                        } else {
                            children = vm.tree;
                        }
                        if(dragElem.parent) {
                            oldParentId = dragElem.parent.id;
                        }

                        let newIndex = children.indexOf(target) + 1;
                        const oldIndex = dragElem.rank - 1;

                        // Check if element was moved (different parent OR different index)
                        if((!parentId && !oldParentId) || parentId == oldParentId) {
                            if(newIndex == oldIndex) return;
                            if(oldIndex < newIndex) newIndex--;
                        }

                        let rank = newIndex + 1;
                        let data = {
                            rank: rank
                        };
                        if(parentId) {
                            data.parent_id = parentId;
                        }

                        vm.$http.patch(`/api/context/${dragElem.id}/rank`, data).then(function(response) {
                            let oldChildren;
                            if(parentId != oldParentId) {
                                if(oldParentId) {
                                    oldChildren = dragElem.parent.children;
                                } else {
                                    oldChildren = vm.tree;
                                }
                                for(let i=oldIndex; i<oldChildren.length; i++) {
                                    oldChildren[i].rank--;
                                }
                                for(let i=newIndex; i<children.length; i++) {
                                    children[i].rank++;
                                }
                            } else {
                                oldChildren = children;
                                if(newIndex < oldIndex) {
                                    for(let i=newIndex; i<oldIndex; i++) {
                                        children[i].rank++;
                                    }
                                } else {
                                    for(let i=oldIndex; i<newIndex; i++) {
                                        children[i].rank--;
                                    }
                                }
                            }

                            const index = oldChildren.findIndex(c => c.id == dragElem.id);
                            if(index > -1) {
                                oldChildren.splice(index, 1);
                            }
                            dragElem.rank = newIndex + 1;
                            children.splice(newIndex, 0, dragElem);
                        });
                    }
                },
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
                        <span>{item.name}</span>
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
