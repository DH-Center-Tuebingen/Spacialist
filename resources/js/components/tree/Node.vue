<template>
    <div
        :id="`tree-node-${data.id}`"
        class="d-flex"
        @dragenter="onDragEnter"
        @dragleave="onDragLeave"
        @click="e => addToMSList(e)"
    >
        <span
            v-if="state.isSelectionMode"
            class="mx-1"
        >
            <span
                v-show="state.multieditSelected"
                class="text-success"
            >
                <i class="fas fa-fw fa-circle-check" />
            </span>
            <span v-show="!state.multieditSelected">
                <i class="far fa-fw fa-circle" />
            </span>
        </span>
        <a
            :id="`tree-node-cm-toggle-${data.id}`"
            href=""
            class="text-body text-decoration-none disabled d-flex flex-row gap-1 ps-1"
            data-bs-toggle="dropdown"
            data-bs-auto-close="true"
            aria-expanded="false"
            @click.prevent
            @contextmenu.stop.prevent="togglePopup()"
        >
            <span
                class="d-flex flex-row align-items-center"
            >
                <span
                    v-if="data.children_count"
                    class="badge rounded-pill"
                    style="font-size: 9px;"
                    :style="state.colorStyles"
                    :title="data.children_count"
                >
                    {{ numPlus(data.children_count, 3) }}
                </span>
                <span
                    v-else
                    class="badge rounded-pill"
                    style="font-size: 8px;"
                    :style="state.colorStyles"
                >
                    &nbsp;&nbsp;
                </span>
            </span>
            <span :class="{ 'fw-bold': state.isSelected }">
                {{ data.name }}
            </span>
        </a>
        <ul
            v-if="state.ddVisible"
            :id="`tree-node-${data.id}-contextmenu`"
            class="dropdown-menu show"
        >
            <li>
                <h6
                    class="dropdown-header"
                    @click.stop.prevent=""
                    @dblclick.stop.prevent=""
                >
                    {{ data.name }}
                </h6>
            </li>
            <li>
                <a
                    class="dropdown-item"
                    href="#"
                    @click.stop.prevent="addNewEntity()"
                    @dblclick.stop.prevent=""
                >
                    <i class="fas fa-fw fa-plus text-success" />
                    <span class="ms-2">
                        {{ t('main.entity.tree.contextmenu.add') }}
                    </span>
                </a>
            </li>
            <li>
                <a
                    class="dropdown-item"
                    href="#"
                    @click.stop.prevent="duplicateEntity()"
                    @dblclick.stop.prevent=""
                >
                    <i class="fas fa-fw fa-clone text-primary" />
                    <span class="ms-2">
                        {{ t('main.entity.tree.contextmenu.duplicate') }}
                    </span>
                </a>
            </li>
            <li>
                <a
                    class="dropdown-item"
                    href="#"
                    @click.stop.prevent="moveEntity()"
                    @dblclick.stop.prevent=""
                >
                    <i class="fas fa-fw fa-external-link-alt text-primary" />
                    <span class="ms-2">
                        {{ t('main.entity.tree.contextmenu.move') }}
                    </span>
                </a>
            </li>
            <li>
                <a
                    v-if="can('entity_delete')"
                    class="dropdown-item"
                    href="#"
                    @click.stop.prevent="deleteEntity()"
                    @dblclick.stop.prevent=""
                >
                    <i class="fas fa-fw fa-trash text-danger" />
                    <span class="ms-2">
                        {{ t('main.entity.tree.contextmenu.delete') }}
                    </span>
                </a>
            </li>
        </ul>
    </div>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
        watch,
    } from 'vue';

import { useI18n } from 'vue-i18n';

import store from '@/bootstrap/store.js';

    import {
        showAddEntity,
        showDeleteEntity,
        ShowMoveEntity,
    } from '@/helpers/modal.js';
    import {
        duplicateEntity as duplicateEntityApi,
    } from '@/api.js';
    import {
        can,
        getEntityColors,
        hasIntersectionWithEntityAttributes,
    } from '@/helpers/helpers.js';
    import {
        numPlus,
    } from '@/helpers/filters.js';

