<template>
    <div
        class="alert position-relative"
        :class="state.classes"
        role="alert"
        v-if="state.isActive"
    >
        <template
            v-if="state.isOpen"
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
        </template>
        <span
            v-else
            class="text-muted fst-italic"
        >
            {{ t('main.app.alert_hidden') }}
        </span>
        <div
            v-if="closeable"
            class="position-absolute top-0 end-0 d-flex flex-row gap-2 me-2 mt-1"
        >
            <a
                v-show="state.isOpen"
                href="#"
                class="text-muted"
                @click.prevent="toggleVisibility"
            >
                <i class="fas fa-fw fa-caret-up" />
            </a>
            <a
                v-show="!state.isOpen"
                href="#"
                class="text-muted"
                @click.prevent="toggleVisibility"
            >
                <i class="fas fa-fw fa-caret-down" />
            </a>
            <a
                href="#"
                class="text-muted"
                @click.prevent="closeAlert"
            >
                <i class="fas fa-fw fa-times" />
            </a>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

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
            },
            closeable: {
                required: false,
                type: Boolean,
                default: false,
            },
        },
        setup(props, context) {
            const { t } = useI18n();
            const {
                message,
                type,
                noicon,
                icontext,
                closeable,
            } = toRefs(props);

            // FUNCTIONS
            const toggleVisibility = _ => {
                state.isOpen = !state.isOpen;
            };
            const closeAlert = _ => {
                state.isActive = false;
            };

            // DATA
            const state = reactive({
                isOpen: true,
                isActive: true,
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
                t,
                // HELPERS
                // LOCAL
                toggleVisibility,
                closeAlert,
                // STATE
                state,
            };
        },
    };
</script>