<template>
    <div
        class="icon-button"
        style="user-select: none; cursor: pointer;"
        :aria-pressed="modelValue"
        @click="_ => $emit('update:modelValue', !modelValue)"
    >
        <div
            v-if="!altIcon || modelValue"
            class="icon"
            :class="activeClass"
        >
            <i :class="iconClass" />
        </div>
        <div
            v-else
            class="icon"
            :class="activeClass"
        >
            <i :class="altIconClass" />
        </div>
    </div>
</template>

<script>
    import { computed } from 'vue';

    export default {
        props: {
            modelValue: {
                type: Boolean,
                required: false,
                default: false,
            },
            iconCategory: {
                type: String,
                required: false,
                default: 'fas',
            },
            icon: {
                type: String,
                required: true
            },
            altIcon: {
                type: String,
                required: false,
                default: null,
            },
            altIconCategory: {
                type: String,
                required: false,
                default: 'fas',
            },
        },
        emits: ['update:modelValue'],
        setup(props) {

            const iconClass = computed(() => {
                return `${props.iconCategory} fa-fw fa-${props.icon}`;
            });

            const altIconClass = computed(() => {
                return `${props.altIconCategory} fa-fw fa-${props.altIcon}`;
            });

            const activeClass = computed(() => {
                return {
                    'active': props.modelValue,
                    'text-primary': props.modelValue,
                };
            });

            return {
                iconClass,
                activeClass,
                altIconClass,
            };
        },
    };
</script>