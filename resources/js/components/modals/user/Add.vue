<template>
    <vue-final-modal
        class="modal-container modal"
        content-class=""
        name="add-user-modal"
    >
        <div class="sp-modal-content sp-modal-content-xs">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{
                        t('main.user.modal.new.title')
                    }}
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body">
                <form
                    id="newUserForm"
                    name="newUserForm"
                    role="form"
                    @submit.prevent="onAdd()"
                >
                    <div class="mb-3">
                        <label
                            class="col-form-label col-12"
                            for="name"
                        >
                            {{ t('global.name') }}
                            <span class="text-danger">*</span>:
                        </label>
                        <div class="col-12">
                            <input
                                id="name"
                                v-model="v.fields.name.value"
                                class="form-control"
                                :class="getClassByValidation(state.errors.name)"
                                type="text"
                                required
                                @input="e => handleChange(e, 'name')"
                            >

                            <div class="invalid-feedback">
                                <span
                                    v-for="(msg, i) in state.errors.name"
                                    :key="i"
                                >
                                    {{ msg }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label
                            class="col-form-label col-12"
                            for="nickname"
                        >
                            {{ t('global.nickname') }}
                            <span class="text-danger">*</span>:
                        </label>
                        <div class="col-12">
                            <input
                                id="nickname"
                                v-model="v.fields.nickname.value"
                                class="form-control"
                                :class="getClassByValidation(state.errors.nickname)"
                                type="text"
                                required
                                @input="e => handleChange(e, 'nickname')"
                            >

                            <div class="invalid-feedback">
                                <span
                                    v-for="(msg, i) in state.errors.nickname"
                                    :key="i"
                                >
                                    {{ msg }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label
                            class="col-form-label col-12"
                            for="email"
                        >
                            {{ t('global.email') }}
                            <span class="text-danger">*</span>:
                        </label>
                        <div class="col-12">
                            <input
                                id="email"
                                v-model="v.fields.email.value"
                                class="form-control"
                                :class="getClassByValidation(state.errors.email)"
                                type="email"
                                required
                                @input="e => handleChange(e, 'email')"
                            >

                            <div class="invalid-feedback">
                                <span
                                    v-for="(msg, i) in state.errors.email"
                                    :key="i"
                                >
                                    {{ msg }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label
                            class="col-form-label col-12"
                            for="password"
                        >
                            {{ t('global.password') }}
                            <span class="text-danger">*</span>:
                        </label>
                        <div class="col-12">
                            <input
                                id="password"
                                v-model="v.fields.password.value"
                                class="form-control d-inline"
                                :class="getClassByValidation(state.errors.password)"
                                type="password"
                                required
                                @input="e => handleChange(e, 'password')"
                            >
                            <a
                                href="#"
                                class="text-muted ms--4-5"
                                tabindex="-1"
                                @click.prevent="togglePasswordVisibility()"
                            >
                                <span v-show="!state.showPassword">
                                    <i class="fas fa-fw fa-eye" />
                                </span>
                                <span v-show="state.showPassword">
                                    <i class="fas fa-fw fa-eye-slash" />
                                </span>
                            </a>

                            <div class="invalid-feedback">
                                <span
                                    v-for="(msg, i) in state.errors.password"
                                    :key="i"
                                >
                                    {{ msg }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label
                            class="col-form-label col-12"
                            for="password_confirm"
                        >
                            {{ t('global.confirm_password') }}
                            <span class="text-danger">*</span>:
                        </label>
                        <div class="col-12">
                            <input
                                id="password_confirm"
                                v-model="v.fields.password_confirm.value"
                                class="form-control"
                                :class="getClassByValidation(state.errors.password_confirm)"
                                type="password"
                                required
                                @input="e => handleChange(e, 'password_confirm')"
                            >

                            <div class="invalid-feedback">
                                <span
                                    v-for="(msg, i) in state.errors.password_confirm"
                                    :key="i"
                                >
                                    {{ msg }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label
                            class="col-form-label col-12"
                            for="accesspoints"
                        >
                            {{ t('global.accesspoints') }}:
                        </label>
                        <div class="col-12">
                            <multiselect
                                v-model="v.fields.accesspoints.value"
                                :name="'accesspoints'"
                                :object="true"
                                :label="'label'"
                                :track-by="'path'"
                                :value-prop="'path'"
                                :close-on-select="false"
                                :mode="'tags'"
                                :options="state.accessPointsArray"
                            />
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button
                    type="submit"
                    class="btn btn-outline-success"
                    :disabled="!state.form.dirty || !state.form.valid"
                    form="newUserForm"
                >
                    <i class="fas fa-fw fa-plus" /> {{ t('global.add') }}
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                >
                    <i class="fas fa-fw fa-times" /> {{ t('global.cancel') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';
    import { useForm, useField } from 'vee-validate';

    import * as yup from 'yup';

    import useSystemStore from '@/bootstrap/stores/system.js';

    import {
        can,
        getClassByValidation,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            errors: {
                required: false,
                type: Object,
                default: _ => ({}),
            },
        },
        emits: ['add', 'cancel'],
        setup(props, context) {
            const {
                errors,
            } = toRefs(props);
            const { t } = useI18n();
            const systemStore = useSystemStore();

            // FUNCTIONS
            const closeModal = _ => {
                context.emit('cancel', false);
            };
            const handleChange = (event, field) => {
                // reset api errors
                delete errors.value[field];

                v.fields[field].handleChange(event);
            };
            const onAdd = _ => {
                const user = {
                    name: v.fields.name.value,
                    nickname: v.fields.nickname.value,
                    email: v.fields.email.value,
                    password: v.fields.password.value,
                    password_confirm: v.fields.password_confirm.value,
                };

                if(v.fields.accesspoints.value.length > 0) {
                    user.accesspoints = [];
                    v.fields.accesspoints.value.forEach(ap => {
                        for(let key in state.accessPoints) {
                            if(state.accessPoints[key].path == ap.path) {
                                user.accesspoints.push(key);
                                break;
                            }
                        }
                    });
                }
                context.emit('add', user);
            };
            const togglePasswordVisibility = _ => {
                state.showPassword = !state.showPassword;

                const pw = document.getElementById('password');
                const pwc = document.getElementById('password_confirm');
                const type = state.showPassword ? 'text' : 'password';
                pw.setAttribute('type', type);
                pwc.setAttribute('type', type);
            };

            // DATA
            const schema = yup.object({
                name: yup.string().required().max(255),
                nickname: yup.string().required().matches(/^[0-9a-zA-Z-_]+$/).max(255),
                email: yup.string().required().email().max(255),
                password: yup.string().required().min(6),
                password_confirm: yup.string().oneOf([yup.ref('password'), null]).required(),
                accesspoints: yup.array(),
            });
            const {
                meta: formMeta
            } = useForm({
                validationSchema: schema,
            });
            const {
                errors: en,
                meta: mn,
                value: vn,
                handleChange: hcn,
            } = useField('name');
            const {
                errors: enn,
                meta: mnn,
                value: vnn,
                handleChange: hcnn,
            } = useField('nickname');
            const {
                errors: ee,
                meta: me,
                value: ve,
                handleChange: hce,
            } = useField('email');
            const {
                errors: ep,
                meta: mp,
                value: vp,
                handleChange: hcp,
            } = useField('password');
            const {
                errors: epc,
                meta: mpc,
                value: vpc,
                handleChange: hcpc,
            } = useField('password_confirm');
            const {
                errors: eap,
                meta: map,
                value: vap,
                handleChange: hcap,
            } = useField('accesspoints');

            const state = reactive({
                showPassword: false,
                form: formMeta,
                accessPoints: computed(_ => systemStore.accessPoints),
                accessPointsArray: computed(_ => systemStore.getAccessPointsAsArray),
                errors: computed(_ => {
                    const errList = {};
                    const fields = Object.keys(v.fields);
                    const hasApiErrors = errors.value && Object.keys(errors.value).length > 0;
                    fields.forEach(f => {
                        errList[f] = v.fields[f].errors;
                        if(hasApiErrors && errors.value[f]) {
                            errList[f].push(...errors.value[f]);
                        }
                    });
                    return errList;
                }),
            });
            const v = reactive({
                fields: {
                    name: {
                        errors: en,
                        meta: mn,
                        value: vn,
                        handleChange: hcn,
                    },
                    nickname: {
                        errors: enn,
                        meta: mnn,
                        value: vnn,
                        handleChange: hcnn,
                    },
                    email: {
                        errors: ee,
                        meta: me,
                        value: ve,
                        handleChange: hce,
                    },
                    password: {
                        errors: ep,
                        meta: mp,
                        value: vp,
                        handleChange: hcp,
                    },
                    password_confirm: {
                        errors: epc,
                        meta: mpc,
                        value: vpc,
                        handleChange: hcpc,
                    },
                    accesspoints: {
                        errors: eap,
                        meta: map,
                        value: vap,
                        handleChange: hcap,
                    }
                },
                schema: schema,
            });

            // RETURN
            return {
                t,
                // HELPERS
                getClassByValidation,
                // LOCAL
                closeModal,
                handleChange,
                onAdd,
                togglePasswordVisibility,
                // STATE
                state,
                v,
            }
        },
    }
</script>
