<template>
    <div class="row">
        <div class="col-3 pt-2 border-end px-5 overflow-hidden">
            <header class="d-flex justify-content-center my-5">
                <div class="position-relative">
                    <file-upload
                        ref="uploadAvatarInput"
                        v-model="state.fileQueue"
                        accept="image/*"
                        class="w-100"
                        :custom-action="uploadFile"
                        :directory="false"
                        :drop="true"
                        :multiple="false"
                        :disabled="state.uploadingAvatar"
                        @input-file="inputFile"
                    >
                        <user-avatar
                            :user="state.user"
                            class="d-flex justify-content-center pe-none user-select-none border-1"
                        />
                    </file-upload>

                    <LoadingButton
                        :loading="state.uploadingAvatar"
                        :spinner-classes="'fs-4'"
                        class="position-absolute bottom-0 end-0 btn-fab-xl btn-outline-primary z-2"
                        @click="clickFileUpload"
                    >
                        <template #icon>
                            <i class="fs-4 fas fa-fw fa-file-upload" />
                        </template>
                    </LoadingButton>

                    <button
                        v-if="state.user.avatar"
                        type="button"
                        class="position-absolute bottom-0 start-0 btn btn-fab-xl btn-outline-danger z-2"
                        @click.prevent="deleteAvatar()"
                    >
                        <i class="fs-4 fas fa-fw fa-trash" />
                    </button>
                </div>
            </header>

            <h2 class="mb-1">
                {{ state.user.name }}
            </h2>
            <div>
                <span class="subtitle opacity-25 fs-4 fst-italic fw-bold">
                    @{{ state.user.nickname }}
                </span>
            </div>
        </div>
        <div class="col-6 pt-2">
            <div class="d-flex justify-content-between">
                <h2>
                    {{ t('global.user.info_title') }}
                </h2>
                <div class="d-flex flex-row align-items-center gap-2">
                    <button
                        type="submit"
                        class="btn btn-outline-success btn-sm"
                        form="profile-user-info-form"
                        :disabled="!v.form.dirty || !v.form.valid"
                    >
                        <i class="fas fa-fw fa-save" />
                        {{ t('global.save') }}
                    </button>
                    <button
                        type="button"
                        class="btn btn-outline-warning btn-sm"
                        :disabled="!v.form.dirty"
                        @click="resetUserInfo()"
                    >
                        <i class="fas fa-fw fa-undo" />
                        {{ t('global.reset') }}
                    </button>
                </div>
            </div>
            <!-- Rollen -->
            <div class="d-flex align-items-center gap-2 mt-1">
                <span>
                    <i class="fas fa-fw fa-shield-alt" />
                </span>
                <span class="fw-bold">
                    {{ t('global.roles') }}
                </span>
                <span
                    v-for="role in state.user.roles"
                    :key="role.id"
                    class="badge bg-primary me-1 clickable"
                    @click.prevent="showAccessControlModal(role.id, true)"
                >
                    {{ role.name }}
                </span>
            </div>
            <form
                id="profile-user-info-form"
                name="profile-user-info-form"
                class="row mt-3"
                role="form"
                @submit.prevent="updateUserInformation()"
            >
                <div class="col-6">
                    <h3>
                        {{ t('global.user.personal_info_title') }}
                    </h3>
                    <div>
                        <label
                            class="form-label mb-1"
                            for="profile-user-info-name"
                        >
                            <i class="fas fa-fw fa-user" />
                            {{ t('global.name') }}:
                        </label>
                        <input
                            id="profile-user-info-name"
                            v-model="v.fields.name.value"
                            type="text"
                            class="form-control"
                            :class="getClassByValidation(v.fields.name.errors)"
                            required
                            name="profile-user-info-name"
                            @input="v.fields.name.handleChange"
                        >

                        <div class="invalid-feedback">
                            <span
                                v-for="(msg, i) in v.fields.name.errors"
                                :key="`name-errors-${i}`"
                            >
                                {{ msg }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label
                            class="form-label mb-1"
                            for="profile-user-info-nickname"
                        >
                            <i class="fas fa-fw fa-user-tag" />
                            {{ t('global.nickname') }}:
                        </label>
                        <input
                            id="profile-user-info-nickname"
                            v-model="v.fields.nickname.value"
                            type="text"
                            class="form-control"
                            :class="getClassByValidation(v.fields.nickname.errors)"
                            required
                            name="profile-user-info-nickname"
                            @input="v.fields.nickname.handleChange"
                        >

                        <div class="invalid-feedback">
                            <span
                                v-for="(msg, i) in v.fields.nickname.errors"
                                :key="`nickname-errors-${i}`"
                            >
                                {{ msg }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label
                            class="form-label mb-1"
                            for="profile-user-info-role"
                        >
                            <i class="fas fa-fw fa-id-card-clip" />
                            {{ t('global.user.role') }}:
                        </label>
                        <input
                            id="profile-user-info-role"
                            v-model="v.fields.role.value"
                            type="text"
                            class="form-control"
                            :class="getClassByValidation(v.fields.role.errors)"
                            name="profile-user-info-role"
                            @input="v.fields.role.handleChange"
                        >

                        <div class="invalid-feedback">
                            <span
                                v-for="(msg, i) in v.fields.role.errors"
                                :key="`role-errors-${i}`"
                            >
                                {{ msg }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label
                            class="form-label mb-1"
                            for="profile-user-info-field"
                        >
                            <i class="fas fa-fw fa-chalkboard-user" />
                            {{ t('global.user.field') }}:
                        </label>
                        <input
                            id="profile-user-info-field"
                            v-model="v.fields.field.value"
                            type="text"
                            class="form-control"
                            :class="getClassByValidation(v.fields.field.errors)"
                            name="profile-user-info-field"
                            @input="v.fields.field.handleChange"
                        >

                        <div class="invalid-feedback">
                            <span
                                v-for="(msg, i) in v.fields.field.errors"
                                :key="`field-errors-${i}`"
                            >
                                {{ msg }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label
                            class="form-label mb-1"
                            for="profile-user-info-institution"
                        >
                            <i class="fas fa-fw fa-school" />
                            {{ t('global.user.institution') }}:
                        </label>
                        <input
                            id="profile-user-info-institution"
                            v-model="v.fields.institution.value"
                            type="text"
                            class="form-control"
                            :class="getClassByValidation(v.fields.institution.errors)"
                            name="profile-user-info-institution"
                            @input="v.fields.institution.handleChange"
                        >

                        <div class="invalid-feedback">
                            <span
                                v-for="(msg, i) in v.fields.institution.errors"
                                :key="`institution-errors-${i}`"
                            >
                                {{ msg }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label
                            class="form-label mb-1"
                            for="profile-user-info-department"
                        >
                            <i class="fas fa-fw fa-users-between-lines" />
                            {{ t('global.user.department') }}:
                        </label>
                        <input
                            id="profile-user-info-department"
                            v-model="v.fields.department.value"
                            type="text"
                            class="form-control"
                            :class="getClassByValidation(v.fields.department.errors)"
                            name="profile-user-info-department"
                            @input="v.fields.department.handleChange"
                        >

                        <div class="invalid-feedback">
                            <span
                                v-for="(msg, i) in v.fields.department.errors"
                                :key="`department-errors-${i}`"
                            >
                                {{ msg }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <h3>
                        {{ t('global.user.contact') }}
                    </h3>
                    <div>
                        <label
                            class="form-label mb-1"
                            for="profile-user-contact-email"
                        >
                            <i class="fas fa-fw fa-envelope" /> {{ t('global.email') }}:
                        </label>
                        <input
                            id="profile-user-contact-email"
                            v-model="v.fields.email.value"
                            type="text"
                            class="form-control"
                            :class="getClassByValidation(v.fields.email.errors)"
                            required
                            name="profile-user-contact-email"
                            @input="v.fields.email.handleChange"
                        >

                        <div class="invalid-feedback">
                            <span
                                v-for="(msg, i) in v.fields.email.errors"
                                :key="`email-errors-${i}`"
                            >
                                {{ msg }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label
                            class="form-label mb-1"
                            for="profile-user-contact-phonenumber"
                        >
                            <i class="fas fa-fw fa-mobile-alt" /> {{ t('global.phonenumber') }}:
                        </label>
                        <input
                            id="profile-user-contact-phonenumber"
                            v-model="v.fields.phonenumber.value"
                            type="text"
                            class="form-control"
                            :class="getClassByValidation(v.fields.phonenumber.errors)"
                            name="profile-user-contact-phonenumber"
                            @input="v.fields.phonenumber.handleChange"
                        >

                        <div class="invalid-feedback">
                            <span
                                v-for="(msg, i) in v.fields.phonenumber.errors"
                                :key="`phonenumber-errors-${i}`"
                            >
                                {{ msg }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label
                            class="form-label mb-1"
                            for="profile-user-contact-orcid"
                        >
                            <i class="fab fa-fw fa-orcid" /> {{ t('global.orcid') }}:
                        </label>
                        <input
                            id="profile-user-contact-orcid"
                            v-model="v.fields.orcid.value"
                            type="text"
                            class="form-control"
                            :class="getClassByValidation(v.fields.orcid.errors)"
                            name="profile-user-contact-orcid"
                            @input="v.fields.orcid.handleChange"
                        >

                        <div class="invalid-feedback">
                            <span
                                v-for="(msg, i) in v.fields.orcid.errors"
                                :key="`orcid-errors-${i}`"
                            >
                                {{ msg }}
                            </span>
                        </div>
                    </div>
                    <h3 class="mt-2">
                        {{ t('global.user.security.title') }}
                    </h3>
                    <TwoFactor :user="state.user" />
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
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import { useForm, useField } from 'vee-validate';
    import {
        object,
    } from 'yup';

    import {
        simple_max as vSimpleMax,
        name as vName,
        nickname as vNickname,
        email as vEmail,
        phone as vPhone,
        orcid as vOrcid,
    } from '@/bootstrap/validation.js';

    import useUserStore from '@/bootstrap/stores/user.js';

    import {
        getUser,
        getClassByValidation,
        _cloneDeep,
    } from '@/helpers/helpers.js';

    import {
        showAccessControlModal,
    } from '@/helpers/modal.js';

    import LoadingButton from '@/components/forms/button/LoadingButton.vue';
    import TwoFactor from '@/components/user/TwoFactor.vue';

    export default {
        components: {
            LoadingButton,
            TwoFactor,
        },
        setup(props) {
            const { t } = useI18n();
            const userStore = useUserStore();

            // FUNCTIONS
            const initializeValidation = _ => {
                const schemaObj = {};
                for(let k in fields) {
                    const curr = fields[k];

                    schemaObj[k] = curr.rules;
                }

                const {
                    meta: formMeta,
                } = useForm({
                    validationSchema: object(schemaObj),
                });
                v.form = formMeta;

                for(let k in fields) {
                    const curr = fields[k];

                    const {
                        errors,
                        meta,
                        value,
                        handleChange,
                        resetField,
                    } = useField(k, curr.rules, {
                        initialValue: curr.meta ? state.user.metadata[k] : state.user[k]
                    });
                    v.fields[k] = {
                        errors,
                        meta,
                        value,
                        handleChange,
                        resetField,
                    };
                }
            };
            const updateUserInformation = _ => {
                if(!v.form.dirty || !v.form.valid) return;

                const data = {};
                for(let k in fields) {
                    if(v.fields[k].meta.dirty && v.fields[k].meta.dirty) {
                        data[k] = v.fields[k].value;
                    }
                }

                // No changes, no update
                if(Object.keys(data).length === 0) return;

                userStore.updateUser(state.user.id, data, true).then(d2 => {
                    resetDirty();
                });
            };
            const resetUserInfo = _ => {
                for(let k in fields) {
                    v.fields[k].resetField({
                        value: fields[k].meta ? state.user.metadata[k] : state.user[k]
                    });
                }
            };
            const resetDirty = _ => {
                for(let k in fields) {
                    v.fields[k].resetField({
                        value: v.fields[k].value,
                    });
                }
            };
            const deleteAvatar = async _ => {
                try {
                    await userStore.deleteAvatar();
                } catch(e) {
                    console.error(e);
                }
            };
            const uploadFile = async (file, component) => {
                try {
                    state.uploadingAvatar = true;
                    await userStore.setAvatar(file.file);
                } catch(e) {
                    console.error(e);
                } finally {
                    state.uploadingAvatar = false;
                }
            };
            const inputFile = (newFile, oldFile) => {
                // Wait for response
                if(newFile && oldFile && newFile.success && !oldFile.success) {
                }

                // Enable automatic upload
                if(!!newFile && (Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error)) {
                    if(!newFile.active) {
                        newFile.active = true;
                    }
                }
            };
            const appliedMetadata = u => {
                const nu = _cloneDeep(u);
                return u.metadata ? nu : {...nu, ...{metadata: {}}};
            };

            // DATA
            const fields = {
                name: {
                    rules: vName(),
                },
                nickname: {
                    rules: vNickname(),
                },
                email: {
                    rules: vEmail(),
                },
                phonenumber: {
                    rules: vPhone(),
                    meta: true,
                },
                orcid: {
                    rules: vOrcid(),
                    meta: true,
                },
                role: {
                    rules: vSimpleMax(),
                    meta: true,
                },
                field: {
                    rules: vSimpleMax(),
                    meta: true,
                },
                institution: {
                    rules: vSimpleMax(),
                    meta: true,
                },
                department: {
                    rules: vSimpleMax(),
                    meta: true,
                },
            };
            const state = reactive({
                uploadingAvatar: false,
                fileQueue: [],
                user: computed(_ => appliedMetadata(getUser())),
            });
            const v = reactive({
                form: null,
                fields: {},
            });

            initializeValidation();

            const uploadAvatarInput = ref(null);
            const clickFileUpload = _ => {
                uploadAvatarInput.value.$el.querySelector('input[type="file"]').click();
            };

            // RETURN
            return {
                t,
                clickFileUpload,
                getClassByValidation,
                inputFile,
                uploadFile,
                deleteAvatar,
                updateUserInformation,
                resetUserInfo,
                state,
                showAccessControlModal,
                uploadAvatarInput,
                v,
            };
        },
    };
</script>
