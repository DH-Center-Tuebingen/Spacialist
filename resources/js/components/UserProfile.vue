<template>
    <div class="row">
        <div class="col-3">
            <h4>
                {{ $t('global.user.avatar') }}
            </h4>
            <user-avatar :user="user" class="d-flex justify-content-center"></user-avatar>
            <div class="text-center mt-3">
                <button type="button" class="btn btn-outline-success mr-2">
                    {{ $t('global.upload') }}
                </button>
                <button type="button" class="btn btn-outline-danger ml-2">
                    {{ $t('global.delete') }}
                </button>
            </div>
        </div>
        <div class="col-3">
            <h4>
                {{ $t('global.user.info_title') }}
            </h4>
            <form id="profile-user-info-form" name="profile-user-info-form" role="form" @submit.prevent="updateUserInformation()">
                <div class="form-group">
                    <label class="font-weight-bold" for="profile-user-info-name">
                        {{ $t('global.name') }}:
                    </label>
                    <input type="text" class="form-control" id="profile-user-info-name" v-model="user.name" />
                </div>
                <div class="form-group">
                    <label class="font-weight-bold" for="profile-user-info-nickname">
                        {{ $t('global.nickname') }}:
                    </label>
                    <input type="text" class="form-control" id="profile-user-info-nickname" v-model="user.nickname" />
                </div>
                <button type="submit" class="btn btn-outline-success">
                    {{ $t('global.save') }}
                </button>
            </form>
        </div>
        <div class="col-3">
            <h4>
                {{ $t('global.user.contact') }}
            </h4>
            <form id="profile-user-contact-form" name="profile-user-contact-form" role="form" @submit.prevent="updateUserInformation()">
                <div class="form-group">
                    <label class="font-weight-bold" for="profile-user-contact-email">
                        <i class="fas fa-fw fa-envelope"></i> {{ $t('global.email') }}:
                    </label>
                    <input type="text" class="form-control" id="profile-user-contact-email" v-model="user.email" />
                </div>
                <div class="form-group">
                    <label class="font-weight-bold" for="profile-user-contact-phonenumber">
                        <i class="fas fa-fw fa-mobile-alt"></i> {{ $t('global.phonenumber') }}:
                    </label>
                    <input type="text" class="form-control" id="profile-user-contact-phonenumber" v-model="user.phonenumber" />
                </div>
                <button type="submit" class="btn btn-outline-success">
                    {{ $t('global.save') }}
                </button>
            </form>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {},
        methods: {
        },
        data() {
            return {
            }
        },
        computed: {
            user() {
                return this.$auth.user();
            }
        }
    }
</script>
