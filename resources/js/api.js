import store from './bootstrap/store.js';
import auth from './bootstrap/auth.js';

export async function fetchUsers() {
    await $httpQueue.add(() => $http.get('user').then(response => {
        store.dispatch('setUsers', {
            active: response.data.users,
            deleted: response.data.deleted_users || []
        });
        store.dispatch('setRoles', response.data.roles);
    }));
}

export async function fetchTopEntities() {
    await $httpQueue.add(() => $http.get('/entity/top').then(response => {
        store.dispatch('setInitialEntities', response.data);
    }));
};

export async function fetchPreData(locale) {
    await $httpQueue.add(() => $http.get('pre').then(response => {
        store.commit('setConcepts', response.data.concepts);
        store.commit('setEntityTypes', response.data.entityTypes);
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

export async function getEntityData(id)  {
    return await $httpQueue.add(
        () => $http.get(`/entity/${id}/data`)
        .then(response => {
            if(response.data instanceof Array) {
                response.data = {};
            }
            return response.data;
        })
    );
}