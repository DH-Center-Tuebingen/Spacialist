import {
    default as http,
    external,
} from './bootstrap/http.js';
import store from './bootstrap/store.js';
import auth from './bootstrap/auth.js';
import {
    simpleResourceType,
} from './helpers/helpers.js';

// GET AND STORE (FETCH)
export async function fetchVersion() {
    await $httpQueue.add(() => http.get('/version').then(response => {
        store.dispatch('setVersion', response.data);
    }));
}

export async function fetchUsers() {
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
        });
    }));
}

export async function fetchTopEntities() {
    await $httpQueue.add(() => http.get('/entity/top').then(response => {
        store.dispatch('setInitialEntities', response.data);
    }));
};

export async function fetchAttributes() {
    return await $httpQueue.add(() => http.get('editor/dm/attribute').then(response => {
        store.dispatch('setAttributes', response.data);
    }));
}

export async function fetchBibliography() {
    await $httpQueue.add(() => http.get('bibliography').then(response => {
        store.dispatch('setBibliography', response.data);
    }));
};

export async function fetchPreData(locale) {
    return $httpQueue.add(() => http.get('pre').then(response => {
        store.commit('setConcepts', response.data.concepts);
        store.dispatch('setEntityTypes', response.data.entityTypes);
        store.commit('setPreferences', response.data.preferences);
        store.commit('setSystemPreferences', response.data.system_preferences);
        store.commit('setUsers', response.data.users);

        store.commit('setUser', auth.user());
        
        if(auth.ready()) {
            auth.load().then(_ => {
                locale.value = store.getters.preferenceByKey('prefs.gui-language');
            });
        } else {
            locale.value = store.getters.preferenceByKey('prefs.gui-language');
        }

        // TODO init spacialist "plugins"
    }));
};

export async function fetchGeometryTypes() {
    return $httpQueue.add(
        () => http.get('editor/dm/geometry').then(response => {
            let geom = [];
            for(let i=0; i<response.data.length; i++) {
                const g = response.data[i];
                geom.push({
                    label: g,
                    key: g.toLowerCase(),
                });
            }
            geom.push({
                label: 'Any',// TODO l18n
                key: 'any'
            });
            store.dispatch('setGeometryTypes', geom);
        })
    );
}

// GET
export async function getEntityComments(id) {
    return fetchComments(id, 'entity');
};

export async function getAttributeValueComments(id) {
    return fetchComments(id, 'attribute_value');
};

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

export async function getEntityTypeAttributes(id) {
    return await $httpQueue.add(
        () => http.get(`/editor/entity_type/${id}/attribute`)
    )
}

export async function getEntityTypeOccurrenceCount(id) {
    return $httpQueue.add(
        () => http.get(`/editor/dm/entity_type/occurrence_count/${id}`).then(response => response.data)
    );
}

async function fetchComments(id, type) {
    return $httpQueue.add(() => http.get(`/comment/resource/${id}?r=${type}`).then(response => response.data).catch(error => error));
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

export async function fetchChildren(id) {
    return $httpQueue.add(
        () => http.get(`/entity/byParent/${id}`).then(response => response.data)
    );
}

// POST
export async function updateEntityTypeRelation(entityType) {
    const id = entityType.id;
    const data = {
        'is_root': entityType.is_root || false,
        'sub_entity_types': entityType.sub_entity_types.map(t => t.id),
    };
    return await $httpQueue.add(
        () => http.post(`/editor/dm/${id}/relation`, data).then(response => response.data)
    );
};

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

    return $httpQueue.add(
        () => http.post(endpoint, data).then(response => response.data)
    );
};

export async function editComment(cid, content, endpoint = '/comment/{cid}') {
    endpoint = endpoint.replaceAll('{cid}', cid);

    const data = {
        content: content,
    };
    
    return $httpQueue.add(
        () => http.patch(endpoint, data).then(response => response.data)
    );
};

export async function getCommentReplies(cid, endpoint = '/comment/{cid}/reply') {
    endpoint = endpoint.replaceAll('{cid}', cid);
    
    return $httpQueue.add(
        () => http.get(endpoint).then(response => response.data)
    );
};

export async function addOrUpdateBibliographyItem(item) {
    let data = {};
    for(let k in item.fields) {
        data[k] = item.fields[k];
    }
    data.type = item.type.name;

    if(item.id) {
        return $httpQueue.add(
            () => http.patch(`bibliography/${item.id}`, data).then(response => response.data)
        );
    } else {
        return $httpQueue.add(
            () => http.post('bibliography', data).then(response => response.data)
        );
    }
};

export async function updateBibliography(file) {
    let formData = new FormData();
    formData.append('file', file);
    return await $httpQueue.add(
        () => http.post('bibliography/import', formData).then(response => response.data)
    );
};

export async function setUserAvatar(id, file) {
    let formData = new FormData();
    formData.append('file', file);
    return await $httpQueue.add(
        () => http.post(`user/${id}/avatar`, formData).then(response => response.data)
    );
};

export async function duplicateEntityType(id) {
    return $httpQueue.add(
        () => http.post(`/editor/dm/entity_type/${id}/duplicate`).then(response => response.data)
    );
};

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
};

// PATCH
export async function patchPreferences(data, uid) {
    const endpoint = !!uid ? `preference/${uid}` : 'preference';
    return await http.patch(endpoint, data).then(response => response.data);
};

export async function patchUserData(uid, data) {
    return $httpQueue.add(
        () => http.patch(`user/${uid}`, data)
    );
};

// DELETE
export async function deleteUserAvatar(id) {
    return await $httpQueue.add(
        () => http.delete(`user/${id}/avatar`).then(response => response.data)
    );
};

export async function deleteComment(cid, endpoint = '/comment/{cid}') {
    endpoint = endpoint.replaceAll('{cid}', cid);
    return $httpQueue.add(
        () => http.delete(endpoint).then(response => response.data)
    );
};

export async function deleteEntityType(etid) {
    return $httpQueue.add(
        () => http.delete(`editor/dm/entity_type/${etid}`).then(response => response.data)
    );
};

export async function deleteBibliographyItem(id) {
    return $httpQueue.add(
        () => http.delete(`bibliography/${id}`).then(response => response.data)
    );
};

// SEARCH
export async function searchGlobal(query = '') {
    return $httpQueue.add(
        () => http.get(`search?q=${query}`).then(response => response.data)
    )
}