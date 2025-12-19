<template>
    <div class="mt-2 mb-0 overflow-auto border rounded p-2 bg-light">
        <vuedraggable
            v-if="state.expanded && v.value.length"
            ref="draggableElement"
            :list="v.value"
            itemKey="id"
            style="max-height: 50vh;"
            ghost-class="vue-draggable-ghost"
            @change="handleDrag"
            @start="dragStart"
            @end="dragEnd"
        >
            <template #item="{ element: l, index: i }">
                <div class="input-group mb-2">
                    <input
                        :type="type"
                        class="form-control"
                        :value="v.value[i].value"
                        @input="(val) => updateItem(i, val)"
                    />
                    <button
                        v-if="!disabled"
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        @click.prevent="removeListEntry(i)"
                    >
                        <i class="fas fa-fw fa-minus" />
                    </button>
                </div>
            </template>
        </vuedraggable>
        <div class="input-group">
            <button
                class="btn btn-sm btn-outline-success w-75"
                @click="addListEntry"
            >
                <i class="fas fa-fw fa-plus" />
            </button>
            <button
                class="btn btn-sm  w-25"
                :class="{ '  btn-outline-secondary': !v.meta.dirty, 'btn-outline-warning': v.meta.dirty }"
                @click="resetFieldState"
                :disabled="!v.meta.dirty"
            >
                <i class="fas fa-fw fa-rotate-left" />
            </button>
        </div>

    </div>
</template>

<script>
    import {
        computed,
        reactive,
        ref,
        toRefs,
        watch,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        createAnchorFromUrl,
    } from '@/helpers/helpers.js';

    import vuedraggable from 'vuedraggable';

    export default {
        components: {
            vuedraggable,
        },
        props: {
            name: {
                type: String,
                required: false,
                default: null
            },
            entries: {
                type: Array,
                default: _ => new Array(),
            },
            disabled: {
                type: Boolean,
            },
            type: {
                type: String,
                validator: value => {
                    return [
                        'text',
                        'number',
                    ].includes(value);
                },
            }
        },
        emits: ['change'],
        setup(props, context) {
            const { t } = useI18n();

            const newItemId = ref(new Date().getTime());

            // FUNCTIONS
            const getDefaultValue = _ => {
                return props.type === 'number' ? 0 : '';
            };

            const addListEntry = _ => {
                v.value.push({ id: newItemId.value++, value: getDefaultValue() });
                v.meta.dirty = true;
                v.meta.validated = true;
            };
            const removeListEntry = index => {
                v.value.splice(index, 1);
                v.meta.dirty = true;
                v.meta.validated = true;
            };
            const toggleList = _ => {
                state.expanded = !state.expanded;
            };
            const resetFieldState = _ => {
                // make sure to keep original array and only re-push values
                v.value.length = 0;
                v.value.push(...state.initialValue);

                v.meta.dirty = false;
                v.meta.valid = true;
                v.meta.validated = false;
            };
            const undirtyField = _ => {
                state.initialValue = [...v.value];

                v.meta.dirty = false;
                v.meta.valid = true;
                v.meta.validated = false;
            };

            // DATA
            const state = reactive({
                input: '',
                initialValue: [...props.entries],
                expanded: true,
            });
            const v = reactive({
                meta: {
                    dirty: false,
                    valid: true,
                    validated: false,
                },
                value: [...props.entries],
            });

            const commaSeparatedList = computed(_ => {
                return props.entries.join(', ');
            });

            watch(v.value, (newValue, oldValue) => {
                // only emit @change event if field is validated (required because Entity.vue components)
                // trigger this watcher several times even if another component is updated/validated
                if(!v.meta.validated) return;
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: v.value,
                });
            });



            const updateItem = (index, val) => {
                v.value[index].value = props.type === 'number' ? val.target.valueAsNumber : val.target.value;
                v.meta.dirty = true;
                v.meta.validated = true;
            };

            const handleDrag = ({ element, oldIndex, newIndex }) => {
                v.meta.dirty = true;
                v.meta.validated = true;
                const tmpValue = v.value.splice(oldIndex, 1)[0];
                v.value.splice(newIndex, 0, tmpValue);
            };

            const dragged = ref(false);
            const draggableElement = ref(null);
            const dragStart = (...args) => {
            };
            const dragEnd = _ => {
                dragged.value = false;
            };

            // RETURN
            return {
                t,
                // HELPERS
                createAnchorFromUrl,
                commaSeparatedList,
                dragged,
                draggableElement,
                dragStart,
                dragEnd,
                updateItem,
                handleDrag,
                // LOCAL
                addListEntry,
                removeListEntry,
                toggleList,
                resetFieldState,
                undirtyField,
                // STATE
                state,
                v,
            };
        },
    };
</script>
