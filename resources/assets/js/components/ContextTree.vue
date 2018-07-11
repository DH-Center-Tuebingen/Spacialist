<template>
    <div class="container">
        <tree
            :data="tree"
            :draggable="true"
            @change="itemClick"
            @drop="itemDrop"
            @toggle="itemToggle">
        </tree>
    </div>
</template>

<script>
    import * as treeUtility from 'tree-vue-component';
    import { VueContext } from 'vue-context';
    import { transliterate as tr, slugify } from 'transliteration';
    Vue.component('tree-node', require('./TreeNode.vue'));
    Vue.component('tree-contextmenu', require('./TreeContextmenu.vue'));

    class Node {
        constructor(data, vm) {
            Object.assign(this, data);
            this.state = {
                opened: false,
                selected: false,
                disabled: false,
                loading: false,
                highlighted: false,
                openable: this.children_count > 0,
                dropPosition: 0, //TODO set to DropPosition.empty once exported by tree-vue-component
                dropAllowed: true,
            };
            this.icon = false;
            this.children = [];
            this.childrenLoaded = this.children.length < this.children_count;
            this.component = 'tree-node';
            this.contextmenu = 'tree-contextmenu';
            this.onContextMenuAdd = vm.onContextMenuAdd;
            this.onContextMenuDuplicate = vm.onContextMenuDuplicate;
            this.onContextMenuDelete = vm.onContextMenuDelete;
        }
    }

    export default {
        components: {
            VueContext,
        },
        props: {
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
            },
            dragDelay: {
                required: false,
                type: Number,
                default: 500
            }
        },
        mounted() {
            this.init();
        },
        methods: {
            itemClick(eventData) {
                const item = eventData.data;
                if(this.selectedItem.id == item.id) {
                    this.selectedItem = {};
                    item.state.selected = false;
                    this.selectionCallback();
                } else {
                    if(this.selectedItem.path) {
                        let oldItem = treeUtility.getNodeFromPath(this.tree, this.selectedItem.path);
                        if(oldItem) {
                            oldItem.state.selected = false;
                        }
                    }
                    item.state.selected = true;
                    this.selectedItem = item;
                    this.selectedItem.path = eventData.path;
                    this.selectionCallback(item);
                }
            },
            itemToggle(eventData) {
                const item = eventData.data;
                if(item.children.length < item.children_count) {
                    item.state.loading = true;
                    this.fetchChildren(item.id).then(response => {
                        item.children =  response;
                        item.state.loading = false;
                        item.childrenLoaded = true;
                    })
                }
                item.state.opened = !item.state.opened;
            },
            itemDrop(dropData) {
                //TODO: remove once DropPosition can be imported from tree-vue-component
                const DropPosition = {
                    empty: 0,
                    up: 1,
                    inside: 2,
                    down: 3,
                };

                if(!this.isDropAllowed(dropData)) {
                    return;
                }

                const draggedElement = dropData.sourceData;
                const targetElement = dropData.targetData;
                let oldParent = treeUtility.getNodeFromPath(this.tree, dropData.sourcePath.slice(0, dropData.sourcePath.length - 1));
                let oldSiblings = oldParent ? oldParent.children : this.tree;
                let newParent;
                let siblingPromise;
                let newRank;
                if(targetElement.state.dropPosition == DropPosition.inside) {
                    if(!targetElement.childrenLoaded) {
                        siblingPromise = fetchChildren(targetElement.id);
                    }
                    newRank = targetElement.children_count + 1;
                    newParent = targetElement;
                } else {
                    newParent = treeUtility.getNodeFromPath(this.tree, dropData.targetPath.slice(0, dropData.targetPath.length - 1));
                    siblingPromise = new Promise((resolve, reject) => resolve(newParent ? newParent.children : this.tree));
                    const targetRank = dropData.targetPath[dropData.targetPath.length - 1] + 1;
                    if(targetElement.state.dropPosition == DropPosition.up) {
                        newRank = targetRank;
                    } else if(targetElement.state.dropPosition == DropPosition.down) {
                        newRank = targetRank + 1;
                    } else {
                        this.$throwError({message: `DropPosition ${targetElement.state.dropPosition} does not correspond to a valid choice`});
                        return;
                    }
                }

                let data = {
                    rank: newRank,
                    parent_id: newParent ? newParent.id : null
                };

                this.$http.patch(`/api/context/${draggedElement.id}/rank`, data).then(function(response) {
                    const oldIndex = oldSiblings.indexOf(draggedElement);
                    oldSiblings.splice(oldIndex, 1);
                    siblingPromise.then(newSiblings => {
                        newSiblings.splice(newRank - 1, 0, draggedElement)
                    });
                    if(oldParent) {
                        oldParent.children_count--;
                    }
                    if(newParent) {
                        newParent.children_count++;
                    }
                }).catch(error => this.$throwError(error));
            },
            fetchChildren(id) {
                const vm = this;
                return $http.get('/api/context/byParent/'+id)
                .then(response => {
                    return response.data.map(n => new Node(n, vm));
                }).catch(error => this.$throwError(error));
            },
            init() {
                this.roots.forEach(n => this.tree.push(new Node(n, this)))
            },
            isDropAllowed(dropData) {
                //TODO check if it works with tree-vue-component
                const item = dropData.sourceData;
                const target = dropData.targetData;
                const vm = this;
                const dragContextType = vm.$getEntityType(item.context_type_id);
                let dropContextType;
                if(dropData.targetPath.length == 1) {
                    dropContextType = {
                        sub_context_types: Object.values(vm.$getEntityTypes()).filter(f => f.is_root)
                    }
                } else {
                    dropContextType = vm.$getEntityType(target.context_type_id);
                }
                // If currently dragged element is not allowed as root
                // and dragged on element is a root element (no parent)
                // do not allow drop
                if(!dragContextType.is_root && dropData.targetPath.length == 1) {
                    return false;
                }

                // Check if currently dragged context type is allowed
                // as subtype of current drop target
                const index = dropContextType.sub_context_types.findIndex(ct => ct.id == dragContextType.id);
                if(index == -1) {
                    return false;
                }
                // In any other cases allow drop
                return true;
            }
        },
        data() {
            return {
                tree: [],
                selectedItem: {},
            }
        }
    }
</script>
