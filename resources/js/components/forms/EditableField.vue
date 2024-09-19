<template>
    <div class="editable-field d-flex gap-2">
        <h3
            class="mb-0 cursor-pointer"
            :contenteditable="editing"
            @click="edit()"
            @input="updateValue"
        >
            {{ editedValue }}
        </h3>
        <form
            v-if="editing"
            class="d-flex flex-row"
            @submit.prevent="submit"
        >
            <button
                :disabled="editedValue === ''"
                type="submit"
                class="btn btn-outline-success btn-sm"
            >
                <i class="fas fa-fw fa-check" />
            </button>
            <button
                type="reset"
                class="btn btn-outline-danger btn-sm"
                @click.prevent="cancelEditing()"
            >
                <i class="fas fa-fw fa-ban" />
            </button>
        </form>
    </div>
</template>

<script>
    import {
        ref,
        watch,
    } from 'vue';

    export default {
        props: {
            value: {
                type: String,
                required: true,
            },
            disabled: {
                type: Boolean,
                default: false,
            },
        },
        emits: ['change'],
        setup(props, context) {

            const editedValue = ref(props.value);
            const editing = ref(false);

            watch(() => props.value, (newValue, oldValue) => {
                editedValue.value = newValue;
            });

            const edit = _ => {
                if(props.disabled) return;
                editing.value = true;
                editedValue.value = props.value;
            };

            const updateValue = event => {
                editedValue.value = event.target.innerText;
            };

            const submit = _ => {
                if(editedValue.value === '') return;

                if(editedValue.value === props.value) {
                    editing.value = false;
                    return;
                }
                context.emit('change', editedValue.value);
            };
            const cancelEditing = _ => {
                editedValue.value = props.value;
                editing.value = false;
            };

            return {
                cancelEditing,
                updateValue,
                edit,
                editing,
                editedValue,
                submit,
            };
        },
    };
</script>