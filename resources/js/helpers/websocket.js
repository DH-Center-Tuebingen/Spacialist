// import Echo from '@/bootstrap/websocket.js';

const activeChannels = {};

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
export function subscribeTo(name, listenTo = null, callback = null, isPrivate = false) {
    const channel = isPrivate ? Echo.private(name) : Echo.channel(name);
    if(!activeChannels[name]) {
        activeChannels[name] = channel;
    }
    if(listenTo && callback) {
        channel.listen(listenTo, callback);
    }
}

export function unsubscribeFrom(name) {
    Echo.leave(name);
}

// Listen for a specific event of an already subscribed channel
export function listenTo(name, event, callback) {
    const channel = activeChannels[name];
    if(!channel) return;

    channel.listen(event, callback);
}

// Listen to a list of events
export function listenToList(name, events) {
    events.forEach(event => {
        listenTo(name, event.name, event.fn);
    });
}

// Join a presence channel
export function join(name, callbacks) {
    const channel = Echo.join(name)
        .here(callbacks.init)
        .joining(callbacks.join)
        .leaving(callbacks.leave)
        .error(callbacks.error);
    if(!activeChannels[name]) {
        activeChannels[name] = channel;
    }
}