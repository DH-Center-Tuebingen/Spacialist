<template>
    <div
        class="alert"
        :class="state.classes"
        role="alert"
    >
        <div
            v-if="state.hasIcon"
            :class="state.iconWrapperClasses"
        >
            <span v-show="type == 'success'">
                <i class="fas fa-fw fa-check" />
            </span>
            <span v-show="type == 'note'">
                <i class="fas fa-fw fa-lightbulb" />
            </span>
            <span v-show="type == 'info'">
                <i class="fas fa-fw fa-info-circle" />
            </span>
            <span v-show="type == 'warning'">
                <i class="fas fa-fw fa-exclamation-triangle" />
            </span>
            <span v-show="type == 'error'">
                <i class="fas fa-fw fa-times" />
            </span>
            <span
                v-if="icontext"
                class="fw-medium ms-2"
            >
                {{ icontext }}
            </span>
        </div>
        <!-- We disable the v-html as there is no user data that get's inserted into the alerts. -->
        <!-- eslint-disable-next-line vue/no-v-html -->
        <div v-html="message" />
        <slot name="addon" />
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';

    export default {
        props: {
            message: {
                required: true,
                type: String,
            },
            type: {
                required: false,
                type: String,
                default: '',
            },
            noicon: {
                required: false,
                type: Boolean,
                default: true,
            },
            icontext: {
                required: false,
                type: String,
                default: null
            }
        },
        setup(props, context) {
            const {
                message,
                type,
                noicon,
                icontext,
            } = toRefs(props);

            // FUNCTIONS

            // DATA
            const state = reactive({
                hasIcon: computed(_ => {
                    return !noicon.value && state.supportsIcon;
                }),
                hasIconText: computed(_ => {
                    return state.hasIcon &&  !!icontext.value;
                }),
                supportsIcon: computed(_ => {
                    switch(type.value) {
                        case 'success':
                        case 'note':
                        case 'info':
                        case 'warning':
                        case 'error':
                            return true;
                        case 'mono':
                        default:
                            return false;
                    }
                }),
                classes: computed(_ => {
                    let classes = [];
                    switch(type.value) {
                        case 'success':
                            classes.push('alert-success');
                            break;
                        case 'note':
                        case 'info':
                            classes.push('alert-info');
                            break;
                        case 'warning':
                            classes.push('alert-warning');
                            break;
                        case 'error':
                            classes.push('alert-danger');
                            break;
                        case 'mono':
                            classes.push('alert-secondary');
                            break;
                        default:
                            classes.push('alert-primary');
                            break;
                    }
                    
                    if(state.hasIcon) {
                        classes.push('d-flex');
                        if(state.hasIconText) {
                            classes.push('flex-column');
                        } else {
                            classes.push('flex-row');
                        }
                    }

                    return classes;
                }),
                iconWrapperClasses: computed(_ => {
                    let classes = [];
                    if(!state.hasIcon) return classes;
                    if(!state.hasIconText) {
                        classes.push('me-2');
                    }
                    return classes;
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
    }
</script>