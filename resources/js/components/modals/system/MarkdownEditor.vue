<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-xl h-100"
        name="markdown-editor-modal"
    >
        <div class="sp-modal-content sp-modal-content-xl h-100">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{
                        t('global.markdown_editor.title')
                    }}

                    <small
                        v-if="options.subtitle"
                        class="ms-2"
                    >
                        {{ options.subtitle }}
                    </small>
                </h5>
            </div>
            <div class="modal-body overflow-hidden">
                <md-editor
                    :ref="el => wrapperRef = el"
                    :classes="'milkdown-wrapper h-100 mt-0 p-0 d-flex flex-column'"
                    :data="content"
                    @update="contentUpdated"
                />
            </div>
            <div class="modal-footer">
                <template v-if="state.isDirty">
                    <button
                        type="button"
                        class="btn btn-warning"
                        data-bs-dismiss="modal"
                        @click="closeModal()"
                    >
                        <i class="fas fa-fw fa-undo" />{{ t('global.discard.changes') }}
                    </button>
                    <button
                        type="button"
                        class="btn btn-outline-success"
                        data-bs-dismiss="modal"
                        @click="updateContent()"
                    >
                        <i class="fas fa-fw fa-check" /> {{ t('global.apply') }}
                    </button>
                </template>
                <button
                    v-else
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                >
                    <i class="fas fa-fw fa-times" /> {{ t('global.cancel') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        reactive,
        ref,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            content: {
                required: true,
                type: String,
            },
            options: {
                default: () => ({}),
                type: Object,
            },
        },
        emits: ['confirm', 'closing'],
        setup(props, context) {
            const { t } = useI18n();

            // FUNCTIONS
            const updateContent = _ => {
                const md = wrapperRef.value.getEditorMarkdown();
                context.emit('confirm', md);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };

            const contentUpdated = _ => {
                state.isDirty = true;
            };

            // DATA
            const wrapperRef = ref({});

            const state = reactive({
                isDirty: false,
            });

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                updateContent,
                contentUpdated,
                closeModal,
                // STATE
                wrapperRef,
                state,
            };
        },
    };
</script>