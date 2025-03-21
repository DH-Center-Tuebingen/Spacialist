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
                <form @submit.prevent="login">
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
                                class="btn btn-primary"
                                :disabled="state.submitting"
                            >
                                {{ t('global.login') }}
                            </button>
                        </div>
                    </div>
                </form>
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
        import useUserStore from '@/bootstrap/stores/user.js';
        import router from '%router';

        import {
            getErrorMessages,
            getValidClass,
            getUser,
        } from '@/helpers/helpers.js';

        export default {
            setup() {
                const { t, locale } = useI18n();
                const route = useRoute();
                const userStore = useUserStore();
                // DATA
                const state = reactive({
                    user: {},
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
                        password: state.user.password
                    };
                    // dirty check if email field should be treated
                    // as actual email address or nickname
                    if(state.user.email.includes('@')) {
                        credentials.email = state.user.email;
                    } else {
                        credentials.nickname = state.user.email;
                    }
                    await userStore.login(credentials)
                        .then(_ => {
                            state.submitting = false;
                            state.error = {};
                            if(route.query.redirectTo) {
                                router.push(route.query.redirectTo);
                            } else {
                                router.push({
                                    name: 'home',
                                });
                            }
                        })
                        .catch(e => {
                            state.submitting = false;
                            userStore.logout();
                            state.error = getErrorMessages(e);
                            return Promise.reject();
                        });
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
                    getValidClass,
                };
            },
        }
</script>
