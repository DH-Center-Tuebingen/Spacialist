<template>
    <div class="d-flex flex-column h-100">
        <nav class="navbar navbar-dark bg-dark navbar-expand-lg overlay-all">
            <div class="container-fluid">
                <!-- Branding Image -->
                <router-link
                    :to="{ name: 'home' }"
                    class="navbar-brand"
                >
                    <img
                        src="favicon.png"
                        class="logo"
                        alt="spacialist logo"
                    >
                    {{ getPreference('prefs.project-name') }}
                </router-link>
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <span class="navbar-toggler-icon" />
                </button>
                <div
                    id="navbarSupportedContent"
                    class="collapse navbar-collapse"
                >
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        <li
                            v-if="state.loggedIn"
                            class="nav-item"
                        >
                            <form class="me-auto">
                                <global-search />
                            </form>
                        </li>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        <li class="nav-item">
                            <a
                                class="nav-link"
                                target="_blank"
                                href="https://github.com/DH-Center-Tuebingen/Spacialist/wiki/User-manual"
                            >
                                <i class="far fa-fw fa-question-circle" />
                            </a>
                        </li>
                        <!-- Authentication Links -->
                        <li
                            v-if="!state.loggedIn"
                            class="nav-item"
                        >
                            <router-link
                                :to="{ name: 'login' }"
                                class="nav-link"
                            >
                                {{ t('global.login') }}
                            </router-link>
                        </li>
                        <li
                            v-if="state.loggedIn"
                            class="nav-item dropdown"
                        >
                            <a
                                id="notifications-navbar"
                                href="#"
                                class="nav-link"
                                data-bs-toggle="dropdown"
                                role="button"
                                aria-expanded="false"
                                aria-haspopup="true"
                            >
                                <i class="fas fa-fw fa-bell" />
                                <span
                                    v-if="state.unreadNotifications.length"
                                    class="badge bg-danger ms-1 align-text-bottom"
                                >
                                    {{ state.unreadNotifications.length }}
                                </span>
                            </a>
                            <div
                                class="dropdown-menu dropdown-menu-end stays-open row bg-dark text-light py-1 end-0 minw-30"
                                aria-labelledby="notifications-navbar"
                            >
                                <div class="col-12 d-flex flex-row justify-content-between pb-1">
                                    <span>
                                        {{ t('global.notifications.count', { cnt: state.notifications.length }) }}
                                    </span>
                                    <a
                                        v-if="state.unreadNotifications.length"
                                        href="#"
                                        class="text-light"
                                        @click.prevent.stop="markAllNotificationsAsRead()"
                                    >
                                        {{ t('global.notifications.mark_all_as_read') }}
                                    </a>
                                </div>
                                <div class="col-12 bg-light text-dark px-0 mh-75v overflow-y-auto">
                                    <notification-body
                                        v-for="(n, idx) in state.notifications"
                                        :key="n.id"
                                        :avatar="32"
                                        :notf="n"
                                        :odd="!!(idx % 2)"
                                        :small-text="true"
                                        @read="markNotificationAsRead"
                                        @delete="deleteNotification"
                                    />
                                    <p
                                        v-if="!state.notifications.length"
                                        class="py-2 px-3 mb-0 bg-light text-dark"
                                    >
                                        {{ t('global.notifications.empty_list') }}
                                    </p>
                                </div>
                                <div class="text-center pt-1">
                                    <router-link
                                        :to="{ name: 'notifications', params: { id: state.authUser.id || -1 } }"
                                        class="text-light"
                                    >
                                        {{ t('global.notifications.view_all') }}
                                    </router-link>
                                </div>
                            </div>
                        </li>
                        <li
                            v-if="state.loggedIn"
                            class="nav-item dropdown"
                        >
                            <a
                                id="tools-navbar"
                                href="#"
                                class="nav-link dropdown-toggle"
                                data-bs-toggle="dropdown"
                                role="button"
                                aria-expanded="false"
                                aria-haspopup="true"
                            >
                                <i class="fas fa-fw fa-cogs" />
                                {{ t('global.tools.title') }}
                            </a>
                            <div
                                class="dropdown-menu dropdown-menu-end"
                                aria-labelledby="tools-navbar"
                            >
                                <router-link
                                    :to="{ name: 'bibliography' }"
                                    class="dropdown-item"
                                >
                                    <i class="fas fa-fw fa-book" />
                                    {{ t('global.tools.bibliography') }}
                                </router-link>
                                <a
                                    v-show="!state.isRecording"
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="startRecording()"
                                >
                                    <i class="fas fa-fw fa-play" />
                                    {{ t('global.tools.record.start') }}
                                </a>
                                <a
                                    v-show="state.isRecording"
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="stopRecording()"
                                >
                                    <i class="fas fa-fw fa-stop" />
                                    {{ t('global.tools.record.stop') }}
                                </a>
                                <router-link
                                    :to="{ name: 'globalactivity' }"
                                    class="dropdown-item"
                                >
                                    <i class="fas fa-fw fa-clock" />
                                    {{ t('global.activity') }}
                                </router-link>
                                <router-link
                                    :to="{ name: 'dataimporter' }"
                                    class="dropdown-item"
                                >
                                    <i class="fas fa-fw fa-file-import" />
                                    {{ t('main.importer.title') }}
                                </router-link>
                                <router-link
                                    v-for="plugin in state.plugins.tools"
                                    :key="plugin.key"
                                    class="dropdown-item"
                                    :to="`/${plugin.of}/${plugin.href}`"
                                >
                                    <i
                                        class="fas fa-fw"
                                        :class="plugin.icon"
                                    />
                                    {{ t(plugin.label) }}
                                </router-link>
                                <div class="dropdown-divider" />
                                <h6 class="dropdown-header">
                                    {{ t('global.tools.external') }} <sup
                                        class="fas fa-fw fa-sm fa-fw fa-external-link-alt"
                                    />
                                </h6>
                                <a
                                    v-if="hasPreference('prefs.link-to-thesaurex')"
                                    class="dropdown-item"
                                    :href="getPreference('prefs.link-to-thesaurex')"
                                    target="_blank"
                                >
                                    <i class="fas fa-fw fa-paw" />
                                    {{ t('global.tools.thesaurex') }}
                                </a>
                                <a
                                    v-if="state.hasAnalysis"
                                    class="dropdown-item"
                                    href="../analysis"
                                    target="_blank"
                                >
                                    <i class="fas fa-fw fa-chart-bar" />
                                    {{ t('global.tools.analysis') }}
                                </a>
                            </div>
                        </li>
                        <li
                            v-if="state.loggedIn"
                            class="nav-item dropdown"
                        >
                            <a
                                id="settings-dropdown"
                                href="#"
                                class="nav-link dropdown-toggle"
                                data-bs-toggle="dropdown"
                                role="button"
                                aria-expanded="false"
                                aria-haspopup="true"
                            >
                                <i class="fas fa-fw fa-sliders-h" />
                                {{ t('global.settings.title') }}
                            </a>
                            <div
                                class="dropdown-menu dropdown-menu-end"
                                aria-labelledby="settings-dropdown"
                            >
                                <router-link
                                    :to="{ name: 'users' }"
                                    class="dropdown-item"
                                >
                                    <i class="fas fa-fw fa-users" />
                                    {{ t('global.settings.users') }}
                                </router-link>
                                <router-link
                                    :to="{ name: 'roles' }"
                                    class="dropdown-item"
                                >
                                    <i class="fas fa-fw fa-shield-alt" />
                                    {{ t('global.settings.roles') }}
                                </router-link>
                                <router-link
                                    :to="{ name: 'plugins' }"
                                    class="dropdown-item"
                                >
                                    <i class="fas fa-fw fa-puzzle-piece" />
                                    {{ t('global.settings.plugins') }}
                                </router-link>
                                <router-link
                                    :to="{ name: 'dme' }"
                                    class="dropdown-item"
                                >
                                    <i class="fas fa-fw fa-sitemap" />
                                    {{ t('global.settings.datamodel') }}
                                </router-link>
                                <router-link
                                    :to="{ name: 'preferences' }"
                                    class="dropdown-item"
                                >
                                    <i class="fas fa-fw fa-cog" />
                                    {{ t('global.settings.system') }}
                                </router-link>
                                <router-link
                                    v-for="plugin in state.plugins.settings"
                                    :key="plugin.key"
                                    class="dropdown-item"
                                    :to="`/${plugin.of}/${plugin.href}`"
                                >
                                    <i
                                        class="fas fa-fw"
                                        :class="plugin.icon"
                                    />
                                    {{ t(plugin.label) }}
                                </router-link>
                                <div class="dropdown-divider" />
                                <a
                                    class="dropdown-item"
                                    href="#"
                                    @click.prevent="showAboutModal"
                                >
                                    <i class="fas fa-fw fa-info-circle" />
                                    {{ t('global.settings.about') }}
                                </a>
                            </div>
                        </li>
                        <li
                            v-if="state.loggedIn"
                            class="nav-item dropdown"
                        >
                            <a
                                id="user-dropdown"
                                href="#"
                                class="nav-link dropdown-toggle"
                                data-bs-toggle="dropdown"
                                role="button"
                                aria-expanded="false"
                                aria-haspopup="true"
                            >
                                <user-avatar
                                    :user="state.authUser"
                                    :size="20"
                                    class="align-middle"
                                />
                                {{ state.authUser.name }}
                            </a>
                            <ul
                                class="dropdown-menu dropdown-menu-end"
                                aria-labelledby="user-dropdown"
                            >
                                <router-link
                                    :to="{ name: 'userprofile' }"
                                    class="dropdown-item"
                                >
                                    <i class="fas fa-fw fa-id-badge" />
                                    {{ t('global.user.profile') }}
                                </router-link>
                                <router-link
                                    v-if="state.authUser.id"
                                    :to="{ name: 'userpreferences', params: { id: state.authUser.id } }"
                                    class="dropdown-item"
                                >
                                    <i class="fas fa-fw fa-user-cog" />
                                    {{ t('global.user.settings') }}
                                </router-link>
                                <router-link
                                    v-if="state.authUser.id"
                                    :to="{ name: 'useractivity', params: { id: state.authUser.id } }"
                                    class="dropdown-item"
                                >
                                    <i class="fas fa-fw fa-user-clock" />
                                    {{ t('global.activity') }}
                                </router-link>
                                <a
                                    class="dropdown-item"
                                    href="#"
                                    @click="logout"
                                >
                                    <i class="fas fa-fw fa-sign-out-alt" />
                                    {{ t('global.user.logout') }}
                                </a>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container-fluid my-3 col overflow-hidden">
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
            <div
                v-show="state.recordingTimeout > 0"
                class="position-absolute top-50 start-50"
            >
                <h1 class="mb-0 ms--50">
                    <span class="badge rounded-pill bg-dark text-light">
                        {{ t('main.app.screencast.recording_begins_in_s', { t: state.recordingTimeout },
                             state.recordingTimeout) }}
                        <a
                            href="#"
                            class="text-reset"
                            @click.prevent="cancelScreencast()"
                        >
                            <i class="fas fa-fw fa-times" />
                        </a>
                    </span>
                </h1>
            </div>
            <video
                id="rtc-sharing-container"
                class="video-js d-none"
            />
        </div>
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

