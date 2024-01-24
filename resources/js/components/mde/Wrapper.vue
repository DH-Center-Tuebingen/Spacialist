<template>
    <MilkdownProvider>
        <MilkdownEditor
            ref="editorRef"
            :class="classes"
            :data="data"
            :readonly="readonly"
        />
    </MilkdownProvider>
</template>
  
<script>
import MilkdownEditor from './Main.vue';
import { MilkdownProvider } from '@milkdown/vue';

export default {
    name: 'MilkdownEditorWrapper',
    components: {
        MilkdownProvider,
        MilkdownEditor,
    },
    props: {
        data: {
            required: true,
            type: String,
        },
        classes: {
            required: false,
            type: String,
            default: 'milkdown-wrapper h-100',
        },
        readonly: {
            required: false,
            type: Boolean,
            default: false,
        },
    },
    watch: {
        data(newData, oldData) {
            if (this.$refs.editorRef && this.$refs.editorRef.setMarkdown) {
                this.$refs.editorRef.setMarkdown(newData);
            }
        }
    },
    methods: {
        getEditorMarkdown() {
            return this.$refs.editorRef.getMarkdown();
        }
    }
};
</script>