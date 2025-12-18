<template>
    <div class="update-password">
        <div class="row">
            <label class="col-form-label col-md-4">
                {{ t('global.user.security.password.current_password') }}
            </label>
            <div class="col-md-8">
                <input
                    class="form-control"
                    type="password"
                    v-model="oldPassword"
                >
            </div>
        </div>

        <div class="row mt-2">
            <label class="col-form-label col-md-4">
                {{ t('global.user.security.password.new_password') }}
            </label>
            <div class="col-md-8">
                <input
                    class="form-control"
                    type="password"
                    v-model="newPassword"
                >
            </div>
        </div>

        <div class="row mt-2">
            <label class="col-form-label col-md-4">
                {{ t('global.user.security.password.new_password_confirmation') }}
            </label>
            <div class="col-md-8">
                <input
                    class="form-control"
                    type="password"
                    v-model="newPasswordConfirmation"
                >
            </div>
        </div>
        <div class="row justify-content-end mt-3">
            <div class="col-auto">
                <button
                    type="button"
                    class="btn btn-outline-primary btn-sm"
                    :disabled="!isValid()"
                    @click="onUpdatePassword"
                >
                    {{ t('global.apply') }}
                </button>
            </div>
        </div>

    </div>
</template>

<script>
    import {
        ref,
    } from 'vue';

    import { useI18n } from 'vue-i18n';
    import { useToast } from '@/plugins/toast.js';
    import { updatePassword } from '@/api/user.js';

    export default {
        emits: ['changed'],
        setup(props, context) {
            const { t } = useI18n();
            const toast = useToast();

            const oldPassword = ref('');
            const newPassword = ref('');
            const newPasswordConfirmation = ref('');

            // FUNCTIONS
            const onUpdatePassword = () => {

                if(newPassword.value !== newPasswordConfirmation.value) {
                    const label = t('main.preference.security.password_update_mismatch');
                    toast.$toast(label, '', {
                        channel: 'error',
                        simple: true,
                    });
                    return;
                }

                updatePassword().then(_ => {
                    const label = t('main.preference.security.password_update_success');
                    toast.$toast(label, '', {
                        channel: 'success',
                        simple: true,
                    });
                    context.emit('changed', true);
                }).catch(error => {
                    const label = t('main.preference.security.password_update_error');
                    toast.$toast(label, '', {
                        channel: 'error',
                        simple: true,
                    });
                });
            };

            const isValid = () => {
                return oldPassword.value.length > 0
                    && newPassword.value.length > 0
                    && newPasswordConfirmation.value.length > 0
            };

            // RETURN
            return {
                t,
                // LOCAL
                oldPassword,
                newPassword,
                newPasswordConfirmation,
                onUpdatePassword,
                isValid,
            };
        }
    }
</script>
