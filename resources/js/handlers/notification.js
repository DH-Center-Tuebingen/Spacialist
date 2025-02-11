import useUserStore from '@/bootstrap/stores/user.js';

export const handleNotifications = e => {
    useUserStore().pushNotification(e.content);
};