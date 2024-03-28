import {
    default as http,
    external,
} from '@/bootstrap/http.js';
import store from '@/bootstrap/store.js';
import auth from '@/bootstrap/auth.js';
import {
    only,
    simpleResourceType,
    getEntityTypeAttributes as storedEntityTypeAttributes,
} from '@/helpers/helpers.js';

// GET AND STORE (FETCH)
export async function fetchVersion() {
    await $httpQueue.add(() => http.get('/version').then(response => {
        store.dispatch('setVersion', response.data);
    }));
}

export async function fetchPlugins() {
    await $httpQueue.add(
        () => http.get('/plugin').then(response => {
            store.dispatch('setPlugins', response.data);
        })
    );
}

export async function uploadPlugin(file) {
    const formData = new FormData();
    formData.append('file', file);

    return $httpQueue.add(
        () => http.post(`/plugin`, formData).then(response => {
            store.dispatch('addPlugin', response.data);
        })
    );
}

export async function installPlugin(id) {
    return $httpQueue.add(
        () => http.get(`/plugin/${id}`).then(response => {
            const plugin = response.data.plugin;
            store.dispatch('updatePlugin', {
                plugin_id: id,
                properties: {
                    installed_at: plugin.installed_at,
                    updated_at: plugin.updated_at,
                },
            });
            return response.data;
        })
    );
}

export async function updatePlugin(id) {
    return $httpQueue.add(
        () => http.patch(`/plugin/${id}`).then(response => {
            store.dispatch('updatePlugin', {
                plugin_id: id,
                properties: {
                    installed_at: response.data.installed_at,
                    updated_at: response.data.updated_at,
                    version: response.data.version,
                    update_available: false,
                },
            });
        })
    );
}

export async function uninstallPlugin(id) {
    return $httpQueue.add(
        () => http.delete(`/plugin/${id}`).then(response => {
            store.dispatch('updatePlugin', {
                plugin_id: id,
                uninstalled: true,
                properties: {
                    installed_at: null,
                    updated_at: response.data.plugin.updated_at,
                },
            });
            return response.data;
        })
    );
}

export async function removePlugin(id) {
    return $httpQueue.add(
        () => http.delete(`/plugin/remove/${id}`).then(response => {
            store.dispatch('updatePlugin', {
                plugin_id: id,
                deleted: true,
            });
            return response.data;
        })
    );
}

export async function fetchUsers() {
    store.commit('setUser', auth.user());
    await $httpQueue.add(() => http.get('user').then(response => {
        store.dispatch('setUsers', {
            active: response.data.users,
            deleted: response.data.deleted_users || []
        });
    }));
    await $httpQueue.add(() => http.get('role').then(response => {
        store.dispatch('setRoles', {
            roles: response.data.roles,
            permissions: response.data.permissions,
            presets: response.data.presets,
        });
    }));
}

export async function fetchTopEntities() {
    await $httpQueue.add(() => http.get('/entity/top').then(response => {
        store.dispatch('setInitialEntities', response.data);
    }));
}

export async function fetchAttributes() {
    return await $httpQueue.add(() => http.get('editor/dm/attribute').then(response => {
        store.dispatch('setAttributes', response.data);
    }));
}

export async function fetchBibliography() {
    await $httpQueue.add(() => http.get('bibliography').then(response => {
        store.dispatch('setBibliography', response.data);
    }));
}

export async function fetchTags() {
    await $httpQueue.add(() => http.get('tag').then(response => {
        store.dispatch('setTags', response.data);
    }));
}

export async function fetchPreData(locale) {
    return $httpQueue.add(() => http.get('pre').then(response => {
        store.commit('setConcepts', response.data.concepts);
        store.dispatch('setEntityTypes', response.data.entityTypes);
        store.commit('setPreferences', response.data.preferences);
        store.commit('setSystemPreferences', response.data.system_preferences);
        store.dispatch('setColorSets', response.data.colorsets);
        store.dispatch('setAnalysis', response.data.analysis);

        if(auth.ready()) {
            auth.load().then(_ => {
                locale.value = store.getters.preferenceByKey('prefs.gui-language');
            });
        } else {
            locale.value = store.getters.preferenceByKey('prefs.gui-language');
        }
    }));
}

