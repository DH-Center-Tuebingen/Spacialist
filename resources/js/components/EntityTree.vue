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
            :on-multiselect="onSearchMultiSelect"
            :on-clear="resetHighlighting">
        </tree-search>
        <div class="d-flex flex-column col px-0">
            <button type="button" class="btn btn-sm btn-outline-success mb-2" @click="requestAddNewEntity()">
                <i class="fas fa-fw fa-plus"></i> {{ $t('main.entity.tree.add') }}
            </button>
            <div class="mb-2 d-flex flex-row justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setSort('rank', 'asc')" data-toggle="popover" :data-content="$t('main.entity.tree.sorts.asc.rank')" data-trigger="hover" data-placement="bottom">
                    <i class="fas fa-fw fa-sort-numeric-down"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setSort('rank', 'desc')" data-toggle="popover" :data-content="$t('main.entity.tree.sorts.desc.rank')" data-trigger="hover" data-placement="bottom">
                    <i class="fas fa-fw fa-sort-numeric-up"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setSort('alpha', 'asc')" data-toggle="popover" :data-content="$t('main.entity.tree.sorts.asc.name')" data-trigger="hover" data-placement="bottom">
                    <i class="fas fa-fw fa-sort-alpha-down"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setSort('alpha', 'desc')" data-toggle="popover" :data-content="$t('main.entity.tree.sorts.desc.name')" data-trigger="hover" data-placement="bottom">
                    <i class="fas fa-fw fa-sort-alpha-up"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setSort('children', 'asc')" data-toggle="popover" :data-content="$t('main.entity.tree.sorts.asc.children')" data-trigger="hover" data-placement="bottom">
                    <i class="fas fa-fw fa-sort-amount-down"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setSort('children', 'desc')" data-toggle="popover" :data-content="$t('main.entity.tree.sorts.desc.children')" data-trigger="hover" data-placement="bottom">
                    <i class="fas fa-fw fa-sort-amount-up"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setSort('type', 'asc')" data-toggle="popover" :data-content="$t('main.entity.tree.sorts.asc.type')" data-trigger="hover" data-placement="bottom">
                    <span class="fa-stack d-inline">
                        <i class="fas fa-long-arrow-alt-down"></i>
                        <i class="fas fa-monument" data-fa-transform="left-4" style="margin-right: -0.2rem;"></i>
                    </span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setSort('type', 'desc')" data-toggle="popover" :data-content="$t('main.entity.tree.sorts.desc.type')" data-trigger="hover" data-placement="bottom">
                    <span class="fa-stack d-inline">
                        <i class="fas fa-long-arrow-alt-up"></i>
                        <i class="fas fa-monument" data-fa-transform="left-4" style="margin-right: -0.2rem;"></i>
                    </span>
                </button>
            </div>
            <tree
                id="entity-tree"
                class="col px-0 scroll-y-auto"
                :data="tree"
                :draggable="isDragAllowed"
                :drop-allowed="isDropAllowed"
                size="small"
                @change="itemClick"
                @drop="itemDrop"
                @toggle="itemToggle">
            </tree>
            <button type="button" class="btn btn-sm btn-outline-success mt-2" @click="requestAddNewEntity()">
                <i class="fas fa-fw fa-plus"></i> {{ $t('main.entity.tree.add') }}
            </button>
        </div>
        <modals-container class="visible-overflow" />
    </div>
</template>

