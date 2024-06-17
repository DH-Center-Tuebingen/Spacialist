<template>
    <div class="d-flex flex-row gap-2 align-items-center bg-primary bg-opacity-10 px-2 py-1 user-select-none">
        <template v-for="(commandGroup, index) of commandGroups">
            <span
                v-for="command of commandGroup"
                :key="command.name"
                class="clickable"
                :class="getClassFromCommand(command)"
                @click="command.command"
            >

                <template v-if="Array.isArray(command.icon)">
                    <i
                        v-for="icon of command.icon"
                        :key="icon"
                        :class="icon"
                    />
                </template>
                <i
                    v-else
                    :class="command.icon"
                />
            </span>

            <separator
                v-if="index !== commandGroups.length - 1"
                :key="`separator-${index}`"
                class="separator"
            />
        </template>
    </div>
</template>

<script>

    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        toggleEmphasisCommand,
        toggleStrongCommand,
        turnIntoTextCommand,
        wrapInHeadingCommand,
        wrapInBulletListCommand,
        wrapInOrderedListCommand,
        sinkListItemCommand,
        liftListItemCommand,
    } from '@milkdown/preset-commonmark';

    import { toggleStrikethroughCommand } from '@milkdown/preset-gfm';

    import {
        redoCommand,
        undoCommand,
    } from '@milkdown/plugin-history';

    import {
        callCommand,
    } from '@milkdown/utils';

    import Separator from '@/components/structure/Separator.vue';

    export default {
        components: {
            Separator,
        },
        props: {
            editor: {
                required: true,
                type: Object,
            },
        },
        emits: ['toggle'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                editor,
            } = toRefs(props);

            // DATA
            const state = reactive({
                mde: computed(_ => {
                    return editor.value;
                }),
                editMode: false,
            });

            // FUNCTIONS
            const command = (action, data) => {
                state.mde.action(callCommand(action, data));
            };

            const heading = i => {
                command(wrapInHeadingCommand.key, i);
            };

            const toggleEditmode = _ => {
                state.editMode = !state.editMode;
                context.emit('toggle', state.editMode);
            };

            // As we need to dynamically change classes
            // it's most suitable to pass a computed prop.
            // Therefore we need to extract the value on change
            // so we use this helper function.
            const getClassFromCommand = command => {
                let cssClass = '';
                if(command.class) {
                    cssClass = command.class;
                    if(cssClass.value) {
                        cssClass = cssClass.value;
                    }
                }

                return cssClass;
            };

            const redoGroup = [
                {
                    name: 'undo',
                    command: _ => command(undoCommand.key),
                    icon: 'fas fa-fw fa-undo',
                }, {
                    name: 'redo',
                    command: _ => command(redoCommand.key),
                    icon: 'fas fa-fw fa-redo',
                },
            ];

            const headingsGroup = [
                {
                    name: 'heading',
                    command: _ => heading(1),
                    icon: ['fas fa-fw fa-h', 'fas fa-1'],

                },
                {
                    name: 'heading',
                    command: _ => heading(2),
                    icon: ['fas fa-fw fa-h', 'fas fa-2'],

                },
                {
                    name: 'heading',
                    command: _ => heading(3),
                    icon: ['fas fa-fw fa-h', 'fas fa-3'],

                },
                {
                    name: 'heading',
                    command: _ => heading(4),
                    icon: ['fas fa-fw fa-h', 'fas fa-4'],

                },
                {
                    name: 'paragraph',
                    command: _ => command(turnIntoTextCommand.key),
                    icon: 'fas fa-fw fa-paragraph',

                },
            ];

            const stylingGroup = [
                {
                    name: 'bold',
                    command: _ => command(toggleStrongCommand.key),
                    icon: 'fas fa-fw fa-bold',

                },
                {
                    name: 'italic',
                    command: _ => command(toggleEmphasisCommand.key),
                    icon: 'fas fa-fw fa-italic',

                },
                {
                    name: 'strikethrough',
                    command: _ => command(toggleStrikethroughCommand.key),
                    icon: 'fas fa-fw fa-strikethrough',

                },
            ];

            const listGroup = [
                {
                    name: 'orderlist',
                    command: _ => command(wrapInOrderedListCommand.key),
                    icon: 'fas fa-fw fa-list-ol',

                },
                {
                    name: 'bulletlist',
                    command: _ => command(wrapInBulletListCommand.key),
                    icon: 'fas fa-fw fa-list-ul',

                },
                {
                    name: 'outdent_item',
                    command: _ => command(liftListItemCommand.key),
                    icon: 'fas fa-fw fa-outdent',

                },
                {
                    name: 'indent_item',
                    command: _ => command(sinkListItemCommand.key),
                    icon: 'fas fa-fw fa-indent',

                },
            ];

            const markdownClass = computed(_ => {
                return state.editMode ? 'opacity-50' : '';
            });

            const utilsGroup = [
                {
                    name: 'editmode',
                    command: _ => toggleEditmode(),
                    icon: 'fab fa-fw fa-markdown',
                    class: markdownClass
                },
            ];

            const commandGroups = [
                redoGroup,
                headingsGroup,
                stylingGroup,
                listGroup,
                utilsGroup,
            ];

            // RETURN
            return {
                t,
                // LOCAL
                getClassFromCommand,
                toggleEditmode,
                // STATE
                state,
                commandGroups,
            };
        },
    };
</script>