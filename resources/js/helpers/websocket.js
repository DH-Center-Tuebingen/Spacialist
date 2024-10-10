// import Echo from '@/bootstrap/websocket.js';

import useEntityStore from '@/bootstrap/stores/entity.js';
import useUserStore from '@/bootstrap/stores/user.js';

const activeChannels = {};

function listen(channelname, event, callback) {
    const channel = activeChannels[channelname];
    if(!channel) return;

    channel.listen(event, callback);
}

export function subscribeToTestWebSockets() {
    Echo.private('private_testchannel')
        .listen('TestEvent', e => {
            console.log('Private WebSockets are working! Received message:', e);
        });
    Echo.channel('testchannel')
        .listen('TestEvent', e => {
            console.log('Public WebSockets are working! Received message:', e);
        });
}

export function unsubscribeFromTestWebSockets() {
    Echo.leave('private_testchannel');
    Echo.leave('testchannel');
}

// Subscribe to public/private channel and optionally listen to an event
export function subscribeTo(channelname, isPrivate = false, listenTo = null, callback = null) {
    const channel = isPrivate ? Echo.private(channelname) : Echo.channel(channelname);
    if(!activeChannels[channelname]) {
        activeChannels[channelname] = channel;
    }
    if(listenTo && callback) {
        channel.listen(listenTo, callback);
    }
}

export function unsubscribeFrom(channelname) {
    Echo.leave(channelname);
}

// Listen for a specific event of an already subscribed channel
// If only event is provided it is interpreted as reaction event object
export function listenTo(channelname, event, callback = null) {
    if(!callback) {
        for(let k in event) {
            listen(channelname, k, event[k]);
        }
    } else {
        listen(channelname, event, callback);
    }
}

// Listen to a list of reaction events
export function listenToList(channelname, eventHandlerList) {
    eventHandlerList.forEach(eventHandlers => {
        for(let k in eventHandlers) {
            listen(channelname, k, eventHandlers[k]);
        }
    });
}

// Join a presence channel
export function join(roomname, callbacks) {
    const room = Echo.join(roomname)
        .here(callbacks.init)
        .joining(callbacks.join)
        .leaving(callbacks.leave)
        .error(callbacks.error);
    if(!activeChannels[roomname]) {
        activeChannels[roomname] = room;
    }
}

export function subscribeNotifications(callback) {
    const uid = useUserStore().getCurrentUserId;
    const channelname = `App.User.${uid}`;
    if(!activeChannels[channelname]) {
        subscribeTo(channelname, true);
    }
    const channel = activeChannels[channelname];
    channel.notification(callback);
    return channelname;
}

export function stopListeningTo(channelname, event) {
    const channel = activeChannels[channelname];
    if(!channel) return;

    channel.stopListening(event);
}

export function subscribeSystemChannel() {
    const channelname = 'channel.system';
    subscribeTo(channelname, true);
    return channelname;
}

export function unsubscribeSystemChannel() {
    unsubscribeFrom('channel.system');
}

export function joinEntityRoom(entityId) {
    const roomname = `room.entity.${entityId}`;
    const entityStore = useEntityStore();
    join(roomname, {
        init: users => entityStore.setActiveUserIds(users),
        join: user => entityStore.addActiveUserId(user),
        leave: user => entityStore.removeActiveUserId(user.id),
        error: error => {
            console.error("[WS] Error occured!", error);
        },
    });
    return roomname;
}

export function leaveEntityRoom(roomname) {
    unsubscribeFrom(roomname);
}