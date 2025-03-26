<template>
    <div class="col-md-4 offset-md-4">
        <div class="login-header mb-3">
            <h1>Spacialist</h1>
            <img
                src="img/logo.png"
                width="100"
            >
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    {{ t('global.login_title') }}
                </h5>
                <h6 class="card-subtitle mb-2 text-muted">
                    {{ t('global.login_subtitle') }}
                </h6>
                <div class="card-text" />
                <form
                    v-if="!state.twoFa.required"
                    @submit.prevent="login"
                >
                    <div class="mb-2">
                        <label
                            for="email"
                            class="col-md-4 col-form-label"
                        >
                            {{ t('global.email_or_nick') }}
                            <i class="fas fa-fw fa-user" />
                        </label>

                        <div class="col-md-6">
                            <input
                                id="email"
                                v-model="state.user.email"
                                type="text"
                                class="form-control"
                                :class="getValidClass(state.error, 'email|nickname|global')"
                                name="email"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="mb-2">
                        <label
                            for="password"
                            class="col-md-4 col-form-label"
                        >
                            {{ t('global.password') }}
                            <i class="fas fa-fw fa-unlock-alt" />
                        </label>

                        <div class="col-md-6">
                            <input
                                id="password"
                                v-model="state.user.password"
                                type="password"
                                class="form-control"
                                :class="getValidClass(state.error, 'password|global')"
                                name="password"
                                required
                            >
                        </div>
                    </div>

                    <div
                        v-if="state.error.global"
                        class="mb-2"
                    >
                        <div class="col-md-6 text-danger small">
                            {{ state.error.global }}
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="col-md-6 col-md-offset-4">
                            <div class="checkbox">
                                <label>
                                    <input
                                        v-model="state.user.remember"
                                        type="checkbox"
                                        name="remember"
                                    > {{ t('global.remember_me') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="col-md-8 col-md-offset-4">
                            <button
                                type="submit"
                                class="btn btn-outline-primary"
                                :disabled="state.submitting"
                            >
                                {{ t('global.login') }}
                            </button>
                        </div>
                    </div>
                </form>
                <div v-else>
                    <Alert
                        type="info"
                        :dismissible="true"
                        :message="t('global.user.security.2fa.login_info')"
                    />
                    <label
                        for="2fa-code"
                        class="text-center w-100 col-form-label"
                    >
                        {{ t('global.2fa_code') }}
                        <i class="fas fa-fw fa-qrcode" />
                    </label>

                    <TwoFactorChallenge
                        classes="w-50 mx-auto"
                        input-classes="w-50 mx-auto"
                        :errors="state.twoFa.errors"
                        @confirm="confirmSecondFactor"
                    />
                </div>
            </div>
        </div>
    </div>
</template>


<script>
        import {
            reactive,
            onMounted,
        } from 'vue';

        import { useI18n } from 'vue-i18n';
        import { useRoute } from 'vue-router';
        import router from '%router';
        import useUserStore from '@/bootstrap/stores/user.js';

        import {
            getErrorMessages,
            getValidClass,
            getUser,
        } from '@/helpers/helpers.js';

        import TwoFactorChallenge from '@/components/user/TwoFactorChallenge.vue';

        export default {
            components: {
                TwoFactorChallenge,
            },
            setup() {
                const { t, locale } = useI18n();
                const route = useRoute();
                const userStore = useUserStore();
                // DATA
                const state = reactive({
                    user: {},
                    twoFa: {
                        required: false,
                        code: '',
                        errors: [],
                    },
                    redirect: {
                        name: 'home'
                    },
                    submitting: false,
                    error: {},
                });

                // FUNCTIONS
                const login = async _ => {
                    state.submitting = true;
                    state.error = {};
                    const credentials = {
                        password: state.user.password,
                        email: state.user.email,
                    };
                    await userStore.login(credentials)
                        .then(data => {
                            if(data?.two_factor === true) {
                                state.twoFa.required = true;
                                state.twoFa.code = '';
                            } else {
                                state.submitting = false;
                                state.error = {};
                                if(route.query.redirectTo) {
                                    router.push(route.query.redirectTo);
                                } else {
                                    router.push({
                                        name: 'home',
                                    });
                                }
                            }
                        })
                        .catch(e => {
                            state.submitting = false;
                            userStore.logout();
                            state.error = getErrorMessages(e);
                            return Promise.reject();
                        });
                };

                const confirmSecondFactor = async challenge => {
                    if(challenge.length != 6) return;
                    const errors = await userStore.confirmTwoFactorChallenge(challenge);
                    if(errors && Object.keys(errors).length > 0) {
                        state.twoFa.errors = errors;
                    } else {
                        state.twoFa.errors = [];
                    }
                };

                // ON MOUNTED
                onMounted(_ => {
                    if(userStore.userLoggedIn) {
                        router.push({
                            name: 'home'
                        });
                    }
                    // if(auth.check()) {
                    //     router.push({
                    //         name: 'home'
                    //     });
                    // }
                    // const lastRoute = auth.redirect() ? auth.redirect().from : undefined;
                    // const currentRoute = useRoute();
                    // if(lastRoute && lastRoute.name != 'login') {
                    //     state.redirect = {
                    //         name: lastRoute.name,
                    //         params: lastRoute.params,
                    //         query: lastRoute.query
                    //     };
                    // } else if(currentRoute.query && currentRoute.query.redirect) {
                    //     state.redirect = {
                    //         path: currentRoute.query.redirect
                    //     };
                    // }
                });

                // RETURN
                return {
                    t,
                    state,
                    login,
                    confirmSecondFactor,
                    getValidClass,
                };
            },
        }
</script>
