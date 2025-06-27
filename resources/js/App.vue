<template>
    <div class="d-flex flex-column h-100">
        <template v-if="state.init">
            <router-view />
        </template>
        <template v-else>
            <div class="h-100 d-flex flex-column justify-content-center align-items-center">
                <div>
                    <i class="fas fa-5x fa-fw fa-spinner fa-spin" />
                </div>

                <!-- eslint-disable vue/no-v-html -->
                <h1
                    class="mt-5"
                    v-html="t('main.app.loading_screen_msg', { appname: state.appName })"
                />
                <!-- eslint-enable-->
            </div>
        </template>
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
        computed,
        onMounted,
        watch,
    } from 'vue';

    import {
        ModalsContainer,
    } from 'vue-final-modal';

    import {
        router,
    } from '@/bootstrap/router.js';

    import useSystemStore from '@/bootstrap/stores/system.js';
    import useUserStore from '@/bootstrap/stores/user.js';
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
            const { t, locale } = useI18n();
            const systemStore = useSystemStore();

            // FETCH
            systemStore.initialize(locale).catch(e => {
                console.log("ERROR", e)
                if(e.response.status == 401) {
                    systemStore.setAppState(true);
                } else {
                    throwError(e);
                }
            });

            const state = reactive({
                init: computed(_ => systemStore.appInitialized),
            });

            // ON MOUNTED
            onMounted(_ => {
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
