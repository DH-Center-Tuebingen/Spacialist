import { defineStore } from 'pinia';

import useEntityStore from './entity.js';

import {
    addReference,
    deleteReferenceFromEntity,
    updateReference,
} from '@/api.js';

export const useReferenceStore = defineStore('reference', {
    state: _ => ({}),
    getters: {},
    actions: {
        currentEntityReferences() {
            const entity = useEntityStore().selectedEntity;
            return entity?.references?.on_entity || [];
        },
        currentAttributeReferences() {
            const entity = useEntityStore().selectedEntity;
            if(!entity?.references) return [];
            const  {
                on_entity,
                ...attributedReferences
            } = entity.references;
            return attributedReferences;
        },
        get(entityId, attributeUrl = null) {
            const entity = useEntityStore().getEntity(entityId);
            if(attributeUrl) {
                return entity?.references?.[attributeUrl] || [];
            } else {
                return entity?.references?.on_entity || [];
            }
        },
        handleUpdate(entityId, attributeUrl, data) {
            const references =this.get(entityId, attributeUrl);
            const id = data.id;
            const refData = data.data;
            const updateData = data.updates;
            const reference = references.find(ref => ref.id == id);
            if(!!reference) {
                for(let k in refData) {
                    reference[k] = refData[k];
                }
                reference.updated_at = updateData.updated_at;
            }
        },
        async add(entityId, attributeId, attributeUrl, refData) {
            const data = await addReference(entityId, attributeId, refData);
            const references = this.get(entityId, attributeUrl);
            references.push(data);
            return data;
        },
        async update(id, entityId, attributeUrl, refData) {
            const data = await updateReference(id, refData);
            this.handleUpdate(entityId, attributeUrl, {
                id: id,
                data: refData,
                updates: data,
            });
        },
        async remove(id, entityId, attributeUrl) {
            await deleteReferenceFromEntity(id);
            const references = this.get(entityId, attributeUrl);
            const idx = references.findIndex(ref => ref.id == id);
            if(idx > -1) {
                references.splice(idx, 1);
            }
        },
    },
});

export default useReferenceStore;