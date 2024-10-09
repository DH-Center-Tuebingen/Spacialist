import {
    default as http,
} from '@/bootstrap/http.js';

import useUserStore from '@/bootstrap/stores/user.js';

import {
    _cloneDeep,
} from '@/helpers/helpers.js';

export async function markAsRead(id, from = useUserStore().getNotifications) {
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

export async function markAllAsRead(ids = null, from = useUserStore().getNotifications) {
    if(!ids) {
        ids = from.map(elem => elem.id);
    }
    const data = {
        ids: ids
    };
    return await $httpQueue.add(() => http.patch(`notification/read`, data).then(response => {
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

export async function deleteNotification(id, from = useUserStore().getNotifications) {
    return await $httpQueue.add(() => http.delete(`notification/${id}`).then(response => {
        const idx = from.findIndex(elem => elem.id === id);
        if(idx > -1) {
            from.splice(idx, 1);
        }
        return response.data;
    }));
}

export async function deleteAllNotifications(ids = null, from = useUserStore().getNotifications) {
    if(!ids) {
        ids = from.map(elem => elem.id);
    }
    const data = {
        ids: ids
    };
    return await $httpQueue.add(() => http.patch(`notification`, data).then(response => {
        ids.forEach(id => {
            const idx = from.findIndex(elem => elem.id === id);
            if(idx > -1) {
                from.splice(idx, 1);
            }
        });
        return response.data;
    }));
}