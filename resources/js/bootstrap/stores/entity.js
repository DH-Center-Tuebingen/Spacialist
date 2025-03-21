import {
    defineStore,
} from 'pinia';

import router from '%router';

import useAttributeStore from './attribute.js';
import useSystemStore from './system.js';
import useUserStore from './user.js';

import {
    Node,
    openPath,
    sortTree,
} from '@/helpers/tree.js';

import {
    can,
    calculateEntityTypeColors,
    fillEntityData,
    only,
} from '@/helpers/helpers.js';

import { getEditedComment } from '@/helpers/comment.js';

import {
    addEntity,
    addEntityType,
    addEntityTypeAttribute,
    deleteEntity,
    deleteEntityType,
    duplicateEntityType,
    fetchEntityMetadata,
    getEntity,
    getEntityComments,
    getEntityData,
    getEntityParentIds,
    getEntityParentMetadata,
    getEntityReferences,
    handleModeration,
    moveEntity,
    patchEntityType,
    patchAttribute,
    patchAttributes,
    removeEntityTypeAttribute,
    reorderEntityAttributes,
    searchEntity,
    updateAttributeDependency,
    updateAttributeMetadata,
} from '@/api.js';

function updateSelectionTypeIdList(selection) {
    const tmpDict = {};
    for(let k in selection) {
        const curr = selection[k];
        if(!tmpDict[curr.entity_type_id]) {
            tmpDict[curr.entity_type_id] = 1;
        }
    }
    return Object.keys(tmpDict).map(tdk => parseInt(tdk));
}

const handleAddEntityType = (context, typeData, attributes = []) => {
    context.entityTypeAttributes[typeData.id] = attributes.slice();
    context.entityTypes[typeData.id] = typeData;
};

const handlePostDelete = (context, entityId) => {
    const currentRoute = router.currentRoute.value;
    // Currently an entity is selected, thus maybe route back is needed
    if(currentRoute.name == 'entitydetail' || currentRoute.name == 'entitydetail') {
        const selectedEntityId = currentRoute.params.id;
        // Selected entity is deleted entity
        if(selectedEntityId == entityId) {
            router.push({
                append: true,
                name: 'home',
                query: currentRoute.query
            });
        } else {
            const selectedEntity = context.getEntity(selectedEntityId);
            const idx = selectedEntity.parentIds.findIndex(pid => pid == entityId);
            // Selected entity is child of deleted entity
            if(idx > -1) {
                router.push({
                    append: true,
                    name: 'home',
                    query: currentRoute.query
                });
            }
        }
    }
};

