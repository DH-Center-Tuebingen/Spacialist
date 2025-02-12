<template>
    <textarea
        ref="textarea"
        :value="modelValue"
        style="resize: none;"
        @input="input"
    />
</template>

<script>
    import { onMounted, ref } from 'vue';

    export default {
        props: {
            modelValue: {
                type: String,
                default: '',
            },
        },
        emits: ['update:modelValue'],
        setup(props, { emit }) {

            const textarea = ref(null);

            onMounted(() => {
                autoExpand();
            });

            const autoExpand = () => {
                textarea.value.style.height = 'auto';
                textarea.value.style.height = (textarea.value.scrollHeight +2) + 'px';
            };

            const input = (event) => {
                autoExpand();
                console.log('update:modelValue', event.target.value);
                emit('update:modelValue', event.target.value);
            };
            return {
                input,
                textarea,
            };
        },
    };
</script>

<style lang='scss' scoped></style>