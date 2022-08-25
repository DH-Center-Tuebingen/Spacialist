import {
    themeFactory,
    ThemeColor,
    ThemeSize,
    ThemeFont,
    ThemeIcon,
    ThemeGlobal,
    ThemeScrollbar,
    ThemeShadow,
    ThemeBorder,
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
        icon: 'fa-minus',
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
        icon: 'fa-hand-pointer',
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
                font-family: ${font('typography')};
                ${mixin.shadow?.()};
                box-sizing: border-box;
                ${mixin.scrollbar?.()};
                .ProseMirror-selectednode {
                    outline: ${size('lineWidth')} solid ${palette('line')};
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

export const spacialistTheme = themeFactory((emotion, manager) => {
    manager.set(ThemeFont, key => {
        if(key == 'typography') {
            return 'Raleway';
        } else if (key == 'code') {
            return 'Source Code Pro';
        } else {
            return 'monospace';
        }
    });
    manager.set(ThemeSize, key => {
        if(key == 'radius') {
            return '2px';
        } else if(key == 'lineWidth') {
            return '1px';
        } else {
            return '1px';
        }
    });
    manager.set(ThemeColor, ([key, opacity]) => {
        opacity = opacity || 1;
        switch(key) {
            case 'primary':
                return `rgba(13, 110, 253, ${opacity})`; // Bootstrap Blue (Primary)
            case 'secondary':
                return `rgba(108, 117, 125, ${opacity})`;
            case 'neutral':
                return `rgba(108, 117, 125, ${opacity})`;
            case 'background':
                return `rgba(108, 117, 125, ${opacity})`;
            case 'solid':
                return `rgba(108, 117, 125, ${opacity})`;
            // case 'shadow':
            //     return `#00f`;
            case 'line':
                return `rgba(33, 37, 41, ${opacity})`;
            case 'surface':
                return `rgba(235, 242, 253, ${opacity})`;
            default:
                return `rgba(0, 0, 0, ${opacity})`;
        }
    });
    manager.set(ThemeGlobal, _ => {
        const options = {
            palette: (color, opacity = 1) => manager.get(ThemeColor, [color, opacity]),
            size: (key) => manager.get(ThemeSize, key),
            font: (font) => manager.get(ThemeFont, font),
            mixin: {
                scrollbar: _ => manager.get(ThemeScrollbar, _),
                shadow: _ => manager.get(ThemeShadow, _),
                border: (dir = '') => manager.get(ThemeBorder, dir),
            }
        };

        emotion.injectGlobal`
            ${view(emotion)};
            ${override(emotion)(options)}
        `;
    });
    manager.set(ThemeIcon, key => {
        const target = iconMapping[key];
        if(!target) return;

        const {
            icon,
            label,
        } = target;
        const span = document.createElement('span');
        const type = target.type || 'fas';
        span.className = `${type} fa-fw ${icon}`;
        span.textContent = '';

        return {
            dom: span,
            label,
        };
    });
});

export const spac = spacialistTheme;