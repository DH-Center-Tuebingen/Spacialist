<template>
    <template v-if="state.init">
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
                    {{ state.appName }}
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
                                    v-for="plugin in pluginStore.getSlotItems('tools')"
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
                                    v-if="state.hasThesaurexLink"
                                    class="dropdown-item"
                                    :href="state.thesaurexLink"
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
                                    v-for="plugin in pluginStore.getSlotItems('settings')"
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
            <router-view />
        </div>
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
        useRoute,
    } from 'vue-router';
    import {
        router,
    } from '@/bootstrap/router.js';

    import usePluginStore from '../bootstrap/stores/plugin';
    import useSystemStore from '@/bootstrap/stores/system.js';
    import useUserStore from '@/bootstrap/stores/user.js';
    import { useI18n } from 'vue-i18n';

    import {
        markAsRead,
        markAllAsRead,
        deleteNotification as deleteNotificationHelper,
    } from '@/api/notification.js';

    import {
        throwError,
    } from '@/helpers/helpers.js';

    import {
        showAbout,
        showConfirmPassword,
    } from '@/helpers/modal.js';

    export default {
        components: {
            'modals-container': ModalsContainer,
        },
        setup(props) {
            const { t, locale } = useI18n();
            const currentRoute = useRoute();
            
            const pluginStore = usePluginStore();
            const systemStore = useSystemStore();
            const userStore = useUserStore();

            // FETCH

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
                hasAnalysis: computed(_ => systemStore.hasAnalysis),
                appName: computed(_ => systemStore.getProjectName()),
                hasThesaurexLink: computed(_ => systemStore.hasPreference('prefs.link-to-thesaurex')),
                thesaurexLink: computed(_ => {
                    if(state.hasThesaurexLink) {
                        return systemStore.getPreference('prefs.link-to-thesaurex');
                    } else {
                        return '';
                    }
                }),
                init: computed(_ => systemStore.appInitialized),
                loggedIn: computed(_ => userStore.userLoggedIn),
                ready: computed(_ => state.loggedIn && state.init),
                authUser: computed(_ => userStore.user),
                notifications: computed(_ => userStore.getNotifications),
                unreadNotifications: computed(_ => state.notifications.filter(n => !n.read_at)),
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
                userStore.logout().then(_ => {
                    router.push({
                        name: 'login'
                    });
                });
            };
            const showAboutModal = _ => {
                showAbout();
            };

            onMounted(async _ => {
                if(!systemStore.checkAccess(currentRoute.matched[0].path)) {
                    return;
                }
                try {
                    await systemStore.initialize(locale);
                    systemStore.setAppState(true);
                } catch(e) {
                    if(e.response.status == 401) {
                        systemStore.setAppState(true);
                    } else {
                        throwError(e);
                    }
                };
            });

            // WATCHER
            watch(_ => state.loggedIn, (newValue, oldValue) => {
                if(newValue && !oldValue) {
                    if(state.authUser.login_attempts > 0 || state.authUser.login_attempts === 0) {
                        showConfirmPassword(state.authUser.id);
                    }
                }
            });

            // RETURN
            return {
                t,
                // HELPERS
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
                pluginStore,
            };
        }
    };
</script>