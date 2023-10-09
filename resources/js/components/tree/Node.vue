<template>
    <div @dragenter="onDragEnter" @dragleave="onDragLeave" :id="`tree-node-${data.id}`">
        <input class="mx-1 form-check-input" type="checkbox" :disabled="state.isSelectionDisabled" :id="`tree-node-mes-${data.id}`" v-model="state.multieditSelected" v-show="state.isSelectionMode" @click.stop="addToMSList()" />
        <a href="" :id="`tree-node-cm-toggle-${data.id}`" @click.prevent @contextmenu.stop.prevent="togglePopup()" class="text-body text-decoration-none disabled" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
            <span style="display: inline-block; text-align: center;" class="px-1">
                <span v-if="data.children_count" class="badge rounded-pill" style="font-size: 9px;" :style="state.colorStyles" :title="data.children_count">
                    {{ numPlus(data.children_count, 3) }}
                </span>
                <span v-else class="badge rounded-pill" style="font-size: 8px;" :style="state.colorStyles">
                    &nbsp;&nbsp;
                </span>
            </span>
            <span :class="{'fw-bold': state.isSelected}">
                {{ data.name }}
            </span>
        </a>
        <ul class="dropdown-menu" :id="`tree-node-${data.id}-contextmenu`">
            <li>
                <h6 class="dropdown-header" @click.stop.prevent="" @dblclick.stop.prevent="">
                    {{ data.name }}
                </h6>
            </li>
            <li>
                <a class="dropdown-item" href="#" @click.stop.prevent="addNewEntity()" @dblclick.stop.prevent="">
                    <i class="fas fa-fw fa-plus text-success"></i>
                    <span class="ms-2">
                        {{ t('main.entity.tree.contextmenu.add') }}
                    </span>
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="#" @click.stop.prevent="duplicateEntity()" @dblclick.stop.prevent="">
                    <i class="fas fa-fw fa-clone text-primary"></i>
                    <span class="ms-2">
                        {{ t('main.entity.tree.contextmenu.duplicate') }}
                    </span>
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="#" @click.stop.prevent="moveEntity()" @dblclick.stop.prevent="">
                    <i class="fas fa-fw fa-external-link-alt text-primary"></i>
                    <span class="ms-2">
                        {{ t('main.entity.tree.contextmenu.move') }}
                    </span>
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="#" @click.stop.prevent="deleteEntity()" @dblclick.stop.prevent="" v-if="can('entity_delete')">
                    <i class="fas fa-fw fa-trash text-danger"></i>
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
        nextTick,
        onMounted,
        reactive,
        toRefs,
        watch,
    } from 'vue';

    import {
        Dropdown,
    } from 'bootstrap';

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
                state.bsElem.hide();
                state.ddVisible = false;
                state.ddDomElem.classList.add('disabled');
            };
            const showPopup = _ => {
                state.ddVisible = true;
                nextTick(_ => {
                    // To prevent opening the dropdown on normal click on Node,
                    // the DD toggle must have class 'disabled'
                    // This also prevents BS API call .show() to work...
                    // Thus we remove the 'disabled' class before the API call and add it back on hide
                    state.ddDomElem.classList.remove('disabled');
                    state.bsElem.show();
                })
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
            const addToMSList = _ => {
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
                ddDomElem: null,
                bsElem: null,
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
                state.ddDomElem = document.getElementById(`tree-node-cm-toggle-${data.value.id}`);
                state.ddDomElem.addEventListener('hidden.bs.dropdown', _ => {
                    hidePopup();
                });
                state.bsElem = new Dropdown(state.ddDomElem);
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
                // PROPS
                data,
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
