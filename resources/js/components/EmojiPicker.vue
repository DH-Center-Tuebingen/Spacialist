<template>
    <button type="button" class="btn btn-outline-secondary" :id="pickId">
        😀
    </button>
</template>

<script>
    import EmojiButton from '@joeattardi/emoji-button';

    export default {
        mounted() {
            const emojiButton = document.getElementById(this.pickId);

            this.picker.on('emoji', emoji => {
                this.$emit('selected', {
                    emoji: emoji
                });
            });

            emojiButton.addEventListener('click', _ => {
                this.picker.togglePicker(emojiButton);
            });
        },
        data() {
            return {
                uid: Date.now(),
                picker: new EmojiButton({
                    autoHide: false,
                    position: 'bottom-end',
                    zIndex: 1021
                }),
            }
        },
        computed: {
            pickId() {
                return `emoji-picker-${this.uid}`;
            }
        }
    }
</script>