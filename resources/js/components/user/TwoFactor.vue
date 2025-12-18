<template>
    <div class="text-center">
        <div v-if="status === 'inactive'">
            <button
                class="btn btn-sm btn-outline-primary"
                @click="request"
            >
                {{ t('global.user.security.2fa.enable') }}
            </button>
        </div>
        <div
            v-else-if="status === 'unconfirmed'"
            class="d-flex flex-column gap-3"
        >
            <!-- <button
                v-if="!"
                class="btn btn-sm btn-outline-primary"
                @click="requestQrCode"
            >
                {{ t('global.user.security.2fa.request_qrcode') }}
            </button> -->
            <div
                v-if="state.qrCode.length > 0"
                v-html="state.qrCode"
            />
            <div
                class="d-flex align-items-center justify-content-center text-white fw-bold bg-secondary rounded mb-2 mx-auto"
                style="width: 192px; height: 192px;"
                v-else
            >
                <span>Loading ...</span>
            </div>
            <TwoFactorChallenge
                :errors="state.challengeErrors"
                @confirm="confirmActivation"
                class="d-flex flex-column align-items-center"
            />

            <p class="mb-0 text-muted">
                {{ t('global.user.security.2fa.qrcode_info') }}
            </p>

            <button
                class="btn btn-sm btn-outline-secondary mx-auto"
                @click="disableAuthentication"
            >
                <i class="fas fa-fw fa-trash" />
                {{ t('global.user.security.2fa.disable') }}
            </button>
        </div>
        <div v-else-if="status === 'saving-backup-codes'">
            <div v-if="state.backupCodes.length > 0">
                <hr>
                <div class="d-flex flex-row justify-content-between flex-wrap">
                    <code
                        v-for="code in state.backupCodes"
                        class="col-md-6"
                    >
                {{ code }}
            </code>
                    {{ t('global.user.security.2fa.backup_codes') }}
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
                        <span
                            v-show="state.codesCopied"
                            class="fade-in"
                        >
                            <i class="fas fa-fw fa-check" />
                        </span>
                        <span
                            v-show="!state.codesCopied"
                            class="fade-in"
                        >
                            <i class="fas fa-fw fa-copy" />
                        </span>
                        {{ t('global.user.security.2fa.copy_backup_codes') }}
                    </button>
                </div>
            </div>

        </div>
        <div v-else-if="status === 'activated'">
            <div class="d-flex flex-column justify-content-center align-items-center gap-3 m-3">
                <span class="fs-4 text-primary">
                    <i class="small far fa-fw fa-circle-check" />
                    {{ t('global.user.security.2fa.disable_info') }}
                </span>
                <button
                    class="btn btn-sm btn-outline-secondary mx-auto"
                    @click="disableAuthentication"
                >
                    <i class="fas fa-fw fa-trash" />
                    {{ t('global.user.security.2fa.disable') }}
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
    } from '@/api/user.js';

    import TwoFactorChallenge from './TwoFactorChallenge.vue';
    import useUserStore from '@/bootstrap/stores/user';

    export default {
        components: {
            TwoFactorChallenge,
        },
        setup(props) {
            const { t } = useI18n();
            let userStore = null;

            const request = async _ => {
                const response = await getTwoFactorState();
                console.log('2FA state response:', response);
                if(response.status === 200) {
                    state.currentState = 1; // unconfirmed
                    await requestQrCode();
                }
            };

            const requestQrCode = async _ => {
                state.qrCode = await getTwoFactorQrCode();
                console.log('QR Code received:', state.qrCode);
            };

            const confirmActivation = async challenge => {
                state.challengeErrors = [];

                try {
                    const response = await confirmTwoFactorActivation(challenge);
                    if(response?.errors && Object.keys(response.errors).length > 0) {
                        state.challengeErrors = response.errors.code;
                    } else {
                        state.unconfirmed = false;
                        state.currentState = 2; // saving-backup-codes
                        state.backupCodes = await getTwoFactorBackupCodes();
                    }
                } catch(error) {
                    console.error('Error confirming 2FA activation:', error);
                    state.challengeErrors = [t('global.error.occur')]
                }


            };

            const reset = () => {
                state.currentState = 0; // inactive
                state.qrCode = '';
                state.backupCodes = [];
                state.codesCopied = false;
                state.challengeErrors = [];
            }

            const disableAuthentication = async _ => {
                try {
                    await disableTwoFactor();
                    reset();
                } catch(error) {
                    console.error(error);
                }
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

            // Use a state machine for clarity
            const states = [
                'inactive',
                'unconfirmed',
                'saving-backup-codes',
                'activated'
            ]

            const state = reactive({
                currentState: 0,
                qrCode: '',
                backupCodes: [],
                codesCopied: false,
                challengeErrors: [],
            });

            onMounted(async () => {
                try {
                    userStore = useUserStore();
                    const activeUser = userStore.user;
                    const activated = (activeUser?.two_factor_secret) ? activeUser.two_factor_secret : false;
                    const confirmed = (activeUser?.two_factor_confirmed_at) ? activeUser.two_factor_confirmed_at : false;

                    console.log('2FA status', {
                        activated,
                        confirmed,
                    });

                    if(!activated) {
                        state.currentState = 0;
                    } else if(!confirmed) {
                        state.currentState = 1;
                        await requestQrCode();
                    } else {
                        state.currentState = 3;
                    }
                } catch(error) {
                    console.error('Error accessing user store:', error);
                    // Default to inactive state if store is not available
                    state.currentState = 0;
                }
            });

            const status = computed(_ => {
                return states[state.currentState] || 'corrupted';
            })

            return {
                t,
                state,
                status,
                request,
                requestQrCode,
                confirmActivation,
                disableAuthentication,
                copyBackupCodes,
            };
        },
    };
</script>