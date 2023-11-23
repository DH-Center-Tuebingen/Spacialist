<template>
    <div v-html="rendered" />
</template>

<script>
    import {
        computed,
        reactive,
        ref,
        toRefs,
watch,
    } from 'vue';

    import Markdown from 'markdown-it';
    import remarkGfm from 'remark-gfm';
    import remarkGemoji from 'remark-gemoji';

    export default {
        props: {
            id: {
                required: true,
                type: String,
            },
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

            const rendered = computed(_ => {
                return new Markdown().render(source.value);
            });            

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

            return {
                state,
                rendered,
            }
        },
    };
</script>


<style scoped>
pre {
    background-color: red;
}
</style>