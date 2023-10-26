<template>
    <MilkdownProvider>
        <MilkdownEditor
            :ref="el => editorRef = el"
            class="milkdown-wrapper h-100"
            :data="data"
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
        },
        setup(props) {
            const {
                data,
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