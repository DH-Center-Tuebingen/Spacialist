<template>
    <div>
        <!-- We reset the input-goups width (100%) width the width:auto style -->
        <form
            class="input-group"
            style="width: auto;"
            :class="inputClasses"
            @submit.prevent="confirmChallenge"
        >
            <input
                id="confirm-2fa-code"
                :value="challenge"
                class="form-control py-1 text-center"
                style="font-size: .8rem;"
                type="text"
                placeholder="XXX XXX"
                @input="styleChallenge"
                :style="{ width: '100px' }"
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
            const onlyDigits = new RegExp(/^\d+$/);

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

                if(currentValue === '') {
                    challenge.value = '';
                    event.target.value = '';
                    return;
                }

                if(currentValue.length > 6) {
                    currentValue = currentValue.substring(0, 6);
                    event.target.value = challenge.value;
                    return
                }
                if(!onlyDigits.test(currentValue)) {
                    event.target.value = challenge.value;
                    return;
                }

                if(currentValue.length > 3) {
                    currentValue = currentValue.substring(0, 3) + ' ' + currentValue.substring(3);
                }
              
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