// import Echo from '@/bootstrap/websocket.js';

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

export function subscribeTo(name, listenTo, callback, isPrivate = false) {
    const channel = isPrivate ? Echo.private(name) : Echo.channel(name);
    channel.listen(listenTo, callback);
}

export function unsubscribeFrom(name) {
    Echo.leave(name);
}