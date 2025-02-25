<template>
    <button
        ref="emojiButton"
        type="button"
        class="btn btn-outline-secondary btn-sm px-1 py-05"
    >
        <span class="far fa-fw fa-laugh" />
    </button>
</template>

<script>
    import {
        reactive,
        ref,
        onMounted,
    } from 'vue';

    import emojiData from 'emojibase-data/en/data.json';
    import messages from 'emojibase-data/en/messages.json';

    import { createPopup } from '@picmo/popup-picker';

    export default {
        emits: ['selected'],
        setup(props, context) {
            // DATA

            const emojiButton = ref(null);

            // ON MOUNTED
            onMounted(_ => {

                const picker = createPopup({
                    emojiData,
                    messages,
                }, {
                    triggerElement: emojiButton.value,
                    referenceElement: emojiButton.value,
                    position: 'bottom-end',
                    hideOnEmojiSelect: false,
                });

                emojiButton.value.addEventListener('click', _ => {
                    picker.toggle();
                });

                picker.addEventListener('emoji:select', event => {
                    context.emit('selected', {
                        emoji: event.emoji
                    });
                });
            });

            return {
                emojiButton,
            };
        },
    };
</script>