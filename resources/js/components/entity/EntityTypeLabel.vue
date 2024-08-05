<template>
    <div 
        class="d-flex flex-row gap-1"
    >
        <span
            :style="state.colorStyles"
            :title="state.label"
        >
            <i class="fas fa-fw fa-circle" />
        </span>
        <span v-if="!iconOnly">
            {{ state.label }}
        </span>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import {
        getEntityColors,
        getEntityTypeName,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            type: {
                required: true,
                type: Number,
            },
            iconOnly: {
                required: false,
                type: Boolean,
                default: false,
            },
        },
        setup(props) {
            const state = reactive({
                colorStyles: computed(_ => {
                    const colors = getEntityColors(props.type);
                    return {
                        color: colors.backgroundColor,
                    };
                }),
                label: computed(_ => getEntityTypeName(props.type)),
            });

            return {
                state,
            };
        }
    };
</script>