export default {
    props: {
        data: {
            required: true,
            type: Object
        }
    },
    setup(props) {
        const { t } = useI18n();
        const {
            data,
        } = toRefs(props);

        // FETCH

            // FUNCTIONS
            const hidePopup = _ => {
                state.ddVisible = false;
            };
            const showPopup = _ => {
                state.ddVisible = true;
            };
            const togglePopup = _ => {
                if(state.ddVisible) {
                    hidePopup();
                } else {
                    showPopup();
                }
            };
            const addNewEntity = _ => {
                showAddEntity(data.value);
            };
            const duplicateEntity = _ => {
                duplicateEntityApi(data.value).then(data => {
                    store.dispatch('addEntity', data);
                });
            };
            const moveEntity = _ => {
                ShowMoveEntity(data.value);
            };
            const deleteEntity = _ => {
                if(!can('entity_delete')) return;

            showDeleteEntity(data.value.id);
        };
        const onDragEnter = _ => {

        };
        const onDragLeave = _ => {

            };
            const addToMSList = event => {
                if(!state.isSelectionMode) return;

                event.stopPropagation();
                event.preventDefault();
                state.multieditSelected = !state.multieditSelected;
                if(state.multieditSelected) {
                    store.dispatch('addToTreeSelection', {
                        id: data.value.id,
                        value: {
                            entity_type_id: data.value.entity_type_id,
                        },
                    });
                } else {
                    store.dispatch('removeFromTreeSelection', {
                        id: data.value.id,
                    });
                }
            };

            // DATA
            const state = reactive({
                ddVisible: false,
                multieditSelected: false,
                colorStyles: computed(_ => getEntityColors(data.value.entity_type_id)),
                isSelected: computed(_ => store.getters.entity.id === data.value.id),
                isSelectionMode: computed(_ => store.getters.treeSelectionMode),
                isSelectionDisabled: computed(_ => {
                    if(store.getters.treeSelectionTypeIds.length == 0 || state.multieditSelected) {
                        return false;
                    }
                    return !hasIntersectionWithEntityAttributes(data.value.entity_type_id, store.getters.treeSelectionTypeIds);
                }),
            });

            // ON MOUNTED
            onMounted(_ => {
                console.log("tree node component mounted");
            });

            // WATCHER
            watch(_ => state.isSelectionMode, (newValue, oldValue) => {
                // if selection mode got disabled (checkbox not visible)
                if(!newValue && oldValue) {
                    state.multieditSelected = false;
                }
            });

            // RETURN
            return {
                t,
                // HELPERS
                can,
                numPlus,
                // LOCAL
                togglePopup,
                addNewEntity,
                duplicateEntity,
                moveEntity,
                deleteEntity,
                onDragEnter,
                onDragLeave,
                addToMSList,
                // STATE
                state,
            };
        }
        // methods: {
        //     onDragEnter() {
        //         if(!this.data.dragAllowed()) return;
        //         this.asyncToggle.cancel();
        //         this.asyncToggle();
        //     },
        //     onDragLeave(item) {
        //         this.asyncToggle.cancel();
        //     },
        //     doToggle() {
        //         if(!this.data.state.opened && this.data.state.openable) {
        //             this.data.onToggle({data: this.data});
        //         }
        //     }
        // },
        // data() {
        //     return {
        //     }
        // },
        // computed: {
        //     asyncToggle() {
        //         return _debounce(this.doToggle, this.data.dragDelay || 500);
        //     },
        //     colorStyles() {
        //         const colors = this.$getEntityColors(this.data.entity_type_id);
        //         if(this.data.children_count) {
        //             return colors;
        //         } else {
        //             return {
        //                 color: colors.backgroundColor
        //             };
        //         }
        //     }
        // }
    }
</script>
