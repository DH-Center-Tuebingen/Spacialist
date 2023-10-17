<template>
    <button
        :id="state.pickId"
        type="button"
        class="btn btn-outline-secondary btn-sm px-1 py-05"
    >
        <span class="far fa-fw fa-laugh" />
    </button>
</template>

<script>
    import {
        reactive,
        onMounted,
    } from 'vue';

    import emojiData from 'emojibase-data/en/data.json';
    import messages from 'emojibase-data/en/messages.json';

    import { createPopup } from '@picmo/popup-picker';

    export default {
        emits: ['selected'],
        setup(props, context) {
            // DATA
            const state = reactive({
                pickId: `emoji-picker-${Date.now()}`,
            });

            // ON MOUNTED
            onMounted(_ => {
                const emojiButton = document.getElementById(state.pickId);

                const picker = createPopup({
                    emojiData,
                    messages,
                }, {
                    triggerElement: emojiButton,
                    referenceElement: emojiButton,
                    position: 'bottom-end',
                    hideOnEmojiSelect: false,
                });

                emojiButton.addEventListener('click', _ => {
                    picker.toggle();
                });

                picker.addEventListener('emoji:select', event => {
                    context.emit('selected', {
                        emoji: event.emoji
                    });
                });
            });

            return {
                state,
            }
        },
    }
</script>