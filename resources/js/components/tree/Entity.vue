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
                <span
                    id="tree-options-dropdown"
                    class="clickable text-body align-middle"
                    data-bs-toggle="dropdown"
                    role="button"
                    aria-haspopup="true"
                    aria-expanded="false"
                >
                    <i class="fas fa-fw fa-ellipsis-vertical" />
                </span>
                <div
                    class="dropdown-menu"
                    aria-labelledby="tree-options-dropdown"
                >
                    <a
                        href="#"
                        class="dropdown-item"
                        @click.prevent
                    >
                        <i class="fas fa-fw fa-sort" />
                        {{ t('global.sort') }}
                        <div class="submenu dropdown-menu">
                            <a
                                href="#"
                                class="dropdown-item"
                                :class="getSortingStateClass('rank', 'asc')"
                                @click.prevent="setSort('rank', 'asc')"
                            >
                                <i class="fas fa-fw fa-sort-numeric-down" />
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.asc.rank') }}
                                </span>
                            </a>
                            <a
                                href="#"
                                class="dropdown-item"
                                :class="getSortingStateClass('rank', 'desc')"
                                @click.prevent="setSort('rank', 'desc')"
                            >
                                <i class="fas fa-fw fa-sort-numeric-up" />
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.desc.rank') }}
                                </span>
                            </a>
                            <a
                                href="#"
                                class="dropdown-item"
                                :class="getSortingStateClass('alpha', 'asc')"
                                @click.prevent="setSort('alpha', 'asc')"
                            >
                                <i class="fas fa-fw fa-sort-alpha-down" />
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.asc.name') }}
                                </span>
                            </a>
                            <a
                                href="#"
                                class="dropdown-item"
                                :class="getSortingStateClass('alpha', 'desc')"
                                @click.prevent="setSort('alpha', 'desc')"
                            >
                                <i class="fas fa-fw fa-sort-alpha-up" />
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.desc.name') }}
                                </span>
                            </a>
                            <a
                                href="#"
                                class="dropdown-item"
                                :class="getSortingStateClass('children', 'asc')"
                                @click.prevent="setSort('children', 'asc')"
                            >
                                <i class="fas fa-fw fa-sort-amount-down" />
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.asc.children') }}
                                </span>
                            </a>
                            <a
                                href="#"
                                class="dropdown-item"
                                :class="getSortingStateClass('children', 'desc')"
                                @click.prevent="setSort('children', 'desc')"
                            >
                                <i class="fas fa-fw fa-sort-amount-up" />
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.desc.children') }}
                                </span>
                            </a>
                            <a
                                href="#"
                                class="dropdown-item"
                                :class="getSortingStateClass('type', 'asc')"
                                @click.prevent="setSort('type', 'asc')"
                            >
                                <span class="fa-stack d-inline">
                                    <i class="fas fa-long-arrow-alt-down" />
                                    <i
                                        class="fas fa-monument"
                                        data-fa-transform="start-4"
                                    />
                                </span>
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.asc.type') }}
                                </span>
                            </a>
                            <a
                                href="#"
                                class="dropdown-item"
                                :class="getSortingStateClass('type', 'desc')"
                                @click.prevent="setSort('type', 'desc')"
                            >
                                <span class="fa-stack d-inline">
                                    <i class="fas fa-long-arrow-alt-up" />
                                    <i
                                        class="fas fa-monument"
                                        data-fa-transform="start-4"
                                    />
                                </span>
                                <span class="ms-2">
                                    {{ t('main.entity.tree.sorts.desc.type') }}
                                </span>
                            </a>
                        </div>
                    </a>
                    <hr class="dropdown-divider">
                    <a
                        class="dropdown-item"
                        href="#"
                        @click.prevent="toggleSelectMode()"
                    >
                        <span>
                            <span v-if="state.selectMode">
                                <i class="fas fa-fw fa-list-ol" />
                            </span>
                            <span v-else>
                                <i class="fas fa-fw fa-list-check" />
                            </span>
                            {{ t('main.entity.tree.multiedit.title') }}
                        </span>
                    </a>
                    <a
                        v-show="state.selectMode"
                        class="dropdown-item"
                        :class="{'disabled': !state.canOpenMultiEditModal}"
                        href="#"
                        @click.prevent="openMultieditModal()"
                    >
                        <i class="fas fa-fw fa-edit" />
                        <span>
                            {{ t('main.entity.tree.multiedit.open') }}
                        </span>
                    </a>
                </div>
            </div>
        </div>
        <tree-search
            class="mb-2"
            :on-multiselect="onSearchMultiSelect"
            :on-clear="resetHighlighting"
            @selected="searchResultSelected"
        />
        <div class="d-flex flex-column col px-0 overflow-hidden">
            <button
                type="button"
                class="btn btn-sm btn-outline-success mb-2"
                @click="openAddEntityDialog()"
            >
                <i class="fas fa-fw fa-plus" /> {{ t('main.entity.tree.add') }}
            </button>
            <tree
                id="entity-tree"
                class="col px-0 overflow-y-auto"
                :data="entityStore.tree"
                size="small"
                preid=""
                :draggable="state.isDragAllowed"
                :drop-allowed="isDropAllowed"
                @change="itemClick"
                @drop="itemDrop"
                @toggle="itemToggle"
            />
            <button
                type="button"
                class="btn btn-sm btn-outline-success mt-2"
                @click="openAddEntityDialog()"
            >
                <i class="fas fa-fw fa-plus" /> {{ t('main.entity.tree.add') }}
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
    import router from '%router';
    import useEntityStore from '@/bootstrap/stores/entity.js';

    import {
        moveEntity,
    } from '@/api.js';

    import {
        fetchChildren,
        openPath,
    } from '@/helpers/tree.js';

    import {
        getEntityType,
        getEntityTypes,
    } from '@/helpers/helpers.js';

    import {
        showAddEntity,
        showMultiEditAttribute,
    } from '@/helpers/modal.js';

    import * as treeUtility from 'tree-vue-component';

    const DropPosition = {
        empty: 0,
        up: 1,
        inside: 2,
        down: 3,
    };

    export default {
        components: {
            'tree-search': TreeSearch,
        },
        setup(props) {
            const { t } = useI18n();
            const currentRoute = useRoute();
            const entityStore = useEntityStore();

            // FETCH

            // FUNCTIONS
            // Drag & Drop helpers
            const entityTypesAsArray = Object.values(getEntityTypes());
            const droppedToRootLevel = (tgt, tgtPath) => {
                return tgt.state.dropPosition != DropPosition.inside && tgtPath.length == 1;
            };
            const getNewRank = dropData => {
                let newRank;
                if(dropData.targetData.state.dropPosition == DropPosition.inside) {
                    newRank = dropData.targetData.children_count + 1;
                } else {
                    const newParent = treeUtility.getNodeFromPath(state.tree, dropData.targetPath.slice(0, dropData.targetPath.length - 1));
                    const oldParent = treeUtility.getNodeFromPath(state.tree, dropData.sourcePath.slice(0, dropData.sourcePath.length - 1));
                    const oldRank = dropData.sourceData.rank;

                    if(state.sort.by == 'rank') {
                        if(dropData.targetData.state.dropPosition == DropPosition.up) {
                            if(state.sort.dir == 'asc') {
                                newRank = dropData.targetData.rank;
                            } else {
                                newRank = dropData.targetData.rank + 1;
                            }
                        } else if(dropData.targetData.state.dropPosition == DropPosition.down) {
                            if(state.sort.dir == 'asc') {
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
                return newRank;
            };
            const isDropAllowed = dropData => {
                const item = dropData.sourceData;
                const target = dropData.targetData;
                const dragEntityType = getEntityType(item.entity_type_id);

                if(target.parentIds.indexOf(item.id) != -1 ||
                    (target.state.dropPosition == DropPosition.inside && target.id == item.root_entity_id)) {
                    return false;
                }

                let realTarget;
                if(droppedToRootLevel(target, dropData.targetPath)) {
                    realTarget = null;
                } else if(target.state.dropPosition == DropPosition.inside) {
                    realTarget = target;
                } else {
                    realTarget = treeUtility.getNodeFromPath(state.tree, dropData.targetPath.slice(0, -1));
                }

                // If currently dragged element is not allowed as root
                // and dragged on element is a root element (no parent)
                // do not allow drop
                if(!dragEntityType.is_root && !realTarget) {
                    return false;
                }

                // Check if currently dragged entity type is allowed
                // as subtype of current drop target
                let index;
                if(!realTarget) {
                    index = entityTypesAsArray.findIndex(et => et.is_root && et.id == dragEntityType.id);
                } else {
                    index = getEntityType(realTarget.entity_type_id).sub_entity_types.findIndex(et => et.id == dragEntityType.id);
                }
                if(index == -1) {
                    return false;
                }

                // In any other cases allow drop
                return true;
            };

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
            const itemDrop = dropData => {
                if(!state.isDragAllowed || !isDropAllowed(dropData)) {
                    return;
                }

                const node = dropData.sourceData;
                const newRank = getNewRank(dropData);
                const oldRank = node.rank;
                let newParent;
                const oldParent = treeUtility.getNodeFromPath(state.tree, dropData.sourcePath.slice(0, dropData.sourcePath.length - 1));
                if(dropData.targetData.state.dropPosition == DropPosition.inside) {
                    newParent = dropData.targetData;
                } else {
                    newParent = treeUtility.getNodeFromPath(state.tree, dropData.targetPath.slice(0, dropData.targetPath.length - 1));
                }

                if(newParent == oldParent && newRank == oldRank) {
                    return;
                }

                const eid = node.id;
                const pid = newParent ? newParent.id : null;

                moveEntity(eid, pid, newRank);
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
                isDragAllowed: computed(_ => state.sort.by == 'rank' && state.sort.dir == 'asc'),
                sort: {
                    by: 'rank',
                    dir: 'asc'
                },
            });

            // ON MOUNTED
            onMounted(_ => {
                console.log('entity tree component mounted');
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
                itemDrop,
                isDropAllowed,
                toggleSelectMode,
                openMultieditModal,
                getSortingStateClass,
                setSort,
                openAddEntityDialog,
                searchResultSelected,
                // STATE
                state,
                entityStore,
            };
        }
    };
</script>
