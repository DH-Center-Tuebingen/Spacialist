<template>
    <li
        v-for="item in menuItems"
        :key="item.name"
        class="multi-select-move-menu-item"
    >
        <a
            class="dropdown-item"
            href="#"
            @click.stop.prevent="item.action"
            @dblclick.stop.prevent=""
        >
            <i class="fas fa-fw" />
            <span class="ms-2">
                {{ t(`main.entity.tree.contextmenu.${item.name}`) }}
            </span>
        </a>
    </li>
</template>

<script>
    import { reactive } from 'vue';
    import { useI18n } from 'vue-i18n';
    import { store } from '@/bootstrap/store';

    export default {
        props: {
            data: {
                required: true,
                type: Object,
            },
        },
        emits: ['close'],
        setup(props, context) {


            function getSiblings(entity) {
                const parentId = entity.root_entity_id;
                let siblings = [];
                if(parent) {
                    const parentEntity = store.getters.entities[parentId];
                    siblings = parentEntity.children;
                } else {
                    siblings = store.getters.tree;
                }
                return siblings;
            }

            function mapToSelectionData(entities) {
                return entities.map(entity => {
                    return { id: entity.id, value: { entity_type_id: entity.entity_type_id } };
                });
            }

            function unselectAll() {
                store.dispatch('resetTreeSelection');
                context.emit('close');
            }

            function selectAllSiblings() {
                let siblings = getSiblings(props.data);
                const selectionData = mapToSelectionData(siblings);
                store.commit('addToTreeSelection', selectionData);
                context.emit('close');
            }
            function selectAllSiblingsOfType() {
                let entityType = props.data.entity_type_id;
                let siblings = getSiblings(props.data).filter(sibling => sibling.entity_type_id === entityType);
                const selectionData = mapToSelectionData(siblings);
                store.commit('addToTreeSelection', selectionData);
                context.emit('close');
            }

            const menuItems = reactive([
                { name: 'unselect-all', action: unselectAll },
                { name: 'select-all-siblings', action: selectAllSiblings },
                { name: 'select-all-siblings-of-type', action: selectAllSiblingsOfType },
            ]);


            return {
                t: useI18n().t,
                menuItems
            };
        }
    };
</script>