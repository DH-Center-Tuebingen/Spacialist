import {
    default as http,
    external,
    web_http,
} from '@/bootstrap/http.js';
import {
    only,
    simpleResourceType,
} from '@/helpers/helpers.js';

// GET AND STORE (FETCH)
export async function logout() {
    return await $httpQueue.add(() => http.post('/auth/logout'));
}

export async function getCsrfCookie() {
    await $httpQueue.add(() => web_http.get('/sanctum/csrf-cookie').then(response => {
    }));
}

export async function fetchVersion() {
    return await $httpQueue.add(() => http.get('/version').then(response => response.data));
}

export async function fetchPlugins() {
    return await $httpQueue.add(() => http.get('/plugin').then(response => response.data));
}

export async function uploadPlugin(file) {
    const formData = new FormData();
    formData.append('file', file);

    return $httpQueue.add(
        () => http.post(`/plugin`, formData).then(response => response.data)
    );
}

export async function installPlugin(id) {
    return $httpQueue.add(
        () => http.get(`/plugin/${id}`).then(response => response.data)
    );
}

export async function updatePlugin(id) {
    return $httpQueue.add(
        () => http.patch(`/plugin/${id}`).then(response => response.data)
    );
}

export async function uninstallPlugin(id) {
    return $httpQueue.add(
        () => http.delete(`/plugin/${id}`).then(response => response.data)
    );
}

export async function removePlugin(id) {
    return $httpQueue.add(
        () => http.delete(`/plugin/remove/${id}`).then(response => response.data)
    );
}

export async function fetchEntityMetadata(id) {
    return await $httpQueue.add(() => http.get(`entity/${id}/metadata`).then(response => response.data));
}

export async function fetchUser() {
    return await $httpQueue.add(() => http.get('/auth/user').then(response => response.data));
}

export async function fetchUsers() {
    const userData = await $httpQueue.add(() => http.get('user').then(response => response.data));
    const roleData = await $httpQueue.add(() => http.get('role').then(response => response.data));
    return {
        user: userData,
        role: roleData,
    };
}

export async function fetchTopEntities() {
    return await $httpQueue.add(() => http.get('/entity/top').then(response => response.data));
}

export async function fetchAttributes() {
    return await $httpQueue.add(() => http.get('editor/dm/attribute').then(response => response.data));
}

export async function fetchBibliography() {
    return await $httpQueue.add(() => http.get('bibliography').then(response => response.data));
}

export async function fetchTags() {
    return await $httpQueue.add(() => http.get('tag').then(response => response.data));
}

export async function fetchPreData() {
    return $httpQueue.add(() => http.get('pre').then(response => response.data));
}

export async function fetchGeometryTypes() {
    return await $httpQueue.add(
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
            return geom;
        })
    );
}

