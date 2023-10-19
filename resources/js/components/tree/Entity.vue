<template>
    <div class="d-flex flex-column">
        <div class="d-flex flex-row justify-content-between mb-2">
            <h3 class="mb-0">
                {{ t('main.entity.title', 2) }}
                <small class="badge bg-secondary fw-light align-middle font-size-50">
                    {{ t('main.entity.count', {cnt: state.topLevelCount}, state.topLevelCount) }}
                </small>
            </h3>
            <div class="dropdown">
                <span id="tree-options-dropdown" class="clickable text-body align-middle" data-bs-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-fw fa-ellipsis-vertical"></i>
                </span>
                <div class="dropdown-menu" aria-labelledby="tree-options-dropdown">
                    <a class="dropdown-item" href="#" @click.prevent>
                        <i class="fas fa-fw fa-sort"></i>
                        {{ t('global.sort') }}
                        <div class="submenu dropdown-menu">
                            <a class="dropdown-item" href="#" :class="getSortingStateClass('rank', 'asc')" @click.prevent="setSort('rank', 'asc')">
                                <i class="fas fa-fw fa-sort-numeric-down"></i>
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.asc.rank') }}
                                </span>
                            </a>
                            <a class="dropdown-item" href="#" :class="getSortingStateClass('rank', 'desc')" @click.prevent="setSort('rank', 'desc')">
                                <i class="fas fa-fw fa-sort-numeric-up"></i>
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.desc.rank') }}
                                </span>
                            </a>
                            <a class="dropdown-item" href="#" :class="getSortingStateClass('alpha', 'asc')" @click.prevent="setSort('alpha', 'asc')">
                                <i class="fas fa-fw fa-sort-alpha-down"></i>
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.asc.name') }}
                                </span>
                            </a>
                            <a class="dropdown-item" href="#" :class="getSortingStateClass('alpha', 'desc')" @click.prevent="setSort('alpha', 'desc')">
                                <i class="fas fa-fw fa-sort-alpha-up"></i>
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.desc.name') }}
                                </span>
                            </a>
                            <a class="dropdown-item" href="#" :class="getSortingStateClass('children', 'asc')" @click.prevent="setSort('children', 'asc')">
                                <i class="fas fa-fw fa-sort-amount-down"></i>
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.asc.children') }}
                                </span>
                            </a>
                            <a class="dropdown-item" href="#" :class="getSortingStateClass('children', 'desc')" @click.prevent="setSort('children', 'desc')">
                                <i class="fas fa-fw fa-sort-amount-up"></i>
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.desc.children') }}
                                </span>
                            </a>
                            <a class="dropdown-item" href="#" :class="getSortingStateClass('type', 'asc')" @click.prevent="setSort('type', 'asc')">
                                <span class="fa-stack d-inline">
                                    <i class="fas fa-long-arrow-alt-down"></i>
                                    <i class="fas fa-monument" data-fa-transform="start-4"></i>
                                </span>
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.asc.type') }}
                                </span>
                            </a>
                            <a class="dropdown-item" href="#" :class="getSortingStateClass('type', 'desc')" @click.prevent="setSort('type', 'desc')">
                                <span class="fa-stack d-inline">
                                    <i class="fas fa-long-arrow-alt-up"></i>
                                    <i class="fas fa-monument" data-fa-transform="start-4"></i>
                                </span>
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.desc.type') }}
                                </span>
                            </a>
                        </div>
                    </a>
                    <hr class="dropdown-divider">
                    <a class="dropdown-item" href="#" @click.prevent="toggleSelectMode()">
                        <span>
                            <span v-if="state.selectMode">
                                <i class="fas fa-fw fa-list-ol"></i>
                            </span>
                            <span v-else>
                                <i class="fas fa-fw fa-list-check"></i>
                            </span>
                            {{ t('main.entity.tree.multiedit.title') }}
                        </span>
                    </a>
                    <a class="dropdown-item" href="#" @click.prevent="openMultieditModal()" v-show="state.selectMode" :class="{'disabled': !state.canOpenMultiEditModal}">
                        <i class="fas fa-fw fa-edit"></i>
                        <span>
                            {{ t('main.entity.tree.multiedit.open') }}
                        </span>
                    </a>
                </div>
            </div>
        </div>
        <tree-search
            class="mb-2"
            @selected="searchResultSelected"
            :on-multiselect="onSearchMultiSelect"
            :on-clear="resetHighlighting">
        </tree-search>
        <div class="d-flex flex-column col px-0 overflow-hidden">
            <button type="button" class="btn btn-sm btn-outline-success mb-2" @click="openAddEntityDialog()">
                <i class="fas fa-fw fa-plus"></i> {{ t('main.entity.tree.add') }}
            </button>
            <tree
                id="entity-tree"
                class="col px-0 scroll-y-auto"
                :data="state.tree"
                size="small"
                @change="itemClick"
                @toggle="itemToggle">
                <!-- <treenode v-for="(child, i) in state.tree" :key="i" :data="child"></treenode> -->
            </tree>
            <!-- <tree
                id="entity-tree"
                class="col px-0 scroll-y-auto"
                :data="tree"
                :draggable="isDragAllowed"
                :drop-allowed="isDropAllowed"
                size="small"
                @change="itemClick"
                @drop="itemDrop"
                @toggle="itemToggle">
            </tree> -->
            <button type="button" class="btn btn-sm btn-outline-success mt-2" @click="openAddEntityDialog()">
                <i class="fas fa-fw fa-plus"></i> {{ t('main.entity.tree.add') }}
            </button>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        onMounted,
        onUnmounted,
        reactive
    } from 'vue';

    import { useRoute } from 'vue-router';
    import { useI18n } from 'vue-i18n';

    import TreeSearch from '@/components/tree/Search.vue';

    import store from '@/bootstrap/store.js';
    import router from '@/bootstrap/router.js';

    import {
        fetchChildren,
        openPath,
    } from '@/helpers/tree.js';

    import {
        showAddEntity,
        showMultiEditAttribute,
    } from '@/helpers/modal.js';

    export default {
        components: {
            'tree-search': TreeSearch,
        },
        setup(props) {
            const { t } = useI18n();
            const currentRoute = useRoute();

            // FETCH

            // FUNCTIONS
            const itemClick = (item) => {
                // if treeSelectionMode is active, itemClick is (wrongly) triggered, but has no data.
                // Preventing itemClick to trigger would result in not checked checkboxes in node component
                if(!item.data) return;

                if(state.entity.id == item.data.id) {
                    router.push({
                        append: true,
                        name: 'home',
                        query: currentRoute.query
                    });
                } else {
                    router.push({
                        name: 'entitydetail',
                        params: {
                            id: item.data.id
                        },
                        query: currentRoute.query
                    });
                }
            };
            const itemToggle = eventData => {
                const item = eventData.data;
                if(item.children.length < item.children_count) {
                    item.state.loading = true;
                    fetchChildren(item.id, state.sort).then(response => {
                        item.children =  response;
                        item.state.loading = false;
                        item.childrenLoaded = true;
                    });
                }
                item.state.opened = !item.state.opened;
            };
            const toggleSelectMode = _ => {
                store.dispatch('toggleTreeSelectionMode');
            };
            const openMultieditModal = _ => {
                const entityIds = Object.keys(store.getters.treeSelection).map(id => parseInt(id));
                const attributes = store.getters.treeSelectionIntersection;
                showMultiEditAttribute(entityIds, attributes);
            };
            const getSortingStateClass = (attr, dir) => {
                if(state.sort.by == attr && state.sort.dir == dir) {
                    return [
                        'active',
                    ];
                } else {
                    return [];
                }
            };
            const setSort = (attr, dir) => {
                state.sort.by = attr;
                state.sort.dir = dir;
                store.dispatch('sortTree', {
                    by: attr,
                    dir: dir
                });
            };
            const openAddEntityDialog = _ => {
                showAddEntity(null);
            };
            const resetHighlighting = _ => {
                state.highlightedItems.forEach(i => i.state.highlighted = false);
                state.highlightedItems = [];
            };
            const highlightItems = async items => {
                for(let i=0; i<items.length; i++) {
                    await openPath(items[i].parentIds, state.sort).then(targetNode => {
                        targetNode.state.highlighted = true;
                        state.highlightedItems.push(targetNode);
                    });
                }
            };
            const searchResultSelected = item => {
                resetHighlighting();
                if(!item) return;

                if(item.glob) {
                    highlightItems(item.results);
                } else {
                    router.push({
                        name: 'entitydetail',
                        params: {
                            id: item.id
                        },
                        query: currentRoute.query
                    });
                }
            };

            // DATA
            const state = reactive({
                selectMode: computed(_ => store.getters.treeSelectionMode),
                canOpenMultiEditModal: computed(_ => store.getters.treeSelectionCount >= 2),
                highlightedItems: [],
                tree: computed(_ => store.getters.tree),
                entity: computed(_ => store.getters.entity),
                entities: computed(_ => store.getters.entities),
                topLevelCount: computed(_ => state.tree.length || 0),
                sort: {
                    by: 'rank',
                    dir: 'asc'
                },
            });

            // ON MOUNTED
            onMounted(_ => {
                console.log("entity tree component mounted");
                // document.addEventListener('click', check)
            });
            onUnmounted(_ => {

            });
            return {
                t,
                // HELPERS
                // LOCAL
                itemClick,
                itemToggle,
                toggleSelectMode,
                openMultieditModal,
                getSortingStateClass,
                setSort,
                openAddEntityDialog,
                searchResultSelected,
                // STATE
                state,
            };
        }
    }
    // import TreeNode from '@/TreeNode.vue';
    // import TreeContextmenu from '@/TreeContextmenu.vue';
    // import TreeSearch from '@/TreeSearch.vue';

    // import * as treeUtility from 'tree-vue-component';
    // import AddNewEntityModal from '@/helpers/modals/AddNewEntity.vue';
    // import DeleteEntityModal from '@/helpers/modals/DeleteEntity.vue';

    // const DropPosition = {
    //     empty: 0,
    //     up: 1,
    //     inside: 2,
    //     down: 3,
    // };

    // export default {
    //     scrollTo: {
    //         duration: 500,
    //         options: {
    //             container: '#entity-tree',
    //             force: false,
    //             cancelable: true,
    //             x: false,
    //             y: true
    //         }
    //     },
    //     name: 'EntityTree',
    //     components: {
    //         'tree-node': TreeNode,
    //         'tree-contextmenu': TreeContextmenu,
    //         'tree-search': TreeSearch,
    //     },
    //     props: {
    //         dragDelay: {
    //             required: false,
    //             type: Number,
    //             default: 500
    //         }
    //     },
    //     beforeMount() {
    //         // Enable popovers
    //         $(function () {
    //             $('[data-bs-toggle="popover"]').popover()
    //         });
    //     },
    //     mounted() {
    //         this.init();
    //         EventBus.$on('entity-update', this.handleEntityUpdate);
    //         EventBus.$on('entity-delete', this.handleEntityDelete);
    //     },
    //     methods: {
    //         itemClick(eventData) {
    //             const item = eventData.data;
    //             if(this.selectedEntity.id == item.id) {
    //                 this.$router.push({
    //                     append: true,
    //                     name: 'home',
    //                     query: this.$route.query
    //                 });
    //             } else {
    //                 this.$router.push({
    //                     name: 'entitydetail',
    //                     params: {
    //                         id: item.id
    //                     },
    //                     query: this.$route.query
    //                 });
    //             }
    //         },
    //         itemToggle(eventData) {
    //             const item = eventData.data;
    //             if(item.children.length < item.children_count) {
    //                 item.state.loading = true;
    //                 this.fetchChildren(item.id).then(response => {
    //                     item.children =  response;
    //                     item.state.loading = false;
    //                     item.childrenLoaded = true;
    //                     this.sortTree(this.sort.by, this.sort.dir, item.children);
    //                 });
    //             }
    //             item.state.opened = !item.state.opened;
    //         },
    //         itemDrop(dropData) {
    //             if(!this.isDragAllowed || !this.isDropAllowed(dropData)) {
    //                 return;
    //             }

    //             const vm = this;
    //             const node = dropData.sourceData;
    //             const newRank = vm.getNewRank(dropData);
    //             const oldRank = node.rank;
    //             let newParent;
    //             const oldParent = treeUtility.getNodeFromPath(this.tree, dropData.sourcePath.slice(0, dropData.sourcePath.length - 1));
    //             if(dropData.targetData.state.dropPosition == DropPosition.inside) {
    //                 newParent = dropData.targetData;
    //             } else {
    //                 newParent = treeUtility.getNodeFromPath(this.tree, dropData.targetPath.slice(0, dropData.targetPath.length - 1));
    //             }

    //             if (newParent == oldParent && newRank == oldRank) {
    //                 return;
    //             }

    //             let data = {
    //                 id: node.id,
    //                 rank: newRank,
    //                 parent_id: newParent ? newParent.id : null
    //             };

    //             $httpQueue.add(() => vm.$http.patch(`/entity/${node.id}/rank`, data).then(function(response) {
    //                 vm.removeFromTree(node, dropData.sourcePath);
    //                 node.rank = newRank;
    //                 vm.insertIntoTree(node, newParent);
    //             }));
    //         },
    //         fetchChildren(id) {
    //             return $httpQueue.add(() => $http.get(`/entity/byParent/${id}`)
    //             .then(response => {
    //                 const newNodes = response.data.map(e => {
    //                     const n = new Node(e, this);
    //                     this.entities[n.id] = n;
    //                     return n;
    //                 });
    //                 return newNodes;
    //             }));
    //         },
    //         getNewRank(dropData) {
    //             let newRank;
    //             if(dropData.targetData.state.dropPosition == DropPosition.inside) {
    //                 newRank = dropData.targetData.children_count + 1;
    //             } else {
    //                 const newParent = treeUtility.getNodeFromPath(this.tree, dropData.targetPath.slice(0, dropData.targetPath.length - 1));
    //                 const oldParent = treeUtility.getNodeFromPath(this.tree, dropData.sourcePath.slice(0, dropData.sourcePath.length - 1));
    //                 const children_count = newParent ? newParent.children_count : this.tree.length;
    //                 const oldRank = dropData.sourceData.rank;

    //                 if(this.sort.by == 'rank') {
    //                     if(dropData.targetData.state.dropPosition == DropPosition.up) {
    //                         if(this.sort.dir == 'asc') {
    //                             newRank = dropData.targetData.rank;
    //                         } else {
    //                             newRank = dropData.targetData.rank + 1;
    //                         }
    //                     } else if(dropData.targetData.state.dropPosition == DropPosition.down) {
    //                         if(this.sort.dir == 'asc') {
    //                             newRank = dropData.targetData.rank + 1;
    //                         } else {
    //                             newRank = dropData.targetData.rank;
    //                         }
    //                     }
    //                     if(newParent == oldParent && newRank > oldRank) {
    //                         newRank--;
    //                     }
    //                 } else {
    //                     newRank = newParent.children_count + 1;
    //                 }
    //             }
    //             return newRank
    //         },
    //         onAdd(entity, parent) {
    //             const vm = this;
    //             if(!vm.$can('create_concepts')) return;
    //             let data = {};
    //             data.name = entity.name;
    //             data.entity_type_id = entity.entity_type_id;
    //             if(entity.root_entity_id) data.root_entity_id = entity.root_entity_id;
    //             if(entity.geodata_id) entity.geodata_id = entity.geodata_id;

    //             $httpQueue.add(() => vm.$http.post('/entity', data).then(function(response) {
    //                 vm.insertIntoTree(response.data, parent);
    //             }));
    //         },
    //         insertIntoTree(entity, parent) {
    //             const vm = this;
    //             const node = new Node(entity, vm);
    //             if (parent && !parent.childrenLoaded) {
    //                 parent.children_count++;
    //                 return;
    //             }
    //             let siblings = parent ? parent.children : vm.tree;
    //             const isAsc = vm.sort.dir == 'asc';
    //             siblings.map(s => {
    //                 if(s.rank >= node.rank) {
    //                     s.rank++;
    //                 }
    //             });
    //             let insertIndex;
    //             if(vm.sort.by == 'rank') {
    //                 if(isAsc) {
    //                     insertIndex = node.rank - 1;
    //                 } else {
    //                     insertIndex = siblings.length - (node.rank - 1);
    //                 }
    //             } else {
    //                 let sortField;
    //                 switch(vm.sort.by) {
    //                     case 'alpha':
    //                     sortField = 'name';
    //                     break;
    //                     case 'children':
    //                     sortField = 'children_count';
    //                     break;
    //                     default:
    //                     vm.$throwError({message: `Sort key unknown.`});
    //                 }
    //                 insertIndex = siblings.length;
    //                 for(let i = 0; i < siblings.length; i++) {
    //                     if((siblings[i][sortField] < node[sortField]) != isAsc) {
    //                         insertIndex = i;
    //                         break;
    //                     }
    //                 }
    //             }
    //             siblings.splice(insertIndex, 0, node);
    //             if(parent) parent.children_count++;
    //             vm.entities[node.id] = node;
    //         },
    //         requestAddNewEntity(parent) {
    //             const vm = this;
    //             if(!vm.$can('create_concepts')) return;

    //             let selection = [];
    //             if(parent) {
    //                 selection = vm.$getEntityType(parent.entity_type_id).sub_entity_types;
    //             } else {
    //                 selection = Object.values(vm.$getEntityTypes()).filter(f => f.is_root);
    //             }
    //             let entity = {
    //                 name: '',
    //                 entity_type_id: selection.length == 1 ? selection[0].id : null,
    //                 selection: selection,
    //                 root_entity_id: parent ? parent.id : null,
    //             };
    //             vm.$modal.show(AddNewEntityModal, {
    //                 newEntity: entity,
    //                 onSubmit: e => vm.onAdd(e, parent)
    //             });
    //         },
    //         requestDeleteEntity(entity, path) {
    //             const vm = this;
    //             if(!vm.$can('delete_move_concepts')) return;
    //             vm.$modal.show(DeleteEntityModal, {
    //                 entity: entity,
    //                 onDelete: e => vm.onDelete(e, path)
    //             })
    //         },
    //         onDelete(entity, path) {
    //             const vm = this;
    //             if(!vm.$can('delete_move_concepts')) return;
    //             const id = entity.id;
    //             $httpQueue.add(() => $http.delete(`/entity/${id}`).then(response => {
    //                 // if deleted entity is currently selected entity...
    //                 if(id == vm.selectedEntity.id) {
    //                     // ...unset it
    //                     this.$router.push({
    //                         append: true,
    //                         name: 'home',
    //                         query: vm.$route.query
    //                     });
    //                 }
    //                 vm.$showToast(
    //                     this.$t('main.entity.toasts.deleted.title'),
    //                     this.$t('main.entity.toasts.deleted.msg', {
    //                         name: entity.name
    //                     }),
    //                     'success'
    //                 );
    //                 vm.removeFromTree(entity, path);
    //                 EventBus.$emit('entity-deleted', {
    //                     entity: entity
    //                 });
    //             }));
    //         },
    //         removeFromTree(entity, path) {
    //             const vm = this;
    //             const index = path.pop();
    //             const parent = treeUtility.getNodeFromPath(vm.tree, path);
    //             const siblings = parent ? parent.children : vm.tree;
    //             siblings.splice(index, 1);
    //             siblings.map(s => {
    //                 if(s.rank > entity.rank) {
    //                     s.rank--;
    //                 }
    //             });

    //             if (parent) {
    //                 parent.children_count--;
    //                 parent.state.openable = parent.children_count > 0;
    //             }
    //             delete vm.entities[entity.id];
    //         },
    //         isDropAllowed(dropData) {
    //             //TODO check if it works with tree-vue-component
    //             const item = dropData.sourceData;
    //             const target = dropData.targetData;
    //             const dragEntityType = this.$getEntityType(item.entity_type_id);

    //             if(target.parentIds.indexOf(item.id) != -1 ||
    //                (target.state.dropPosition == DropPosition.inside && target.id == item.root_entity_id)) {
    //                 return false;
    //             }

    //             let realTarget;
    //             if(this.droppedToRootLevel(target, dropData.targetPath)) {
    //                 realTarget = null;
    //             } else if(target.state.dropPosition == DropPosition.inside) {
    //                 realTarget = target;
    //             } else {
    //                 realTarget = treeUtility.getNodeFromPath(this.tree, dropData.targetPath.slice(0, -1));
    //             }

    //             // If currently dragged element is not allowed as root
    //             // and dragged on element is a root element (no parent)
    //             // do not allow drop
    //             if(!dragEntityType.is_root && !realTarget) {
    //                 return false;
    //             }

    //             // Check if currently dragged entity type is allowed
    //             // as subtype of current drop target
    //             let index;
    //             if(!realTarget) {
    //                 index = Object.values(this.$getEntityTypes()).findIndex(et => et.is_root && et.id == dragEntityType.id);
    //             } else {
    //                 index = this.$getEntityType(realTarget.entity_type_id).sub_entity_types.findIndex(et => et.id == dragEntityType.id);
    //             }
    //             if(index == -1) {
    //                 return false;
    //             }

    //             // In any other cases allow drop
    //             return true;
    //         },
    //         droppedToRootLevel(tgt, tgtPath) {
    //             return tgt.state.dropPosition != DropPosition.inside && tgtPath.length == 1;
    //         },
    //         onSearchClear() {
    //             this.resetHighlighting();
    //         },
    //         selectEntity() {
    //             if(!this.selectedEntity.id) return;
    //             this.openPath(this.selectedEntity.parentIds.slice()).then(targetNode => {
    //                 targetNode.state.selected = true;
    //                 // Scroll tree to selected element
    //                 const elem = document.getElementById(`tree-node-${targetNode.id}`);
    //                 this.$scrollTo(elem, this.$options.scrollTo.duration, this.$options.scrollTo.options);
    //             });
    //         },
    //         deselectNode(id) {
    //             if(this.entities[id]) {
    //                 this.entities[id].state.selected = false;
    //             }
    //         },
    //         handleEntityUpdate(e) {
    //             switch(e.type) {
    //                 case 'name':
    //                     this.entities[e.entity_id].name = e.value;
    //                     break;
    //                 default:
    //                     vm.$throwError({message: `Unknown event type ${e.type} received.`});
    //             }
    //         },
    //         handleEntityDelete(e) {
    //             const id = e.entity.id;
    //             if(!id) return;
    //             const path = document.getElementById(`tree-node-${id}`).parentElement.getAttribute('data-path').split(',');
    //             this.requestDeleteEntity(e.entity, path);
    //         }
    //     },
    //     data() {
    //         return {
    //             entities: [],
    //             tree: [],
    //             highlightedItems: [],
    //             sort: {
    //                 by: 'rank',
    //                 dir: 'asc'
    //             },
    //         }
    //     },
    //     computed: {
    //         // drag is only allowed, when sorted by rank (asc)
    //         isDragAllowed() {
    //             return this.sort.by == 'rank' && this.sort.dir == 'asc';
    //         }
    //     },
    //     watch: {
    //         'selectedEntity.id': function(newId, oldId) {
    //             if(oldId) {
    //                 this.deselectNode(oldId);
    //             }
    //             if(newId) {
    //                 this.selectEntity();
    //             }
    //         }
    //     }
    // }
</script>
