<template>
    <button type="button" class="btn btn-outline-secondary" :id="state.pickId">
        😀
    </button>
</template>

<script>
    import {
        reactive,
        onMounted,
    } from 'vue';

    import { EmojiButton } from '@joeattardi/emoji-button';

    export default {
        emits: ['selected'],
        setup(props, context) {
            // DATA
            const state = reactive({
                pickId: `emoji-picker-${Date.now()}`,
                picker: new EmojiButton({
                    autoHide: false,
                    position: 'bottom-end',
                    zIndex: 1021
                }),
            });

            // ON MOUNTED
            onMounted(_ => {
                const emojiButton = document.getElementById(state.pickId);
    
                state.picker.on('emoji', emoji => {
                    context.emit('selected', {
                        emoji: emoji.emoji
                    });
                });
    
                emojiButton.addEventListener('click', _ => {
                    state.picker.togglePicker(emojiButton);
                });
            });

            return {
                state,
            }
        },
    }
</script>