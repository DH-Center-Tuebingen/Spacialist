<template>
    <div
        :class="styling.classes"
        :style="styling.style"
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
            size: {
                type: Number,
                required: false,
                default: 10,
            },
        },
	setup(props) {
            const isDirty = computed(_ => {
                return props?.value?.meta?.dirty;
            });

            const isUnset = computed(_ => {
                return props?.value?.meta?.dirty == null;
            });

            const styling = computed(_ => {
                const classes = ['rounded-circle'];
                if(isUnset.value) {
                    classes.push('bg-danger');
                } else if(isDirty.value) {
                    classes.push('bg-warning');
                } else {
                    classes.push('bg-success');
                }
                return {
                    style: {
                        width: `${props.size}px`,
                        height: `${props.size}px`,
                    },
                    classes: classes,
                };
            });

            return {
                styling,
            };
        },
    };
</script>
