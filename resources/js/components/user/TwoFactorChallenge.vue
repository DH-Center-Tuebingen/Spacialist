<template>
    <div :class="classes">
        <form
            class="input-group"
            :class="inputClasses"
            @submit.prevent="confirmChallenge"
        >
            <input
                id="confirm-2fa-code"
                :value="challenge"
                class="form-control py-1"
                style="font-size: .8rem;"
                type="text"
                placeholder="XXX XXX"
                @input="styleChallenge"
            >
            <button
                id="confirm-2fa-code-btn"
                class="btn btn-sm btn-outline-success"
                type="submit"
            >
                <i class="fas fa-fw fa-check" />
            </button>
        </form>

        <div
            v-if="errors.length > 0"
            class="mt-2 mb-0 py-1 px-2 alert alert-danger"
        >
            <span v-for="error in errors">
                {{ error }}
            </span>
        </div>
    </div>
</template>

<script>
    import {
        ref,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            classes: {
                type: String,
                required: false,
                default: 'w-25 mx-auto',
            },
            inputClasses: {
                type: String,
                required: false,
                default: '',
            },
            errors: {
                type: Array,
                required: false,
                default: [],
            },
        },
        emits: ['confirm'],
        setup(props, {emit}) {
            const { t } = useI18n();

            const challenge = ref('');
            const format = new RegExp(/\d{6}/);
            const onlyDigits = new RegExp(/\d+/);

            const confirmChallenge = _ => {
                const trimmedChallenge = challenge.value.replace(/ /g, '');
                if(!format.test(trimmedChallenge)) {
                    // TODO show error?
                    return;
                }

                emit('confirm', trimmedChallenge);
            };

            const styleChallenge = event => {
                let currentValue = event.target.value.replaceAll(' ', '');
                if(currentValue.length > 6) {
                    currentValue = currentValue.substring(0, 6);
                }
                if(!onlyDigits.test(currentValue)) {
                    // TODO set error?
                    return;
                }

                if(currentValue.length > 3) {
                    currentValue = currentValue.substring(0, 3) + ' ' + currentValue.substring(3);
                }
                // need to reset it to trigger update
                challenge.value = '';
                challenge.value = currentValue;
            };

            return {
                t,
                challenge,
                confirmChallenge,
                styleChallenge,
            };
        },
    };
</script>