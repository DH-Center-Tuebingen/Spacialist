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

    import useEntityStore from '@/bootstrap/stores/entity.js';

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
            const entityStore = useEntityStore();

            const state = reactive({
                colorStyles: computed(_ => {
                    const colors = entityStore.getEntityTypeColors(props.type);
                    return {
                        color: colors.backgroundColor,
                    };
                }),
                label: computed(_ => entityStore.getEntityTypeName(props.type)),
            });

            return {
                state,
            };
        }
    };
</script>