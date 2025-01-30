<template>
    <div
        v-if="visible"
        class="web-socket-connection d-flex align-items-center gap-2 position-absolute start-50 translate-middle mt-2 z-2 bg-white order p-2 border border-1 border-secondary rounded"
        :title="status"
    >
        <DotIndicator :type="indicatorStatus" />
        {{ message }}
    </div>
</template>

<script>
    import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
    import DotIndicator from '@/components/indicators/DotIndicator.vue';
    import { getState, getConnection } from '@/helpers/websocket';
    import { useI18n } from 'vue-i18n';

    export default {
        components: {
            DotIndicator
        },
        setup() {
            const t = useI18n().t;
            const status = ref(getState());
            const message = computed(() => {
                if(indicatorStatus.value === 'success') {
                    return t('websockets.service_available_again');
                } else {
                    return t('websockets.service_unavailable');
                }
            });
            const indicatorStatus = computed(() => {
                switch(status.value) {
                    case 'connected':
                        return 'success';
                    case 'connecting':
                    case 'initialized':
                    case 'unavailable':
                    case 'disconnected':
                    case 'failed':
                    default:
                        return 'danger';
                }
            });

            const visible = ref(indicatorStatus.value === 'danger');
            console.log('visible', visible.value);

            let timeout = null;
            const updateState = _ => {
                status.value = getState();
                console.log(status.value);
                
                clearTimeout(timeout);
                if(status.value === 'connected') {
                    timeout = setTimeout(() => {
                        visible.value = false;
                    }, 3000);
                } else {
                    visible.value = true;
                }
            };

            onMounted(() => {
                console.log('WebSocketConnection mounted');
                const connection = getConnection();
                if(!connection) {
                    console.error('Could not get connection object');
                } else {
                    console.log(connection, connection.bind);
                    connection.bind('state_change', function (states) {
                        updateState();
                    });
                }
            });

            onBeforeUnmount(() => {
                const connection = getConnection();
                if(!connection) {
                    console.error('Could not get connection object');
                } else {
                    connection.unbind('state_change ', updateState);
                }
            });

            return {
                status,
                indicatorStatus,
                message,
                visible,
            };
        }
    };
</script>