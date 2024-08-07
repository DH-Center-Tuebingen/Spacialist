<template>
    <string-attribute
        v-if="data.datatype == 'string'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        @change="updateDirtyState"
    />

    <stringfield-attribute
        v-else-if="data.datatype == 'stringf'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        @change="updateDirtyState"
    />

    <richtext-attribute
        v-else-if="data.datatype == 'richtext'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        @change="updateDirtyState"
    />

    <integer-attribute
        v-else-if="data.datatype == 'integer'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        @change="updateDirtyState"
    />

    <float-attribute
        v-else-if="data.datatype == 'double'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        @change="updateDirtyState"
    />

    <bool-attribute
        v-else-if="data.datatype == 'boolean'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        @change="updateDirtyState"
    />

    <percentage-attribute
        v-else-if="data.datatype == 'percentage'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        @change="updateDirtyState"
    />

    <serial-attribute
        v-else-if="data.datatype == 'serial'"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
    />

    <list-attribute
        v-else-if="data.datatype == 'list'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :entries="state.value"
        @change="updateDirtyState"
    />

    <epoch-attribute
        v-else-if="data.datatype == 'epoch' || data.datatype == 'timeperiod'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        :epochs="state.selection"
        :type="data.datatype"
        @change="updateDirtyState"
    />

    <dimension-attribute
        v-else-if="data.datatype == 'dimension'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        @change="updateDirtyState"
    />

    <si-unit-attribute
        v-else-if="data.datatype == 'si-unit'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        :metadata="data.metadata"
        @change="updateDirtyState"
    />

    <tabular-attribute
        v-else-if="data.datatype == 'table'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        :attribute="data"
        :preview-columns="preview ? previewData[data.id] : null"
        @expanded="e => $emit('expanded', e)"
        @change="updateDirtyState"
    />

    <iconclass-attribute
        v-else-if="data.datatype == 'iconclass'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        :attribute="data"
        @change="updateDirtyState"
    />

    <rism-attribute
        v-else-if="data.datatype == 'rism'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        :attribute="data"
        @change="updateDirtyState"
    />

    <geography-attribute
        v-else-if="data.datatype == 'geography'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        :attribute="data"
        @change="updateDirtyState"
    />

    <entity-attribute
        v-else-if="data.datatype == 'entity' || data.datatype == 'entity-mc'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :multiple="data.datatype == 'entity-mc'"
        :hide-link="hideLinks"
        :value="state.value"
        :search-in="data.restrictions"
        @change="updateDirtyState"
    />

    <date-attribute
        v-else-if="data.datatype == 'date'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        @change="updateDirtyState"
    />

    <daterange-attribute
        v-else-if="data.datatype == 'daterange'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        @change="updateDirtyState"
    />

    <singlechoice-attribute
        v-else-if="data.datatype == 'string-sc'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        :selections="state.selection"
        :selection-from="data.root_attribute_id"
        :selection-from-value="reactTo"
        @update-selection="onSelectionUpdate"
        @change="updateDirtyState"
    />

    <multichoice-attribute
        v-else-if="data.datatype == 'string-mc'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        :selections="state.selection"
        @change="updateDirtyState"
    />

    <userlist-attribute
        v-else-if="data.datatype == 'userlist'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        @change="updateDirtyState"
    />

    <url-attribute
        v-else-if="data.datatype == 'url'"
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        @change="updateDirtyState"
    />

    <sql-attribute
        v-else-if="data.datatype == 'sql'"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
    />

    <system-separator-attribute
        v-else-if="data.datatype == 'system-separator'"
        :name="`attr-${data.id}`"
        :title="getSeparatorTitle(data)"
    />

    <default-attribute
        v-else
        :ref="el => setRef(el)"
        :disabled="state.disabled"
        :name="`attr-${data.id}`"
        :value="state.value"
        @change="updateDirtyState"
    />
</template>

