<template>
    <div class="col-md-4 offset-md-4">
        <div class="login-header mb-3">
            <h1>Spacialist</h1>
            <img src="img/logo.png" width="100" />
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    {{ t('global.login-title') }}
                </h5>
                <h6 class="card-subtitle mb-2 text-muted">
                    {{ t('global.login-subtitle') }}
                </h6>
                <p class="card-text">
                    <form @submit.prevent="login">
                        <div class="form-group mb-2">
                            <label for="email" class="col-md-4 col-form-label">
                                {{ t('global.email_or_nick') }}
                                <i class="fas fa-fw fa-user"></i>
                            </label>

                            <div class="col-md-6">
                                <input id="email" type="text" class="form-control" :class="getValidClass(state.error, 'email|nickname')" v-model="state.user.email" name="email" required autofocus>

                                <div class="invalid-feedback">
                                    <span v-for="(msg, i) in state.error.email" :key="i">
                                        {{ msg }}
                                    </span>
                                    <span v-for="(msg, i) in state.error.nickname" :key="i">
                                        {{ msg }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label for="password" class="col-md-4 col-form-label">
                                {{ t('global.password') }}
                                <i class="fas fa-fw fa-unlock-alt"></i>
                            </label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" :class="getValidClass(state.error, 'password')" v-model="state.user.password" name="password" required>

                                <div class="invalid-feedback">
                                    <span v-for="(msg, i) in state.error.password" :key="i">
                                        {{ msg }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-2" v-if="state.error.global">
                            <div class="col-md-6 text-danger small">
                                {{ state.error.global }}
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <div class="col-md-6 col-md-offset-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember" v-model="state.user.remember"> {{ t('global.remember-me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ t('global.login') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </p>
            </div>
        </div>
    </div>
</template>


<script>
    import {
        reactive,
        onMounted,
    } from 'vue';

    import { useRoute } from 'vue-router';
    import { useAuth } from '@websanova/vue-auth';
    import { useI18n } from 'vue-i18n';

    import {
        getErrorMessages,
        getValidClass
    } from '../helpers/helpers.js';

    export default {
        setup() {
            const auth = useAuth();
            const { t } = useI18n();
            // DATA
            const state = reactive({
                user: {},
                redirect: {
                    name: 'home'
                },
                error: {},
            });

            // FUNCTIONS
            function login() {
                let data = {
                    password: state.user.password
                };
                // dirty check if email field should be treated
                // as actual email address or nickname
                if(state.user.email.includes('@')) {
                    data.email = state.user.email;
                } else {
                    data.nickname = state.user.email;
                }
                auth.login({
                    data: data,
                    staySignedIn: state.user.remember,
                    redirect: state.redirect,
                    fetchUser: true
                }).then(_ => {
                    state.error = {};
                }, e => {
                    state.error = getErrorMessages(e);
                    console.log(state.error, "check error object to set v-key");
                });
            }

            // ON MOUNTED
            onMounted(_ => {
                if(auth.check()) {
                    this.$router.push({
                        name: 'home'
                    });
                }
                const lastRoute = auth.redirect() ? auth.redirect().from : undefined;
                const currentRoute = useRoute();
                if(lastRoute && lastRoute.name != 'login') {
                    state.redirect = {
                        name: lastRoute.name,
                        params: lastRoute.params,
                        query: lastRoute.query
                    };
                } else if(currentRoute.query && currentRoute.query.redirect) {
                    state.redirect = {
                        path: currentRoute.query.redirect
                    };
                }
            });

            // RETURN
            return {
                t,
                state,
                login,
                getValidClass,
            };
        },
    }
</script>