export async function fetchGeometryTypes() {
    return $httpQueue.add(
        () => http.get('editor/dm/geometry').then(response => {
            let geom = [];
            for(let i = 0; i < response.data.length; i++) {
                const g = response.data[i];
                geom.push({
                    label: g,
                    key: g.toLowerCase(),
                });
            }
            geom.push({
                label: 'Any',
                key: 'any'
            });
            store.dispatch('setGeometryTypes', geom);
        })
    );
}

export async function fetchAttributeTypes() {
    return $httpQueue.add(
        () => http.get('/editor/dm/attribute_types').then(response => {
            store.dispatch('setAttributeTypes', response.data);
        })
    );
}

// GET
export async function getEntityComments(id) {
    return fetchComments(id, 'entity');
}

export async function getAttributeValueComments(eid, aid) {
    return fetchComments(eid, 'attribute_value', aid);
}

export async function getCommentReplies(cid, endpoint = '/comment/{cid}/reply') {
    endpoint = endpoint.replaceAll('{cid}', cid);

    return $httpQueue.add(
        () => http.get(endpoint).then(response => response.data)
    );
}

export async function getEntityParentIds(id) {
    return await $httpQueue.add(
        () => http.get(`/entity/${id}/parentIds`)
            .then(response => {
                return response.data;
            })
    );
}

export async function getEntityData(id) {
    return await $httpQueue.add(
        () => http.get(`/entity/${id}/data`)
            .then(response => {
                // PHP returns Array if it is empty
                if(response.data instanceof Array) {
                    response.data = {};
                }
                return response.data;
            })
    );
}

export async function getEntityReferences(id) {
    return await $httpQueue.add(
        () => http.get(`/entity/${id}/reference`)
            .then(response => {
                // PHP returns Array if it is empty
                if(response.data instanceof Array) {
                    response.data = {};
                }
                return response.data;
            })
    );
}

export async function getEntityTypeOccurrenceCount(id) {
    return $httpQueue.add(
        () => http.get(`/editor/dm/entity_type/occurrence_count/${id}`).then(response => response.data)
    );
}

export async function getAttributeOccurrenceCount(aid, etid) {
    let endpoint = `/editor/dm/attribute/occurrence_count/${aid}`;
    if(!!etid) {
        endpoint += `/${etid}`;
    }
    return $httpQueue.add(
        () => http.get(endpoint).then(response => response.data)
    );
}

async function fetchComments(id, type, aid = null) {
    let endpoint = `/comment/resource/${id}?r=${type}`;
    if(!!aid) {
        endpoint = `${endpoint}&aid=${aid}`;
    }
    return $httpQueue.add(() => http.get(endpoint).then(response => response.data).catch(error => { throw error; }));
}

export async function getBibtexFile() {
    return await $httpQueue.add(
        () => http.get('bibliography/export').then(response => response.data)
    );
}

export async function getIconClassInfo(iconClass) {
    return external.get(`http://iconclass.org/${iconClass}.json`, {
        crossdomain: true
    }).then(response => response.data).catch(e => e);
}

export async function getRismInfo(rismId) {
    return external.get(`https://muscat.rism.info/sru/sources?operation=searchRetrieve&version=1.1&query=id=${rismId}&maximumRecords=1`, {
        crossdomain: true
    }).then(response => response.data).catch(e => e);
}

export async function fetchChildren(id) {
    return $httpQueue.add(
        () => http.get(`/entity/byParent/${id}`).then(response => response.data)
    );
}

export async function getMapLayers(includeEntityLayers) {
    let url = `map/layer`;
    if(!includeEntityLayers) {
        url += `?basic=1`;
    }
    return $httpQueue.add(
        () => http.get(url).then(response => response.data)
    );
}

export async function moveMapLayer(id, neighborId) {
    const data = {
        neighbor: neighborId,
    };
    return $httpQueue.add(
        () => http.patch(`/map/layer/${id}/switch`, data).then(response => response.data)
    );
}

