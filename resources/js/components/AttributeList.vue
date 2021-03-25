<template>
    <draggable v-if="state.componentLoaded"
        class="h-100 pe-1"
        v-model="state.attributeList"
        item-key="id"
        :disabled="disableDrag"
        :group="group"
        :move="handleMove">
            <template #item="{element, index}">
                <div class="mb-3 row" @mouseenter="onEnter(index)" @mouseleave="onLeave(index)" v-if="!state.hiddenAttributeList[element.id]">
                    <label
                        class="col-form-label col-md-3 d-flex flex-row justify-content-between text-break"
                        v-if="!nolabels"
                        :for="`attr-${element.id}`"
                        :class="attributeClasses(element)">
                        <div v-show="!!state.hoverStates[index]" class="btn-fab-list">
                            <button v-show="hasEmitter('onReorder')" class="reorder-handle btn btn-outline-secondary btn-fab rounded-circle" data-bs-toggle="popover" :data-content="t('global.resort')" data-trigger="hover" data-placement="bottom">
                                <i class="fas fa-fw fa-sort"></i>
                            </button>
                            <button v-show="hasEmitter('onEdit')" class="btn btn-outline-info btn-fab rounded-circle" @click="onEditHandler(element)" data-bs-toggle="popover" :data-content="t('global.edit')" data-trigger="hover" data-placement="bottom">
                                <i class="fas fa-fw fa-xs fa-edit" style="vertical-align: 0;"></i>
                            </button>
                            <button v-show="hasEmitter('onRemove')" class="btn btn-outline-danger btn-fab rounded-circle" @click="onRemoveHandler(element)" data-bs-toggle="popover" :data-content="t('global.remove')" data-trigger="hover" data-placement="bottom">
                                <i class="fas fa-fw fa-xs fa-times" style="vertical-align: 0;"></i>
                            </button>
                            <button v-show="hasEmitter('onDelete')" class="btn btn-outline-danger btn-fab rounded-circle" @click="onDeleteHandler(element)" data-bs-toggle="popover" :data-content="t('global.delete')" data-trigger="hover" data-placement="bottom">
                                <i class="fas fa-fw fa-xs fa-trash" style="vertical-align: 0;"></i>
                            </button>
                        </div>
                        <span class="text-end col">
                            {{ translateConcept(element.thesaurus_url) }}:
                        </span>
                        <sup class="clickable" v-if="hasEmitter('onMetadata')" @click="onMetadataHandler(element)">
                            <span :class="getCertaintyClass(localValues[element.id].certainty, 'text')">
                                <i class="fas fa-fw fa-exclamation"></i>
                            </span>
                            <span v-if="localValues[element.id].comments_count > 0">
                                <i class="fas fa-fw fa-comment"></i>
                            </span>
                            <span v-if="metadataAddon(element.thesaurus_url)">
                                <i class="fas fa-fw fa-bookmark"></i>
                            </span>
                        </sup>
                    </label>
                    <div :class="expandedClasses(index)">
                        <string-attribute
                            v-if="element.datatype == 'string'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="updateDirtyState" />

                        <stringfield-attribute
                            v-else-if="element.datatype == 'stringf'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="updateDirtyState" />

                        <integer-attribute
                            v-else-if="element.datatype == 'integer'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="updateDirtyState" />

                        <float-attribute
                            v-else-if="element.datatype == 'double'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="updateDirtyState" />

                        <bool-attribute
                            v-else-if="element.datatype == 'boolean'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="updateDirtyState" />

                        <percentage-attribute
                            v-else-if="element.datatype == 'percentage'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="updateDirtyState" />
                            
                        <serial-attribute
                            v-else-if="element.datatype == 'serial'"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value" />

                        <list-attribute
                            v-else-if="element.datatype == 'list'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :on-change="value => onChange(null, value, element.id)"
                            :entries="state.attributeValues[element.id].value" />

                        <epoch-attribute
                            v-else-if="element.datatype == 'epoch' || element.datatype == 'timeperiod'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :on-change="(field, value) => onChange(field, value, element.id)"
                            :value="state.attributeValues[element.id].value"
                            :epochs="state.selectionLists[element.id]"
                            :type="element.datatype" />

                        <dimension-attribute
                            v-else-if="element.datatype == 'dimension'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :on-change="(field, value) => onChange(field, value, element.id)"
                            :value="state.attributeValues[element.id].value" />

                        <tabular-attribute
                            v-else-if="element.datatype == 'table'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :on-change="(field, value) => onChange(field, value, element.id)"
                            :value="state.attributeValues[element.id].value"
                            :attribute="element"
                            :selections="state.selectionLists"
                            @expanded="e => onAttributeExpand(e, index)" />

                        <iconclass-attribute
                            v-else-if="element.datatype == 'iconclass'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            :attribute="element"
                            @input="updateValue($event, element.id)" />

                        <geography-attribute
                            v-else-if="element.datatype == 'geography'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            :attribute="element" />

                        <entity-attribute v-else-if="element.datatype == 'entity'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value" />

                        <date-attribute
                            v-else-if="element.datatype == 'date'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="updateDirtyState" />

                        <singlechoice-attribute
                            v-else-if="element.datatype == 'string-sc'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value" />

                        <multichoice-attribute
                            v-else-if="element.datatype == 'string-mc'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            :selections="state.selectionLists[element.id]" />

                        <sql-attribute
                            v-else-if="element.datatype == 'sql'"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value" />

                        <default-attribute
                            v-else
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value" />
                    </div>
                </div>
            </template>
    </draggable>
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

    import { useI18n } from 'vue-i18n';

    import {
        getCertaintyClass,
        translateConcept,
    } from '../helpers/helpers.js';

    import StringAttr from './attribute/String.vue';
    import Stringfield from './attribute/Stringfield.vue';
    import IntegerAttr from './attribute/Integer.vue';
    import FloatAttr from './attribute/Float.vue';
    import Bool from './attribute/Bool.vue';
    import Percentage from './attribute/Percentage.vue';
    import Serial from './attribute/Serial.vue';
    import List from './attribute/List.vue';
    import Epoch from './attribute/Epoch.vue';
    import Dimension from './attribute/Dimension.vue';
    import Tabular from './attribute/Tabular.vue';
    import Iconclass from './attribute/Iconclass.vue';
    import Geography from './attribute/Geography.vue';
    import Entity from './attribute/Entity.vue';
    import DateAttr from './attribute/Date.vue';
    import SingleChoice from './attribute/SingleChoice.vue';
    import MultiChoice from './attribute/MultiChoice.vue';
    import SqlAttr from './attribute/Sql.vue';
    import DefaultAttr from './attribute/Default.vue';

    export default {
        props: {
            attributes: {
                required: true,
                type: Array
            },
            hiddenAttributes: {
                required: false,
                type: Array,
                default: [],
            },
            dependencies: {
                required: false,
                type: Object,
                default: _ => new Object()
            },
            disableDrag: {
                required: false,
                type: Boolean,
                default: false
            },
            group: { // required if onReorder is set // TODO
                required: false,
                type: String
            },
            isSource: {
                required: false,
                type: Boolean,
                default: false
            },
            metadataAddon: {
                required: false,
                type: Function,
                default: () => false
            },
            selections: {
                required: true,
                type: Object
            },
            showInfo: { // shows parent on hover
                required: false,
                type: Boolean
            },
            values: {
                required: true,
                type: Object
            },
            nolabels: {
                required: false,
                type: Boolean,
                default: false,
            },
        },
        components: {
            'string-attribute': StringAttr,
            'stringfield-attribute': Stringfield,
            'integer-attribute': IntegerAttr,
            'float-attribute': FloatAttr,
            'bool-attribute': Bool,
            'percentage-attribute': Percentage,
            'serial-attribute': Serial,
            'dimension-attribute': Dimension,
            'epoch-attribute': Epoch,
            'list-attribute': List,
            'tabular-attribute': Tabular,
            'iconclass-attribute': Iconclass,
            'geography-attribute': Geography,
            'entity-attribute': Entity,
            'date-attribute': DateAttr,
            'singlechoice-attribute': SingleChoice,
            'multichoice-attribute': MultiChoice,
            'sql-attribute': SqlAttr,
            'default-attribute': DefaultAttr,
        },
        emits: ['add', 'dirty'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                attributes,
                hiddenAttributes,
                disableDrag,
                group,
                metadataAddon,
                selections,
                values,
                nolabels,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS
            const attributeClasses = attribute => {
                return {
                    'copy-handle': props.isSource.value && !attribute.isDisabled,
                    'not-allowed-handle text-muted': attribute.isDisabled,
                };
            };
            const expandedClasses = i => {
                let expClasses = {};

                if(nolabels.value || state.expansionStates[i]) {
                    expClasses['col-md-12'] = true;
                } else {
                    expClasses['col-md-9'] = true;
                }
                
                return expClasses;
            };
            const onAttributeExpand = (e, i) => {
                state.expansionStates[i] = !state.expansionStates[i];
            };
            const onEnter = i => {
                state.hoverStates[i] = state.isHoveringPossible;
            };
            const onLeave = i => {
                state.hoverStates[i] = false;
            };
            const handleMove = (e, orgE) => {
                return !disableDrag.value;
            };
            const updateValue = (eventValue, aid) => {
                state.attributeValues[aid].value = eventValue;
            };
            const updateDirtyState = e => {
                context.emit('dirty', e);
            };
            const resetListValues = _ => {
                for(let k in attrRefs) {
                    const curr = attrRefs[k];
                    if(!!curr.resetFieldState) {
                        curr.resetFieldState();
                    }
                }
            };
            const undirtyList = _ => {
                for(let k in attrRefs) {
                    const curr = attrRefs[k];
                    if(!!curr.undirtyField) {
                        curr.undirtyField();
                    }
                }
            };
            const setRef = (el, id) => {
                attrRefs[id] = el;
            };
            const checkDependency = id => {

            };
            const onReorderHandler = element => {
                context.emit('reorder', {
                    element: element
                });
            };
            const onEditHandler = element => {
                context.emit('edit', {
                    element: element
                });
            };
            const onRemoveHandler = element => {
                context.emit('remove', {
                    element: element
                });
            };
            const onDeleteHandler = element => {
                context.emit('delete', {
                    element: element
                });
            };
            const onMetadataHandler = element => {
                context.emit('metadata', {
                    element: element
                });
            };
            const hasEmitter = which => {
                return !!attrs[which];
            };

            const attrs = context.attrs;
            // DATA
            const attrRefs = ref({});
            const state = reactive({
                attributeList: attributes,
                attributeValues: values,
                selectionLists: selections,
                hoverStates: new Array(attributes.value.length).fill(false),
                expansionStates: new Array(attributes.value.length).fill(false),
                componentLoaded: computed(_ => state.attributeList.length > 0 && state.attributeValues),
                isHoveringPossible: computed(_ => {
                    return !!attrs.onReorder || !!attrs.onEdit || !!attrs.onRemove || !!attrs.onDelete;
                }),
                hiddenAttributeList: computed(_ => {
                    if(!state.componentLoaded) return {};

                    const list = {};
                    for(let i=0; i<hiddenAttributes.value.length; i++) {
                        const disId = hiddenAttributes.value[i];
                        list[disId] = true;
                    }
                    return list;
                }),
            });

            // ON MOUNTED
            onMounted(_ => {
                console.log(values, "values raw");
                console.log(state.attributeValues.value, "state values");
            });
            onBeforeUpdate(_ => {
                attrRefs.value = {};
            });

            // RETURN
            return {
                t,
                // HELPERS
                getCertaintyClass,
                translateConcept,
                // LOCAL
                attributeClasses,
                expandedClasses,
                onAttributeExpand,
                onEnter,
                onLeave,
                handleMove,
                updateValue,
                updateDirtyState,
                resetListValues,
                undirtyList,
                setRef,
                checkDependency,
                onReorderHandler,
                onEditHandler,
                onRemoveHandler,
                onDeleteHandler,
                onMetadataHandler,
                hasEmitter,
                // PROPS
                disableDrag,
                group,
                nolabels,
                // STATE
                attrRefs,
                state,
            }
        },
    }
</script>
