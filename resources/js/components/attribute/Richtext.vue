<template>
    <div
        class="position-relative"
        @mouseenter="state.hovered = true"
        @mouseleave="state.hovered = false"
    >
        <md-viewer
            v-if="current"
            :id="name"
            :classes="state.classes"
            :source="current"
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
        ref,
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
        },
        emits: ['change'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                value: initial,
            } = toRefs(props);

            const handleInput = text => {
                current.value = text || '';
                meta.dirty = true;
                meta.valid = true;

                context.emit('change', {
                    dirty: meta.dirty,
                    valid: meta.valid,
                    value: current.value,
                });
            };

            const resetFieldState = _ => {
                current.value = initial.value || '';
                undirtyField();
            };

            const undirtyField = _ => {
                meta.dirty = false;
            };

            watch(initial, _ => {
                resetFieldState();
            });


            const openMdEditor = _ => {
                showMarkdownEditor(current.value, text => {
                    handleInput(text);
                });
            };

            const state = reactive({
                classes: 'mt-0 bg-none h-100 form-control px-4 py-3',
                hovered: false,
                onHoverBtnClasses: computed(_ => {
                    if(state.hovered) {
                        return '';
                    } else {
                        return 'd-none';
                    }
                }),
            });
            const current = ref(initial.value || '');
            const meta = reactive({
                dirty: false,
                valid: true,
            });

            /**
             * v is required as the attr-list fetches 
             * the values of the attributes via every
             * attribute's v.value.
             */
            const v = computed(_ => {
                return {
                    value: current.value,
                    meta: {
                        ...meta
                    }
                };
            });

            // RETURN
            return {
                t,
                // FUNCTIONS
                resetFieldState,
                undirtyField,
                openMdEditor,
                // STATE
                current,
                state,
                v,
            };
        },
    };
</script>
