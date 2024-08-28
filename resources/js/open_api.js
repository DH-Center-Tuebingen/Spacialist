import {
    open as http,
} from '@/bootstrap/http.js';

export async function fetchGlobals() {
    return $httpQueue.add(
        () => http.get('/global').then(response => response.data)
    );
}

export async function fetchEntityTypes() {
    return $httpQueue.add(
        () => http.get('/types').then(response => response.data)
    );
}

export async function fetchAttributes(entityTypeId = null, countData = false) {
    let url = '/attributes';
    if(entityTypeId) {
        url += `?entity_type=${entityTypeId}`;
        if(countData) {
            url += `&with_data=true`;
        }
    }

    return $httpQueue.add(
        () => http.get(url).then(response => response.data)
    );
}

export async function getEntity(eid) {
    return $httpQueue.add(
        () => http.get(`/entity/${eid}`).then(response => response.data)
    );
}

export async function getEntityData(eid) {
    return $httpQueue.add(
        () => http.get(`/entity/${eid}/data`).then(response => response.data)
    );
}

export async function getFilterResults(entityTypeIds, attributeIds, page = 1) {
    const data = {
        types: entityTypeIds,
        attributes: attributeIds,
    };

    return $httpQueue.add(
        () => http.post(`/result?page=${page}`, data).then(response => response.data)
    );
}

export async function getFilterResultsForType(entityTypeId, filters = [], page = 1, or = false) {
    const data = {
        filters: filters,
        or: or,
    };

    return $httpQueue.add(
        () => http.post(`/result/by_type/${entityTypeId}?page=${page}`, data).then(response => response.data)
    );
}