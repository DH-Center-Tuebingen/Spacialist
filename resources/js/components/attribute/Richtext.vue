<template>
    <div
        class="position-relative"
        @mouseenter="state.hovered = true"
        @mouseleave="state.hovered = false"
    >
        <md-viewer
            v-if="v.value"
            :id="name"
            :classes="state.classes"
            :source="v.value"
        />
        <div
            v-else
            class="text-secondary fst-italic fw-medium opacity-50 user-select-none"
            :class="state.classes"
        >
            {{ t('global.missing.content') }}
        </div>
        <div
            class="position-absolute top-0 end-0 h-100 pe-none"
            :class="state.onHoverBtnClasses"
        >
            <div class="position-sticky top-0 bg-light pe-auto m-2 rounded">
                <button
                    v-if="!disabled"
                    class="px-2 py-1 btn btn-outline-secondary btn-sm"
                    href="#"
                    @click.prevent="openMdEditor()"
                >
                    <i class="fas fa-fw fa-edit" />
                    <span class="ms-2">
                        {{ t('global.edit') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
        watch,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        showMarkdownEditor,
    } from '@/helpers/modal.js';

    export default {
        props: {
            name: {
                type: String,
                required: true,
            },
            disabled: {
                type: Boolean,
                required: false,
                default: false,
            },
            value: {
                type: String,
                required: true,
            },
            classes: {
                type: String,
                required: false,
                default: 'mt-0 bg-none h-100 form-control px-4 py-3',
            },
        },
        emits: ['change'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                value: initial,
                classes,
            } = toRefs(props);

            const handleInput = text => {
                v.value = text || '';
                v.meta.dirty = true;
                v.meta.valid = true;

                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: v.value,
                });
            };

            const resetFieldState = _ => {
                v.value = initial.value || '';
                undirtyField();
            };

            const undirtyField = _ => {
                v.meta.dirty = false;
            };

            watch(initial, _ => {
                resetFieldState();
            });

            const openMdEditor = _ => {
                showMarkdownEditor(v.value, text => {
                    handleInput(text);
                });
            };

            const state = reactive({
                hovered: false,
                classes: classes,
                onHoverBtnClasses: computed(_ => {
                    if(state.hovered) {
                        return '';
                    } else {
                        return 'd-none';
                    }
                }),
            });

            /**
             * v is required as the attr-list fetches
             * the values of the attributes via every
             * attribute's v.value.
             */
            const v = reactive({
                value: initial.value || '',
                meta: {
                    dirty: false,
                    valid: true,
                }
            });

            // RETURN
            return {
                t,
                // FUNCTIONS
                resetFieldState,
                undirtyField,
                openMdEditor,
                // STATE
                state,
                v,
            };
        },
    };
</script>
