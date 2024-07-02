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
    toRefs,
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
        const {
            type,
            iconOnly,
        } = toRefs(props);

        const state = reactive({
            colorStyles: computed(_ => {
                const colors = getEntityColors(type.value);
                return {
                    color: colors.backgroundColor,
                };
            }),
            label: computed(_ => getEntityTypeName(type.value)),
        });

        return {
            // HELPERS
            // LOCAL
            iconOnly,
            // STATE
            state,
        }
    }
}
</script>