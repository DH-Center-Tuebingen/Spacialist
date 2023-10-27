<template>
    <div class="position-relative px-3 py-1 bg-secondary bg-opacity-10 rounded">
        <md-viewer
            v-if="v.value"
            :source="v.value"
        />
        <div
            v-else
        >
            No content yet.
        </div>
        <a
            class="position-absolute top-0 end-0 text-reset"
            href="#"
            @click.prevent="openMdEditor()"
        >
            <i class="fas fa-fw fa-edit"></i>
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
                name,
                disabled,
                value,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS
            const resetField = data => {
                v.value = data.value || '';
                v.meta.dirty = false;
                v.meta.valid = true;
            };
            const handleInput = text => {
                v.value = text || '';
                v.meta.dirty = true;
                v.meta.valid = true;
            };
            const resetFieldState = _ => {
                resetField({
                    value: value.value
                });
            };
            const undirtyField = _ => {
                resetField({
                    value: v.value,
                });
            };
            const openMdEditor = _ => {
                showMarkdownEditor(v.value, text => {
                    handleInput(text);
                });
            };

            // DATA
            const state = reactive({
                currentValue: '',
            });

            state.currentValue = value.value || '';

            const v = reactive({
                value: state.currentValue,
                meta: {
                    dirty: false,
                    valid: true,
                },
            });

            watch(value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(v.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: v.value,
                });
            });

            // RETURN
            return {
                // HELPERS
                // LOCAL
                resetFieldState,
                undirtyField,
                openMdEditor,
                // STATE
                state,
                v,
            }
        },
    }
</script>
