<template>
    <div class="d-flex flex-column">
        <h3>
            {{ $t('main.entity.title') }}
            <small class="badge badge-secondary font-weight-light align-middle font-size-50">
                {{ $tc('main.entity.count', topLevelCount, {cnt: topLevelCount}) }}
            </small>
        </h3>
        <tree-search
            class="mb-2"
            :on-select="selectionCallback"
            :on-multiselect="onSearchMultiSelect"
            :on-clear="resetHighlighting">
        </tree-search>
        <div class="d-flex flex-column col px-0">
            <button type="button" class="btn btn-sm btn-outline-success mb-2" @click="onEntityAdd(onAdd)">
                <i class="fas fa-fw fa-plus"></i> {{ $t('main.entity.tree.add') }}
            </button>
            <tree
                class="col px-0 scroll-y-auto"
                :data="tree"
                :draggable="true"
                :drop-allowed="isDropAllowed"
                size="small"
                @change="itemClick"
                @drop="itemDrop"
                @toggle="itemToggle">
            </tree>
            <button type="button" class="btn btn-sm btn-outline-success mb-2" @click="onEntityAdd(onAdd)">
                <i class="fas fa-fw fa-plus"></i> {{ $t('main.entity.tree.add') }}
            </button>
        </div>
    </div>
</template>

<script>
    import * as treeUtility from 'tree-vue-component';
    import { VueContext } from 'vue-context';
    import { transliterate as tr, slugify } from 'transliteration';
    import { bus } from './MainView.vue';
    Vue.component('tree-node', require('./TreeNode.vue'));
    Vue.component('tree-contextmenu', require('./TreeContextmenu.vue'));
    Vue.component('tree-search', require('./TreeSearch.vue'));

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
            this.dragDelay = vm.dragDelay;
            this.onToggle = vm.itemToggle;
            this.contextmenu = 'tree-contextmenu';
            this.onContextMenuAdd = function(parent) {
                vm.onEntityAdd(vm.onAdd, parent);
            };
            this.onContextMenuDuplicate = function(entity, path) {
                let parent;
                if (path.length > 1) {
                    parent = treeUtility.getNodeFromPath(vm.tree, path.slice(0, path.length - 1));
                }
                vm.onContextMenuDuplicate(vm.onAdd, entity, parent);
            };
            this.onContextMenuDelete = function(entity, path) {
                vm.onContextMenuDelete(vm.onDelete, entity, path);
            };
        }
    }

    export default {
        components: {
            VueContext,
        },
        props: {
            onEntityAdd: {
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
            },
            eventBus: {
                required: true,
                type: Object
            }
        },
        mounted() {
            this.init();
            this.eventBus.$on('entity-change', this.handleEntityChange);
            this.eventBus.$on('entity-delete', this.handleEntityDelete);
        },
        methods: {
            itemClick(eventData) {
                const item = eventData.data;
                if(this.selectedItem.id == item.id) {
                    this.selectionCallback();
                } else {
                    this.selectionCallback(item.id);
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
                        siblingPromise = this.fetchChildren(targetElement.id);
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

                this.$http.patch(`/context/${draggedElement.id}/rank`, data).then(function(response) {
                    const oldIndex = oldSiblings.indexOf(draggedElement);
                    oldSiblings.splice(oldIndex, 1);
                    siblingPromise.then(newSiblings => {
                        newSiblings.splice(newRank - 1, 0, draggedElement);
                        if(newParent) {
                            newParent.children_count++;
                            newParent.state.openable = true;
                        }
                    });
                    if(oldParent) {
                        oldParent.children_count--;
                        oldParent.state.openable = oldParent.children_count > 0;
                    }
                });
            },
            fetchChildren(id) {
                const vm = this;
                return $http.get('/context/byParent/'+id)
                .then(response => {
                    return response.data.map(n => new Node(n, vm));
                });
            },
            onAdd(entity, parent) {
                const node = new Node(entity, this);
                let siblings;
                if(parent) {
                    siblings = parent.children;
                    parent.children_count++;
                    parent.state.openable = true;
                } else {
                    siblings = this.tree;
                }
                siblings.splice(entity.rank, 0, node);
            },
            onDelete(entity) {
                const index = entity.path.pop();
                if (entity.path.length > 0) {
                    let parent = treeUtility.getNodeFromPath(this.tree, entity.path);
                    parent.children.splice(index, 1);
                    parent.children_count--;
                    parent.state.openable = parent.children_count > 0;
                } else {
                    this.tree.splice(index, 1);
                }
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
            },
            onSearchMultiSelect(items) {
                this.resetHighlighting();
                this.highlightItems(items);
            },
            onSearchClear() {
                this.resetHighlighting();
            },
            highlightItems(items) {
                let p = new Promise((resolve, reject) => resolve(0));
                items.forEach(i => {
                    p = p.then(_ => {
                        return this.openPath(i.path).then(targetNode => {
                            targetNode.state.highlighted = true;
                            this.highlightedItems.push(targetNode);
                        });
                    });
                });
            },
            resetHighlighting() {
                this.highlightedItems.forEach(i => i.state.highlighted = false);
                this.highlightedItems = [];
            },
            openPath(path) {
                const vm = this;
                let openRecursive = (path, tree) => {
                    const idx = path[0];
                    return new Promise((resolve, reject) => {
                        if (path.length <= 1) {
                            // terminate recursion
                            resolve(tree[idx]);
                        }
                        else {
                            // recurse further
                            const rest = path.slice(1);
                            const curNode = tree[idx];
                            if(curNode.children.length < curNode.children_count) {
                                //async load children
                                curNode.state.loading = true;
                                resolve(
                                    vm.fetchChildren(curNode.id).then(children => {
                                        curNode.children = children;
                                        curNode.state.loading = false;
                                        curNode.childrenLoaded = true;
                                        curNode.state.opened = true;
                                        return openRecursive(rest, children).then(targetNode => {
                                            return targetNode;
                                        });
                                    })
                                );
                            } else {
                                if(!curNode.state.opened) {
                                    // open node
                                    curNode.state.opened = true;
                                }
                                resolve(
                                    openRecursive(rest, curNode.children).then(targetNode => targetNode)
                                );
                            }
                        }
                    });
                };
                return openRecursive(path, this.tree);
            },
            selectNodeById(id) {
                const vm = this;
                $http.get(`/context/${id}/path`).then(response => {
                    const path = response.data;
                    vm.openPath(path).then(targetNode => {
                        targetNode.state.selected = true;
                        vm.selectedItem = targetNode;
                        vm.selectedItem.path = path;
                    });
                });
            },
            deselectNode() {
                this.selectedItem.state.selected = false;
                this.selectedItem = {};
            },
            handleEntityChange(e) {
                const vm = this;
                const from = e.from;
                const to = e.to;
                switch (e.type) {
                    case 'enter':
                        vm.selectNodeById(to.params.id);
                    break;
                    case 'update':
                        vm.deselectNode();
                        vm.selectNodeById(to.params.id);
                    break;
                    case 'leave':
                        vm.deselectNode();
                    break;
                    default:
                        vm.$throwError({message: `Unknown event type ${e.type} received.`});
                }
            },
            handleEntityDelete(e) {
                this.onDelete(e.entity)
            }
        },
        data() {
            return {
                tree: [],
                selectedItem: {},
                highlightedItems: [],
            }
        },
        computed: {
            topLevelCount: function() {
                return this.tree.length || 0;
            },
        }
    }
</script>