export async function changeMapLayerClass(id) {
    return $httpQueue.add(
        () => http.patch(`/map/layer/${id}/move`).then(response => response.data)
    );
}

export async function getMapProjection(srid) {
    return $httpQueue.add(
        () => http.get(`map/epsg/${srid}`).then(response => response.data)
    );
}

// POST
export async function addUser(user) {
    const data = only(user, ['name', 'nickname', 'email', 'password']);
    return $httpQueue.add(
        () => http.post('user', data).then(response => response.data)
    );
}

export async function addRole(role) {
    const data = only(role, ['name', 'display_name', 'description', 'derived_from']);
    return $httpQueue.add(
        () => http.post('role', data).then(response => response.data)
    );
}

export async function resetUserPassword(uid, password) {
    const data = {
        password: password,
    };
    return $httpQueue.add(
        () => http.patch(`user/${uid}/password/reset`, data).then(response => response.data)
    );
}

export async function confirmUserPassword(uid, password = null) {
    const data = !!password ? { password: password } : {};
    return $httpQueue.add(
        () => http.patch(`user/${uid}/password/confirm`, data).then(response => response.data)
    );
}

export async function updateEntityTypeRelation(etid, values) {
    const data = only(values, ['is_root', 'sub_entity_types']);
    const apiData = {...data};
    if(data.sub_entity_types) {
        apiData.sub_entity_types = data.sub_entity_types.map(t => t.id);
    }

    return await $httpQueue.add(
        () => http.post(`/editor/dm/${etid}/relation`, apiData).then(response => {
            store.dispatch('updateEntityType', {
                ...data,
                id: etid,
            });

            return response.data;
        })
    );
}

export async function postComment(content, resource, replyTo = null, metadata = null, endpoint = '/comment') {
    let data = {
        content: content,
        resource_id: resource.id,
        resource_type: simpleResourceType(resource.type),
    };
    if(replyTo) {
        data.reply_to = replyTo;
    }
    if(metadata) {
        data.metadata = metadata;
    }
    if(!content) {
        if(!data.metadata) {
            data.metadata = {};
        }
        data.metadata.is_empty = true;
    }

    return $httpQueue.add(
        () => http.post(endpoint, data).then(response => response.data)
    );
}

export async function editComment(cid, content, endpoint = '/comment/{cid}') {
    endpoint = endpoint.replaceAll('{cid}', cid);

    const data = {
        content: content,
    };

    return $httpQueue.add(
        () => http.patch(endpoint, data).then(response => response.data)
    );
}

export async function addOrUpdateBibliographyItem(item, file) {
    const data = new FormData();
    for(let k in item.fields) {
        data.append(k, item.fields[k]);
    }
    data.append('type', item.type.name);
    if(file) {
        if(file == 'delete') {
            data.append('delete_file', true);
        } else {
            data.append('file', file);
        }
    }

    if(item.id) {
        return $httpQueue.add(
            () => http.post(`bibliography/${item.id}`, data).then(response => response.data)
        );
    } else {
        return $httpQueue.add(
            () => http.post('bibliography', data).then(response => response.data)
        );
    }
}

export async function updateBibliography(file) {
    let formData = new FormData();
    formData.append('file', file);
    return await $httpQueue.add(
        () => http.post('bibliography/import', formData).then(response => response.data)
    );
}

export async function setUserAvatar(file) {
    let formData = new FormData();
    formData.append('file', file);
    return await $httpQueue.add(
        () => http.post(`user/avatar`, formData).then(response => response.data)
    );
}

export async function addEntity(entity) {
    const data = {
        entity_type_id: entity.type_id,
        name: entity.name,
    };
    if(!!entity.parent_id) {
        data.root_entity_id = entity.parent_id;
    }
    return $httpQueue.add(
        () => http.post(`/entity`, data).then(response => response.data)
    );
}

export async function duplicateEntity(entity) {
    const data = {};
    return $httpQueue.add(
        () => http.post(`/entity/${entity.id}/duplicate`, data).then(response => response.data)
    );
}

