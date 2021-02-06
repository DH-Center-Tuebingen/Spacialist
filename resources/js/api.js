import store from './bootstrap/store.js';
import auth from './bootstrap/auth.js';

export async function fetchUsers() {
    await $httpQueue.add(() => $http.get('user').then(response => {
        store.dispatch('setUsers', {
            active: response.data.users,
            deleted: response.data.deleted_users || []
        });
    }));
    await $httpQueue.add(() => $http.get('role').then(response => {
        store.dispatch('setRoles', {
            roles: response.data.roles,
            permissions: response.data.permissions,
        });
    }));
}

export async function fetchTopEntities() {
    await $httpQueue.add(() => $http.get('/entity/top').then(response => {
        store.dispatch('setInitialEntities', response.data);
    }));
};

export async function fetchAttributes() {
    return await $httpQueue.add(() => $http.get('editor/dm/attribute').then(response => {
        store.dispatch('setAttributes', response.data);
    }));
}

export async function fetchBibliography() {
    await $httpQueue.add(() => $http.get('bibliography').then(response => {
        store.dispatch('setBibliography', response.data);
    }));
};

export async function fetchPreData(locale) {
    await $httpQueue.add(() => $http.get('pre').then(response => {
        store.commit('setConcepts', response.data.concepts);
        store.dispatch('setEntityTypes', response.data.entityTypes);
        store.commit('setPreferences', response.data.preferences);
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

export async function getEntityComments(id) {
    return fetchComments(id, 'entity');
};

export async function getAttributeValueComments(id) {
    return fetchComments(id, 'attribute_value');
};

export async function getEntityData(id) {
    return await $httpQueue.add(
        () => $http.get(`/entity/${id}/data`)
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
        () => $http.get(`/editor/entity_type/${id}/attribute`)
    )
}

export async function updateEntityTypeRelation(entityType) {
    const id = entityType.id;
    const data = {
        'is_root': entityType.is_root || false,
        'sub_entity_types': entityType.sub_entity_types.map(t => t.id),
    };
    return await $httpQueue.add(() => $http.post(`/editor/dm/${id}/relation`, data).then(response => response.data));
};

export async function setUserAvatar(id, file) {
    let formData = new FormData();
    formData.append('file', file);
    return await $httpQueue.add(
        () => $http.post(`user/${id}/avatar`, formData).then(response => response.data)
    );
};

export async function deleteUserAvatar(id) {
    return await $httpQueue.add(
        () => $http.delete(`user/${id}/avatar`).then(response => response.data)
    );
};

async function fetchComments(id, type) {
    return $httpQueue.add(() => $http.get(`/comment/resource/${id}?r=${type}`).then(response => response.data).catch(error => error));
}