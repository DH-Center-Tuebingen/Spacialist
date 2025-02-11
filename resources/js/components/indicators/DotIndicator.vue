<template>
    <div
        class="dot-indicator"
        :class="state.merged"
        :style="state.size"
    />
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    export default {
        props: {
            size:{
                required: false,
                type: String,
                default: '0.6rem',
            },
            type: {
                required: false,
                type: String,
                default: 'default',
            },
            classes: {
                required: false,
                type: String,
                default: 'rounded-circle ratio ratio-1x1',
            },
            opacity: {
                required: false,
                type: Number,
                default: 4,
            }
        },
        setup(props, context) {
            // FUNCTIONS

            // DATA
            const state = reactive({
                size: computed(_ => {
                    return {
                        width: props.size,
                        height: props.size,
                    };
                }),
                merged: computed(_ => {
                    const mergedClasses = props.classes.split(' ');
                    switch(props.type) {
                        case 'success':
                        case 'valid':
                            mergedClasses.push('bg-success');
                            break;
                        case 'note':
                        case 'info':
                            mergedClasses.push('bg-info');
                            break;
                        case 'warning':
                            mergedClasses.push('bg-warning');
                            break;
                        case 'error':
                        case 'danger':
                            mergedClasses.push('bg-danger');
                            break;
                        case 'mono':
                            mergedClasses.push('bg-success');
                            break;
                        case 'default':
                        case 'primary':
                            mergedClasses.push('bg-primary');
                    }
                    switch(props.opacity) {
                        case 0:
                            mergedClasses.push('bg-opacity-0');
                            break;
                        case 1:
                            mergedClasses.push('bg-opacity-10');
                            break;
                        case 2:
                            mergedClasses.push('bg-opacity-25');
                            break;
                        case 3:
                            mergedClasses.push('bg-opacity-50');
                            break;
                        case 4:
                            mergedClasses.push('bg-opacity-75');
                            break;
                        case 5:
                            mergedClasses.push('bg-opacity-100');
                            break;
                    }
                    return mergedClasses;
                }),
            });

            // RETURN
            return {
                // HELPERS
                // LOCAL
                // STATE
                state,
            };
        },
    };
</script>