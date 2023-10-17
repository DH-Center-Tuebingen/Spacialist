<template>
    <vue-markdown
        class="markdown-viewer"
        :rehype-plugins="state.plugins"
        :source="source"
    />
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';

    import remarkGfm from 'remark-gfm';
    import remarkGemoji from 'remark-gemoji';

    export default {
        props: {
            source: {
                required: true,
                type: String,
            },
            plugins: {
                required: false,
                type: String,
                default: 'gfm,emoji',
            },
        },
        setup(props) {
            const {
                source,
                plugins,
            } = toRefs(props);

            // FUNCTIONS

            // DATA
            const state = reactive({
                plugins: computed(_ => {
                    const selected = [];
                    if(!plugins.value) {
                        return selected;
                    }

                    plugins.value.split(',').forEach(p => {
                        p = p.trim();
                        if(p == 'gfm') {
                            selected.push(remarkGfm);
                        } else if(p == 'emoji') {
                            selected.push(remarkGemoji);
                        }
                    });

                    return selected;
                }),
            });

            // RETURN
            return {
                // HELPERS
                // LOCAL
                // STATE
                state,
            }
        },
    };
</script>
