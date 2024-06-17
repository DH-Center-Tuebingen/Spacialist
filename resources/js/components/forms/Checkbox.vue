<template>
    <input
        :ref="ref"
        type="checkbox"
        class="dhc-checkbox form-check-input"
        :checked="modelValue"
        :true-value="true"
        :false-value="false"
        @change="onInput"
    >
</template>


<script setup>
    const props = defineProps({
        modelValue: {
            required: true,
            type: Boolean,
        },
        readonly: {
            required: false,
            type: Boolean,
            default: false,
        },
    })

    const emit = defineEmits(['input', 'changed', 'update:modelValue'])

    const onInput = (e)=> {
        if(props.readonly.value) return;
        emit('update:modelValue', !props.modelValue);
        emit('input', !props.modelValue);
        emit('changed', !props.modelValue);

    }
</script>