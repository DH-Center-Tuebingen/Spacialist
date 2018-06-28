<template>
    <div class="container">
        <v-jstree
            :async="loadAsync"
            :data="tree"
            :draggable="true"
            :drag-over-background-color="dragOverColor"
            :item-events="itemEvents"
            @item-click="itemClick"
            @item-toggle="itemToggle"
            @item-drag-start="itemDragStart"
            @item-drag-enter="itemDragEnter"
            @item-drag-leave="itemDragLeave"
            @item-drag-end="itemDragEnd"
            @item-drop-before="itemDropBefore"
            @item-drop="itemDrop">
            <div slot-scope="_">
                <i class="fas fa-fw fa-spa"></i>
                <span>{{_.model.name}}</span>
                <span class="pl-1 font-italic mb-0" v-if="_.model.context_type_id">
                    {{ $translateConcept($getEntityType(_.model.context_type_id).thesaurus_url) }}
                </span>
                <span class="pl-1" v-show="_.model.children_count">
                    ({{ _.model.children_count }})
                </span>
            </div>
        </v-jstree>

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
    import VJstree from 'vue-jstree';
    import { VueContext } from 'vue-context';
    import { transliterate as tr, slugify } from 'transliteration';

    class Node {
        constructor(data) {
            Object.assign(this, data);
            this.opened = false;
            this.selected = false;
            this.disabled = false;
            this.loading = false;
            this.isLeaf = false;
            this.dropDisabled = false;
            this.dragDisabled = false;
            if(this.children_count > 0) {
                this.children = [{
                    name: 'Loading...',
                    opened: false,
                    selected: false,
                    disabled: true,
                    loading: true,
                    children: []
                }];
                this.asyncChildren = () => this.fetchChildren(this.id);
            } else {
                this.children = [];
            }
        }

        fetchChildren(id) {
            return $http.get('/api/context/byParent/'+id)
            .then(response => {
                const result = response.data.map(n => new Node({...n, parent: this}));
                return result;
            }).catch(function(error) {
                log.error("FIX ME");
                // TODO neither vm nor this nor Vue are accessible here
                // vm.$throwError(error);
            });
        }
    }

    export default {
        components: {
            VueContext,
            VJstree
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
            itemClick(node, item, e) {
                this.selectionCallback(item);
            },
            itemToggle(node, item, e) {
            },
            itemDragStart(node, item, e) {
                this.dragging = true;
            },
            itemDragEnter(node, item, draggedItem, e) {
                const vm = this;
                if(vm.dragDelayId) {
                    clearTimeout(vm.dragDelayId);
                }
                item.dropDisabled = !vm.isDropAllowed(item, draggedItem);
                if(item.dropDisabled) {
                    vm.dragOverColor = '#FDC9C9';
                } else {
                    vm.dragOverColor = '#C9FDC9'
                }
                vm.dragDelayId = setTimeout(function() {
                    if(!item.opened && item.children_count) {
                        item.opened = true;
                    }
                    vm.dragDelayId = 0;
                }, vm.dragDelay);
            },
            itemDragLeave(node, item, draggedItem, e) {
            },
            itemDragEnd(node, item, e) {
                this.dragging = false;
            },
            itemDropBefore(node, item, draggedItem, e) {
            },
            itemDrop(node, item, draggedItem, e) {
                const vm = this;
                const rank = item.children ? item.children.length + 1 : 1;
                let data = {
                    rank: rank
                };
                if(!item.isRootNode) {
                    data.parent_id = item.id;
                }

                vm.$http.patch(`/api/context/${draggedItem.id}/rank`, data).then(function(response) {
                    // Because dropped elements are added at the end,
                    // we do not have to update the local tree and
                    // simply use array length as rank
                });
            },
            loadAsync(node, resolve) {
                if(!node.data.id || !node.data.asyncChildren) {
                    // resolve([]);
                } else {
                    const id = node.data.id;
                    node.data.asyncChildren().then(function(response) {
                        resolve(response);
                    });
                }
            },
            init() {
                this.tree = [{
                    name: 'Root Node',
                    opened: true,
                    selected: false,
                    disabled: true,
                    loading: false,
                    isLeaf: false,
                    dropDisabled: false,
                    dragDisabled: true,
                    isRootNode: true,
                    children: this.roots.map(n => new Node(n))
                }];
            },
            isDropAllowed(tgtItem, item) {
                const vm = this;
                const dragContextType = vm.$getEntityType(item.context_type_id);
                let dropContextType;
                if(tgtItem.isRootNode) {
                    dropContextType = {
                        sub_context_types: Object.values(vm.$getEntityTypes()).filter(f => f.is_root)
                    }
                } else {
                    dropContextType = vm.$getEntityType(tgtItem.context_type_id);
                }
                // If currently dragged element is not allowed as root
                // and dragged on element is a root element (no parent)
                // do not allow drop
                if(!dragContextType.is_root && tgtItem.isRootNode) {
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
            treeContextMenuEvent(node, item, e) {
                this.openContextMenu(e, item);
            },
            openContextMenu(e, item) {
                this.$refs.contextMenu.open(e, { item: item });
                e.preventDefault();
            }
        },
        data() {
            return {
                tree: [],
                dragging: false,
                dragOverColor: '#C9FDC9',
                itemEvents: {
                    contextmenu: this.treeContextMenuEvent
                },
                dragDelayId: 0
            }
        }
    }
</script>
