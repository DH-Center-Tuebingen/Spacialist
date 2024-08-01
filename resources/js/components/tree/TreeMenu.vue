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
        <li>
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
        <li>
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
                    {{ t('main.entity.tree.contextmenu.move') }}
                </span>
            </a>
        </li>
        <li v-if="can('entity_share')">
            <a
                class="dropdown-item"
                href="#"
                @click.stop.prevent="exportWithChildren"
                @dblclick.stop.prevent=""
            >
                <i class="fas fa-fw file-export" />
                <span class="ms-2">
                    {{ t('global.export') }}
                </span>
            </a>
        </li>
        <li>
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
        exportEntityTree as exportEntityTreeApi,
    } from '@/api.js';

    import {
        can,
    } from '@/helpers/helpers.js';

    import store from '@/bootstrap/store.js';

    export default {
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
            useGlobalClick(function () {
                context.emit('close');
            });

            const addEntity = _ => {
                showAddEntity(props.data);
            };
            const duplicateEntity = _ => {
                duplicateEntityApi(props.data).then(data => {
                    store.dispatch('addEntity', data);
                    context.emit('close');
                });
            };
            const moveEntity = _ => {
                ShowMoveEntity(props.data);
                context.emit('close');
            };

            const deleteEntity = _ => {
                if(!can('entity_delete')) return;
                showDeleteEntity(props.data.id);
                context.emit('close');
            };

            const exportWithChildren = async _ => {
                if(!can('entity_share')) return;
                try{
                    await exportEntityTreeApi(props.data.id);
                } catch(e) {
                    console.error(e);
                }
                context.emit('close');
            };

            return {
                t: useI18n().t,
                can,
                addEntity,
                duplicateEntity,
                moveEntity,
                deleteEntity,
                exportWithChildren,
            };
        }
    };
</script>