<script>
    import TreeNode from './TreeNode.vue';
    import TreeContextmenu from './TreeContextmenu.vue';
    import TreeSearch from './TreeSearch.vue';

    import * as treeUtility from 'tree-vue-component';
    import { VueContext } from 'vue-context';
    import { transliterate as tr, slugify } from 'transliteration';
    import AddNewEntityModal from './modals/AddNewEntity.vue';
    import DeleteEntityModal from './modals/DeleteEntity.vue';

    const DropPosition = {
        empty: 0,
        up: 1,
        inside: 2,
        down: 3,
    };

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
                dropPosition: DropPosition.empty,
                dropAllowed: true,
            };
            this.icon = false;
            this.children = [];
            this.childrenLoaded = this.children.length == this.children_count;
            this.component = TreeNode;
            this.dragDelay = vm.dragDelay;
            this.dragAllowed = _ => vm.isDragAllowed;
            this.onToggle = vm.itemToggle;
            this.contextmenu = TreeContextmenu;
            this.onContextMenuAdd = function(parent) {
                vm.requestAddNewEntity(parent);
            };
            this.onContextMenuDuplicate = function(entity, path) {
                const parent = entity.root_entity_id ? vm.entities[entity.root_entity_id] : null;
                vm.onAdd(entity, parent);
            };
            this.onContextMenuDelete = function(entity, path) {
                vm.eventBus.$emit('entity-delete', {
                    entity: entity
                });
            };
        }
    }

    export default {
        name: 'EntityTree',
        components: {
            'tree-node': TreeNode,
            'tree-contextmenu': TreeContextmenu,
            'tree-search': TreeSearch,
            VueContext
        },
        props: {
            selectedEntity: {
                required: false,
                type: Object
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
        beforeMount() {
            // Enable popovers
            $(function () {
                $('[data-toggle="popover"]').popover()
            });
        },
        mounted() {
            this.init();
            this.eventBus.$on('entity-update', this.handleEntityUpdate);
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
                    case 'type':
                        sortFn = (a, b) => {
                            const aurl = this.$translateEntityType(a.entity_type_id);
                            const burl = this.$translateEntityType(b.entity_type_id);
                            let value = 0;
                            if(aurl < burl) value = -1;
                            if(aurl > burl) value = 1;
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
                if(this.selectedEntity.id == item.id) {
                    this.$router.push({
                        append: true,
                        name: 'home',
                        query: this.$route.query
                    });
                } else {
                    this.$router.push({
                        name: 'entitydetail',
                        params: {
                            id: item.id
                        },
                        query: this.$route.query
                    });
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
                if(!this.isDragAllowed || !this.isDropAllowed(dropData)) {
                    return;
                }

                const vm = this;
                const node = dropData.sourceData;
                const newRank = vm.getNewRank(dropData);
                const oldRank = node.rank;
                let newParent;
                const oldParent = treeUtility.getNodeFromPath(this.tree, dropData.sourcePath.slice(0, dropData.sourcePath.length - 1));
                if(dropData.targetData.state.dropPosition == DropPosition.inside) {
                    newParent = dropData.targetData;
                } else {
                    newParent = treeUtility.getNodeFromPath(this.tree, dropData.targetPath.slice(0, dropData.targetPath.length - 1));
                }

                if (newParent == oldParent && newRank == oldRank) {
                    return;
                }

                let data = {
                    id: node.id,
                    rank: newRank,
                    parent_id: newParent ? newParent.id : null
                };

                $httpQueue.add(() => vm.$http.patch(`/entity/${node.id}/rank`, data).then(function(response) {
                    vm.removeFromTree(node, dropData.sourcePath);
                    node.rank = newRank;
                    vm.insertIntoTree(node, newParent);
                }));
            },
            fetchChildren(id) {
                return $httpQueue.add(() => $http.get(`/entity/byParent/${id}`)
                .then(response => {
                    const newNodes = response.data.map(e => {
                        const n = new Node(e, this);
                        this.entities[n.id] = n;
                        return n;
                    });
                    return newNodes;
                }));
            },
            getNewRank(dropData) {
                let newRank;
                if(dropData.targetData.state.dropPosition == DropPosition.inside) {
                    newRank = dropData.targetData.children_count + 1;
                } else {
                    const newParent = treeUtility.getNodeFromPath(this.tree, dropData.targetPath.slice(0, dropData.targetPath.length - 1));
                    const oldParent = treeUtility.getNodeFromPath(this.tree, dropData.sourcePath.slice(0, dropData.sourcePath.length - 1));
                    const children_count = newParent ? newParent.children_count : this.tree.length;
                    const oldRank = dropData.sourceData.rank;

                    if(this.sort.by == 'rank') {
                        if(dropData.targetData.state.dropPosition == DropPosition.up) {
                            if(this.sort.dir == 'asc') {
                                newRank = dropData.targetData.rank;
                            } else {
                                newRank = dropData.targetData.rank + 1;
                            }
                        } else if(dropData.targetData.state.dropPosition == DropPosition.down) {
                            if(this.sort.dir == 'asc') {
                                newRank = dropData.targetData.rank + 1;
                            } else {
                                newRank = dropData.targetData.rank;
                            }
                        }
                        if(newParent == oldParent && newRank > oldRank) {
                            newRank--;
                        }
                    } else {
                        newRank = newParent.children_count + 1;
                    }
                }
                return newRank
            },
            onAdd(entity, parent) {
                const vm = this;
                if(!vm.$can('create_concepts')) return;
                let data = {};
                data.name = entity.name;
                data.entity_type_id = entity.entity_type_id;
                if(entity.root_entity_id) data.root_entity_id = entity.root_entity_id;
                if(entity.geodata_id) entity.geodata_id = entity.geodata_id;

                $httpQueue.add(() => vm.$http.post('/entity', data).then(function(response) {
                    vm.insertIntoTree(response.data, parent);
                }));
            },
            insertIntoTree(entity, parent) {
                const vm = this;
                const node = new Node(entity, vm);
                if (parent && !parent.childrenLoaded) {
                    parent.children_count++;
                    return;
                }
                let siblings = parent ? parent.children : vm.tree;
                const isAsc = vm.sort.dir == 'asc';
                siblings.map(s => {
                    if(s.rank >= node.rank) {
                        s.rank++;
                    }
                });
                let insertIndex;
                if(vm.sort.by == 'rank') {
                    if(isAsc) {
                        insertIndex = node.rank - 1;
                    } else {
                        insertIndex = siblings.length - (node.rank - 1);
                    }
                } else {
                    let sortField;
                    switch(vm.sort.by) {
                        case 'alpha':
                        sortField = 'name';
                        break;
                        case 'children':
                        sortField = 'children_count';
                        break;
                        default:
                        vm.$throwError({message: `Sort key unknown.`});
                    }
                    insertIndex = siblings.length;
                    for(let i = 0; i < siblings.length; i++) {
                        if((siblings[i][sortField] < node[sortField]) != isAsc) {
                            insertIndex = i;
                            break;
                        }
                    }
                }
                siblings.splice(insertIndex, 0, node);
                if(parent) parent.children_count++;
                vm.entities[node.id] = node;
            },
            requestAddNewEntity(parent) {
                const vm = this;
                if(!vm.$can('create_concepts')) return;

                let selection = [];
                if(parent) {
                    selection = vm.$getEntityType(parent.entity_type_id).sub_entity_types;
                } else {
                    selection = Object.values(vm.$getEntityTypes()).filter(f => f.is_root);
                }
                let entity = {
                    name: '',
                    entity_type_id: selection.length == 1 ? selection[0].id : null,
                    selection: selection,
                    root_entity_id: parent ? parent.id : null,
                };
                vm.$modal.show(AddNewEntityModal, {
                    newEntity: entity,
                    onSubmit: e => vm.onAdd(e, parent)
                });
            },
            requestDeleteEntity(entity, path) {
                const vm = this;
                if(!vm.$can('delete_move_concepts')) return;
                vm.$modal.show(DeleteEntityModal, {
                    entity: entity,
                    onDelete: e => vm.onDelete(e, path)
                })
            },
            onDelete(entity, path) {
                const vm = this;
                if(!vm.$can('delete_move_concepts')) return;
                const id = entity.id;
                $httpQueue.add(() => $http.delete(`/entity/${id}`).then(response => {
                    // if deleted entity is currently selected entity...
                    if(id == vm.selectedEntity.id) {
                        // ...unset it
                        this.$router.push({
                            append: true,
                            name: 'home',
                            query: vm.$route.query
                        });
                    }
                    vm.$showToast(
                        this.$t('main.entity.toasts.deleted.title'),
                        this.$t('main.entity.toasts.deleted.msg', {
                            name: entity.name
                        }),
                        'success'
                    );
                    vm.removeFromTree(entity, path);
                }));
            },
            removeFromTree(entity, path) {
                const vm = this;
                const index = path.pop();
                const parent = treeUtility.getNodeFromPath(vm.tree, path);
                const siblings = parent ? parent.children : vm.tree;
                siblings.splice(index, 1);
                siblings.map(s => {
                    if(s.rank > entity.rank) {
                        s.rank--;
                    }
                });

                if (parent) {
                    parent.children_count--;
                    parent.state.openable = parent.children_count > 0;
                }
                delete vm.entities[entity.id];
            },
            init() {
                const vm = this
                $httpQueue.add(() => $http.get('/entity/top').then(response => {
                    response.data.forEach(e => {
                        const n = new Node(e, vm);
                        vm.entities[n.id] = n;
                        vm.tree.push(n);
                    });
                    vm.sortTree(vm.sort.by, vm.sort.dir, vm.tree);
                    vm.selectEntity();
                }));
            },
            isDropAllowed(dropData) {
                //TODO check if it works with tree-vue-component
                const item = dropData.sourceData;
                const target = dropData.targetData;
                const vm = this;
                const dragEntityType = vm.$getEntityType(item.entity_type_id);

                if(target.parentIds.indexOf(item.id) != -1 ||
                   (target.state.dropPosition == DropPosition.inside && target.id == item.root_entity_id)) {
                    return false;
                }
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
                items.forEach(i => {
                    return this.openPath(i.parentIds).then(targetNode => {
                        targetNode.state.highlighted = true;
                        this.highlightedItems.push(targetNode);
                    });
                });
            },
            resetHighlighting() {
                this.highlightedItems.forEach(i => i.state.highlighted = false);
                this.highlightedItems = [];
            },
            async openPath(ids, tree=this.tree) {
                const index = ids.pop();
                const elem = this.entities[index];
                if(ids.length == 0) {
                    return elem;
                }
                if(!elem.childrenLoaded) {
                    elem.state.loading = true;
                    const children = await this.fetchChildren(elem.id);
                    elem.state.loading = false;
                    elem.children = children;
                    elem.childrenLoaded = true;
                }
                elem.state.opened = true;
                return this.openPath(ids, elem.children);
            },
            selectEntity() {
                if(!this.selectedEntity.id) return;
                this.openPath(this.selectedEntity.parentIds.slice()).then(targetNode => {
                    targetNode.state.selected = true;
                    // Scroll tree to selected element
                    const elem = document.getElementById(`tree-node-${targetNode.id}`);
                    VueScrollTo.scrollTo(elem, this.scrollTo.duration, this.scrollTo.options);
                });
            },
            deselectNode(id) {
                if(this.entities[id]) {
                    this.entities[id].state.selected = false;
                }
            },
            handleEntityUpdate(e) {
                switch(e.type) {
                    case 'name':
                        this.entities[e.entity_id].name = e.value;
                        break;
                    default:
                        vm.$throwError({message: `Unknown event type ${e.type} received.`});
                }
            },
            handleEntityDelete(e) {
                const id = e.entity.id;
                if(!id) return;
                const path = document.getElementById(`tree-node-${id}`).parentElement.getAttribute('data-path').split(',');
                this.requestDeleteEntity(e.entity, path);
            }
        },
        data() {
            return {
                entities: [],
                tree: [],
                highlightedItems: [],
                sort: {
                    by: 'rank',
                    dir: 'asc'
                },
                scrollTo: {
                    duration: 500,
                    options: {
                        container: '#entity-tree',
                        force: false,
                        cancelable: true,
                        x: false,
                        y: true
                    }
                }
            }
        },
        computed: {
            topLevelCount() {
                return this.tree.length || 0;
            },
            // drag is only allowed, when sorted by rank (asc)
            isDragAllowed() {
                return this.sort.by == 'rank' && this.sort.dir == 'asc';
            }
        },
        watch: {
            'selectedEntity.id': function(newId, oldId) {
                if(oldId) {
                    this.deselectNode(oldId);
                }
                if(newId) {
                    this.selectEntity();
                }
            }
        }
    }
</script>
