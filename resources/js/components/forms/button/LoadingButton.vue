<template>
    <button
        class="btn"
        role="button"
        :disabled="computedDisabled"
    >
        <span
            v-if="loading"
            :class="iconWrapperClasses"
        >
            <i
                class="fas fa-fw fa-circle-notch fa-spin"
                :class="spinnerClasses"
            />
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
        computed,
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
            },
            spinnerClasses: {
                type: String,
                required: false,
                default: '',
            }
        },
        setup(props, { slots }) {

            const computedDisabled = computed(_ => {
                return props.loading || props.disabled;
            });

            const iconWrapperClasses = computed(() => {
                return slots.default ? `me-1 ${props.spinnerClasses}` : props.spinnerClasses;
            });

            return {
                computedDisabled,
                iconWrapperClasses,
            };
        }
    };
</script>