<template>
    <tr
        @mouseenter="state.isHovered = true"
        @mouseleave="state.isHovered = false"
    >
        <td
            class="fw-bold"
            :class="state.rowStateClasses"
        >
            {{ number + 1 }}
        </td>
        <td
            v-for="(column, i) in columns"
            :key="`tabular-row-${number}-column-${i}`"
            :class="state.rowStateClasses"
        >
            <Attribute
                :ref="el => setRef(el, i)"
                :data="column"
                :value-wrapper="{value: data[column.id]}"
                :disabled="disabled || data.mark_deleted"
                :react-to="state.rootAttributeValues[column.root_attribute_id]"
                :hide-links="hideLinks"
                @change="e => updateDirtyState(e, column.id)"
                @update-selection="handleSelectionUpdate"
            />
        </td>
        <td
            v-if="!disabled"
            class="text-center"
            :class="state.rowStateClasses"
        >
            <div
                v-if="state.isHovered"
                class="dropdown"
            >
                <span
                    :id="`tabular-row-options-${number}`"
                    class="clickable"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                >
                    <i class="fas fa-fw fa-ellipsis-h" />
                </span>
                <div
                    class="dropdown-menu"
                    :aria-labelledby="`tabular-row-options-${number}`"
                >
                    <a
                        class="dropdown-item"
                        href="#"
                        @click.prevent="reset"
                    >
                        <i class="fas fa-fw fa-undo text-info" /> {{ t('global.reset') }}
                    </a>
                    <a
                        v-if="data.mark_deleted"
                        class="dropdown-item"
                        href="#"
                        @click.prevent="restore"
                    >
                        <i class="fas fa-fw fa-trash-restore text-warning" /> {{ t('global.restore') }}
                    </a>
                    <a
                        v-else
                        class="dropdown-item"
                        href="#"
                        @click.prevent="markForDelete"
                    >
                        <i class="fas fa-fw fa-trash text-danger" /> {{ t('global.delete') }}
                    </a>
                </div>
            </div>
        </td>
    </tr>
</template>

<script>
    import {
        computed,
        onBeforeUpdate,
        onMounted,
        reactive,
        ref,
        toRefs,
        watch,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        _cloneDeep,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            data: {
                type: Object,
                required: true,
            },
            columns: {
                type: Object,
                required: true,
            },
            disabled: {
                type: Boolean,
                required: false,
                default: false,
            },
            number: {
                type: Number,
                required: false,
                default: 0,
            },
            hideLinks: {
                type: Boolean,
                required: false,
                default: false,
            },
        },
        emits: ['change', 'delete', 'reset', 'restore'],
        setup(props, context) {
            const { t } = useI18n();

            const {
                data,
                columns,
                disabled,
                number,
                hideLinks,
            } = toRefs(props);

            // FETCH

            // FUNCTIONS
            const handleSelectionUpdate = e => {
                const elemId = e.elemId;
                const conceptId = e.conceptId;
                if(state.dynamicSelectionList.includes(elemId)) {
                    state.rootAttributeValues[elemId] = conceptId;
                }
            };
            const updateDirtyState = (e, columnId) => {
                state.currentValue[columnId] = e.value;
                v.meta.dirty = getRowDirty();
                v.meta.valid = getRowValid();
                const emitData = {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: state.currentValue,
                };
                context.emit('change', emitData);
            };
            const setRef = (el, idx) => {
                columnRefs.value[idx] = el;
            };
            const resetFieldState = _ => {
                state.currentValue = _cloneDeep(state.initialValue);
                for(let k in columnRefs.value) {
                    const curr = columnRefs.value[k];
                    if(curr?.v?.meta?.dirty && !!curr.resetFieldState) {
                        curr.resetFieldState();
                    }
                }
            };
            const undirtyField = _ => {
                state.initialValue = _cloneDeep(state.currentValue);
                for(let k in columnRefs.value) {
                    const curr = columnRefs.value[k];
                    if(curr?.v?.meta?.dirty && !!curr.undirtyField) {
                        curr.undirtyField();
                    }
                }
            };
            const getRowValid = _ => {
                for(let k in columnRefs.value) {
                    const curr = columnRefs.value[k];
                    if(!curr?.v?.meta?.valid) return false;
                }
                return true;
            };
            const getRowDirty = _ => {
                for(let k in columnRefs.value) {
                    const curr = columnRefs.value[k];
                    if(!!curr?.v?.meta?.dirty) return true;
                }
                return false;
            };
            const markForDelete = _ => {
                context.emit('delete');
            };
            const restore = _ => {
                context.emit('restore');
            };
            const reset = _ => {
                context.emit('reset');
            };

            // DATA
            const columnRefs = ref({});
            const state = reactive({
                initialValue: _cloneDeep(data.value),
                currentValue: _cloneDeep(data.value),
                isHovered: false,
                rootAttributeValues: {},
                dynamicSelectionList: computed(_ => {
                    const list = [];
                    for(let k in columns.value) {
                        const a = columns.value[k];
                        if(a.root_attribute_id) {
                            list.push(a.root_attribute_id);
                        }
                    }
                    return list;
                }),
                rowStateClasses: computed(_ => {
                    const classes = [];
                    if(data.value.mark_deleted) {
                        classes.push('bg-danger', 'bg-opacity-50');
                    }
                    return classes;
                }),
            });
            const v = reactive({
                value: state.currentValue,
                meta: {
                    dirty: false,
                    valid: true,
                },
            });

            watch(_ => data, (newValue, oldValue) => {
                state.currentValue = _cloneDeep(data.value);
                resetFieldState();

            });

            // ON MOUNTED
            onMounted(_ => {
                state.dynamicSelectionList.forEach(rootId => {
                    const attrValue = row.value[rootId];
                    if(attrValue) {
                        handleSelectionUpdate({
                            elemId: rootId,
                            conceptId: attrValue.id,
                        });
                    }
                });
            });
            onBeforeUpdate(_ => {
                columnRefs.value = {};
            });

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                resetFieldState,
                undirtyField,
                handleSelectionUpdate,
                updateDirtyState,
                setRef,
                markForDelete,
                restore,
                reset,
                // PROPS
                // STATE
                state,
                v,
            };
        },
    };
</script>