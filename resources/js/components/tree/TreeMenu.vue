<template>
    <ul
        :id="`tree-node-${data.id}-contextmenu`"
        class="tree-menu dropdown-menu show"
        @click.stop.prevent
    >
        <li>
            <h6 class="dropdown-header">
                {{ data.name }}
            </h6>
        </li>
        <li v-if="!multiSelectActive">
            <a
                class="dropdown-item"
                href="#"
                @click.stop.prevent="addEntity"
                @dblclick.stop.prevent=""
            >
                <i class="fas fa-fw fa-plus text-success" />
                <span class="ms-2">
                    {{ t('main.entity.tree.contextmenu.add') }}
                </span>
            </a>
        </li>
        <li v-if="!multiSelectActive">
            <a
                class="dropdown-item"
                href="#"
                @click.stop.prevent="duplicateEntity"
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
                @click.stop.prevent="moveEntity"
                @dblclick.stop.prevent=""
            >
                <i class="fas fa-fw fa-external-link-alt text-primary" />
                <span class="ms-2">
                    {{
                        (multiSelectActive) ?
                            t('main.entity.tree.contextmenu.move-selection') :
                            t('main.entity.tree.contextmenu.move')
                    }}
                </span>
            </a>
        </li>
        <li v-if="!multiSelectActive">
            <a
                v-if="can('entity_delete')"
                class="dropdown-item"
                href="#"
                @click.stop.prevent="deleteEntity"
                @dblclick.stop.prevent=""
            >
                <i class="fas fa-fw fa-trash text-danger" />
                <span class="ms-2">
                    {{ t('main.entity.tree.contextmenu.delete') }}
                </span>
            </a>
        </li>
        <template v-if="multiSelectActive">
            <li>
                <hr class="dropdown-divider">
            </li>
            <MultiselectMoveMenu
                :data="data"
                @close="close"
            />
        </template>
    </ul>
</template>

<script>

    import {
        useGlobalClick
    } from '@/composables/global-click';


    import {
        useI18n
    } from 'vue-i18n';

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
    } from '@/helpers/helpers.js';

    import store from '@/bootstrap/store.js';
    import MultiselectMoveMenu from './menu/MultiselectMoveMenu.vue';
    import {computed} from 'vue';

    export default {
        components: {
            MultiselectMoveMenu
        },
        props: {
            data: {
                type: Object,
                required: true
            }
        },
        emits: [
            'close'
        ],
        setup(props, context) {

            const close = _ => {
                context.emit('close');
            };

            useGlobalClick(function () {
                close();
            });

            const addEntity = _ => {
                showAddEntity(props.data);
            };
            const duplicateEntity = _ => {
                duplicateEntityApi(props.data).then(data => {
                    store.dispatch('addEntity', data);
                    close();
                });
            };
            const moveEntity = _ => {
                ShowMoveEntity(props.data);
                close();
            };

            const deleteEntity = _ => {
                if(!can('entity_delete')) return;
                showDeleteEntity(props.data.id);
                close();
            };

            const multiSelectActive = computed(() => {
                return store.getters.treeSelectionMode;
            });

            return {
                t: useI18n().t,
                can,
                close,
                addEntity,
                duplicateEntity,
                moveEntity,
                multiSelectActive,
                deleteEntity,
            };
        }
    };
</script>