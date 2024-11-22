<template>
    <div
        class="dity-state-indicator"
        :style="style"
    />
</template>

<script>
    import { computed } from 'vue';

    export default {
        props: {
            value: {
                type: Object,
                required: true,
            },
        }, setup(props) {

            const isDirty = computed(() => {
                return props?.value?.meta?.dirty;
            });


            const isUnset = computed(() => {
                return props?.value?.meta?.dirty == null;
            });


            const size = 10;
            const style = computed(() => {
                return {
                    width: `${size}px`,
                    height: `${size}px`,
                    borderRadius: '50%',
                    backgroundColor: isUnset.value ? 'red' : isDirty.value ? 'orange' : 'green',
                };
            });

            return {
                style
            };
        },
    };
</script>