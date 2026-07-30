import {
    default as http
} from '@/bootstrap/http.js';

/**
 * Uploads a plugin archive to the server.
 *
 * @param {File} file - The plugin archive file to upload.
 * @returns {Promise<{plugin: object, updated: boolean, fromVersion: string|null}>} The plugin record, whether it was an update, and the previous version if applicable.
 */
export async function upload(file) {
    const formData = new FormData();
    formData.append('file', file);

    return $httpQueue.add(
        () => http.post(`/plugin`, formData).then(response => response.data)
    );
}

/**
 * Installs an already uploaded plugin.
 *
 * @param {number|string} id - The ID of the plugin to install.
 * @returns {Promise<{plugin: object, scripts: string[], styles: string[]}>} The installed plugin with its scripts and styles.
 */
export async function install(id) {
    return $httpQueue.add(
        () => http.get(`/plugin/${id}`).then(response => response.data)
    );
}

/**
 * Uninstalls an installed plugin.
 *
 * @param {number|string} id - The ID of the plugin to uninstall.
 * @returns {Promise<{plugin: object, scripts: string[], styles: string[]}>} The uninstalled plugin with its scripts and styles.
 */
export async function uninstall(id) {
    return $httpQueue.add(
        () => http.delete(`/plugin/${id}`).then(response => response.data)
    );
}

/**
 * Rebuilds the plugin cache and returns all plugins with their metadata.
 *
 * @returns {Promise<object[]>} All plugins with their metadata.
 */
export async function refresh() {
    return $httpQueue.add(
        () => http.post(`/plugin/refresh`).then(response => response.data)
    );
}

/**
 * Refreshes the metadata of a single plugin.
 *
 * @param {number|string} id - The ID of the plugin to refresh.
 * @returns {Promise<object>} The plugin with its updated metadata.
 */
export async function refreshInfo(id) {
    return $httpQueue.add(
        () => http.post(`/plugin/refresh_info/${id}`).then(response => response.data)
    );
}

/**
 * Removes an uninstalled plugin from the system entirely.
 *
 * @param {number|string} id - The ID of the plugin to remove.
 * @returns {Promise<{plugin: object, scripts: string[], styles: string[]}>} The scripts and styles that were associated with the removed plugin.
 */
export async function remove(id) {
    return $httpQueue.add(
        () => http.delete(`/plugin/remove/${id}`).then(response => response.data)
    );
}

/**
 * Publishes the script of an installed plugin and returns its public URL.
 *
 * @param {number|string} id - The ID of the plugin whose script should be published.
 * @returns {Promise<string>} The public URL of the published script.
 */
export async function publishScript(id) {
    return $httpQueue.add(
        () => http.post(`/plugin/${id}/publish_script`).then(response => response.data)
    );
}

/**
 * Retrieves the changelog of a plugin.
 *
 * @param {number|string} id - The ID of the plugin.
 * @returns {Promise<string>} The changelog content.
 */
export async function getChangelog(id) {
    return $httpQueue.add(
        () => http.get(`/plugin/${id}/changelog`).then(response => response.data)
    );
}