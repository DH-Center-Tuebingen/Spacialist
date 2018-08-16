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
                            <template v-if="$getPreference('prefs.load-extensions')['data-analysis']">
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="/analysis">
                                    <i class="far fa-fw fa-chart-bar"></i> {{ $t('global.tools.analysis') }}
                                </a>
                            </template>
                            <template v-if="$getPreference('prefs.load-extensions')['data-analysis']">
                                <div class="dropdown-divider"></div>
                                <h6 class="dropdown-header">
                                    {{ $t('global.tools.external') }} <sup class="fas fa-fw fa-sm fa-fw fa-external-link-alt"></sup>
                                </h6>
                            </template>
                            <template v-if="$getPreference('prefs.link-to-thesaurex')">
                                <a class="dropdown-item" :href="$getPreference('prefs.link-to-thesaurex')" target="_blank">
                                    <i class="fas fa-fw fa-paw"></i> {{ $t('global.tools.thesaurex') }}
                                </a>
                            </template>
                            <template v-if="$getPreference('prefs.load-extensions')['data-analysis']">
                                <a class="dropdown-item" href="/db" target="_blank">
                                    <i class="fas fa-fw fa-chart-bar"></i> {{ $t('global.tools.dbwebgen') }}
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
                            <a class="dropdown-item" href="">
                                <i class="fas fa-fw fa-pencil-alt"></i> {{ $t('global.settings.editmode') }}
                            </a>
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
                        <ul class="dropdown-menu" aria-labelledby="user-dropdown">
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
            <about-dialog></about-dialog>
            <error-modal></error-modal>
        </div>
        <notifications group="spacialist" position="bottom left" class="m-2" />
    </div>
</template>

<script>
    export default {
        props: {
            onInit: {
                required: false,
                type: Function
            }
        },
        mounted() {},
        methods: {
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
                plugins: {}
            }
        },
        computed: {
            loggedIn: function() {
                return this.$auth.check();
            }
        }
    }
</script>
