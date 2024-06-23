// import Echo from '@/bootstrap/websocket.js';

export function listenToTest() {
    console.log("NOW LISTEN!");

    Echo.private('private_testchannel')
        .listen('TestEvent', e => {
            console.log("Private WebSockets are working! Received message:", e);
        });
    Echo.channel('testchannel')
        .listen('TestEvent', e => {
            console.log("Public WebSockets are working! Received message:", e);
        });
}