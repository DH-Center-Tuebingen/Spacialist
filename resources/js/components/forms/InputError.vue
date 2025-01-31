<template>
    <span
        v-if="errorMessage"
        class="text-danger font-weight-bold"
    >
        {{ errorMessage }}
    </span>
</template>

<script>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

    export default {
        props: {
            v: {
                type: Object,
                required: true,
            },
            error: {
                type: String,
                required: true,
            },
        },
        setup(props, context) {
            
            const errorMessage = computed(() => {
                if(props.error) return props.error;
                
                // Vee validate not always provides an error message
                // this results in a very unsatisfying state, where the 
                // save button is disabled, but the user does not know why
                // this is a workaround to provide a default error message.
                const invalid = props.v?.meta?.valid === false || false;
                if(invalid){
                    const t = useI18n().t;
                    return t('global.error.value_invalid');
                }

                return '';
            });

            return {
                errorMessage
            };
        },
    };
</script>