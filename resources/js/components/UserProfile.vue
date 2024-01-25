<template>
    <div class="row">
        <div class="col-3 pt-2 border-end">
            <file-upload
                ref="upload"
                v-model="state.fileQueue"
                accept="image/*"
                class="w-100"
                :custom-action="uploadFile"
                :directory="false"
                :drop="true"
                :multiple="false"
                @input-file="inputFile"
            >
                <user-avatar
                    :user="state.avatarUser"
                    class="d-flex justify-content-center"
                />
            </file-upload>
            <div class="text-center mt-3">
                <button
                    type="button"
                    class="btn btn-outline-danger ms-2"
                    :disabled="!state.avatarUser.avatar"
                    @click="deleteAvatar()"
                >
                    {{ t('global.delete') }}
                </button>
            </div>
        </div>
        <div class="col-6 pt-2">
            <div class="d-flex justify-content-between">
                <h3>
                    {{ t('global.user.info_title') }}
                </h3>
                <div>
                    <button
                        type="submit"
                        class="btn btn-outline-success"
                        form="profile-user-info-form"
                    >
                        <i class="fas fa-fw fa-save" />
                        {{ t('global.save') }}
                    </button>
                    <button
                        type="button"
                        class="btn btn-outline-warning ms-3"
                        :disabled="!state.isDirty"
                        @click="resetUserInfo()"
                    >
                        <i class="fas fa-fw fa-undo" />
                        {{ t('global.reset') }}
                    </button>
                </div>
            </div>
            <form
                id="profile-user-info-form"
                name="profile-user-info-form"
                class="row mt-3"
                role="form"
                @submit.prevent="updateUserInformation()"
            >
                <div class="col-6">
                    <h4>
                        {{ t('global.user.personal_info_title') }}
                    </h4>
                    <div>
                        <label
                            class="fw-bold"
                            for="profile-user-info-name"
                        >
                            <i class="fas fa-fw fa-user" />
                            {{ t('global.name') }}:
                        </label>
                        <input
                            id="profile-user-info-name"
                            v-model="state.localUser.name"
                            type="text"
                            class="form-control"
                        >
                    </div>
                    <div class="mt-2">
                        <label
                            class="fw-bold"
                            for="profile-user-info-nickname"
                        >
                            <i class="fas fa-fw fa-user-tag" />
                            {{ t('global.nickname') }}:
                        </label>
                        <input
                            id="profile-user-info-nickname"
                            v-model="state.localUser.nickname"
                            type="text"
                            class="form-control"
                        >
                    </div>
                    <div class="mt-2">
                        <label
                            class="fw-bold"
                            for="profile-user-info-role"
                        >
                            <i class="fas fa-fw fa-id-card-clip" />
                            {{ t('global.user.role') }}:
                        </label>
                        <input
                            id="profile-user-info-role"
                            v-model="state.localUser.metadata.role"
                            type="text"
                            class="form-control"
                        >
                    </div>
                    <div class="mt-2">
                        <label
                            class="fw-bold"
                            for="profile-user-info-field"
                        >
                            <i class="fas fa-fw fa-chalkboard-user" />
                            {{ t('global.user.field') }}:
                        </label>
                        <input
                            id="profile-user-info-field"
                            v-model="state.localUser.metadata.field"
                            type="text"
                            class="form-control"
                        >
                    </div>
                    <div class="mt-2">
                        <label
                            class="fw-bold"
                            for="profile-user-info-institution"
                        >
                            <i class="fas fa-fw fa-school" />
                            {{ t('global.user.institution') }}:
                        </label>
                        <input
                            id="profile-user-info-institution"
                            v-model="state.localUser.metadata.institution"
                            type="text"
                            class="form-control"
                        >
                    </div>
                    <div class="mt-2">
                        <label
                            class="fw-bold"
                            for="profile-user-info-department"
                        >
                            <i class="fas fa-fw fa-users-between-lines" />
                            {{ t('global.user.department') }}:
                        </label>
                        <input
                            id="profile-user-info-department"
                            v-model="state.localUser.metadata.department"
                            type="text"
                            class="form-control"
                        >
                    </div>
                </div>
                <div class="col-6">
                    <h4>
                        {{ t('global.user.contact') }}
                    </h4>
                    <div>
                        <label
                            class="fw-bold"
                            for="profile-user-contact-email"
                        >
                            <i class="fas fa-fw fa-envelope" /> {{ t('global.email') }}:
                        </label>
                        <input
                            id="profile-user-contact-email"
                            v-model="state.localUser.email"
                            type="email"
                            class="form-control"
                        >
                    </div>
                    <div class="mt-2">
                        <label
                            class="fw-bold"
                            for="profile-user-contact-phonenumber"
                        >
                            <i class="fas fa-fw fa-mobile-alt" /> {{ t('global.phonenumber') }}:
                        </label>
                        <input
                            id="profile-user-contact-phonenumber"
                            v-model="state.localUser.metadata.phonenumber"
                            type="tel"
                            class="form-control"
                        >
                    </div>
                    <div class="mt-2">
                        <label
                            class="fw-bold"
                            for="profile-user-contact-orcid"
                        >
                            <i class="fab fa-fw fa-orcid" /> {{ t('global.orcid') }}:
                        </label>
                        <input
                            id="profile-user-contact-orcid"
                            v-model="state.localUser.metadata.orcid"
                            type="text"
                            class="form-control"
                            :class="{'is-invalid': state.invalidOrcid}"
                        >
                        <div
                            v-if="state.invalidOrcid"
                            class="invalid-feedback"
                        >
                            {{ t('global.user.invalid_orcid') }}
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        ref,
        watch,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import auth from '@/bootstrap/auth.js';
    import store from '@/bootstrap/store.js';

    import {
        getUser,
        _cloneDeep,
    } from '@/helpers/helpers.js';
    import {
        isValidOrcid,
    } from '@/helpers/validators.js';
    import {
        setUserAvatar,
        patchUserData,
        deleteUserAvatar,
    } from '@/api.js';

    export default {
        setup(props) {
            const { t } = useI18n();

            // FETCH

            // FUNCTIONS
            const fields = {
                name: {
                    required: true,
                },
                nickname: {
                    required: true,
                },
                email: {
                    required: true,
                },
                phonenumber: {
                    meta: true,
                },
                role: {
                    meta: true,
                },
                field: {
                    meta: true,
                },
                institution: {
                    meta: true,
                },
                department: {
                    meta: true,
                },
            };
            // TODO replace fields and function with vee-validate
            const updateUserInformation = _ => {
                let data = {};
                for(let k in fields) {
                    let validEntry = true;
                    const curr = fields[k];
                    const compA = curr.meta ? state.localUser.metadata[k] : state.localUser[k];
                    const compB = curr.meta ? state.user.metadata[k] : state.user[k];
                    if(compA == compB) {
                        validEntry = false;
                    } else {
                        if(curr.required && compA === '') {
                            validEntry = false;
                        }
                    }
                    if(validEntry) {
                        data[k] = compA;
                    }
                }
                if(state.localUser.metadata.orcid != state.user.metadata.orcid && (!state.localUser.metadata.orcid || isValidOrcid(state.localUser.metadata.orcid))) {
                    data.orcid = state.localUser.metadata.orcid;
                    state.invalidOrcid = false;
                } else if(state.localUser.metadata.orcid && !isValidOrcid(state.localUser.metadata.orcid)) {
                    state.invalidOrcid = true;
                    return;
                }

                // No changes, no update
                if(Object.keys(data).length === 0) return;

                patchUserData(state.user.id, data).then(data => {
                    updateUserObjects(data);
                });
            };
            const resetUserInfo = _ => {
                state.localUser = appliedMetadata(state.user);
                state.isDirty = false;
            };
            const deleteAvatar = _ => {
                deleteUserAvatar().then(data => {
                    updateUserObjects({
                        avatar: false,
                        avatar_url: '',
                    });
                });
            };
            const updateUserObjects = data => {
                // Workaround to update avatar image, because url may not change
                data.avatar_url += `#${Date.now()}`;
                auth.user({
                    ...getUser(),
                    ...data
                });
                auth.user(
                    appliedMetadata(getUser())
                );
                state.localUser = appliedMetadata(getUser());
                state.avatarUser = appliedMetadata(getUser());
            };
            const uploadFile = (file, component) => {
                return setUserAvatar(file.file).then(data => {
                    updateUserObjects(data)
                });
            };
            const inputFile = (newFile, oldFile) => {
                // Wait for response
                if(newFile && oldFile && newFile.success && !oldFile.success) {
                }

                // Enable automatic upload
                if(!!newFile && (Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error)) {
                    if(!newFile.active) {
                        newFile.active = true
                    }
                }
            };
            const appliedMetadata = u => {
                const nu = _cloneDeep(u)
                return u.metadata ? nu : {...nu, ...{metadata: {}}};
            };

            // DATA
            const state = reactive({
                localUser: appliedMetadata(getUser()),
                avatarUser: appliedMetadata(getUser()),
                fileQueue: [],
                invalidOrcid: ref(false),
                user: computed(_ => appliedMetadata(getUser())),
                isDirty: false,
            });

            // WATCHER
            watch(state.localUser, (newValue, oldValue) => {
                state.isDirty = true;
            });

            // RETURN
            return {
                t,
                inputFile,
                uploadFile,
                deleteAvatar,
                updateUserInformation,
                resetUserInfo,
                state,
            };
        },
    }
</script>