<script>
    import {
        computed,
        reactive,
        ref,
        toRefs,
    } from 'vue';

    import {
        getAttributeSelection,
        getEmptyAttributeValue,
        _cloneDeep,
    } from '@/helpers/helpers.js';

    import StringAttr from '@/components/attribute/String.vue';
    import Stringfield from '@/components/attribute/Stringfield.vue';
    import Richtext from '@/components/attribute/Richtext.vue';
    import IntegerAttr from '@/components/attribute/Integer.vue';
    import FloatAttr from '@/components/attribute/Float.vue';
    import Bool from '@/components/attribute/Bool.vue';
    import Percentage from '@/components/attribute/Percentage.vue';
    import Serial from '@/components/attribute/Serial.vue';
    import List from '@/components/attribute/List.vue';
    import Epoch from '@/components/attribute/Epoch.vue';
    import Dimension from '@/components/attribute/Dimension.vue';
    import SiUnit from '@/components/attribute/SiUnit.vue';
    import Tabular from '@/components/attribute/Tabular.vue';
    import Iconclass from '@/components/attribute/Iconclass.vue';
    import RISM from '@/components/attribute/Rism.vue';
    import Geography from '@/components/attribute/Geography.vue';
    import Entity from '@/components/attribute/Entity.vue';
    import DateAttr from '@/components/attribute/Date.vue';
    import DaterangeAttr from '@/components/attribute/Daterange.vue';
    import SingleChoice from '@/components/attribute/SingleChoice.vue';
    import MultiChoice from '@/components/attribute/MultiChoice.vue';
    import UserList from '@/components/attribute/UserList.vue';
    import Url from '@/components/attribute/Url.vue';
    import SqlAttr from '@/components/attribute/Sql.vue';
    import SystemSeparator from '@/components/attribute/SystemSeparator.vue';
    import DefaultAttr from '@/components/attribute/Default.vue';

    export default {
        components: {
            'string-attribute': StringAttr,
            'stringfield-attribute': Stringfield,
            'richtext-attribute': Richtext,
            'integer-attribute': IntegerAttr,
            'float-attribute': FloatAttr,
            'bool-attribute': Bool,
            'percentage-attribute': Percentage,
            'serial-attribute': Serial,
            'dimension-attribute': Dimension,
            'si-unit-attribute': SiUnit,
            'epoch-attribute': Epoch,
            'list-attribute': List,
            'tabular-attribute': Tabular,
            'iconclass-attribute': Iconclass,
            'rism-attribute': RISM,
            'geography-attribute': Geography,
            'entity-attribute': Entity,
            'date-attribute': DateAttr,
            'daterange-attribute': DaterangeAttr,
            'singlechoice-attribute': SingleChoice,
            'multichoice-attribute': MultiChoice,
            'userlist-attribute': UserList,
            'url-attribute': Url,
            'sql-attribute': SqlAttr,
            'system-separator-attribute': SystemSeparator,
            'default-attribute': DefaultAttr,
        },
        props: {
            data: {
                type: Object,
                required: true,
            },
            valueWrapper: {
                type: Object,
                required: false,
                default: _ => new Object(),
            },
            disabled: {
                type: Boolean,
                required: false,
                default: false,
            },
            reactTo: {
                type: Number,
                required: false,
                default: -1,
            },
            hideLinks: {
                type: Boolean,
                required: false,
                default: false,
            },
            preview: {
                type: Boolean,
                required: false,
                default: false,
            },
            previewData: {
                type: Object,
                required: false,
                default: _ => new Object(),
            },
        },
        emits: ['expanded','change', 'update-selection'],
        setup(props, context) {
            const {
                data,
                valueWrapper,
                disabled,
            } = toRefs(props);
            // FETCH
            
            const getValueOrDefault = _ => {
                return valueWrapper.value.value || getEmptyAttributeValue(data.value.datatype);
            };

            const attrRef = ref({});
            const state = reactive({
                type: computed(_ => data.value.datatype),
                disabled: computed(_ => data.value.isDisabled || disabled.value),
                value: _cloneDeep(getValueOrDefault()),
                // TODO check for selection need?
                selection: computed(_ => getAttributeSelection(data.value.id) || []),

            });

            const setRef = el => {
                attrRef.value = el;
                v.value = attrRef.value?.v;
            };

            const updateDirtyState = e => {
                context.emit('change', {
                    ...e,
                    attribute_id: data.value.id,
                });
            };

            const onSelectionUpdate = conceptId => {
                context.emit('update-selection', {
                    elemId: data.value.id,
                    conceptId: conceptId,
                });
            };

            const getSeparatorTitle = _ => {
                if(state.type != 'system-separator') return;

                if(data.value.pivot && data.value.pivot.metadata) {
                    return data.value.pivot.metadata.title;
                } else {
                    return null;
                }
            };

            const undirtyField = _ => {
                if(attrRef.value && attrRef.value.undirtyField) {
                    attrRef.value.undirtyField();
                }
            };

            const resetFieldState = _ => {
                if(attrRef.value && attrRef.value.resetFieldState) {
                    attrRef.value.resetFieldState();
                }
            };

            const v = ref({});

            // RETURN
            return {
                // HELPERS
                // LOCAL
                setRef,
                updateDirtyState,
                onSelectionUpdate,
                getSeparatorTitle,
                getValueOrDefault,
                //
                undirtyField,
                resetFieldState,
                v,
                // STATE
                state,
            };
        },
    };
</script>