export async function importEntityData(data) {
    return $httpQueue.add(
        () => http.post(`/entity/import`, data).then(response => response.data).catch(e => { throw e; })
    );
}

export async function addEntityType(et) {
    const data = {
        concept_url: et.label.concept_url,
        is_root: et.is_root,
        geometry_type: et.geometryType.label,
    };
    return $httpQueue.add(
        () => http.post(`/editor/dm/entity_type`, data).then(response => response.data)
    );
}

export async function duplicateEntityType(id) {
    return $httpQueue.add(
        () => http.post(`/editor/dm/entity_type/${id}/duplicate`).then(response => response.data)
    );
}

export async function addAttribute(attribute) {
    const data = {
        label_id: attribute.label.id,
        datatype: attribute.type,
        recursive: !!attribute.recursive,
    };
    if(attribute.rootLabel) {
        data.root_id = attribute.rootLabel.id;
    }
    if(attribute.rootAttributeLabel) {
        data.root_attribute_id = attribute.rootAttributeLabel.id;
    }
    if(attribute.restrictedTypes) {
        data.restricted_types = attribute.restrictedTypes.map(t => t.id);
    }
    if(attribute.columns && attribute.columns.length > 0) {
        data.columns = attribute.columns.map(c => {
            const mappedC = { ...c };
            if(mappedC.label) {
                mappedC.label_id = mappedC.label.id;
                delete mappedC.label;
            }
            if(mappedC.rootLabel) {
                mappedC.root_id = mappedC.rootLabel.id;
                delete mappedC.rootLabel;
            }
            mappedC.datatype = mappedC.type;
            delete mappedC.type;
            return mappedC;
        });
    }
    if(attribute.textContent) {
        data.text = attribute.textContent;
    }

    return $httpQueue.add(
        () => http.post(`/editor/dm/attribute`, data).then(response => response.data)
    );
}

export async function addEntityTypeAttribute(etid, aid, to) {
    const attrs = storedEntityTypeAttributes(etid);
    // Already added
    if(attrs.length < to && attrs[to].id == aid) {
        return;
    }

    const rank = to + 1;
    const data = {
        attribute_id: aid,
        position: rank,
    };

    return $httpQueue.add(
        () => http.post(`/editor/dm/entity_type/${etid}/attribute`, data).then(response => {
            const relation = response.data.attribute;
            delete response.data.attribute;

            const data = {
                ...response.data,
                ...relation,
                entity_attribute_id: response.data.id,
                pivot: {
                    id: response.data.id,
                    entity_type_id: response.data.entity_type_id,
                    attribute_id: response.data.attribute_id,
                    position: response.data.position,
                    depends_on: response.data.depends_on,
                    metadata: response.data.metadata,
                },
            };

            store.dispatch('addEntityTypeAttribute', data);
            return new Promise(r => r(data));
        })
    );
}

export async function addReference(eid, aid, url, data) {
    $httpQueue.add(
        () => http.post(`/entity/${eid}/reference/${aid}`, data).then(response => {
            store.dispatch('addReference', {
                ...response.data,
                attribute_url: url,
            });
            return response.data;
        })
    );
}

export async function getFilteredActivity(pageUrl, payload) {
    pageUrl = pageUrl || 'activity';
    return $httpQueue.add(
        () => http.post(pageUrl, payload).then(response => {
            const pagination = response.data;
            const data = pagination.data;
            delete pagination.data;
            return {
                data: data,
                pagination: pagination,
            };
        })
    );
}

// PATCH
export async function patchEntityName(eid, name) {
    const data = {
        name: name,
    };
    return $httpQueue.add(
        () => http.patch(`entity/${eid}/name`, data).then(response => response.data)
    );
}

export async function patchAttribute(entityId, attributeId, data) {
    return $httpQueue.add(
        () => http.patch(`/entity/${entityId}/attribute/${attributeId}`, data).then(response => response.data)
    );
}

