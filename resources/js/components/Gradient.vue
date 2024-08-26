<template>
    <div class="d-flex justify-content-start align-items-center">
        <div
            :style="state.style"
            class="color-gradient"
        />
        <span>{{ label }}</span>
    </div>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            values: {
                required: true,
                type: Array,
            },
            label: {
                required: true,
                type: String
            }
        },
        setup(props, context) {
            const { t } = useI18n();
            const {
                values,
                label,
            } = toRefs(props);

            // FETCH

            // FUNCTIONS

            // DATA
            const state = reactive({
                style: computed(_ => {
                    const valueList = values.value.map(v => `${v.color} ${v.index}%`).join(', ');
                    return `background: linear-gradient(90deg, ${valueList});`;
                }),
            });

            // ON MOUNTED

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                // STATE
                state,
            };
        },
    }
</script>