import videojs from 'video.js';
// import adapter from 'webrtc-adapter';
import Record from 'videojs-record';

import store from '@/bootstrap/store.js';
import { useI18n } from 'vue-i18n';
import { provideToast, useToast } from '@/plugins/toast.js';

import {
    logout as apiLogout,
} from '@/api.js';

import {
    getPreference,
    getProjectName,
    hasPreference,
    initApp,
    throwError,
    userNotifications,
} from '@/helpers/helpers.js';

import {
    markAsRead,
    markAllAsRead,
    deleteNotification as deleteNotificationHelper,
} from '@/api/notification.js';
import {
    showAbout,
    showSaveScreencast,
} from '@/helpers/modal.js';
import {
    searchParamsToObject
} from '@/helpers/routing.js';

export default {
    components: {
        'modals-container': ModalsContainer,
    },
    setup(props) {
        const { t, locale } = useI18n();

        // FETCH
        initApp(locale).then(_ => {
            store.dispatch('setAppState', true);
        }).catch(e => {
            if(e.response.status == 401) {
                store.dispatch('setAppState', true);
            } else {
                throwError(e);
            }
        });

        // DATA
        const rtc = {
            player: null,
            data: null,
            requestRecord: false,
            deviceReady: false,
            options: {
                controls: true,
                autoplay: false,
                plugins: {
                    record: {
                        maxLength: 120,
                        audio: true,
                        screen: true
                    },
                },
            },
        };
        let recTimerId = -1;
        const state = reactive({
            recordingTimeout: 0,
            isRecording: false,
            plugins: computed(_ => store.getters.slotPlugins()),
            hasAnalysis: computed(_ => store.getters.hasAnalysis),
            appName: computed(_ => getProjectName()),
            init: computed(_ => store.getters.appInitialized),
            loggedIn: computed(_ => store.getters.isLoggedIn),
            ready: computed(_ => state.loggedIn && state.init),
            authUser: computed(_ => store.getters.user),
            notifications: computed(_ => {
                return userNotifications();
            }),
            unreadNotifications: computed(_ => {
                return state.notifications.filter(n => !n.read_at);
            }),
        });

        // FUNCTIONS
        const triggerDelayedRecord = (countdown = 5) => {
            state.recordingTimeout = countdown;
            const dest = (new Date()).getTime() + (countdown * 1000);
            recTimerId = setInterval(_ => {
                const now = (new Date()).getTime();
                const dist = Math.ceil((dest - now) / 1000);
                state.recordingTimeout = dist;
                if(dist < 0) {
                    state.recordingTimeout = 0;
                    clearInterval(recTimerId);
                    rtc.player.record().start();
                }
            }, 1000);
        };
        const startRecording = _ => {
            if(!rtc.player) return;
            if(!rtc.deviceReady) {
                rtc.requestRecord = true;
                rtc.player.record().getDevice();
            } else {
                triggerDelayedRecord();
            }
        };
        const stopRecording = _ => {
            if(!rtc.player) return;
            rtc.player.record().stop();
        };
        const cancelScreencast = _ => {
            state.recordingTimeout = 0;
            clearInterval(recTimerId);
            if(rtc.player) {
                rtc.player.record().stop();
                rtc.player.record().stopDevice();
            }
        };
        const markNotificationAsRead = event => {
            markAsRead(event);
        };
        const markAllNotificationsAsRead = _ => {
            markAllAsRead();
        };
        const deleteNotification = event => {
            deleteNotificationHelper(event);
        };
        const logout = _ => {
            apiLogout().then(_ => {
                router.push({
                    name: 'login'
                });
            });
        };
        const showAboutModal = _ => {
            showAbout();
        };

        // WATCHER
        // watch(_ => state.ready, (newValue, oldValue) => {
        //     if(state.ready) {
        //     }
        // });
        watch(_ => state.loggedIn, (newValue, oldValue) => {
            if(newValue && !oldValue) {
                const route = router.currentRoute.value;
                if(route.query.redirect) {
                    // get path without potential query params
                    const path = route.query.redirect.split('?')[0];
                    // extract query params to explicitly set in new route
                    const query = searchParamsToObject(route.query.redirect);
                    router.push({
                        path: path,
                        query: query,
                    });
                }
                // prevent notification dropdown from close on click
                // this.$nextTick(_ => {
                //     $('.dropdown-menu.stays-open').on("click.bs.dropdown", function (e) {
                //         console.log("hier?");
                //         e.stopPropagation();
                //         e.preventDefault();
                //     });
                // })
            }
        });
        // watch(state.auth, (newValue, oldValue) => {
        //     store.commit('setUser', state.auth.user());
        // });

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

            Echo.channel('entity_updates')
                .listen('EntityUpdated', e => {
                    store.dispatch('updateEntityModificationState', {
                        status: e.status,
                        id: e.entity.id,
                        data: e.entity,
                    });
                })

            // if (adapter.browserDetails.browser == 'firefox') {
            //     adapter.browserShim.shimGetDisplayMedia(window, 'window');
            // }
            rtc.player = videojs('#rtc-sharing-container', rtc.options);
            rtc.player.on('deviceReady', () => {
                // if we requested recording, but device was not ready,
                // start recording after device is ready
                if(rtc.requestRecord) {
                    triggerDelayedRecord();
                }
            });
            rtc.player.on('startRecord', _ => {
                state.isRecording = true;
            });
            rtc.player.on('finishRecord', _ => {
                state.isRecording = false;
                rtc.data = rtc.player.recordedData;
                const dur = rtc.player.record().getDuration();
                // Release devices
                rtc.player.record().stopDevice();
                showSaveScreencast({
                    data: rtc.data,
                    duration: dur,
                });
                rtc.player.record().removeRecording();
            });
        });

        // RETURN
        return {
            t,
            // HELPERS
            getPreference,
            hasPreference,
            // LOCAL
            startRecording,
            stopRecording,
            cancelScreencast,
            markNotificationAsRead,
            markAllNotificationsAsRead,
            deleteNotification,
            logout,
            showAboutModal,
            // PROPS
            // STATE
            state,
        };
    }
};
</script>
