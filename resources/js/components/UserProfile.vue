<template>
    <div class="row">
        <div class="col-3">
            <h4>
                {{ $t('global.user.avatar') }}
            </h4>
            <file-upload
                accept="image/*"
                class="w-100"
                ref="upload"
                v-model="fileQueue"
                :custom-action="uploadFile"
                :directory="false"
                :drop="true"
                :multiple="false"
                @input-file="inputFile">
                    <user-avatar :user="avatarUser" class="d-flex justify-content-center"></user-avatar>
            </file-upload>
            <div class="text-center mt-3">
                <button type="button" class="btn btn-outline-danger ml-2" :disabled="!avatarUser.avatar" @click="deleteAvatar()">
                    {{ $t('global.delete') }}
                </button>
            </div>
        </div>
        <div class="col-6">
            <form id="profile-user-info-form" name="profile-user-info-form" class="row" role="form" @submit.prevent="updateUserInformation()">
                <div class="col-6">
                    <h4>
                        {{ $t('global.user.info_title') }}
                    </h4>
                    <div class="form-group">
                        <label class="font-weight-bold" for="profile-user-info-name">
                            {{ $t('global.name') }}:
                        </label>
                        <input type="text" class="form-control" id="profile-user-info-name" v-model="localUser.name" />
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold" for="profile-user-info-nickname">
                            {{ $t('global.nickname') }}:
                        </label>
                        <input type="text" class="form-control" id="profile-user-info-nickname" v-model="localUser.nickname" />
                    </div>
                    <button type="submit" class="btn btn-outline-success">
                        {{ $t('global.save') }}
                    </button>
                </div>
                <div class="col-6">
                    <h4>
                        {{ $t('global.user.contact') }}
                    </h4>
                    <div class="form-group">
                        <label class="font-weight-bold" for="profile-user-contact-email">
                            <i class="fas fa-fw fa-envelope"></i> {{ $t('global.email') }}:
                        </label>
                        <input type="email" class="form-control" id="profile-user-contact-email" v-model="localUser.email" />
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold" for="profile-user-contact-phonenumber">
                            <i class="fas fa-fw fa-mobile-alt"></i> {{ $t('global.phonenumber') }}:
                        </label>
                        <input type="tel" class="form-control" id="profile-user-contact-phonenumber" v-model="localUser.phonenumber" />
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {},
        methods: {
            updateUserInformation() {
                let data = {};
                if(this.localUser.name !== '' && this.localUser.name != this.user.name) {
                    data.name = this.localUser.name;
                }
                if(this.localUser.nickname !== '' && this.localUser.nickname != this.user.nickname) {
                    data.nickname = this.localUser.nickname;
                }
                if(this.localUser.email !== '' && this.localUser.email != this.user.email) {
                    data.email = this.localUser.email;
                }
                if(this.localUser.phonenumber !== '' && this.localUser.phonenumber != this.user.phonenumber) {
                    data.phonenumber = this.localUser.phonenumber;
                }

                this.$http.patch(`user/${this.user.id}`, data).then(response => {
                    this.updateUserObjects(response.data);
                });
            },
            deleteAvatar() {
                this.$http.delete(`user/${this.user.id}/avatar`).then(response => {
                    this.updateUserObjects({
                        avatar: false,
                        avatar_url: '',
                    });
                });
            },
            updateUserObjects(data) {
                this.$auth.user({
                    ...this.$auth.user(),
                    ...data
                });
                this.localUser = {
                    ...this.$auth.user()
                };
                this.avatarUser = {
                    ...this.$auth.user()
                };
            },
            uploadFile(file, component) {
                let formData = new FormData();
                formData.append('file', file.file);
                return $http.post(`user/${this.user.id}/avatar`, formData).then(response => {
                    this.updateUserObjects(response.data);
                });
            },
            inputFile(newFile, oldFile) {
                // Wait for response
                if(newFile && oldFile && newFile.success && !oldFile.success) {
                }

                // Enable automatic upload
                if(Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error) {
                    if(!this.$refs.upload.active) {
                        this.$refs.upload.active = true
                    }
                }
            },
        },
        data() {
            return {
                localUser: {
                    ...this.$auth.user()
                },
                avatarUser: {
                    ...this.$auth.user()
                },
                fileQueue: [],
            }
        },
        computed: {
            user() {
                return this.$auth.user();
            }
        }
    }
</script>
