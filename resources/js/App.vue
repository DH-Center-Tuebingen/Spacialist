<template>
    <div
        v-if="state.init"
        class="d-flex flex-column h-100"
    >
        <router-view />
        <modals-container />
        <div
            id="toast-container"
            class="toast-container ps-3 pb-3"
        />
    </div>
</template>

<script>
    import {
        reactive,
        nextTick,
        onMounted,
    } from 'vue';

    import {
        ModalsContainer,
    } from 'vue-final-modal';

    import useSystemStore from '@/bootstrap/stores/system.js';
    import useUserStore from './bootstrap/stores/user.js';

    import { useI18n } from 'vue-i18n';
    import { provideToast, useToast } from '@/plugins/toast.js';

    import {
        throwError,
    } from '@/helpers/helpers.js';

    export default {
        components: {
            'modals-container': ModalsContainer,
        },
        setup(props) {
            const { t } = useI18n();
            const systemStore = useSystemStore();
            const userStore = useUserStore();

            // FETCH

            const state = reactive({
                init: false,
                // init: computed(_ => systemStore.appInitialized),
            });

            // ON MOUNTED
            onMounted(async _ => {
                try {
                    await userStore.checkAuth();
                } catch(e) {
                    if(e.response.status == 401) {
                        systemStore.setAppState(true);
                    } else {
                        throwError(e);
                    }
                }
                state.init = true;

                nextTick(_ => {
                    provideToast({
                        duration: 2500,
                        autohide: true,
                        channel: 'success',
                        icon: true,
                        simple: false,
                        is_tag: false,
                        container: 'toast-container',
                    });
                    useToast();
                });
            });

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                // PROPS
                // STATE
                state,
            };
        }
    };
</script>
