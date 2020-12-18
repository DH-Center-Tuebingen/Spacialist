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
                            <router-link :to="{name: 'globalactivity'}" class="dropdown-item">
                                <i class="fas fa-fw fa-clock"></i> {{ $t('global.activity') }}
                            </router-link>
                            <template v-if="$hasPreference('prefs.load-extensions', 'data-analysis') || $hasPreference('prefs.link-to-thesaurex')">
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
                            <user-avatar :user="authUser" :size="20" class="align-middle"></user-avatar>
                            {{ authUser.name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="user-dropdown">
                            <router-link :to="{name: 'userprofile'}" class="dropdown-item">
                                <i class="fas fa-fw fa-id-badge"></i> {{ $t('global.user.profile') }}
                            </router-link>
                            <router-link :to="{name: 'userpreferences', params: { id: authUser.id }}" class="dropdown-item">
                                <i class="fas fa-fw fa-user-cog"></i> {{ $t('global.user.settings') }}
                            </router-link>
                            <router-link :to="{name: 'useractivity', params: { id: $auth.user().id }}" class="dropdown-item">
                                <i class="fas fa-fw fa-user-clock"></i> {{ $t('global.activity') }}
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
            loggedIn() {
                return this.$auth.check();
            },
            authUser() {
                if(!this.loggedIn) return {};

                return this.$auth.user() ? this.$auth.user() : {};
            }
        }
    }
</script>
