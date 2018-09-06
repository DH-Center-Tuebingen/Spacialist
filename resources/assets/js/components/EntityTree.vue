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
            <div class="mb-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setSort('rank', 'asc')">
                    <i class="fas fa-fw fa-sort-numeric-down"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setSort('rank', 'desc')">
                    <i class="fas fa-fw fa-sort-numeric-up"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setSort('alpha', 'asc')">
                    <i class="fas fa-fw fa-sort-alpha-down"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setSort('alpha', 'desc')">
                    <i class="fas fa-fw fa-sort-alpha-up"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setSort('children', 'asc')">
                    <i class="fas fa-fw fa-sort-amount-down"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setSort('children', 'desc')">
                    <i class="fas fa-fw fa-sort-amount-up"></i>
                </button>
            </div>
            <tree
                class="col px-0 scroll-y-auto"
                :data="tree"
                :draggable="isDragAllowed"
                :drop-allowed="isDropAllowed"
                size="small"
                @change="itemClick"
                @drop="itemDrop"
                @toggle="itemToggle">
            </tree>
            <button type="button" class="btn btn-sm btn-outline-success mt-2" @click="onEntityAdd(onAdd)">
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
            this.dragAllowed = _ => vm.isDragAllowed;
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
            setSort(by, dir) {
                this.sort.by = by;
                this.sort.dir = dir;
                this.sortTree(by, dir, this.tree);
            },
            sortTree(by, dir = 'asc', tree = this.tree) {
                if(dir != 'asc' && dir != 'desc') {
                    return;
                }
                let sortFn;
                switch(by) {
                    case 'rank':
                        sortFn = (a, b) => {
                            let value = a.rank - b.rank;
                            if(dir == 'desc') {
                                value = -value;
                            }
                            return value;
                        };
                        break;
                    case 'alpha':
                        sortFn = (a, b) => {
                            let value = 0;
                            if(a.name < b.name) value = -1;
                            if(a.name > b.name) value = 1;
                            if(dir == 'desc') {
                                value = -value;
                            }
                            return value;
                        };
                        break;
                    case 'children':
                        sortFn = (a, b) => {
                            let value = a.children_count - b.children_count;
                            if(dir == 'desc') {
                                value = -value;
                            }
                            return value;
                        };
                        break;
                }
                this.sortTreeLevel(tree, sortFn);
            },
            sortTreeLevel(nodes, sortFn) {
                if(!nodes) return;
                nodes.sort(sortFn);
                nodes.forEach(n => {
                    if(n.childrenLoaded) {
                        this.sortTreeLevel(n.children, sortFn);
                    }
                });
            },
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
                        this.sortTree(this.sort.by, this.sort.dir, item.children);
                    });
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

                if(!this.isDragAllowed || !this.isDropAllowed(dropData)) {
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

                this.$http.patch(`/entity/${draggedElement.id}/rank`, data).then(function(response) {
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
                return $http.get('/entity/byParent/'+id)
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
                this.sortTree(this.sort.by, this.sort.dir, this.tree);
            },
            isDropAllowed(dropData) {
                //TODO check if it works with tree-vue-component
                const item = dropData.sourceData;
                const target = dropData.targetData;
                const vm = this;
                const dragEntityType = vm.$getEntityType(item.entity_type_id);
                let dropEntityType;
                if(dropData.targetPath.length == 1) {
                    dropEntityType = {
                        sub_entity_types: Object.values(vm.$getEntityTypes()).filter(f => f.is_root)
                    }
                } else {
                    dropEntityType = vm.$getEntityType(target.entity_type_id);
                }
                // If currently dragged element is not allowed as root
                // and dragged on element is a root element (no parent)
                // do not allow drop
                if(!dragEntityType.is_root && dropData.targetPath.length == 1) {
                    return false;
                }

                // Check if currently dragged entity type is allowed
                // as subtype of current drop target
                const index = dropEntityType.sub_entity_types.findIndex(ct => ct.id == dragEntityType.id);
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
                $http.get(`/entity/${id}/path`).then(response => {
                    const path = response.data;
                    this.openPath(path).then(targetNode => {
                        targetNode.state.selected = true;
                        this.selectedItem = targetNode;
                        this.selectedItem.path = path;
                        // Scroll tree to selected element
                        const elem = document.getElementById(`tree-node-${targetNode.id}`);
                        elem.scrollIntoView();
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
                sort: {
                    by: 'rank',
                    dir: 'asc'
                }
            }
        },
        computed: {
            topLevelCount: function() {
                return this.tree.length || 0;
            },
            isDragAllowed: function() {
                return this.sort.by == 'rank' && this.sort.dir == 'asc';
            }
        }
    }
</script>
