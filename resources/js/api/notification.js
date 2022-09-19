import {
    default as http,
} from '@/bootstrap/http.js';

import {
    userNotifications,
    _cloneDeep,
} from '@/helpers/helpers.js';

export async function markAsRead(id, from = userNotifications()) {
    const elem = from.find(elem => elem.id === id)
    if(elem) {
        return await $httpQueue.add(() => http.patch(`notification/read/${id}`).then(response => {
            elem.read_at = Date();
            return response.data;
        }));
    } else {
        return new Promise(r => r(null));
    }
}

export async function markAllAsRead(ids = null, from = userNotifications()) {
    if(!ids) {
        ids = from.map(elem => elem.id);
    }
    const data = {
        ids: ids
    };
    return await $httpQueue.add(() => http.patch(`notification/read/`, data).then(response => {
        let idsC = _cloneDeep(ids);
        from.forEach(elem => {
            const idx = idsC.findIndex(id => id === elem.id);
            if(idx > -1) {
                elem.read_at = Date();
                idsC.splice(idx, 1);
            }
        });
        return response.data;
    }));
}

export async function deleteNotification(id, from = userNotifications()) {
    return await $httpQueue.add(() => http.delete(`notification/${id}`).then(response => {
        const idx = from.findIndex(elem => elem.id === id);
        if(idx > -1) {
            from.splice(idx, 1);
        }
        return response.data;
    }));
}

export async function deleteAllNotifications(ids = null, from = userNotifications()) {
    if(!ids) {
        ids = from.map(elem => elem.id);
    }
    const data = {
        ids: ids
    };
    return await $httpQueue.add(() => http.patch(`notification/`, data).then(response => {
        ids.forEach(id => {
            const idx = from.findIndex(elem => elem.id === id);
            if(idx > -1) {
                from.splice(idx, 1);
            }
        });
        return response.data;
    }));
}