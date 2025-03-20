<template>
    <div class="toolbar list-toolbar input-group">
        <span
            class="input-group-text"
            :class="{
                'active':
                    modelValue.text.length
                    > 0
            }"
        >
            <i class="fas fa-search" />
        </span>
        <input
            class="form-control"
            type="search"
            :value="modelValue.text"
            @input="updateText"
        >
        <button
            class="btn input-group-text d-flex align-items-center"
            type="button"
            :class="{
                'btn-outline-primary': modelValue.type === 'text',
                'btn-outline-secondary': modelValue.type !== 'text',
                'active': modelValue.type === 'text'
            }"
            @click="sortClicked('text')"
        >
            <span v-show="modelValue.type === 'text'">
                <i class="fas fa-fw fa-arrow-down-a-z" />
            </span>
            <span v-show="modelValue.type === 'number'">
                <i class="fas fa-fw fa-arrow-down-1-9" />
            </span>
        </button>
    </div>
</template>

<script>
    import { _cloneDeep } from '@/helpers/helpers.js';

    export default {
        props: {
            modelValue: {
                type: Object,
                required: true,
                validation: (obj) => {
                    return obj.hasOwnProperty('text') && obj.hasOwnProperty('type') && obj.hasOwnProperty('asc');
                }
            },
        },
        emits: ['update:modelValue'],
        setup(props, context) {
            const sortClicked = _ => {
                const clone = _cloneDeep(props.modelValue);
                clone.type = clone.type === 'text' ? 'number' : 'text';
                context.emit('update:modelValue', clone);
            };

            const updateText = event => {
                const clone = _cloneDeep(props.modelValue);
                clone.text = event.target.value;
                context.emit('update:modelValue', clone);
            };

            return {
                updateText,
                sortClicked,
            };
        },
    };
</script>