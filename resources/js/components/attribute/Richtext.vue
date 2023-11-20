<template>
    <div class="position-relative px-3 py-1 bg-secondary bg-opacity-10 rounded">
        <md-viewer
            v-if="state.value"
            :source="state.value"
        />
        <div
            v-else
        >
            No content yet.
        </div>
        <a
            v-if="!disabled"
            class="position-absolute top-0 end-0 text-reset"
            href="#"
            @click.prevent="openMdEditor()"
        >
            <i class="fas fa-fw fa-edit" />
        </a>
    </div>
</template>

<script>
    import {
        reactive,
        toRefs,
        watch,
    } from 'vue';

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
            const {
                value: initial,
            } = toRefs(props);

            const defaultValue = () => {
                    return{
                    value: initial.value || '',
                    meta: {
                        dirty: false,
                        valid: true,
                    },
                }
            }

            const state = reactive(defaultValue()); 

            // FUNCTIONS 
            const resetField = data => {
                Object.assign(state, defaultValue());
            };
            const handleInput = text => {
                state.value = text || '';
                state.meta.dirty = true;
                state.meta.valid = true; 
            };
            const resetFieldState = _ => {
                resetField({
                    value: initial.value
                });
            };
            const undirtyField = _ => {
                resetField({
                    value: state.value,
                });
            };
            const openMdEditor = _ => {
                showMarkdownEditor(state.value, text => {
                    handleInput(text);
                });
            };

            watch(initial.value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(state.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: state.meta.dirty,
                    valid: state.meta.valid,
                    value: state.value,
                });
            });

            // RETURN
            return {
                // FUNCTIONS
                resetFieldState,
                undirtyField,
                openMdEditor,
                // STATE
                state,
                
            }
        },
    }
</script>
