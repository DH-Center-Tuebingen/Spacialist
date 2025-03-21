<template>
    <button
        class="btn"
        role="button"
        :disabled="computedDisabled"
    >
        <span
            v-if="loading"
            class="me-1"
        >
            <i class="fas fa-fw fa-circle-notch fa-spin" />
        </span>
        <slot
            v-else
            name="icon"
        />

        <slot />
    </button>
</template>

<script>

    import {
        computed
    } from 'vue';

    export default {
        props: {
            loading: {
                type: Boolean,
                required: true,
            },
            disabled: {
                type: Boolean,
                required: false,
                default: false,
            }
        },
        setup(props){

            const computedDisabled = computed(_ => {
                return props.loading || props.disabled;
            });

            return {
                computedDisabled,
            };
        }
    };
</script>