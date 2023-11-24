<template>
    <MilkdownProvider>
        <MilkdownEditor
            :ref="el => editorRef = el"
            :class="classes"
            :data="data"
            :readonly="readonly"
        />
    </MilkdownProvider>
</template>
  
<script>
    import {
        ref,
        toRefs,
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
                default: 'milkdown-wrapper h-100',
            },
            readonly: {
                required: false,
                type: Boolean,
                default: false,
            },
        },
        setup(props) {
            const {
                data,
                classes,
                readonly,
            } = toRefs(props);

            const getEditorMarkdown = _ => {
                return editorRef.value.getMarkdown();
            };

            const editorRef = ref({});

            return {
                // HELPERS
                // LOCAL
                getEditorMarkdown,
                // STATE
                editorRef,
            };
        },
    };
</script>