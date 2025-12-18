import {
        web_http,
} from '@/bootstrap/http.js';

export async function confirmTwoFactorChallenge(code) {
    const data = {
        'code': code,
    };
    return $httpQueue.add(() => web_http.post('two-factor-challenge', data).catch(e => e.response.data));
}

export async function confirmTwoFactorActivation(code) {
    const data = {
        'code': code,
    };
    return $httpQueue.add(() => web_http.post('user/confirmed-two-factor-authentication', data).catch(e => e.response.data));
}

export async function disableTwoFactor() {
    return $httpQueue.add(() => web_http.delete('user/two-factor-authentication'));
}

export async function getTwoFactorBackupCodes() {
    return $httpQueue.add(() => web_http.get('user/two-factor-recovery-codes').then(response => response.data));
}

export async function getTwoFactorState() {
    return $httpQueue.add(() => web_http.post('user/two-factor-authentication'));
}

export async function getTwoFactorQrCode() {
    return $httpQueue.add(() => web_http.get('user/two-factor-qr-code').then(response => response.data.svg));
}

export async function updatePassword(oldPassword, newPassword, newPasswordConfirmation) {
    return $httpQueue.add(() => web_http.put('/user/password',{
        current_password: oldPassword,
        password: newPassword,
        password_confirmation: newPasswordConfirmation,
    }));
}

