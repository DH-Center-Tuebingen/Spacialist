<template>
    <Milkdown :editor="editor" />
</template>

<script>
import {
    reactive,
    toRefs,
} from 'vue';
import { useI18n } from 'vue-i18n';

import {
    Editor,
    rootCtx,
    defaultValueCtx,
    rootAttrsCtx,
    editorViewOptionsCtx,
} from '@milkdown/core';
import {
    Milkdown,
    useEditor
} from '@milkdown/vue';
import { commonmark } from '@milkdown/preset-commonmark';
import { gfm } from '@milkdown/preset-gfm';
import { listener, listenerCtx } from '@milkdown/plugin-listener';
import { history } from '@milkdown/plugin-history';
import { clipboard } from '@milkdown/plugin-clipboard';
import { prism } from '@milkdown/plugin-prism';
import { math } from '@milkdown/plugin-math';
import {
    emojiAttr,
    remarkEmojiPlugin,
    emojiSchema,
} from '@milkdown/plugin-emoji';
import { diagram } from '@milkdown/plugin-diagram';
import { indent } from '@milkdown/plugin-indent';
import { upload } from '@milkdown/plugin-upload';
import { replaceAll } from '@milkdown/utils';

export default {
    components: {
        Milkdown,
    },
    props: {
        data: {
            required: true,
            type: String,
        },
        readonly: {
            required: false,
            type: Boolean,
            default: false,
        },
    },
    emits: ['closing'],
    setup(props, context) {
        const { t } = useI18n();
        const {
            data,
            readonly,
        } = toRefs(props);

        // FUNCTIONS
        const getMarkdown = _ => {
            return state.markdownString;
        };
        const setMarkdown = markdown => {
            if(editor) {
                editor.action(replaceAll(markdown));
            }
        };

        // DATA
        const emojiPlugin = [
            emojiAttr,
            remarkEmojiPlugin,
            emojiSchema,
        ].flat()

        let editor = null

        useEditor((root) =>
            editor = Editor.make()
                .config((ctx) => {
                    ctx.set(rootCtx, root);
                    ctx.set(defaultValueCtx, data.value);
                    ctx.update(rootAttrsCtx, (prev) => ({
                        ...prev,
                        class: `milkdown h-100`,
                    }));
                    ctx.update(editorViewOptionsCtx, (prev) => ({
                        ...prev,
                        editable: _ => !readonly.value,
                    }));
                    ctx.get(listenerCtx).markdownUpdated((ctx, markdown, prevMarkdown) => {
                        state.markdownString = markdown;
                    });
                })
                .use(commonmark)
                .use(gfm)
                .use(listener)
                .use(history)
                .use(clipboard)
                .use(prism)
                .use(math)
                .use(emojiPlugin)
                .use(diagram)
                .use(indent)
                .use(upload)
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
            setMarkdown,
            // STATE
            editor,
            state,
        };
    },
}
</script>