<template>
    <div class="d-flex flex-column h-100">
        <nav class="navbar navbar-dark bg-dark navbar-expand-lg overlay-all">
            <div class="container-fluid">
                <!-- Branding Image -->
                <router-link :to="{name: 'home'}" class="navbar-brand">
                    <img src="favicon.png" class="logo" alt="spacialist logo" />
                    {{ getPreference('prefs.project-name') }}
                </router-link>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item" v-if="state.loggedIn">
                            <form class="me-auto">
                                <global-search></global-search>
                            </form>
                        </li>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" target="_blank" href="https://github.com/eScienceCenter/Spacialist/wiki/User-manual">
                                <i class="far fa-fw fa-question-circle"></i>
                            </a>
                        </li>
                        <!-- Authentication Links -->
                        <li class="nav-item" v-if="!state.loggedIn">
                            <router-link :to="{name: 'login'}" class="nav-link">
                                {{ t('global.login') }}
                            </router-link>
                        </li>
                        <li class="nav-item dropdown" v-if="state.loggedIn">
                            <a href="#" class="nav-link" id="notifications-navbar" data-bs-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                                <i class="fas fa-fw fa-bell"></i>
                                <span class="badge bg-danger ms-1 align-text-bottom" v-if="state.unreadNotifications.length">
                                    {{ state.unreadNotifications.length }}
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end stays-open row bg-dark text-light py-1" aria-labelledby="notifications-navbar" style="min-width: 30rem; right: 0 !important;">
                                <div class="col-12 d-flex flex-row justify-content-between pb-1">
                                    <span>
                                        {{ t('global.notifications.count', {cnt: state.notifications.length}) }}
                                    </span>
                                    <a href="#" class="text-light" @click.prevent.stop="markAllNotificationsAsRead()" v-if="state.unreadNotifications.length">
                                        {{ t('global.notifications.mark_all_as_read') }}
                                    </a>
                                </div>
                                <div class="col-12 bg-light text-dark px-0">
                                    <notification-body
                                        v-for="(n, idx) in state.notifications"
                                        :key="n.id"
                                        :avatar="32"
                                        :notf="n"
                                        :odd="!!(idx % 2)"
                                        :small-text="true"
                                        @read="markNotificationAsRead"
                                        @delete="deleteNotification">
                                    </notification-body>
                                    <p class="py-2 px-3 mb-0 bg-light text-dark" v-if="!state.notifications.length">
                                        {{ t('global.notifications.empty_list') }}
                                    </p>
                                </div>
                                <div class="text-center pt-1">
                                    <!-- <router-link :to="{name: 'notifications', params: { id: $auth.user().id }}" class="text-light">
                                        {{ t('global.notifications.view_all') }}
                                    </router-link> -->
                                    <router-link :to="{name: 'notifications', params: { id: 1 }}" class="text-light">
                                        {{ t('global.notifications.view_all') }}
                                    </router-link>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item dropdown" v-if="state.loggedIn">
                            <a href="#" class="nav-link dropdown-toggle" id="tools-navbar" data-bs-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                                <i class="fas fa-fw fa-cogs"></i>
                                {{ t('global.tools.title') }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="tools-navbar">
                                <router-link :to="{name: 'bibliography'}" class="dropdown-item">
                                    <i class="fas fa-fw fa-book"></i>
                                    {{ t('global.tools.bibliography') }}
                                </router-link>
                                <a v-show="!state.isRecording" class="dropdown-item" href="#" @click.prevent="startRecording()">
                                    <i class="fas fa-fw fa-play"></i>
                                    {{ t('global.tools.record.start') }}
                                </a>
                                <a v-show="state.isRecording" class="dropdown-item" href="#" @click.prevent="stopRecording()">
                                    <i class="fas fa-fw fa-stop"></i>
                                    {{ t('global.tools.record.stop') }}
                                </a>
                                <router-link :to="{name: 'globalactivity'}" class="dropdown-item">
                                    <i class="fas fa-fw fa-clock"></i>
                                    {{ t('global.activity') }}
                                </router-link>
                                <router-link :to="{name: 'dataimporter'}" class="dropdown-item">
                                    <i class="fas fa-fw fa-file-import"></i>
                                    {{ t('main.importer.title') }}
                                </router-link>
                                <router-link class="dropdown-item" v-for="plugin in state.plugins.tools" :to="`/${plugin.of}/${plugin.href}`" :key="plugin.key">
                                    <i class="fas fa-fw" :class="plugin.icon"></i>
                                    {{ t(plugin.label) }}
                                </router-link>
                                <template v-if="hasPreference('prefs.load-extensions', 'data-analysis') || hasPreference('prefs.link-to-thesaurex')">
                                    <div class="dropdown-divider"></div>
                                    <h6 class="dropdown-header">
                                        {{ t('global.tools.external') }} <sup class="fas fa-fw fa-sm fa-fw fa-external-link-alt"></sup>
                                    </h6>
                                </template>
                                <template v-if="hasPreference('prefs.link-to-thesaurex')">
                                    <a class="dropdown-item" :href="getPreference('prefs.link-to-thesaurex')" target="_blank">
                                        <i class="fas fa-fw fa-paw"></i>
                                        {{ t('global.tools.thesaurex') }}
                                    </a>
                                </template>
                                <template v-if="hasPreference('prefs.load-extensions', 'data-analysis')">
                                    <a class="dropdown-item" href="../db" target="_blank">
                                        <i class="fas fa-fw fa-chart-bar"></i>
                                        {{ t('global.tools.dbwebgen') }}
                                    </a>
                                    <a class="dropdown-item" href="../analysis" target="_blank">
                                        <i class="fas fa-fw fa-chart-bar"></i>
                                        {{ t('global.tools.analysis') }}
                                    </a>
                                </template>
                            </div>
                        </li>
                        <li class="nav-item dropdown" v-if="state.loggedIn">
                            <a href="#" class="nav-link dropdown-toggle" id="settings-dropdown" data-bs-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                                <i class="fas fa-fw fa-sliders-h"></i>
                                {{ t('global.settings.title') }} <span class="caret"></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="settings-dropdown">
                                <router-link :to="{name: 'users'}" class="dropdown-item">
                                    <i class="fas fa-fw fa-users"></i>
                                    {{ t('global.settings.users') }}
                                </router-link>
                                <router-link :to="{name: 'roles'}" class="dropdown-item">
                                    <i class="fas fa-fw fa-shield-alt"></i>
                                    {{ t('global.settings.roles') }}
                                </router-link>
                                <router-link :to="{name: 'plugins'}" class="dropdown-item">
                                    <i class="fas fa-fw fa-puzzle-piece"></i>
                                    {{ t('global.settings.plugins') }}
                                </router-link>
                                <router-link :to="{name: 'dme'}" class="dropdown-item">
                                    <i class="fas fa-fw fa-sitemap"></i>
                                    {{ t('global.settings.datamodel') }}
                                </router-link>
                                <router-link :to="{name: 'preferences'}" class="dropdown-item">
                                    <i class="fas fa-fw fa-cog"></i>
                                    {{ t('global.settings.system') }}
                                </router-link>
                                <router-link class="dropdown-item" v-for="plugin in state.plugins.settings" :to="`/${plugin.of}/${plugin.href}`" :key="plugin.key">
                                    <i class="fas fa-fw" :class="plugin.icon"></i>
                                    {{ t(plugin.label) }}
                                </router-link>
                                <!-- <router-link class="dropdown-item" v-for="plugin in state.plugins.settings" :to="plugin.href" :key="plugin.key">
                                    <i class="fas fa-fw" :class="plugin.icon"></i> {{ t(plugin.label) }}
                                </router-link> -->
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" @click.prevent="showAboutModal">
                                    <i class="fas fa-fw fa-info-circle"></i>
                                    {{ t('global.settings.about') }}
                                </a>
                            </div>
                        </li>
                        <li class="nav-item dropdown" v-if="state.loggedIn">
                            <a href="#" class="nav-link dropdown-toggle" id="user-dropdown" data-bs-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                                <user-avatar :user="state.authUser" :size="20" class="align-middle"></user-avatar>
                                {{ state.authUser.name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="user-dropdown">
                                <router-link :to="{name: 'userprofile'}" class="dropdown-item">
                                    <i class="fas fa-fw fa-id-badge"></i>
                                    {{ t('global.user.profile') }}
                                </router-link>
                                <router-link :to="{name: 'userpreferences', params: { id: state.authUser.id }}" class="dropdown-item" v-if="state.authUser.id">
                                    <i class="fas fa-fw fa-user-cog"></i>
                                    {{ t('global.user.settings') }}
                                </router-link>
                                <router-link :to="{name: 'useractivity', params: { id: state.authUser.id }}" class="dropdown-item" v-if="state.authUser.id">
                                    <i class="fas fa-fw fa-user-clock"></i>
                                    {{ t('global.activity') }}
                                </router-link>
                                <a class="dropdown-item" href="#"
                                    @click="logout">
                                    <i class="fas fa-fw fa-sign-out-alt"></i>
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
                <router-view></router-view>
            </template>
            <template v-else>
                <div class="h-100 d-flex flex-column justify-content-center align-items-center">
                    <div>
                        <i class="fas fa-5x fa-fw fa-spinner fa-spin"></i>
                    </div>
                    <h1 class="mt-5" v-html="t('main.app.loading_screen_msg', {appname: state.appName})"></h1>
                </div>
            </template>
            <div class="position-absolute top-50 start-50" v-show="state.recordingTimeout > 0">
                <h1 class="mb-0">
                    <span class="badge rounded-pill bg-dark text-light">
                        {{ state.recordingTimeout }}
                    </span>
                </h1>
            </div>
            <video id="rtc-sharing-container" class="video-js d-none"></video>
        </div>
        <modals-container></modals-container>
        <div class="toast-container ps-3 pb-3" id="toast-container"></div>
    </div>
</template>

<script>
    import {
        reactive,
        computed,
        inject,
        onMounted,
        watch,
    } from 'vue';

    import videojs from 'video.js';
    // import adapter from 'webrtc-adapter';
    import Record from 'videojs-record';

    import store from '@/bootstrap/store.js';
    import auth from '@/bootstrap/auth.js';
    import { useI18n } from 'vue-i18n';
    import { provideToast, useToast } from '@/plugins/toast.js';

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

    export default {
        setup(props) {
            const { t, locale } = useI18n();
            store.dispatch('setModalInstance', inject('$vfm'));

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
            const state = reactive({
                recordingTimeout: 0,
                isRecording: false,
                plugins: computed(_ => store.getters.slotPlugins()),
                auth: auth,
                appName: computed(_ => getProjectName()),
                init: computed(_ => store.getters.appInitialized),
                loggedIn: computed(_ => store.getters.isLoggedIn),
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
                const timerId = setInterval(_ => {
                    const now = (new Date()).getTime();
                    const dist = Math.ceil((dest - now) / 1000);
                    state.recordingTimeout = dist;
                    if(dist < 0) {
                        state.recordingTimeout = 0;
                        clearInterval(timerId);
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
                auth.logout({
                    makeRequest: true,
                    redirect: '/login'
                });
            };
            const showAboutModal = _ => {
                showAbout();
            };

            // WATCHER
            watch(state.loggedIn, (newValue, oldValue) => {
                // prevent notification dropdown from close on click
                if(newVal && !oldVal) {
                    this.$nextTick(_ => {
                        $('.dropdown-menu.stays-open').on("click.bs.dropdown", function (e) {
                            console.log("hier?");
                            e.stopPropagation();
                            e.preventDefault();
                        });
                    })
                }
            });
            watch(state.auth, (newValue, oldValue) => {
                store.commit('setUser', state.auth.user());
            })

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
    }
</script>
