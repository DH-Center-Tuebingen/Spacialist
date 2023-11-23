<template>
    <div class="position-relative px-3 py-1 bg-secondary bg-opacity-10 rounded">
        <md-viewer
            v-if="current"
            :id="name"
            :source="current"
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
computed,
        reactive,
        ref,
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

            const current = ref(initial.value || '');
            const meta = reactive({
                dirty: false,
                valid: true,
            });

            /**
             * v is required as the attr-list fetches 
             * the values of the attributes vai every
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



            // RETURN
            return {
                // FUNCTIONS
                resetFieldState,
                undirtyField,
                openMdEditor,
                // STATE
                current,
                v
            }
        },
    }
</script>