export const useEntityStore = defineStore('entity', {
    state: _ => ({
        selectedEntity: {},
        selectedEntityUserIds: [],
        backup: {},
        entities: {},
        entityTypes: {},
        entityTypeAttributes: {},
        entityTypeColors: {},
        receivedEntityData: {},
        tree: [],
        treeSelectionMode: false,
        treeSelection: {},
        treeSelectionTypeIds: [],
    }),
    getters: {
        getActiveEntityUsers: state => {
            return state.selectedEntityUserIds.map(user => {
                return useUserStore().getUserBy(user.id);
            });
        },
        getEntity: state => id => {
            return state.entities[id] || { id: 0 };
        },
        fetchEntity: function (state) {
            return async id => {
                const cachedEntity = this.getEntity(id);
                if(cachedEntity.id !== 0) {
                    return Promise.resolve(cachedEntity);
                }

                const entity = await getEntity(id);
                this.add(entity);
                return entity;
            };
        },
        getEntityType: state => id => {
            if(!id) return {};
            return state.entityTypes[id];
        },
        getEntityTypeAttributes: state => (id, exclude = false) => {
            if(!id || !state.entityTypeAttributes[id]) return [];

            if(exclude === true) {
                return state.entityTypeAttributes[id].filter(a => a.datatype != 'system-separator');
            } else if(Array.isArray(exclude)) {
                return state.entityTypeAttributes[id].filter(a => !exclude.includes(a.datatype));
            }

            return state.entityTypeAttributes[id];
        },
        getEntityTypeAttributeSelections(state) {
            return id => {
                const attrs = this.getEntityTypeAttributes(id);
                if(!attrs) return {};
                return useAttributeStore().getAttributeSelections(attrs);
            };
        },
        getEntityAttributeIntersection(state) {
            return entityTypes => {
                if(entityTypes.length == 0) return [];

                let compArr = this.getEntityTypeAttributes(entityTypes[0]);

                if(entityTypes.length == 1) {
                    return compArr;
                }

                let intersections = [];
                for(let i = 1; i < entityTypes.length; i++) {
                    intersections = [];
                    const attrN = this.getEntityTypeAttributes(entityTypes[i]);
                    for(let j = 0; j < compArr.length; j++) {
                        for(let k = 0; k < attrN.length; k++) {
                            const a1 = compArr[j];
                            const a2 = attrN[k];
                            if(a1.id == a2.id) {
                                intersections.push(a1);
                            }
                        }
                    }
                    compArr = intersections;
                }
                return intersections;
            };
        },
        hasIntersectionWithEntityAttributes(state) {
            return (entityTypeId, entityTypes) => {
                return this.getEntityAttributeIntersection([
                    entityTypeId,
                    ...entityTypes,
                ]).length > 0;
            };
        },
        getEntityTypeColors(state) {
            return id => {
                if(!id) return {};
                let colors = state.entityTypeColors[id];
                if(!colors) {
                    const entityType = this.getEntityType(id);
                    const calculatedColors = calculateEntityTypeColors(entityType);
                    state.entityTypeColors[id] = calculatedColors;
                    colors = state.entityTypeColors[id];
                }
                return colors;
            };
        },
        getEntityTypeName(state) {
            return id => {
                const entityType = this.getEntityType(id);
                if(!entityType) return '';
                const systemStore = useSystemStore();
                return systemStore.translateConcept(entityType.thesaurus_url);
            };
        },
        getTreeSelectionCount: state => {
            return Object.keys(state.treeSelection).length;
        },
        getTreeSelectionIntersection(state) {
            return this.getEntityAttributeIntersection(state.treeSelectionTypeIds);
        },
    },
    actions: {
        async create(entityData) {
            return addEntity(entityData).then(entity => {
                return this.add(entity);
            });
        },
        add(entity, from_ws = false) {
            const node = new Node(entity);
            // If we get an existing entity from Websocket, back it up in case we need it
            const entityExists = from_ws && this.entities[node.id];
            if(entityExists) {
                // const localVerion = this.entities[node.id];
                // this.backup[node.id] = localVerion;
                this.entities[node.id] = {
                    ...this.entities[node.id],
                    ...node,
                };
                if(node.id == this.selectedEntity?.id) {
                    this.selectedEntity = {
                        ...this.selectedEntity,
                        ...node,
                    };
                }
                return;
            }
            this.entities[node.id] = node;
            if(node.id == this.selectedEntity?.id) {
                this.selectedEntity = {
                    ...this.selectedEntity,
                    ...node,
                };
            }
            if(!entity.root_entity_id) {
                if(entityExists) {
                    const idx = this.tree.findIndex(itm => itm.id == node.id);
                    if(idx > -1) {
                        this.tree.splice(idx, 1, node);
                    }
                } else {
                    if(this.tree.length == 0 || node.rank > this.tree.at(-1).rank) {
                        this.tree.push(node);
                    } else {
                        const idx = this.tree.findIndex(c => c.rank == node.rank);
                        this.tree.splice(idx, 0, node);
                        for(let i=idx+1; i<this.tree.length; i++) {
                            this.tree[i].rank++;
                        }
                    }
                }
            } else {
                const doCount = !node?.already_existing;
                delete node.already_existing;
                const parent = this.entities[node.root_entity_id];
                if(parent) {
                    if(parent.childrenLoaded) {
                        if(entityExists) {
                            const idx = parent.children.findIndex(itm => itm.id == node.id);
                            if(idx > -1) {
                                parent.children.splice(idx, 1, node);
                            }
                        } else {
                            if(node.rank > parent.children.at(-1).rank) {
                                parent.children.push(node);
                            } else {
                                const idx = parent.children.findIndex(c => c.rank == node.rank);
                                parent.children.splice(idx, 0, node);
                                for(let i=idx+1; i<parent.children.length; i++) {
                                    parent.children[i].rank++;
                                }
                            }
                        }
                    }
                    if(doCount) {
                        if(!entityExists) {
                            parent.children_count++;
                            parent.state.openable = true;
                        }
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

            if(this.selectedEntity.id == entity.id) {
                this.selectedEntity = {
                    ...this.selectedEntity,
                    ...entityData,
                };
            }
        },
        move(entityId, parentId, rank) {
            const data = {
                parent_id: parentId,
            };
            if(rank || rank === 0) {
                data.rank = rank;
            } else {
                data.rank = 0;
                data.to_end = true;
            }
            return moveEntity(entityId, data).then(_ => {
                const entity = this.getEntity(entityId);
                const oldRank = entity.rank;
                const newRank = data.rank;
                const rankIdx = newRank - 1;
                const append = data.to_end;

                entity.rank = newRank;

                let oldSiblings;
                if(!!entity.root_entity_id) {
                    oldSiblings = this.getEntity(entity.root_entity_id).children;
                } else {
                    oldSiblings = this.tree;
                }
                const idx = oldSiblings.findIndex(n => n.id == entity.id);
                if(idx > -1) {
                    oldSiblings.splice(idx, 1);
                    oldSiblings.map(s => {
                        if(s.rank > oldRank) {
                            s.rank--;
                        }
                    });
                }

                // Update children state of old parent
                if(!!entity.root_entity_id) {
                    const oldParent = this.getEntity(entity.root_entity_id);
                    oldParent.children_count--;
                    if(oldParent.children_count == 0) {
                        oldParent.state.openable = false;
                        oldParent.state.opened = false;
                    }
                }

                if(!parentId) {
                    // Set new (= unset) parent
                    entity.root_entity_id = null;
                    if(append) {
                        this.tree.push(entity);
                    } else {
                        this.tree.splice(rankIdx, 0, entity);
                        this.tree.map(s => {
                            if(s.rank >= newRank) {
                                s.rank++;
                            }
                        });
                    }
                } else {
                    // Update children state of new parent
                    const parent = this.getEntity(data.parent_id);
                    if(!!parent) {
                        if(parent.childrenLoaded) {
                            if(append) {
                                parent.children.push(entity);
                            } else {
                                parent.children.splice(rankIdx, 0, entity);
                                parent.children.map(s => {
                                    if(s.rank >= newRank) {
                                        s.rank++;
                                    }
                                });
                            }
                        }
                        parent.children_count++;
                        parent.state.openable = true;
                    }
                    // Set new parent
                    entity.root_entity_id = data.parent_id;
                }
            });
        },
        // delete entity from store (after delete event from websocket)
        soft_delete(node) {
            const entity = this.entities[node.id];
            if(entity) {
                this.backup[node.id] = entity;
                delete this.entities[node.id];
            }
            if(!entity.root_entity_id) {
                const idx = this.tree.findIndex(itm => itm.id == entity.id);
                if(idx > -1) {
                    this.tree.splice(idx, 1);
                }
            } else {
                const parent = this.entities[entity.root_entity_id];
                if(parent) {
                    if(parent.childrenLoaded) {
                        const idx = parent.children.findIndex(itm => itm.id == entity.id);
                        if(idx > -1) {
                            parent.children.splice(idx, 1);
                        }
                    }
                    parent.children_count--;
                    parent.state.openable = parent.children_count > 0;
                }
            }
            return node;
        },
        async delete(entityId) {
            return deleteEntity(entityId).then(_ => {
                const entity = this.entities[entityId];
                if(entity.root_entity_id) {
                    const parent = this.getEntity(entity.root_entity_id);
                    if(parent.childrenLoaded) {
                        const idx = parent.children.findIndex(c => c.id == entity.id);
                        if(idx > -1) {
                            parent.children.splice(idx, 1);
                        }
                    }
                    parent.children_count--;
                    parent.state.openable = parent.children_count > 0;
                } else {
                    const idx = this.tree.findIndex(l => l.id == entity.id);
                    if(idx > -1) {
                        this.tree.splice(idx, 1);
                    }
                }
                delete this.entities[entityId];

                handlePostDelete(entityId);
                return entity;
            });
        },
        updateEntityMetadata(id, data) {
            const metadata = {};
            for(let k in data) {
                metadata[k] = data[k];
            }

            if(!this.entities?.[id]) {
                return;
            }

            if(this.entities?.[id].metadata && this.selectedEntity.id == id) {
                this.selectedEntity.metadata = {};
            }

            this.entities[id].metadata = {
                ...this.entities[id].metadata,
                ...metadata,
            };
            return metadata;
        },
        updateAttributeMetadata(entityTypeId, attributeId, etAttrId, metadata) {
            const attributes = this.getEntityTypeAttributes(entityTypeId);
            const attribute = attributes.find(a => a.id == attributeId && a.pivot.id == etAttrId);
            attribute.pivot.metadata = metadata;
        },
        set(data) {
            this.selectedEntity = data;
        },
        unset() {
            this.set({});
        },
        setActiveUserIds(userIdList) {
            this.selectedEntityUserIds = [];
            this.selectedEntityUserIds = userIdList;
        },
        addActiveUserId(userId) {
            this.selectedEntityUserIds.push(userId);
        },
        removeActiveUserId(userId) {
            const idx = this.selectedEntityUserIds.findIndex(u => u.id == userId);
            if(idx > -1) {
                this.selectedEntityUserIds.splice(idx, 1);
            }
        },
        async fetchEntityComments(id) {
            if(id != this.selectedEntity?.id) return;

            return getEntityComments(id).then(comments => {
                this.selectedEntity.comments = comments;
            }).catch(e => {
                throw e;
            });
        },
        async fetchEntityMetadata(id) {
            return fetchEntityMetadata(id).then(data => {
                return this.updateEntityMetadata(id, data);
            });
        },
        async patchEntityMetadata(entityTypeId, attributeId, etAttrId, metadata) {
            return updateAttributeMetadata(etAttrId, metadata).then(data => {
                this.updateAttributeMetadata(entityTypeId, attributeId, etAttrId, data);
            });
        },
        externalAttributeValueDeleted(entityId, attributeId) {
            this.receivedEntityData[entityId] = {};
            this.receivedEntityData[entityId][attributeId] = {};
        },
        externalAttributeValueUpdated(entityId, attributeValue, value) {
            const attributeId = attributeValue.attribute_id;
            this.receivedEntityData[entityId] = {};
            this.receivedEntityData[entityId][attributeId] = attributeValue;
            this.receivedEntityData[entityId][attributeId].value = value;
        },
        updateEntityData(entityId, updatedValues, patchedData, removedData) {
            const entity = this.getEntity(entityId);
            for(let k in updatedValues) {
                // when attribute value is set empty, delete whole attribute
                if(!updatedValues[k] && updatedValues[k] != false) {
                    entity.data[k] = {};
                } else {
                    // if no id exists, this data is added
                    if(!entity.data[k].id) {
                        entity.data[k] = patchedData[k];
                        entity.data[k].value = updatedValues[k];
                    } else {
                        entity.data[k].value = updatedValues[k];
                    }
                }
            }

            // Remove the data from the entity.
            // We need to do this as the 'replace', 'add' 'remove'
            // operations are calculated based on this value.
            for(const attributeId in removedData) {
                if(entity.data[attributeId]) {
                    this.entity.data[attributeId].id = null;
                    if(sync) {
                        this.selectedEntity.data[attributeId].id = null;
                    }
                }
            }
        },
        updateEntityDataModerations(entityId, attributeIds, state = null) {
            const entity = this.getEntity(entityId);
            for(let i = 0; i < attributeIds.length; i++) {
                const curr = attributeIds[i];
                entity.data[curr].moderation_state = state;
            }
        },
        async patchEntityDataModerations(action, entityId, attributeId, overwriteValue) {
            return handleModeration(action, entityId, attributeId, overwriteValue).then(data => {
                this.updateEntityDataModerations(entityId, [attributeId]);
                if(overwriteValue) {
                    const updateData = {
                        [attributeId]: overwriteValue,
                    };
                    this.updateEntityData(entityId, updateData);
                }
                return data;
            });
        },
        async addComment(entityId, comment, { replyTo = null } = {}) {
            const entity = this.getEntity(entityId);
            if(replyTo) {
                const op = entity.comments.find(c => c.id == replyTo);
                if(op.replies) {
                    op.replies.push(comment);
                }
                op.replies_count++;
            } else {
                if(!entity.comments) {
                    entity.comments = [];
                }
                // We dont need to add the comment manually
                // as it is broadcasted via websocket
                entity.comments.push(comment);
                entity.comments_count++;
            }

            if(this.selectedEntity) {
                this.selectedEntity.comments = entity.comments;
            }
        },
        async updateComment(entityId, comment, { replyTo = null } = {}) {
            const entity = this.getEntity(entityId);
            let editedComment = getEditedComment(entity, comment, replyTo);
            if(editedComment) {
                editedComment.content = comment.content;
                editedComment.updated_at = comment.updated_at;

            }
        },
        async deleteComment(entityId, comment, { replyTo = null } = {}) {
            const entity = this.getEntity(entityId);
            let editedComment = getEditedComment(entity, comment, replyTo);
            if(editedComment) {
                editedComment.content = null;
                editedComment.updated_at = Date();
                editedComment.deleted_at = Date();
            }
        },
        async setById(entityId) {
            let entity = this.entities[entityId];
            if(!entity) {
                const ids = await getEntityParentIds(entityId);
                await openPath(ids);
                entity = this.entities[entityId];
            }
            if(!entity.parentIds) {
                const parentMetadata = await getEntityParentMetadata(entityId);
                this.entities[entityId].parentIds = parentMetadata.parentIds;
                this.entities[entityId].parentNames = parentMetadata.parentNames;
                this.entities[entityId].attributeLinks = parentMetadata.attributeLinks;
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
        async addEntityType(entityType) {
            return addEntityType(entityType).then(data => {
                const {
                    attributes,
                    ...typeData
                } = data;
                handleAddEntityType(this, typeData, attributes);
            });
        },
        async duplicateEntityType(entityTypeId) {
            return duplicateEntityType(entityTypeId).then(data => {
                handleAddEntityType(this, data, this.getEntityTypeAttributes(entityTypeId));
            });
        },
        async updateEntityType(id, props) {
            return patchEntityType(id, props).then(data => {
                const entityType = this.entityTypes[id];
                const values = only(data, ['thesaurus_url', 'updated_at', 'is_root', 'sub_entity_types']);
                for(let k in values) {
                    entityType[k] = values[k];
                }
            });
        },
        async deleteEntityType(id) {
            return deleteEntityType(id).then(_ => {
                delete this.entityTypes[id];
                delete this.entityTypeAttributes[id];
            });
        },
        async addEntityTypeAttribute(entityTypeId, attributeId, rank) {
            return addEntityTypeAttribute(entityTypeId, attributeId, rank).then(data => {
                const relation = data.attribute;
                delete data.attribute;

                const mergedData = {
                    ...data,
                    ...relation,
                    entity_attribute_id: data.id,
                    pivot: {
                        id: data.id,
                        entity_type_id: data.entity_type_id,
                        attribute_id: data.attribute_id,
                        position: data.position,
                        depends_on: data.depends_on,
                        metadata: data.metadata,
                    },
                };

                const attributes = this.getEntityTypeAttributes(mergedData.entity_type_id);
                attributes.splice(mergedData.position - 1, 0, mergedData);
                for(let i = mergedData.position; i < attributes.length; i++) {
                    if(attributes[i].position) {
                        attributes[i].position++;
                    } else if(attributes[i]?.pivot?.position) {
                        attributes[i].pivot.position++;
                    }
                }
                useAttributeStore().increaseEntityTypeCounter(mergedData.pivot.attribute_id);
                return mergedData;
            });
        },
        async patchAttribute(entityId, attributeId, apiData) {
            return patchAttribute.then(data => {
                const updatedValues = {
                    [attributeId]: data,
                };
                this.updateEntityData(entityId, updatedValues, updatedValues, {});
                return data;
            });
        },
        async patchAttributes(entityId, patchData, dirtyValues, moderations) {
            const moderated = useUserStore().getUserModerated;
            return patchAttributes(entityId, patchData).then(data => {
                this.update(data.entity);
                this.updateEntityData(entityId, dirtyValues, data.added_attributes, data.removed_attributes);
                if(moderated) {
                    this.updateEntityDataModerations(entityId, moderations, 'pending');
                }
                return data;
            });
        },
        async removeEntityTypeAttribute(id, entityTypeId) {
            return removeEntityTypeAttribute(id).then(_ => {
                const attributes = this.getEntityTypeAttributes(entityTypeId);
                const idx = attributes.findIndex(a => a.pivot.id == id);
                if(idx > -1) {
                    const aid = attributes[idx].attribute_id;
                    attributes.splice(idx, 1);
                    useAttributeStore().decreaseEntityTypeCounter(aid);
                }
                // TODO was: position++; what is correct?
                for(let i = idx; i < attributes.length; i++) {
                    if(attributes[i].position) {
                        attributes[i].position--;
                    } else if(attributes[i]?.pivot?.position) {
                        attributes[i].pivot.position--;
                    }
                }
            });
        },
        async reorderAttributes(entityTypeId, attributeId, from, to) {
            if(from == to) {
                return;
            }
            const attributes = this.getEntityTypeAttributes(entityTypeId);
            // Already added
            if(attributes.length < to && attributes[to].id == attributeId) {
                return;
            }
            // Return if moved attribute does not match
            if(attributes[from].id != attributeId) {
                return;
            }
            const rank = to + 1;
            return reorderEntityAttributes(entityTypeId, attributeId, rank).then(_ => {
                attributes[from].position = rank;
                const movedAttrs = attributes.splice(from, 1);
                attributes.splice(to, 0, ...movedAttrs);
                if(from < to) {
                    for(let i = from; i < to; i++) {
                        if(attributes[i].position) {
                            attributes[i].position++;
                        } else if(attributes[i].pivot && attributes[i].pivot.position) {
                            attributes[i].pivot.position++;
                        }
                    }
                } else {
                    for(let i = to + 1; i <= from; i++) {
                        if(attributes[i].position) {
                            attributes[i].position--;
                        } else if(attributes[i].pivot && attributes[i].pivot.position) {
                            attributes[i].pivot.position--;
                        }
                    }
                }
            });
        },
        async updateDependency(entityTypeId, attributeId, dependency) {
            return updateAttributeDependency(entityTypeId, attributeId, dependency).then(response => {
                const attributes = this.getEntityTypeAttributes(entityTypeId);
                const attribute = attributes.find(a => a.id == attributeId);
                if(attribute) {
                    attribute.pivot.depends_on = response.data;
                }
            });
        },
        initialize(topEntities) {
            this.backup = {};
            this.entities = {};
            this.tree = [];
            topEntities.forEach(entity => {
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
        removeAttributeFromEntityTypes: state => attributeId => {
            for(let k in this.entityTypeAttributes) {
                const curr = this.entityTypeAttributes[k];
                const idx = curr.findIndex(a => a.id == attributeId);
                if(idx > -1) {
                    curr.splice(idx, 1);
                }
            }
        },
        sortTree(sortOpts) {
            sortTree(sortOpts.by, sortOpts.dir, this.tree);
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
        },
        setTreeSelectionMode(data) {
            this.treeSelectionMode = data;

            if(!this.treeSelectionMode) {
                this.treeSelection = {};
                this.treeSelectionTypeIds = [];
            }
        },
        toggleTreeSelectionMode() {
            this.setTreeSelectionMode(!this.treeSelectionMode);
        },
        addToTreeSelection(data) {
            const addPossible = this.hasIntersectionWithEntityAttributes(data.value.entity_type_id, this.treeSelectionTypeIds);
            if(addPossible || this.treeSelectionTypeIds.length == 0) {
                this.treeSelection[data.id] = data.value;

                this.treeSelectionTypeIds = [];
                this.treeSelectionTypeIds = updateSelectionTypeIdList(this.treeSelection);
            }
        },
        removeFromTreeSelection(id) {
            delete this.treeSelection[id];

            this.treeSelectionTypeIds = [];
            this.treeSelectionTypeIds = updateSelectionTypeIdList(this.treeSelection);
        },
        async search(query){
            return searchEntity(query);
        }
    },
});

export default useEntityStore;