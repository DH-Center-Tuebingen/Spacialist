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
import {
    ref,
    watch,
} from 'vue';
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
            default: 'milkdown-wrapper p-3 mt-1 h-100',
        },
        readonly: {
            required: false,
            type: Boolean,
            default: false,
        },
    },
    setup(props) {
        const getEditorMarkdown = _ => {
            return editorRef.value.getMarkdown();
        };

        const editorRef = ref({});
        watch(_ => props.data, (newData, oldData) => {
            if(editorRef.value && editorRef.value.setMarkdown) {
                editorRef.value.setMarkdown(newData);
            }
        });

        return {
            editorRef,
            getEditorMarkdown,
        }
    },
};
</script>