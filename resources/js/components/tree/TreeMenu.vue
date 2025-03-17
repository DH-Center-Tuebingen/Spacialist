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
                @click.stop.prevent="addEntity('above')"
                @dblclick.stop.prevent=""
            >
                <i class="fas fa-fw fa-turn-up text-success" />
                <span class="ms-2">
                    {{ t('main.entity.tree.contextmenu.add_above') }}
                </span>
            </a>
        </li>
        <li>
            <a
                class="dropdown-item"
                href="#"
                @click.stop.prevent="addEntity('below')"
                @dblclick.stop.prevent=""
            >
                <i class="fas fa-fw fa-turn-down text-success" />
                <span class="ms-2">
                    {{ t('main.entity.tree.contextmenu.add_below') }}
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
    } from '@/api.js';

    import {
        can,
    } from '@/helpers/helpers.js';

    import useEntityStore from '@/bootstrap/stores/entity.js';

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
            const entityStore = useEntityStore();

            useGlobalClick(function () {
                context.emit('close');
            });

            const addEntity = where => {
                if(where == 'above') {
                    const parent = entityStore.getEntity(props.data.root_entity_id);
                    showAddEntity(parent, null, props.data.rank);
                } else if(where == 'below') {
                    const parent = entityStore.getEntity(props.data.root_entity_id);
                    showAddEntity(parent, null, props.data.rank + 1);
                } else {
                    showAddEntity(props.data);
                }
            };
            const duplicateEntity = _ => {
                duplicateEntityApi(props.data).then(data => {
                    entityStore.add(data);
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

            return {
                t,
                can,
                addEntity,
                duplicateEntity,
                moveEntity,
                deleteEntity,
            };
        }
    };
</script>