export async function patchAttributes(entityId, data) {
    return $httpQueue.add(
        () => http.patch(`/entity/${entityId}/attributes`, data).then(response => response.data).catch(error => { throw error; })
    );
}

export async function handleModeration(modAction, entity_id, attribute_id, overwrite_value = null) {
    const data = {
        action: modAction,
    };

    if(overwrite_value) {
        data.value = overwrite_value;
    }

    return $httpQueue.add(
        () => http.patch(`/entity/${entity_id}/attribute/${attribute_id}/moderate`, data).then(response => {
            store.dispatch('updateEntityDataModerations', {
                entity_id: entity_id,
                attribute_ids: [attribute_id],
                state: null,
            });
            if(overwrite_value) {
                store.dispatch('updateEntityData', {
                    eid: entity_id,
                    data: {
                        [attribute_id]: overwrite_value,
                    },
                });
            }
            return response.data;
        }).catch(error => { throw error; })
    );
}

export async function multieditAttributes(entityIds, entries) {
    const data = {
        entity_ids: entityIds,
        entries: entries,
    };
    return $httpQueue.add(
        () => http.patch(`/entity/multiedit`, data).then(response => {
            return response.data;
        }).catch(error => {
            throw error;
        })
    );
}

export async function moveEntity(entityId, parentId = null, rank = null) {
    const data = {
        parent_id: parentId,
    };
    if(!!rank) {
        data.rank = rank;
    } else {
        data.rank = 0;
        data.to_end = true;
    }

    return $httpQueue.add(
        () => http.patch(`/entity/${entityId}/rank`, data).then(response => {
            store.dispatch('moveEntity', {
                entity_id: entityId,
                parent_id: parentId,
                rank: data.rank,
                to_end: data.to_end,
            });
            return response.data;
        })
    );
}

export async function patchEntityType(etid, updatedProps) {
    // If no props updated, do nothing
    if(Object.keys(updatedProps).length < 1) {
        return;
    }

    // Currently only thesaurus_url is allowed to be changed
    const data = {
        data: only(updatedProps, ['thesaurus_url'])
    };

    return $httpQueue.add(
        () => http.patch(`/editor/dm/entity_type/${etid}`, data).then(response => {
            store.dispatch('updateEntityType', {
                ...data.data,
                id: etid,
                updated_at: response.data.updated_at,
            });
            return response.data;
        })
    );
}

export async function reorderEntityAttributes(etid, aid, from, to) {
    if(from == to) {
        return;
    }
    const attrs = storedEntityTypeAttributes(etid);
    // Return if moved attribute does not match
    if(attrs[from].id != aid) {
        return;
    }

    const rank = to + 1;
    const data = {
        position: rank
    };

    return $httpQueue.add(
        () => http.patch(`/editor/dm/entity_type/${etid}/attribute/${aid}/position`, data).then(response => {
            store.dispatch('reorderAttributes', {
                rank: rank,
                from: from,
                to: to,
                entity_type_id: etid,
                attribute_id: aid,
            });
            return response.data;
        })
    );
}

export async function updateAttributeDependency(etid, aid, dependency) {
    const data = {};
    if(!!dependency.attribute) {
        data.attribute = dependency.attribute.id;
        data.operator = dependency.operator.label;
        data.value = dependency.value.value || dependency.value;
    }
    return $httpQueue.add(
        () => http.patch(`/editor/dm/entity_type/${etid}/attribute/${aid}/dependency`, data).then(response => {
            const data = Object.values(response.data).length > 0 ? response.data : null;
            store.dispatch('updateDependency', {
                entity_type_id: etid,
                attribute_id: aid,
                data: data,
            });

            return data;
        })
    );
}

export async function updateAttributeMetadata(etid, aid, pivid, data) {
    return $httpQueue.add(
        () => http.patch(`/editor/dm/entity_type/attribute/system/${pivid}`, data).then(response => {
            store.dispatch('updateAttributeMetadata', {
                entity_type_id: etid,
                attribute_id: aid,
                id: pivid,
                data: response.data,
            });
            return response.data;
        })
    );
}

