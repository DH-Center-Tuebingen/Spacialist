<template>
    <MilkdownProvider>
        <MilkdownEditor class="milkdown-wrapper h-100" :ref="el => editorRef = el" :data="data" />
    </MilkdownProvider>
</template>
  
<script>
    import {
        ref,
        toRefs,
    } from 'vue';
    import MilkdownEditor from "./Main.vue";
    import { MilkdownProvider } from "@milkdown/vue";
    
    export default {
        name: "MilkdownEditorWrapper",
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
                // PROPS
                data,
                // LOCAL
                getEditorMarkdown,
                // STATE
                editorRef,
            };
        },
    };
</script>