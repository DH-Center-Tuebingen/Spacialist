<template>
  <vue-final-modal
    class="modal-container modal"
    content-class=""
    name="confirm-user-password-reset-modal">
    <div class="sp-modal-content sp-modal-content-xs">
        <div class="modal-header">
            <h5 class="modal-title">
                {{
                    t('main.user.modal.confirm_password.title')
                }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body">
            <alert
                :message="t('main.user.modal.confirm_password.message', {attempts: state.user.login_attempts}, state.user.login_attempts)"
                :type="'info'"
                :icontext="t('global.information')"
                />
            <button type="button" class="btn btn-sm btn-outline-primary" @click="confirmSetPassword()">
                <i class="fas fa-fw fa-check"></i>
                Confirm set password
            </button>
            <form id="confirmUserPasswordForm" name="confirmUserPasswordForm" role="form" @submit.prevent="confirmOwnPassword()">
                <div class="mb-3">
                    <label class="col-form-label col-12" for="password">
                        {{ t('global.password') }}
                        <span class="text-danger">*</span>:
                    </label>
                    <div class="col-12">
                        <input class="form-control d-inline" :class="getClassByValidation(v.fields.password.errors)" type="password" id="password" v-model="v.fields.password.value" @input="v.fields.password.handleChange" required />
                        <a href="#" class="text-muted ms--4-5" @click.prevent="togglePasswordVisibility()" tabindex="-1">
                            <span v-show="!state.showPassword">
                                <i class="fas fa-fw fa-eye"></i>
                            </span>
                            <span v-show="state.showPassword">
                                <i class="fas fa-fw fa-eye-slash"></i>
                            </span>
                        </a>
    
                        <div class="invalid-feedback">
                            <span v-for="(msg, i) in v.fields.password.errors" :key="i">
                                {{ msg }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="col-form-label col-12" for="password_confirm">
                        {{ t('global.confirm_password') }}
                        <span class="text-danger">*</span>:
                    </label>
                    <div class="col-12">
                        <input class="form-control" :class="getClassByValidation(v.fields.password_confirm.errors)" type="password" id="password_confirm" v-model="v.fields.password_confirm.value" @input="v.fields.password_confirm.handleChange" required />
    
                        <div class="invalid-feedback">
                            <span v-for="(msg, i) in v.fields.password_confirm.errors" :key="i">
                                {{ msg }}
                            </span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success" :disabled="!state.form.dirty || !state.form.valid" form="confirmUserPasswordForm">
                <i class="fas fa-fw fa-check"></i> {{ t('global.confirm') }}
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" @click="closeModal()">
                <i class="fas fa-fw fa-times"></i> {{ t('global.cancel') }}
            </button>
        </div>
    </div>
  </vue-final-modal>
</template>

<script>
    import {
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';
    import { useForm, useField } from 'vee-validate';

    import * as yup from 'yup';

    import {
        getUser,
        getClassByValidation,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            userId: {
                required: true,
                type: Number,
            },
        },
        emits: ['confirm', 'cancel'],
        setup(props, context) {
            const {
                userId,
            } = toRefs(props);
            const { t } = useI18n();

            // FUNCTIONS
            const closeModal = _ => {
                context.emit('cancel', false);
            };
            const confirmSetPassword = _ => {
                context.emit('confirm', {
                    existing: true,
                });
            };
            const confirmOwnPassword = _ => {
                context.emit('confirm', {
                    password: v.fields.password.value,
                    existing: false,
                });
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
                password: yup.string().required().min(6),
                password_confirm: yup.string().oneOf([yup.ref('password'), null]).required(),
            });
            const {
                meta: formMeta
            } = useForm({
                validationSchema: schema,
            });
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

            const state = reactive({
                user: getUser(),
                showPassword: false,
                form: formMeta,
            });
            const v = reactive({
                fields: {
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
                },
                schema: schema,
            });

            // RETURN
            return {
                t,
                // HELPERS
                getClassByValidation,
                // PROPS
                // LOCAL
                closeModal,
                confirmSetPassword,
                confirmOwnPassword,
                togglePasswordVisibility,
                // STATE
                state,
                v,
            };
        },
    }
</script>
