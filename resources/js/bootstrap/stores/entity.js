import { defineStore } from 'pinia';
import {
    Node,
    sortTree,
} from '@/helpers/tree.js';

export const useEntityStore = defineStore('entity', {
    state: _ => ({
        backup: {},
        entities: {},
        entityTypes: {},
        tree: [],
    }),
    getters: {
    },
    actions: {
        add(entity, from_ws = false) {
            const node = new Node(entity);
            // If we get an existing entity from Websocket, back it up in case we need it
            if(from_ws && this.entities[node.id]) {
                const localVerion = this.entities[node.id];
                this.backup[node.id] = localVerion;
            }
            this.entities[node.id] = node;
            if(!entity.root_entity_id) {
                this.tree.push(node);
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
        initialize(rootEntities) {
            rootEntities.forEach(entity => {
                this.add(entity);
            });
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

export default useEntityStore;