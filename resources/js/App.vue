<template>
    <div class="d-flex flex-column h-100">
        <nav class="navbar navbar-dark bg-dark navbar-expand-lg overlay-all">
            <!-- Branding Image -->
            <router-link :to="{name: 'home'}" class="navbar-brand">
                <img src="favicon.png" class="logo" alt="spacialist logo" />
                {{ $getPreference('prefs.project-name') }}
            </router-link>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item" v-if="loggedIn">
                        <form class="form-inline mr-auto">
                            <div class="form-group">
                                <global-search></global-search>
                            </div>
                        </form>
                    </li>
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="nav navbar-nav navbar-right">
                    <li class="nav-item">
                        <a class="nav-link" target="_blank" href="https://github.com/eScienceCenter/Spacialist/wiki/User-manual">
                            <i class="far fa-fw fa-question-circle"></i>
                        </a>
                    </li>
                    <!-- Authentication Links -->
                    <li class="nav-item" v-if="!loggedIn">
                        <router-link :to="{name: 'login'}" class="nav-link">
                            {{ $t('global.login') }}
                        </router-link>
                    </li>
                    <li class="nav-item dropdown" v-if="loggedIn">
                        <a href="#" class="nav-link" id="notifications-navbar" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                            <i class="fas fa-fw fa-bell"></i>
                            <span class="badge badge-danger align-text-bottom" v-if="unreadNotifications.length">
                                {{ unreadNotifications.length }}
                            </span>
                        </a>
                        <div class="dropdown-menu row dropdown-menu-right bg-dark text-light py-1" aria-labelledby="notifications-navbar" style="min-width: 30rem;">
                            <div class="col-12 d-flex flex-row justify-content-between pb-1">
                                <span>Notifications ({{ notifications.length }})</span>
                                <a href="#" class="text-light" @click.prevent.stop="markAllNotificationsAsRead()">
                                    Mark all as read
                                </a>
                            </div>
                            <div class="col-12 bg-light text-dark px-0">
                                <div class="d-flex flex-row py-2 px-3 bg-light" :class="{'bg-light-dark': !(idx % 2), 'bg-light': (idx % 2)}" v-for="(n, idx) in notifications" :key="n.id">
                                    <div class="mr-5" :class="{'opacity-50': !!n.read_at}">
                                        <span>
                                            {{ n.data.user_id }}
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column small flex-grow-1">
                                        <div class="d-flex flex-row justify-content-between">
                                            <span class="font-weight-bold text-primary">
                                                <template v-if="n.type == 'App\\Notifications\\CommentPosted'">
                                                    New Comment/Reply
                                                </template>
                                                <template v-else>
                                                    New Notification
                                                </template>
                                            </span>
                                            <div class="d-flex flex-row">
                                                <div v-if="n.data.persistent">
                                                    <span class="badge badge-warning mr-2">
                                                        persistent
                                                    </span>
                                                </div>
                                                <a href="#" class="text-muted" @click.prevent.stop="markNotificationAsRead(n.id)" v-if="!n.read_at">
                                                    <i class="fas fa-xs fa-check"></i>
                                                </a>
                                                <a v-if="!n.data.persistent" href="#" class="text-muted ml-2" @click.prevent.stop="deleteNotification(n.id)">
                                                    <i class="fas fa-xs fa-times"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="text-muted" :class="{'opacity-50': !!n.read_at}">
                                            <div v-if="n.type == 'App\\Notifications\\CommentPosted'">
                                                {{ n.data.user_id }} commented on ...
                                            </div>
                                            <div>
                                                {{ n.data.content | truncate(100) }}
                                            </div>
                                        </div>
                                        <div class="text-info" :class="{'opacity-50': !!n.read_at}">
                                            <span data-toggle="tooltip" :title="n.created_at | datestring">
                                                {{ n.created_at | ago }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <p class="py-2 px-3 mb-0 bg-light text-dark" v-if="!notifications.length">
                                    No Notifications
                                </p>
                            </div>
                            <div class="text-center pt-1">
                                <router-link :to="{name: 'notifications', params: { id: $auth.user().id }}" class="text-light">
                                    View All
                                </router-link>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item dropdown" v-if="loggedIn">
                        <a href="#" class="nav-link dropdown-toggle" id="tools-navbar" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                            <i class="fas fa-fw fa-cogs"></i> {{ $t('global.tools.title') }}
                        </a>
                        <div class="dropdown-menu" aria-labelledby="tools-navbar">
                            <router-link class="dropdown-item" v-for="plugin in $getToolPlugins()" :to="plugin.href" :key="plugin.key">
                                <i class="fas fa-fw" :class="plugin.icon"></i> {{ $t(plugin.label) }}
                            </router-link>
                            <router-link :to="{name: 'bibliography'}" class="dropdown-item">
                                <i class="fas fa-fw fa-book"></i> {{ $t('global.tools.bibliography') }}
                            </router-link>
                            <a v-show="!rtc.isRecording" class="dropdown-item" href="#" @click.prevent="startRecording">
                                <i class="fas fa-fw fa-play"></i> {{ $t('global.tools.record.start') }}
                            </a>
                            <a v-show="rtc.isRecording" class="dropdown-item" href="#" @click.prevent="stopRecording">
                                <i class="fas fa-fw fa-stop"></i> {{ $t('global.tools.record.stop') }}
                            </a>
                            <template v-if="$hasPreference('prefs.load-extensions', 'data-analysis')">
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header">
                                    {{ $t('global.tools.external') }} <sup class="fas fa-fw fa-sm fa-fw fa-external-link-alt"></sup>
                                </h6>
                            </template>
                            <template v-if="$hasPreference('prefs.link-to-thesaurex')">
                                <a class="dropdown-item" :href="$getPreference('prefs.link-to-thesaurex')" target="_blank">
                                    <i class="fas fa-fw fa-paw"></i> {{ $t('global.tools.thesaurex') }}
                                </a>
                            </template>
                            <template v-if="$hasPreference('prefs.load-extensions', 'data-analysis')">
                                <a class="dropdown-item" href="../db" target="_blank">
                                    <i class="fas fa-fw fa-chart-bar"></i> {{ $t('global.tools.dbwebgen') }}
                                </a>
                                <a class="dropdown-item" href="../analysis" target="_blank">
                                    <i class="fas fa-fw fa-chart-bar"></i> {{ $t('global.tools.analysis') }}
                                </a>
                            </template>
                        </div>
                    </li>
                    <li class="nav-item dropdown" v-if="loggedIn">
                        <a href="#" class="nav-link dropdown-toggle" id="settings-dropdown" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                            <i class="fas fa-fw fa-sliders-h"></i> {{ $t('global.settings.title') }} <span class="caret"></span>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="settings-dropdown">
                            <router-link :to="{name: 'users'}" class="dropdown-item">
                                <i class="fas fa-fw fa-users"></i> {{ $t('global.settings.users') }}
                            </router-link>
                            <router-link :to="{name: 'roles'}" class="dropdown-item">
                                <i class="fas fa-fw fa-shield-alt"></i> {{ $t('global.settings.roles') }}
                            </router-link>
                            <router-link :to="{name: 'dme'}" class="dropdown-item">
                                <i class="fas fa-fw fa-sitemap"></i> {{ $t('global.settings.datamodel') }}
                            </router-link>
                            <router-link :to="{name: 'preferences'}" class="dropdown-item">
                                <i class="fas fa-fw fa-cog"></i> {{ $t('global.settings.system') }}
                            </router-link>
                            <router-link class="dropdown-item" v-for="plugin in $getSettingsPlugins()" :to="plugin.href" :key="plugin.key">
                                <i class="fas fa-fw" :class="plugin.icon"></i> {{ $t(plugin.label) }}
                            </router-link>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" @click="showAboutModal">
                                <i class="fas fa-fw fa-info-circle"></i> {{ $t('global.settings.about') }}
                            </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown" v-if="loggedIn">
                        <a href="#" class="nav-link dropdown-toggle" id="user-dropdown" data-toggle="dropdown" role="button" aria-expanded="false" aria-haspopup="true">
                            <i class="fas fa-fw fa-user"></i> {{ $auth.user().name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="user-dropdown">
                            <router-link :to="{name: 'userpreferences', params: { id: $auth.user().id }}" class="dropdown-item">
                                <i class="fas fa-fw fa-cog"></i> {{ $t('global.user.settings') }}
                            </router-link>
                            <a class="dropdown-item" href="#"
                                @click="logout">
                                <i class="fas fa-fw fa-sign-out-alt"></i> {{ $t('global.user.logout') }}
                            </a>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
        <div class="container-fluid mt-3 mb-3 col">
            <router-view :on-login="onInit"></router-view>
            <video id="rtc-sharing-container" class="video-js d-none"></video>
            <about-dialog></about-dialog>
            <error-modal></error-modal>
        </div>
        <notifications group="spacialist" position="bottom left" class="m-2" />
    </div>
</template>

<script>
    import videojs from 'video.js';
    import adapter from 'webrtc-adapter';
    import Record from 'videojs-record';
    import SaveScreencastModal from './components/modals/SaveScreencastModal.vue';

    export default {
        props: {
            onInit: {
                required: false,
                type: Function
            }
        },
        mounted() {
            if (adapter.browserDetails.browser == 'firefox') {
                adapter.browserShim.shimGetDisplayMedia(window, 'window');
            }
            this.rtc.player = videojs('#rtc-sharing-container', this.rtc.opts);
            this.rtc.player.on('deviceReady', () => {
                // if we requested recording, but device was not ready,
                // start recording after device is ready
                if(this.rtc.requestRecord) {
                    this.rtc.player.record().start();
                }
           });
            this.rtc.player.on('startRecord', _ => {
                this.rtc.isRecording = true;
            });
            this.rtc.player.on('finishRecord', _ => {
                this.rtc.isRecording = false;
                this.rtc.data = this.rtc.player.recordedData;
                const dur = this.rtc.player.record().getDuration();
                // Release devices
                this.rtc.player.record().stopDevice();
                this.onRecordingFinished(this.rtc.data, dur);
                this.rtc.player.record().removeRecording();
            });
        },
        methods: {
            onRecordingFinished(data, duration) {
                const file = new File([data], data.name, {
                    type: data.type
                });
                this.$modal.show(SaveScreencastModal, {
                    content: file,
                    duration: duration,
                    storeLocal: _ => this.saveToDisk(data),
                    storeServer: _ => this.saveToSpacialist(file)
                }, {
                    height: 'auto'
                });
            },
            saveToDisk(content) {
                this.$createDownloadLink(content, `spacialist-screencapture-${this.$getTs()}.webm`, false, 'video/webm');
            },
            saveToSpacialist(file) {
                if(!this.$hasPlugin('files')) return;
                this.$uploadFile({
                    file: file
                }).then(response => {
                    EventBus.$emit('files-uploaded', {
                        new: true
                    });
                });
            },
            startRecording() {
                if(!this.rtc.player) return;
                if(!this.rtc.deviceReady) {
                    this.rtc.requestRecord = true;
                    this.rtc.player.record().getDevice();
                } else {
                    this.rtc.player.record().start();
                }
            },
            stopRecording() {
                if(!this.rtc.player) return;
                this.rtc.player.record().stop();
            },
            markNotificationAsRead(id) {
                // return $httpQueue.add(() => $http.patch(`notification/read/${id}`, data).then(response => {
                    const notifs = this.$auth.user().notifications;
                    for(let i=0; i<notifs.length; i++) {
                        if(notifs[i].id == id) {
                            notifs[i].read_at = Date();
                            break;
                        }
                    }
                // }));
            },
            markAllNotificationsAsRead() {
                // return $httpQueue.add(() => $http.patch(`notification/read/all`, data).then(response => {
                    const notifs = this.$auth.user().notifications;
                    for(let i=0; i<notifs.length; i++) {
                        notifs[i].read_at = Date();
                    }
                // }));
            },
            deleteNotification(id) {
                // return $httpQueue.add(() => $http.delete(`notification/${id}`, data).then(response => {
                    const notifs = this.$auth.user().notifications;
                    for(let i=0; i<notifs.length; i++) {
                        if(notifs[i].id == id) {
                            notifs.splice(i, 1);
                            break;
                        }
                    }
                // }));
            },
            deleteNotifications() {
                // return $httpQueue.add(() => $http.delete(`notification/`, data).then(response => {
                    this.$auth.user().notifications = [];
                // }));
            },
            logout() {
                this.$auth.logout({
                    makeRequest: true,
                    redirect: '/login'
                });
            },
            showAboutModal() {
                this.$modal.show('about-modal');
            }
        },
        data() {
            return {
                plugins: {},
                rtc: {
                    data: null,
                    player: null,
                    isRecording: false,
                    requestRecord: false,
                    deviceReady: false,
                    opts: {
                        controls: true,
                        autoplay: false,
                        plugins: {
                            record: {
                                maxLength: 120,
                                audio: true,
                                screen: true
                            }
                        }
                    }
                }
            }
        },
        computed: {
            loggedIn: function() {
                return this.$isLoggedIn();
            },
            notifications() {
                return this.$userNotifications();
            },
            unreadNotifications() {
                return this.notifications.filter(n => !n.read_at);
            }
        }
    }
</script>
