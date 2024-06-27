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
        <li v-if="can('entity_create') && canCreate(data)">
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
        <li v-if="can('entity_create') && canCreate(data)">
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
        <li v-if="can('entity_write') && canWrite(data)">
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
        <li v-if="can('entity_delete') && canDelete(data)">
            <a
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
    } from '@/composables/global-click.js';


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
        canWrite,
        canCreate,
        canDelete,
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
            const { t } = useI18n();

            useGlobalClick(function () {
                context.emit('close');
            });

            const addEntity = _ => {
                if(!can('entity_create') || !canCreate(props.data)) return;

                showAddEntity(props.data);
            };
            const duplicateEntity = _ => {
                if(!can('entity_create') || !canCreate(props.data)) return;

                duplicateEntityApi(props.data).then(data => {
                    store.dispatch('addEntity', props.data);
                    context.emit('close');
                });
            };
            const moveEntity = _ => {
                if(!can('entity_write') || !canWrite(props.data)) return;

                ShowMoveEntity(props.data);
                context.emit('close');
            };

            const deleteEntity = _ => {
                if(!can('entity_delete') || !canDelete(props.data)) return;

                showDeleteEntity(props.data.id);
                context.emit('close');
            };

            // RETURN
            return {
                t,
                // HELPERS
                can,
                canWrite,
                canCreate,
                canDelete,
                // LOCAL
                addEntity,
                duplicateEntity,
                moveEntity,
                deleteEntity,
                // STATE
            };
        }
    };
</script>
