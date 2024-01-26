<template>
    <div>
        <command-palette
            v-if="!readonly"
            :editor="editor"
            @toggle="setEditorType"
        />
        <Milkdown
            v-show="state.type == 'md'"
            :class="state.editModeClasses"
            :editor="editor"
        />
        <textarea
            v-show="state.type == 'raw'"
            v-model="state.markdownString"
            class="font-monospace border-0 px-5 py-4 flex-grow-1 w-100 rounded-3 bg-transparent"
            style="resize: none; outline: none;"
        />
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        ref,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import CommandPalette from './CommandPalette.vue';

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
    import { watch } from 'vue';
    import { usePreventNavigation } from '../../helpers/form';

    export default {
        components: {
            Milkdown,
            CommandPalette,
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
        emits: ['closing', 'update'],
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
                if(editor.value) {
                    editor.value.action(replaceAll(markdown));
                }
            };
            const setEditorType = _ => {
                if(state.type == 'md') {
                    state.type = 'raw';
                } else {
                    setMarkdown(getMarkdown());
                    state.type = 'md';
                }
            };

            // DATA
            const emojiPlugin = [
                emojiAttr,
                remarkEmojiPlugin,
                emojiSchema,
            ].flat();

            const editor = ref({});

            useEditor((root) =>
                editor.value = Editor.make()
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
                dirty: false,
                show: false,
                markdownString: data.value,
                type: 'md',
                editModeClasses: computed(_ => {
                    if(!readonly.value) {
                        return [
                            'flex-grow-1',
                            'overflow-y-auto',
                        ];
                    }
                    return [];
                }),
            });

            // Only add the prevent navigation hook if the editor is not readonly
            // otherwise the hook will be added concurrently and unecessary when the editor is used
            // in preview mode.
            if(!readonly.value)
                usePreventNavigation(() => state.dirty);

            watch(() => state.markdownString,
                (markdownString) => {
                    state.dirty = markdownString != data.value;
                    context.emit('update', markdownString);
                }
            );

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                // LOCAL
                getMarkdown,
                setMarkdown,
                setEditorType,
                // STATE
                editor,
                state,
            };
        },
    };
</script>