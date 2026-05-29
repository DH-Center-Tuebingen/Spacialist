import {
    default as http
} from '@/bootstrap/http.js';

export async function upload(file) {
    const formData = new FormData();
    formData.append('file', file);

    return $httpQueue.add(
        () => http.post(`/plugin`, formData).then(response => response.data)
    );
}

export async function install(id) {
    return $httpQueue.add(
        () => http.get(`/plugin/${id}`).then(response => response.data)
    );
}

export async function update(id) {
    return $httpQueue.add(
        () => http.patch(`/plugin/${id}`).then(response => response.data)
    );
}

export async function uninstall(id) {
    return $httpQueue.add(
        () => http.delete(`/plugin/${id}`).then(response => response.data)
    );
}

export async function refresh() {
    return $httpQueue.add(
        () => http.post(`/plugin/refresh`).then(response => response.data)
    );
}

export async function refreshInfo(id) {
    return $httpQueue.add(
        () => http.post(`/plugin/refresh_info/${id}`).then(response => response.data)
    );
}

export async function remove(id) {
    return $httpQueue.add(
        () => http.delete(`/plugin/remove/${id}`).then(response => response.data)
    );
}

export async function publishScript(id) {
    return $httpQueue.add(
        () => http.post(`/plugin/${id}/publish_script`).then(response => response.data)
    );
}

export async function getChangelog(id) {
    return $httpQueue.add(
        () => http.get(`/plugin/${id}/changelog`).then(response => response.data)
    );
}