import {
    themeFactory,
} from '@milkdown/core';

const iconMapping = {
    h1: {
        label: 'h1',
        icon: 'fa-h',
    },
    h2: {
        label: 'h2',
        icon: 'fa-h',
    },
    h3: {
        label: 'h3',
        icon: 'fa-h',
    },
    loading: {
        label: 'loading',
        icon: 'fa-hourglass-empty',
    },
    quote: {
        label: 'quote',
        icon: 'fa-quote-right',
    },
    code: {
        label: 'code',
        icon: 'fa-code',
    },
    table: {
        label: 'table',
        icon: 'fa-table',
    },
    divider: {
        label: 'divider',
        icon: 'fa-grip-lines',
    },
    image: {
        label: 'image',
        icon: 'fa-image',
    },
    brokenImage: {
        label: 'broken image',
        icon: 'fa-bolt',
    },
    bulletList: {
        label: 'bullet list',
        icon: 'fa-list-ul',
    },
    orderedList: {
        label: 'ordered list',
        icon: 'fa-list-ol',
    },
    taskList: {
        label: 'task list',
        icon: 'fa-list-check',
    },
    bold: {
        label: 'bold',
        icon: 'fa-bold',
    },
    italic: {
        label: 'italic',
        icon: 'fa-italic',
    },
    inlineCode: {
        label: 'inline code',
        icon: 'fa-code',
    },
    strikeThrough: {
        label: 'strike through',
        icon: 'fa-strikethrough',
    },
    link: {
        label: 'link',
        icon: 'fa-link',
    },
    leftArrow: {
        label: 'left arrow',
        icon: 'fa-caret-left',
    },
    rightArrow: {
        label: 'right arrow',
        icon: 'fa-caret-right',
    },
    upArrow: {
        label: 'up arrow',
        icon: 'fa-caret-up',
    },
    downArrow: {
        label: 'down arrow',
        icon: 'fa-caret-down',
    },
    alignLeft: {
        label: 'align left',
        icon: 'fa-align-left',
    },
    alignRight: {
        label: 'align right',
        icon: 'fa-align-right',
    },
    alignCenter: {
        label: 'align center',
        icon: 'fa-align-center',
    },
    delete: {
        label: 'delete',
        icon: 'fa-trash',
    },
    select: {
        label: 'select',
        icon: 'select_all',
    },
    unchecked: {
        label: 'unchecked',
        icon: 'fa-square',
        type: 'far',
    },
    checked: {
        label: 'checked',
        icon: 'fa-square-check',
        type: 'far',
    },
    undo: {
        label: 'undo',
        icon: 'fa-undo-alt',
    },
    redo: {
        label: 'redo',
        icon: 'fa-redo-alt',
    },
    liftList: {
        label: 'lift list',
        icon: 'fa-outdent',
    },
    sinkList: {
        label: 'sink list',
        icon: 'fa-indent',
    },
};

const view = ({ css }) => css`
    /* copy from https://github.com/ProseMirror/@milkdown/prose/blob/master/style/prosemirror.css */
    .ProseMirror {
        word-wrap: break-word;
        white-space: pre-wrap;
        white-space: break-spaces;
        -webkit-font-variant-ligatures: none;
        font-variant-ligatures: none;
        font-feature-settings: 'liga' 0; /* the above doesn't seem to work in Edge */
    }
    .ProseMirror pre {
        white-space: pre-wrap;
    }
    .ProseMirror-hideselection *::selection {
        background: transparent;
    }
    .ProseMirror-hideselection *::-moz-selection {
        background: transparent;
    }
    .ProseMirror-hideselection {
        caret-color: transparent;
    }
    .ProseMirror-selectednode {
        outline: 2px solid #8cf;
    }
    /* Make sure li selections wrap around markers */
    li.ProseMirror-selectednode {
        outline: none;
    }
    li.ProseMirror-selectednode:after {
        content: '';
        left: -32px;
        right: -2px;
        top: -2px;
        bottom: -2px;
        border: 2px solid #8cf;
        pointer-events: none;
    }
`;

const override =
    ({ css }) =>
    ({ palette, mixin, size, font }) =>
        css`
            .milkdown {
                color: ${palette('neutral', 0.87)};
                background: ${palette('surface')};
                font-family: ${font.typography};
                ${mixin.shadow?.()};
                box-sizing: border-box;
                ${mixin.scrollbar?.()};
                .ProseMirror-selectednode {
                    outline: ${size.lineWidth} solid ${palette('line')};
                }
                li.ProseMirror-selectednode {
                    outline: none;
                }
                li.ProseMirror-selectednode::after {
                    ${mixin.border?.()};
                }
                & ::selection {
                    background: ${palette('secondary', 0.38)};
                }
                overflow-x: auto;
            }
            .milkdown-menu {
                overflow: unset !important;
            }
        `;

export const spacialistTheme = themeFactory(emotion => ({
    font: {
        typography: ['Raleway'],
        code: ['Source Code Pro'],
    },
    size: {
        radius: '2px',
        lineWidth: '1px',
    },
    color: {
        primary: '#0d6efd', // Bootstrap Blue (Primary)
        secondary: '#6C757D',
        neutral: '#6c757d',
        background: '#6C757D',
        solid: '#6c757d',
        // shadow: '#00f',
        line: '#212529',
        surface: '#EBF2FD',
    },
    global: themeTool => {
        const css = emotion.injectGlobal;
        css`
            ${view(emotion)};
            ${override(emotion)(themeTool)}
        `;
    },
    slots: () => ({
        icon: id => {
            const span = document.createElement('span');
            const type = iconMapping[id].type || 'fas';
            span.className = `${type} fa-fw ${iconMapping[id].icon}`;
            return span;
        },
        label: id => {
            return '';
        }
    })
}));

export const spac = spacialistTheme;