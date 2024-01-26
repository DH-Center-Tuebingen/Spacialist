<template>
    <div class="position-relative">
        <md-viewer
            v-if="current"
            :id="name"
            :classes="'mt-0 bg-none h-100 form-control px-4 py-3'"
            :source="current"
        />
        <div v-else>
            No content yet.
        </div>
        <div class="position-absolute top-0 end-0 h-100 pe-none ">
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

    import {
        showMarkdownEditor,
    } from '@/helpers/modal.js';
    import { useI18n } from 'vue-i18n';

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

            const { t } = useI18n();

            // RETURN
            return {
                // FUNCTIONS
                t,
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