export async function fetchAttributeTypes() {
    return await $httpQueue.add(() => http.get('/editor/dm/attribute_types').then(response => response.data));
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

export async function getEntityTypeAttributes(id) {
    return await $httpQueue.add(
        () => http.get(`/editor/entity_type/${id}/attribute`)
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

export async function exportBibtexFile(selection) {
    const data = {};
    if(!!selection) {
        data.selection = selection;
    }

    return await $httpQueue.add(
        () => http.post('bibliography/export', data).then(response => response.data)
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
export async function login(credentials) {
    return await $httpQueue.add(() => http.post('/auth/login', credentials).then(response => {
        return response.data;
    }));
}

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

export async function importBibliographyFile(file) {
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
    if(!!entity.rank) {
        data.rank = entity.rank;
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

export async function validateEntityData(data) {
    return $httpQueue.add(
        () => http.post(`/entity/import/validate`, data).then(response => response.data).catch(e => { throw e; })
    );
}

export async function addEntityType(entityType) {
    const data = {
        concept_url: entityType.label.concept_url,
        is_root: entityType.is_root,
        geometry_type: entityType.geometryType.label,
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
        data.columns = attribute.columns.map(column => {
            const mappedColumn = { ...column };
            if(mappedColumn.label) {
                mappedColumn.label_id = mappedColumn.label.id;
                delete mappedColumn.label;
            }
            if(mappedColumn.rootLabel) {
                mappedColumn.root_id = mappedColumn.rootLabel.id;
                delete mappedColumn.rootLabel;
            }
            if(mappedColumn.restrictedTypes) {
                mappedColumn.restricted_types = mappedColumn.restrictedTypes.map(t => t.id);
                delete mappedColumn.restrictedTypes;
            }
            if(mappedColumn.siGroup) {
                mappedColumn.si_base = mappedColumn.siGroup;
                mappedColumn.si_default = mappedColumn.siGroupUnit;
                delete mappedColumn.siGroup;
                delete mappedColumn.siGroupUnit;
            }
            mappedColumn.datatype = mappedColumn.type;
            delete mappedColumn.type;
            return mappedColumn;
        });
    }
    if(attribute.textContent) {
        data.text = attribute.textContent;
    }
    if(attribute.siGroup) {
        data.si_base = attribute.siGroup;
        data.si_default = attribute.siGroupUnit;
    }

    return $httpQueue.add(
        () => http.post(`/editor/dm/attribute`, data).then(response => response.data)
    );
}

export async function addEntityTypeAttribute(etid, aid, rank) {
    const data = {
        attribute_id: aid,
        position: rank,
    };

    return $httpQueue.add(
        () => http.post(`/editor/dm/entity_type/${etid}/attribute`, data).then(response => response.data)
    );
}

export async function addReference(eid, aid, data) {
    return $httpQueue.add(
        () => http.post(`/entity/${eid}/reference/${aid}`, data).then(response => response.data)
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

export async function patchEntityMetadata(eid, metadata) {
    return $httpQueue.add(
        () => http.patch(`entity/${eid}/metadata`, metadata).then(response => response.data).catch(error => { throw error; })
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
        () => http.patch(`/entity/${entity_id}/attribute/${attribute_id}/moderate`, data)
            .then(response => response.data)
            .catch(error => {
                throw error;
            })
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

    return $httpQueue.add(
        () => http.patch(`/entity/${entityId}/rank`, data).then(response => response.data)
    );
}

export async function patchEntityType(etid, updatedProps) {
    const allowedData = only(updatedProps, ['thesaurus_url', 'is_root', 'sub_entity_types']);
    // If no allowed props updated, do nothing
    if(Object.keys(allowedData).length < 1) {
        return;
    }
    const data = {
        data: {...allowedData},
    };

    if(allowedData.sub_entity_types) {
        data.data.sub_entity_types = allowedData.sub_entity_types.map(t => t.id);
    }

    return $httpQueue.add(
        () => http.patch(`/editor/dm/entity_type/${etid}`, data).then(response => response.data)
    );
}

export async function reorderEntityAttributes(etid, aid, position) {
    const data = {
        position: position,
    };

    return $httpQueue.add(
        () => http.patch(`/editor/dm/entity_type/${etid}/attribute/${aid}/position`, data).then(response => response.data)
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
            return Object.values(response.data).length > 0 ? response.data : null;
        })
    );
}

export async function updateAttributeMetadata(pivid, data) {
    return $httpQueue.add(
        () => http.patch(`/editor/dm/entity_type/attribute/system/${pivid}`, data).then(response => response.data)
    );
}

export async function patchPreferences(data, uid) {
    const endpoint = 'preference';
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

export async function updateReference(id, data) {
    return $httpQueue.add(
        () => http.patch(`/entity/reference/${id}`, data).then(response => response.data)
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

export async function deleteReferenceFromEntity(id) {
    return $httpQueue.add(
        () => http.delete(`/entity/reference/${id}`).then(response => response.data)
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

export async function searchEntity(query = '', page = 1) {
    return $httpQueue.add(
        () => http.get(`search/entity?q=${query}&page=${page}`).then(response => response.data)
    );
}

export async function searchConceptSelection(cid) {
    return $httpQueue.add(
        () => http.get(`search/selection/${cid}`).then(response => response.data)
    );
}

export async function searchEntityInTypes(query = '', types = [], page = 1) {
    const typeList = types.join(',');
    return $httpQueue.add(
        () => http.get(`search/entity?q=${query}&t=${typeList}&page=${page}`).then(response => response.data)
    );
}