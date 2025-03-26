<template>
    <div class="text-center">
        <div v-if="state.unconfigured">
            <Alert
                :message="t('global.user.security.2fa.enable_info')"
                :dismissible="true"
            />
            <button
                class="btn btn-sm btn-outline-primary"
                @click="request"
            >
                {{ t('global.user.security.2fa.enable') }}
            </button>
        </div>
        <div v-else-if="state.unconfirmed">
            <Alert
                :message="t('global.user.security.2fa.qrcode_info')"
                :dismissible="true"
            />
            <button
                v-if="!state.showQrCode"
                class="btn btn-sm btn-outline-primary"
                @click="requestQrCode"
            >
                {{ t('global.user.security.2fa.request_qrcode') }}
            </button>
        </div>
        <div v-else-if="!state.unconfirmed">
            <div class="d-flex flex-row justify-content-center align-items-center gap-3">
                <span
                    class="fs-4 text-success"
                >
                    <i class="small fas fa-fw fa-check" />
                    {{ t('global.user.security.2fa.disable_info') }}
                </span>
                <button
                    class="btn btn-sm btn-outline-danger"
                    @click="disableAuthentication"
                >
                    <i class="fas fa-fw fa-trash" />
                    {{ t('global.user.security.2fa.disable') }}
                </button>
            </div>
        </div>
        <div v-if="state.showQrCode">
            <div v-html="state.qrCode" />
            <TwoFactorChallenge
                :errors="state.challengeErrors"
                @confirm="confirmActivation"
            />
        </div>
        <div v-if="state.backupCodes.length > 0">
            <hr>
            <Alert
                type="warning"
                :dismissible="true"
                :message="t('global.user.security.2fa.backup_codes')"
            />
            <hr class="w-50 mx-auto">
            <div class="d-flex flex-row justify-content-between flex-wrap">
                <code
                    v-for="code in state.backupCodes"
                    class="col-md-6"
                >
                    {{ code }}
                </code>
            </div>
            <div class="mt-1 d-flex flex-row justify-content-center gap-2">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    @click="state.backupCodes = []"
                >
                    {{ t('global.user.security.2fa.confirm_backup_codes') }}
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-success"
                    @click="copyBackupCodes"
                >
                    <span v-show="state.codesCopied" class="fade-in">
                        <i class="fas fa-fw fa-check" />
                    </span>
                    <span v-show="!state.codesCopied" class="fade-in">
                        <i class="fas fa-fw fa-copy" />
                    </span>
                    {{ t('global.user.security.2fa.copy_backup_codes') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        getTwoFactorState,
        getTwoFactorQrCode,
        confirmTwoFactorActivation,
        getTwoFactorBackupCodes,
        disableTwoFactor,
    } from '@/api.js';

    import TwoFactorChallenge from './TwoFactorChallenge.vue';

    export default {
        components: {
            TwoFactorChallenge,
        },
        props: {
            user: {
                type: Object,
                required: true,
            },
        },
        setup(props) {
            const { t } = useI18n();

            const request = async _ => {
                const response = await getTwoFactorState();
                state.unconfigured = response.status != 200;
                if(!state.unconfigured) {
                    requestQrCode();
                }
            };

            const requestQrCode = async _ => {
                state.qrCode = await getTwoFactorQrCode();
            };

            const confirmActivation = async challenge => {
                const response = await confirmTwoFactorActivation(challenge);
                if(response?.errors && Object.keys(response.errors).length > 0) {
                    state.challengeErrors = response.errors.code;
                } else {
                    state.unconfirmed = false;
                    state.backupCodes = await getTwoFactorBackupCodes();
                    state.challengeErrors = [];
                }
            };

            const disableAuthentication = async _ => {
                await disableTwoFactor();
                state.unconfigured = true;
                state.unconfirmed = true;
                state.qrCode = '';
                state.backupCodes = [];
                state.codesCopied = false;
                state.challengeErrors = [];
            };

            const copyBackupCodes = async _ => {
                try {
                    await navigator.clipboard.writeText(state.backupCodes.join('\n'));
                    state.codesCopied = true;
                    setTimeout(_ => {
                        state.codesCopied = false;
                    }, 2000);
                } catch(error) {
                    console.error(error);
                }
            };

            const state = reactive({
                unconfigured: true,
                unconfirmed: true,
                qrCode: '',
                backupCodes: [],
                codesCopied: false,
                challengeErrors: [],
                showQrCode: computed(_ => !state.unconfigured && state.unconfirmed && state.qrCode),
            });

            onMounted(_ => {
                state.unconfigured = !props.user.two_factor_secret;
                state.unconfirmed = !props.user.two_factor_confirmed_at;
            });

            return {
                t,
                state,
                request,
                requestQrCode,
                confirmActivation,
                disableAuthentication,
                copyBackupCodes,
            };
        },
    };
</script>