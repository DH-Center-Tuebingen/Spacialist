<template>
    <div
        class="editable-field d-flex gap-2 position-relative"
        @click.stop
        @mousedown.stop
    >
        <form
            class="d-flex flex-row input-group bg-white rounded"
            @submit.prevent="submit"
        >
            <div
                v-if="!editing"
                class="form-control  bg-white cursor-pointer"
                :style="inputStyle"
                @click.stop="edit()"
                @mousedown.stop
            >
                {{ value }}
            </div>
            <input
                v-else
                ref="inputRef"
                v-model="editedValue"
                class="form-control"
                :style="inputStyle"
                @click.stop
                @mousedown.stop
            >
            <button
                v-visible="!editing"
                :disabled="true"
                class="btn position-absolute end-0 border-0"
            >
                <i class="fas fa-fw fa-pencil" />
            </button>
            <button
                v-visible="editing"
                type="reset"
                class="btn btn-outline-secondary"
                @click.prevent="cancelEditing()"
            >
                <i class="fas fa-fw fa-times" />
            </button>
            <button
                v-visible="editing"
                :disabled="editedValue === ''"
                type="submit"
                class="btn btn-outline-success"
            >
                <i class="fas fa-fw fa-check" />
            </button>
        </form>
    </div>
</template>

<script>
    import {
        ref,
        watch,
    } from 'vue';

    import {
        useGlobalClick
    } from '@/composables/global-click';
    import { nextTick } from 'process';

    export default {
        props: {
            value: {
                type: String,
                required: true,
            },
        },
        emits: ['change'],
        setup(props, context) {

            const editedValue = ref(props.value);
            const editing = ref(false);
            const inputRef = ref(null);

            useGlobalClick(() => {
                editing.value = false;
            }, 'mousedown');

            watch(() => props.value, (newValue, oldValue) => {
                editedValue.value = newValue;
            });

            const edit = _ => {
                editing.value = true;
                editedValue.value = props.value;
                nextTick(_ => inputRef.value.focus());
            };

            const submit = _ => {
                console.log('submitting: ', editedValue.value);
                if(editedValue.value === '') return;

                editing.value = false;
                if(editedValue.value === props.value) {
                    return;
                } else {
                    context.emit('change', editedValue.value);
                }
            };
            const cancelEditing = _ => {
                editedValue.value = props.value;
                editing.value = false;
            };
            
            const inputStyle = `width: 200px; border-color: transparent`;

            return {
                cancelEditing,
                edit,
                editing,
                editedValue,
                inputRef,
                inputStyle,
                submit,
            };
        },
    };
</script>