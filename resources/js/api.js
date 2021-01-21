import store from './bootstrap/store.js';

export async function fetchTopEntities() {
    await $httpQueue.add(() => $http.get('/entity/top').then(response => {
        store.commit('setTopEntities', response.data);
    }))
};