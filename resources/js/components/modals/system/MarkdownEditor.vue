<template>
  <vue-final-modal
    classes="modal-container"
    content-class="sp-modal-content sp-modal-content-xl h-100"
    v-model="state.show"
    name="user-info-modal">
    <div class="modal-header">
        <h5 class="modal-title">
            {{
                t('global.markdown_editor.title')
            }}
        </h5>
        <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
        </button>
    </div>
    <div class="modal-body overflow-hidden">
        <md-editor class="h-100" :ref="el => editorRef = el" :data="data" />
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-outline-success" data-bs-dismiss="modal" @click="updateContent()">
            <i class="fas fa-fw fa-save"></i> {{ t('global.save') }}
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" @click="closeModal()">
            <i class="fas fa-fw fa-times"></i> {{ t('global.close') }}
        </button>
    </div>
  </vue-final-modal>
</template>

<script>
    import {
        computed,
        onMounted,
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
                const md = editorRef.value.getMarkdown();
                state.show = false;
                context.emit('confirm', md);
            };
            const closeModal = _ => {
                state.show = false;
                context.emit('closing', false);
            };

            // DATA
            const editorRef = ref({});
            const state = reactive({
                show: false,
            });

            // ON MOUNTED
            onMounted(_ => {
                state.show = true;
                console.log(editorRef);
            });

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                data,
                // LOCAL
                updateContent,
                closeModal,
                // STATE
                state,
                editorRef,
            }
        },
    }
</script>