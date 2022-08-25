<template>
    <button type="button" class="btn btn-outline-secondary btn-sm px-1 py-05" :id="state.pickId">
        <span class="far fa-fw fa-laugh"></span>
    </button>
</template>

<script>
    import {
        reactive,
        onMounted,
    } from 'vue';

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

                const picker = createPopup({}, {
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