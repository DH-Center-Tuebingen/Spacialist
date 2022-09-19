import {
    default as http,
} from '@/bootstrap/http.js';

export async function fetchAccessGroups(withPlugins = true) {
    let url = `/access/groups`;
    if(withPlugins) {
        url += `?plugins=true`
    }
    return await $httpQueue.add(() => http.get(url).then(response => response.data));
}