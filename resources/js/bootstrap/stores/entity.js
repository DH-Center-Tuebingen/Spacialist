import {
    defineStore,
    mapStores,
} from 'pinia';

import {
    Node,
    openPath,
    sortTree,
} from '@/helpers/tree.js';

import {
    can,
    fillEntityData,
} from '@/helpers/helpers.js';

import {
    getEntityData,
    getEntityParentIds,
    getEntityReferences,
} from '@/api.js';

export const useEntityStore = defineStore('entity', {
    state: _ => ({
        selectedEntity: {},
        backup: {},
        entities: {},
        entityTypes: {},
        entityTypeAttributes: {},
        tree: [],
    }),
    getters: {
        getEntityTypeAttributes: state => (id, exclude = false) => {
            if(exclude === true) {
                return state.entityTypeAttributes[id].filter(a => a.datatype != 'system-separator');
            } else if(Array.isArray(exclude)) {
                return state.entityTypeAttributes[id].filter(a => !exclude.includes(a.datatype));
            }

            return state.entityTypeAttributes[id];
        },
    },
    actions: {
        add(entity, from_ws = false) {
            const node = new Node(entity);
            // If we get an existing entity from Websocket, back it up in case we need it
            const entityExists = from_ws && this.entities[node.id];
            if(entityExists) {
                const localVerion = this.entities[node.id];
                this.backup[node.id] = localVerion;
            }
            this.entities[node.id] = node;
            if(!entity.root_entity_id) {
                if(entityExists) {
                    const idx = this.tree.findIndex(itm => itm.id == node.id);
                    if(idx > -1) {
                        this.tree.splice(idx, 1, node);
                    }
                } else {
                    this.tree.push(node);
                }
            } else {
                const doCount = !node?.already_existing;
                delete node.already_existing;
                const parent = this.entities[node.root_entity_id];
                if(parent) {
                    if(parent.childrenLoaded) {
                        parent.children.push(node);
                    }
                    if(doCount) {
                        parent.children_count++;
                        parent.state.openable = true;
                    }
                }
            }
            return node;
        },
        update(entityData) {
            const entity = this.entities[entityData.id];
            entity.updated_at = entityData.updated_at;
            entity.user_id = entityData.user_id;
            entity.user = entityData.user;
            if(!!entityData.name) {
                entity.name = entityData.name;
            }
        },
        set(data) {
            this.selectedEntity = data;
        },
        unset() {
            this.set({});
        },
        async setById(entityId) {
            let entity = this.entities[entityId];
            if(!entity) {
                const ids = await getEntityParentIds(entityId);
                await openPath(ids);
                entity = this.entities[entityId];
            }
            if(!can('entity_data_read')) {
                entity = {
                    ...entity,
                    data: {},
                    attributes: [],
                    selections: {},
                    dependencies: [],
                    references: [],
                    comments: [],
                };
                fillEntityData(entity.data, entity.entity_type_id);
            } else {
                entity.data = await getEntityData(entityId);
                fillEntityData(entity.data, entity.entity_type_id);
                entity.references = await getEntityReferences(entityId) || {};
                for(let k in entity.data) {
                    const curr = entity.data[k];
                    if(curr.attribute) {
                        const key = curr.attribute.thesaurus_url;
                        if(!entity.references[key]) {
                            entity.references[key] = [];
                        }
                    }
                }
            }
            this.set(entity);
        },
        initialize(rootEntities) {
            this.backup = {};
            this.entities = {};
            this.tree = [];
            rootEntities.forEach(entity => {
                this.add(entity);
            });
        },
        initializeEntityTypes(data) {
            this.entityTypes = {};
            this.entityTypeAttributes = {};
            for(let k in data) {
                const entityType = data[k];
                this.entityTypeAttributes[entityType.id] = entityType.attributes.slice();
                delete entityType.attributes;
            }
            this.entityTypes = data;
        },
        setDescendants(data) {
            let descendants = [];
            data.entities.forEach(entity => {
                const node = this.add({
                    ...entity,
                    // flag to make sure to not increase children_count as we simply load already existing children
                    already_existing: true,
                });
                descendants.push(node);
            });
            sortTree(data.sort.by, data.sort.dir, descendants);
            return descendants;
        }
    },
});

const mappedEntityStore = {
    computed: {
        ...mapStores(useEntityStore),
    },
};
export const useGlobalEntityStore = _ => {
    return mappedEntityStore.computed.entityStore();
};

export default useEntityStore;