export async function patchPreferences(data, uid) {
    const endpoint = !!uid ? `preference/${uid}` : 'preference';
    return await http.patch(endpoint, data).then(response => response.data);
}

export async function reactivateUser(uid) {
    return $httpQueue.add(
        () => http.patch(`user/restore/${uid}`).then(response => response.data)
    );
}

export async function patchUserData(uid, data) {
    return $httpQueue.add(
        () => http.patch(`user/${uid}`, data).then(response => response.data)
    );
}

export async function patchRoleData(rid, data) {
    return $httpQueue.add(
        () => http.patch(`role/${rid}`, data).then(response => response.data)
    );
}

export async function updateReference(id, eid, url, data) {
    $httpQueue.add(
        () => http.patch(`/entity/reference/${id}`, data).then(response => {
            const updData = {
                ...data,
                updated_at: response.data.updated_at,
            };
            store.dispatch('updateReference', {
                reference_id: id,
                entity_id: eid,
                attribute_url: url,
                data: updData,
            });
            return response.data;
        })
    );
}

// DELETE
export async function deactivateUser(id) {
    return $httpQueue.add(
        () => http.delete(`user/${id}`).then(response => response.data)
    );
}

export async function deleteRole(id) {
    return $httpQueue.add(
        () => http.delete(`role/${id}`).then(response => response.data)
    );
}

export async function deleteUserAvatar() {
    return await $httpQueue.add(
        () => http.delete(`user/avatar`).then(response => response.data)
    );
}

export async function deleteComment(cid, endpoint = '/comment/{cid}') {
    endpoint = endpoint.replaceAll('{cid}', cid);
    return $httpQueue.add(
        () => http.delete(endpoint).then(response => response.data)
    );
}

export async function deleteEntity(eid) {
    return $httpQueue.add(
        () => http.delete(`entity/${eid}`).then(response => response.data)
    );
}

export async function deleteEntityType(etid) {
    return $httpQueue.add(
        () => http.delete(`editor/dm/entity_type/${etid}`).then(response => response.data)
    );
}

export async function removeEntityTypeAttribute(id) {
    return $httpQueue.add(
        () => http.delete(`/editor/dm/entity_type/attribute/${id}`).then(response => response.data)
    );
}

export async function deleteAttribute(aid) {
    return $httpQueue.add(
        () => http.delete(`editor/dm/attribute/${aid}`).then(response => response.data)
    );
}

export async function deleteBibliographyItemFile(id) {
    return await $httpQueue.add(
        () => http.delete(`bibliography/${id}/file`).then(response => response.data)
    );
}

export async function deleteBibliographyItem(id) {
    return $httpQueue.add(
        () => http.delete(`bibliography/${id}`).then(response => response.data)
    );
}

export async function deleteReferenceFromEntity(id, eid, url) {
    return $httpQueue.add(
        () => http.delete(`/entity/reference/${id}`).then(response => {
            store.dispatch('removeReferenceFromEntity', {
                reference_id: id,
                entity_id: eid,
                attribute_url: url,
            });
            return response.data;
        })
    );
}

// SEARCH
export async function searchGlobal(query = '') {
    return $httpQueue.add(
        () => http.get(`search?q=${query}`).then(response => response.data)
    );
}

export async function searchAttribute(query = '') {
    return $httpQueue.add(
        () => http.get(`search/attribute?q=${query}`).then(response => response.data)
    );
}

export async function searchLabel(query = '') {
    return $httpQueue.add(
        () => http.get(`search/label?q=${query}`).then(response => response.data)
    );
}

export async function searchEntity(query = '') {
    return $httpQueue.add(
        () => http.get(`search/entity?q=${query}`).then(response => response.data)
    );
}

export async function searchConceptSelection(cid) {
    return $httpQueue.add(
        () => http.get(`search/selection/${cid}`).then(response => response.data)
    );
}

export async function searchEntityInTypes(query = '', types = []) {
    const typeList = types.join(',');
    return $httpQueue.add(
        () => http.get(`search/entity?q=${query}&t=${typeList}`).then(response => response.data)
    );
}