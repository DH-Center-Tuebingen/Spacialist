<template>
    <tr>
        <td class="fw-bold">{{ number + 1 }}</td>
        <td
            v-for="(column, i) in columns"
            :key="`tabluar-row-${number}-column-${i}`"
        >
            <Attribute
                :ref="el => setRef(el, i)"
                :data="column"
                :value-wrapper="{value: data[column.id]}"
                :disabled="disabled"
                :react-to="state.rootAttributeValues[column.root_attribute_id]"
                :hide-links="hideLinks"
                @change="e => updateDirtyState(e, column.id)"
                @update-selection="handleSelectionUpdate"
            />
        </td>
        <td
            v-if="!disabled"
            class="text-center"
        >
            <div class="dropdown">
                <span
                    :id="`tabular-row-options-${$index}`"
                    class="clickable"
                    data-bs-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                >
                    <i class="fas fa-fw fa-ellipsis-h" />
                </span>
                <div
                    class="dropdown-menu"
                    :aria-labelledby="`tabular-row-options-${$index}`"
                >
                    <a
                        class="dropdown-item"
                        href="#"
                        @click.prevent="resetRow($index)"
                    >
                        <i class="fas fa-fw fa-undo text-info" /> {{ t('global.reset') }}
                    </a>
                    <a
                        v-if="data.mark_deleted"
                        class="dropdown-item"
                        href="#"
                        @click.prevent="restoreTableRow($index)"
                    >
                        <i class="fas fa-fw fa-trash-restore text-warning" /> {{ t('global.restore') }}
                    </a>
                    <a
                        v-else
                        class="dropdown-item"
                        href="#"
                        @click.prevent="markTableRowForDelete($index)"
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
    } from 'vue';

    import { useField } from 'vee-validate';

    import * as yup from 'yup';

    import { useI18n } from 'vue-i18n';
    import store from '@/bootstrap/store.js';

    import {
        createDownloadLink,
        getTs,
        getAttribute,
        slugify,
        translateConcept,
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
        emits: ['change'],
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
                // const currentValue = _cloneDeep(v.value);
                // currentValue[rowIdx][columnId] = e.value;
                // v.handleChange(currentValue);
                // context.emit('change', e);
                console.log("TODO", e, columnId);
            };
            const setRef = (el, idx) => {
                columnRefs.value[idx] = el;
            };
            // const resetFieldState = _ => {
            //     v.resetField({
            //         value: value.value
            //     });
            //     for(let k in columnRefs.value) {
            //         const curr = columnRefs.value[k];
            //         if(!!curr && !!curr.v && curr.v.meta.dirty && !!curr.resetFieldState) {
            //             curr.resetFieldState();
            //         }
            //     }
            //     state.deletedRows = {};
            // };
            // const undirtyField = _ => {
            //     v.resetField({
            //         value: v.value.filter(cv => !cv.mark_deleted),
            //     });
            //     for(let k in columnRefs.value) {
            //         const curr = columnRefs.value[k];
            //         if(!!curr.v && curr.v.meta.dirty && !!curr.undirtyField) {
            //             curr.undirtyField();
            //         }
            //     }
            // };
            // const restoreTableRow = index => {
            //     const currentValue = v.value;
            //     delete currentValue[index].mark_deleted;
            //     v.handleChange(currentValue);
            // };
            // const markTableRowForDelete = index => {
            //     const currentValue = _cloneDeep(v.value);
            //     currentValue[index].mark_deleted = true;
            //     v.handleChange(currentValue);
            // };
            // const resetRow = index => {
            //     for(let k in state.columns) {
            //         const reference = columnRefs.value[`${index}_${state.columns[k].id}`];
            //         if(!!reference.resetFieldState) {
            //             reference.resetFieldState();
            //         }
            //     }
            //     restoreTableRow(index);
            // };

            // DATA
            const columnRefs = ref({});
            // const {
            //     handleChange,
            //     value: fieldValue,
            //     meta,
            //     resetField,
            // } = useField(`tabular_${name.value}`, yup.array(), {
            //     initialValue: value.value,
            // });
            const state = reactive({
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
            });
            // const v = reactive({
            //     value: fieldValue,
            //     handleChange,
            //     meta,
            //     resetField,
            // });

            // watch(_ => value, (newValue, oldValue) => {
            //     resetFieldState();
            // });
            // watch(_ => v.value, (newValue, oldValue) => {
            //     // only emit @change event if field is validated (required because Entity.vue components)
            //     // trigger this watcher several times even if another component is updated/validated
            //     if(!v.meta.validated) return;
            //     context.emit('change', {
            //         dirty: v.meta.dirty,
            //         valid: v.meta.valid,
            //         value: v.value,
            //     });
            // });

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
                handleSelectionUpdate,
                updateDirtyState,
                setRef,
                // PROPS
                // STATE
                state,
                // v,
            };
        },
    };
</script>