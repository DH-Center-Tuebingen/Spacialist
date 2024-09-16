<template>
    <div
        class="editable-field d-flex gap-2 position-relative overflow-hidden rounded"
        style=""
        @click="edit"
        @mousedown.stop
    >
        <form
            class="d-flex flex-row flex-nowrap input-group rounded"
            @submit.prevent="submit"
        >
            <div
                v-if="!editing"
                class="cursor-pointer text-truncate"
                :style="inputStyle"
            >
                {{ value }}
            </div>
            <input
                v-else
                ref="inputRef"
                v-model="editedValue"
                class="text-truncate shadow-none"
                :style="inputStyle"
            >
            <template v-if="!disabled">
                <button
                    v-visible="!editing"
                    :disabled="true"
                    class="btn position-absolute end-0 border-0 pointer-events-none"
                >
                    <i class="fas fa-fw fa-pencil" />
                </button>
                <button
                    v-visible="editing"
                    :disabled="!editing"
                    type="submit"
                    class="btn btn-outline-success border-0"
                >
                    <i class="fas fa-fw fa-check" />
                </button>
                <button
                    v-visible="editing"
                    :disabled="!editing"
                    type="reset"
                    class="btn btn-outline-secondary border-0"
                    @click.stop.prevent="cancelEditing()"
                >
                    <i class="fas fa-fw fa-times" />
                </button>
            </template>
        </form>
    </div>
</template>

<script>

    // OUTSOURCE - TODO - Move to dhc-components

    import {
        computed,
        readonly,
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
            disabled: {
                type: Boolean,
                default: false,
            },
        },
        emits: ['change'],
        setup(props, context) {

            const editedValue = ref(props.value == '');
            const editing = ref(false);
            const inputRef = ref(null);

            useGlobalClick(() => {
                editing.value = false;
            }, 'mousedown');

            watch(() => props.value, (newValue, oldValue) => {
                editedValue.value = newValue ?? '';
            });

            const edit = _ => {
                if(props.disabled) return;
                editing.value = true;
                editedValue.value = props.value;
                nextTick(_ => {
                    if(inputRef.value)
                        inputRef.value.focus();
                });
            };

            const submit = _ => {
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

            // The input field cannot be dynamically be sized to the content, so we set a calculated value here.
            // Note: A contenteditable was considered, but it's quite a hassle to have it work properly in vue (jumping cursor problem)
            //       that's why we stick with the input field.
            const inputStyle = computed(() => {

                let paddingLeft = 0;
                let paddingRight = 0;
                if(inputRef.value) {
                    const computedStyle = window.getComputedStyle(inputRef.value);
                    paddingLeft = computedStyle.paddingLeft;
                    paddingRight = computedStyle.paddingRight;
                }

                return {
                    'min-width': '7ch',
                    // The sizing is on non-mono fonts not perfect, but it's good enough for now.
                    'width': `calc(${editedValue.value.length + 2}ch + ${paddingLeft} + ${paddingLeft})`,
                    'border-color': 'transparent',
                    'background-color': 'transparent',
                    'overflow': 'hidden',
                    'text-overflow': 'ellipsis',
                    'white-space': 'nowrap',
                    'outline-offset': '-3px'
                };
            });

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