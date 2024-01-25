<template>
    <div
        class="d-flex flex-row gap-2 align-items-center bg-primary bg-opacity-10 px-2 py-1"
    >
        <span
            @click="undo()"
        >
            <i class="fas fa-fw fa-undo" />
        </span>
        <span
            @click="redo()"
        >
            <i class="fas fa-fw fa-redo" />
        </span>
        |
        <div
            class="d-flex flex-row"
        >
            <span
                @click="heading(1)"
            >
                <i class="fas fa-fw fa-h" />
            </span>
            <div class="dropdown">
                <span
                    class="dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                />
                <ul class="dropdown-menu small">
                    <li>
                        <span
                            class="dropdown-item text-muted d-flex flex-row gap-1"
                            @click="heading(1)"
                        >
                            <i class="fas fa-h" />
                            <i class="fas fa-1" />
                        </span>
                    </li>
                    <li>
                        <span
                            class="dropdown-item text-muted d-flex flex-row gap-1"
                            @click="heading(2)"
                        >
                            <i class="fas fa-h" />
                            <i class="fas fa-2" />
                        </span>
                    </li>
                    <li>
                        <span
                            class="dropdown-item text-muted d-flex flex-row gap-1"
                            @click="heading(3)"
                        >
                            <i class="fas fa-h" />
                            <i class="fas fa-3" />
                        </span>
                    </li>
                    <li>
                        <span
                            class="dropdown-item text-muted d-flex flex-row gap-1"
                            @click="heading(4)"
                        >
                            <i class="fas fa-h" />
                            <i class="fas fa-4" />
                        </span>
                    </li>
                    <li>
                        <span
                            class="dropdown-item text-muted d-flex flex-row gap-1"
                            @click="heading(5)"
                        >
                            <i class="fas fa-h" />
                            <i class="fas fa-5" />
                        </span>
                    </li>
                    <li>
                        <span
                            class="dropdown-item text-muted d-flex flex-row gap-1"
                            @click="heading(6)"
                        >
                            <i class="fas fa-h" />
                            <i class="fas fa-6" />
                        </span>
                    </li>
                    <li>
                        <span
                            class="dropdown-item text-muted"
                            @click="paragraph()"
                        >
                            <i class="fas fa-paragraph" />
                        </span>
                    </li>
                </ul>
            </div>
        </div>
        <span
            @click="bold()"
        >
            <i class="fas fa-fw fa-bold" />
        </span>
        <span
            @click="emph()"
        >
            <i class="fas fa-fw fa-italic" />
        </span>
        <span
            @click="strike()"
        >
            <i class="fas fa-fw fa-strikethrough" />
        </span>
        |
        <span
            @click="orderlist()"
        >
            <i class="fas fa-fw fa-list-ol" />
        </span>
        <span
            @click="bulletlist()"
        >
            <i class="fas fa-fw fa-list-ul" />
        </span>
        <span
            @click="indent_item()"
        >
            <i class="fas fa-fw fa-indent" />
        </span>
        |
        <span
            :class="{'opacity-50': state.editMode}"
            @click="editmode()"
        >
            <i class="fab fa-fw fa-markdown" />
        </span>
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
} from '@milkdown/preset-commonmark';
import { toggleStrikethroughCommand } from '@milkdown/preset-gfm';
import {
    redoCommand,
    undoCommand,
} from '@milkdown/plugin-history';
import {
    callCommand,
} from '@milkdown/utils';

export default {
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

        // FUNCTIONS
        const command = (action, data) => {
            state.mde.action(callCommand(action, data));
        }
        const undo = _ => {
            command(undoCommand.key);
        };
        const redo = _ => {
            command(redoCommand.key);
        };
        const heading = i => {
            command(wrapInHeadingCommand.key, i);
        };
        const paragraph = _ => {
            command(turnIntoTextCommand.key);
        };
        const bold = _ => {
            command(toggleStrongCommand.key);
        };
        const emph = _ => {
            command(toggleEmphasisCommand.key);
        };
        const strike = _ => {
            command(toggleStrikethroughCommand.key);
        };
        const orderlist = _ => {
            command(wrapInOrderedListCommand.key);
        };
        const bulletlist = _ => {
            command(wrapInBulletListCommand.key);
        };
        const indent_item = _ => {
            command(sinkListItemCommand.key);
        };
        const editmode = _ => {
            state.editMode = !state.editMode;
            context.emit('toggle', state.editMode);
        };

        // DATA
        const state = reactive({
            mde: computed(_ => {
                return editor.value
            }),
            editMode: false,
        });

        // ON MOUNTED

        // RETURN
        return {
            t,
            // HELPERS
            // PROPS
            // LOCAL
            undo,
            redo,
            heading,
            paragraph,
            bold,
            emph,
            strike,
            orderlist,
            bulletlist,
            indent_item,
            editmode,
            // STATE
            state,
        };
    },
}
</script>