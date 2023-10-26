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
                    <small>
                        {{ t('main.preference.key.project.maintainer') }}
                    </small>
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body overflow-hidden">
                <md-editor
                    :ref="el => wrapperRef = el"
                    class="h-100"
                    :data="data"
                />
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-outline-success"
                    data-bs-dismiss="modal"
                    @click="updateContent()"
                >
                    <i class="fas fa-fw fa-save" /> {{ t('global.save') }}
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                >
                    <i class="fas fa-fw fa-times" /> {{ t('global.close') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        reactive,
        ref,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            data: {
                required: true,
                type: String,
            },
        },
        emits: ['confirm', 'closing'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                data,
            } = toRefs(props);

            // FUNCTIONS
            const updateContent = _ => {
                const md = wrapperRef.value.getEditorMarkdown();
                context.emit('confirm', md);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const wrapperRef = ref({});
            const state = reactive({
            });

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                updateContent,
                closeModal,
                // STATE
                state,
                wrapperRef,
            }
        },
    }
</script>