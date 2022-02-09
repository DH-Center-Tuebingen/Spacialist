<template>
    <VueEditor :editor="editor"/>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import {
        Editor,
        rootCtx,
        defaultValueCtx,
    } from '@milkdown/core';
    import {
        VueEditor,
        useEditor
    } from '@milkdown/vue';
    import {
        gfm,
    } from '@milkdown/preset-gfm';
    import { spac } from './theme.js';
    import { menu } from './menu.js';
    import { listener, listenerCtx } from '@milkdown/plugin-listener';
    import { history } from '@milkdown/plugin-history';
    import { clipboard } from '@milkdown/plugin-clipboard';
    import { prism } from '@milkdown/plugin-prism';
    import { math } from '@milkdown/plugin-math';
    import { tooltip } from '@milkdown/plugin-tooltip';
    import { emoji } from '@milkdown/plugin-emoji';
    import { diagram } from '@milkdown/plugin-diagram';
    import { indent } from '@milkdown/plugin-indent';
    import { upload } from '@milkdown/plugin-upload';

    export default {
        // components: {
        //     EditorContent,
        // },
        components: {
            VueEditor,
        },
        props: {
            data: {
                required: true,
                type: String,
            },
        },
        emits: ['closing'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                data,
            } = toRefs(props);

            // FUNCTIONS
            const getMarkdown = _ => {
                return state.markdownString;
            };

            // DATA
            const editor = useEditor((root) =>
                Editor.make()
                    .config((ctx) => {
                        ctx.set(rootCtx, root);
                        ctx.set(defaultValueCtx, data.value);
                        ctx.get(listenerCtx).markdownUpdated((ctx, markdown, prevMarkdown) => {
                            state.markdownString = markdown;
                        });
                    })
                    .use(spac)
                    // .use(commonmark)
                    .use(gfm)
                    .use(listener)
                    .use(history)
                    .use(clipboard)
                    .use(prism)
                    .use(math)
                    .use(tooltip)
                    .use(emoji)
                    .use(diagram)
                    .use(indent)
                    .use(upload)
                    .use(menu)
            );
            const state = reactive({
                show: false,
                markdownString: data.value,
            });

            // ON MOUNTED

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                // LOCAL
                getMarkdown,
                // STATE
                editor,
                state,
            }
        },
    }
</script>