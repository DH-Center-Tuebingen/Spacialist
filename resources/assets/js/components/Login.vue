<template>
    <div class="col-md-4 offset-md-4">
        <div class="login-header mb-3">
            <h1>Spacialist</h1>
            <img src="img/logo.png" width="100px;" />
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Login</h5>
                <h6 class="card-subtitle mb-2 text-muted">Welcome to Spacialist</h6>
                <p class="card-text">
                    <form @submit.prevent="login">
                        <div class="form-group">
                            <label for="email" class="col-md-4 col-form-label">E-Mail Address <i class="fas fa-fw fa-envelope"></i></label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" v-model="user.email" name="email" required autofocus>

                                <!-- @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif -->
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="col-md-4 col-form-label">Password <i class="fas fa-fw fa-unlock-alt"></i></label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" v-model="user.password" name="password" required>

                                <!-- @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif -->
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember" v-model="user.remember"> Remember Me
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Login
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
    export default {
        props: {
            onLogin: {
                required: false,
                type: Function
            }
        },
        mounted() {},
        methods: {
            login() {
                const vm = this;
                let redirect;
                if(vm.$router.currentRoute.query) {
                    redirect = vm.$router.currentRoute.query.redirect;
                }
                let to = {};
                if(redirect) {
                    to.path = redirect;
                } else {
                    to.name = 'home';
                }
                vm.$auth.login({
                    data: {
                        email: vm.user.email,
                        password: vm.user.password
                    },
                    rememberMe: vm.user.remember,
                    redirect: to,
                    success: _ => {
                        if(vm.onLogin) {
                            vm.onLogin();
                        }
                    },
                    fetchUser: true
                });
            }
        },
        data() {
            return {
                user: {}
            }
        }
    }
</script>
