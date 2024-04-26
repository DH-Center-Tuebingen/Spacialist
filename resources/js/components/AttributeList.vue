<template>
    <draggable
        v-if="state.componentLoaded"
        v-model="state.attributeList"
        item-key="id"
        class="attribute-list-container align-content-start"
        :class="classes"
        :disabled="disableDrag || preview"
        :group="group"
        :move="handleMove"
        @change="handleUpdate"
    >
        <template #item="{ element, index }">
            <div
                v-if="!state.hiddenAttributeList[element.id] || showHidden"
                class="mb-3"
                :class="clFromMetadata(element)"
                @mouseenter="onEnter(index)"
                @mouseleave="onLeave(index)"
            >
                <div
                    class="row"
                    :class="addModerationStateClasses(element.id)"
                >
                    <label
                        v-if="!state.hideLabels"
                        class="col-form-label col-md-3 d-flex flex-row justify-content-between text-break"
                        :for="`attr-${element.id}`"
                        :class="attributeClasses(element)"
                        @click="e => handleLabelClick(e, element.datatype)"
                    >
                        <div
                            v-show="!!state.hoverStates[index]"
                            class="btn-fab-list"
                        >
                            <button
                                v-show="hasEmitter('onReorderList')"
                                class="reorder-handle btn btn-outline-secondary btn-fab rounded-circle"
                                data-bs-toggle="popover"
                                :data-content="t('global.resort')"
                                data-trigger="hover"
                                data-placement="bottom"
                            >
                                <i class="fas fa-fw fa-sort" />
                            </button>
                            <button
                                v-show="hasEmitter('onEditElement')"
                                class="btn btn-outline-info btn-fab rounded-circle"
                                data-bs-toggle="popover"
                                :data-content="t('global.edit')"
                                data-trigger="hover"
                                data-placement="bottom"
                                @click="onEditHandler(element)"
                            >
                                <i
                                    class="fas fa-fw fa-xs fa-edit"
                                    style="vertical-align: 0;"
                                />
                            </button>
                            <button
                                v-show="hasEmitter('onRemoveElement')"
                                class="btn btn-outline-danger btn-fab rounded-circle"
                                data-bs-toggle="popover"
                                :data-content="t('global.remove')"
                                data-trigger="hover"
                                data-placement="bottom"
                                @click="onRemoveHandler(element)"
                            >
                                <i
                                    class="fas fa-fw fa-xs fa-times"
                                    style="vertical-align: 0;"
                                />
                            </button>
                            <button
                                v-show="hasEmitter('onDeleteElement')"
                                class="btn btn-outline-danger btn-fab rounded-circle"
                                data-bs-toggle="popover"
                                :data-content="t('global.delete')"
                                data-trigger="hover"
                                data-placement="bottom"
                                @click="onDeleteHandler(element)"
                            >
                                <i
                                    class="fas fa-fw fa-xs fa-trash"
                                    style="vertical-align: 0;"
                                />
                            </button>
                        </div>
                        <span
                            v-if="!element.is_system"
                            class="text-end col"
                        >
                            {{ translateConcept(element.thesaurus_url) }}:
                        </span>
                        <sup
                            v-if="hasEmitter('onMetadata')"
                            class="clickable"
                            @click="onMetadataHandler(element)"
                        >
                            <span :class="getCertaintyClass(state.attributeValues[element.id].certainty, 'text')">
                                <i class="fas fa-fw fa-exclamation" />
                            </span>
                            <span v-if="state.attributeValues[element.id].comments_count > 0">
                                <i class="fas fa-fw fa-comment" />
                            </span>
                            <span v-if="metadataAddon(element.thesaurus_url)">
                                <i class="fas fa-fw fa-bookmark" />
                            </span>
                        </sup>
                        <sup
                            v-if="hasEmitter('onEditElement') && !!element.pivot.depends_on"
                            class="font-size-50"
                        >
                            <i class="fas fa-fw fa-circle text-warning" />
                        </sup>
                    </label>
                    <div :class="expandedClasses(index)">
                        <string-attribute
                            v-if="element.datatype == 'string'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <stringfield-attribute
                            v-else-if="element.datatype == 'stringf'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <richtext-attribute
                            v-else-if="element.datatype == 'richtext'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <integer-attribute
                            v-else-if="element.datatype == 'integer'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <float-attribute
                            v-else-if="element.datatype == 'double'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <bool-attribute
                            v-else-if="element.datatype == 'boolean'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <percentage-attribute
                            v-else-if="element.datatype == 'percentage'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <serial-attribute
                            v-else-if="element.datatype == 'serial'"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                        />

                        <list-attribute
                            v-else-if="element.datatype == 'list'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :entries="state.attributeValues[element.id].value"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <epoch-attribute
                            v-else-if="element.datatype == 'epoch' || element.datatype == 'timeperiod'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            :epochs="selections[element.id]"
                            :type="element.datatype"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <dimension-attribute
                            v-else-if="element.datatype == 'dimension'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <si-unit-attribute
                            v-else-if="element.datatype == 'si-unit'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            :metadata="element.metadata"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <tabular-attribute
                            v-else-if="element.datatype == 'table'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            :attribute="element"
                            :selections="selections"
                            :preview-columns="preview ? previewData[element.id] : null"
                            @expanded="e => onAttributeExpand(e, index)"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <iconclass-attribute
                            v-else-if="element.datatype == 'iconclass'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            :attribute="element"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <rism-attribute
                            v-else-if="element.datatype == 'rism'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            :attribute="element"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <geography-attribute
                            v-else-if="element.datatype == 'geography'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            :attribute="element"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <entity-attribute
                            v-else-if="element.datatype == 'entity' || element.datatype == 'entity-mc'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :multiple="element.datatype == 'entity-mc'"
                            :hide-link="state.hideEntityLink"
                            :value="convertEntityValue(state.attributeValues[element.id], element.datatype == 'entity-mc')"
                            :search-in="element.restrictions"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <date-attribute
                            v-else-if="element.datatype == 'date'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <daterange-attribute
                            v-else-if="element.datatype == 'daterange'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <singlechoice-attribute
                            v-else-if="element.datatype == 'string-sc'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            :selections="selections[element.id]"
                            :selection-from="element.root_attribute_id"
                            :selection-from-value="state.rootAttributeValues[element.root_attribute_id]"
                            @update-selection="e => handleSelectionUpdate(element.id, e)"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <multichoice-attribute
                            v-else-if="element.datatype == 'string-mc'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            :selections="selections[element.id]"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <userlist-attribute
                            v-else-if="element.datatype == 'userlist'"
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <sql-attribute
                            v-else-if="element.datatype == 'sql'"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                        />

                        <system-separator-attribute
                            v-else-if="element.datatype == 'system-separator'"
                            :name="`attr-${element.id}`"
                            :title="getSeparatorTitle(element)"
                        />

                        <default-attribute
                            v-else
                            :ref="el => setRef(el, element.id)"
                            :disabled="element.isDisabled || state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                            :name="`attr-${element.id}`"
                            :value="state.attributeValues[element.id].value"
                            @change="e => updateDirtyState(e, element.id)"
                        />

                        <attribute-moderation-panel
                            v-if="isInModeration(element.id)"
                            :element="element"
                            :value="state.attributeValues[element.id]"
                            @toggle-data="e => toggleAttributeValue(element.id)"
                            @moderate="e => handleModeration(element.id, e)"
                            @edit="e => handleEditModeration(element.id, e)"
                        />
                    </div>
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
        getAttribute,
        getCertaintyClass,
        translateConcept,
    } from '@/helpers/helpers.js';

    import {
        handleModeration as handleModerationApi,
    } from '@/api.js';

    import store from '@/bootstrap/store.js';

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
    import SqlAttr from '@/components/attribute/Sql.vue';
    import SystemSeparator from '@/components/attribute/SystemSeparator.vue';
    import DefaultAttr from '@/components/attribute/Default.vue';
    import ModerationPanel from '@/components/moderation/Panel.vue';

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
            'sql-attribute': SqlAttr,
            'system-separator-attribute': SystemSeparator,
            'default-attribute': DefaultAttr,
            'attribute-moderation-panel': ModerationPanel,
        },
        props: {
            classes: {
                required: false,
                type: String,
                default: 'h-100 pe-2',
            },
            attributes: {
                required: true,
                type: Array
            },
            hiddenAttributes: {
                required: false,
                type: Array,
                default: _ => ([]),
            },
            showHidden: {
                required: false,
                type: Boolean,
                default: false,
            },
            disableDrag: {
                required: false,
                type: Boolean,
                default: false
            },
            group: { // required if onReorder is set // TODO
                required: false,
                type: String,
                default: null,
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
            values: {
                required: true,
                type: Object
            },
            options: {
                required: false,
                type: Object,
                default: _ => new Object(),
            },
            preview: {
                required: false,
                type: Boolean,
                default: false,
            },
            previewData: {
                required: false,
                type: Object,
                default: _ => new Object(),
            },
        },
        emits: ['dirty'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                classes,
                attributes,
                hiddenAttributes,
                showHidden,
                disableDrag,
                group,
                metadataAddon,
                selections,
                values,
                options,
                preview,
                previewData,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS
            const handleSelectionUpdate = (elemId, conceptId) => {
                if(state.dynamicSelectionList.includes(elemId)) {
                    state.rootAttributeValues[elemId] = conceptId;
                }
            };

            const clFromMetadata = elem => {
                if(!state.ignoreMetadata && elem.pivot && elem.pivot.metadata && elem.pivot.metadata.width) {
                    const width = elem.pivot.metadata.width;
                    switch(width) {
                        case 50:
                            return 'col-6';
                        default:
                            return 'col-12';
                    }
                }
                return 'col-12';
            };
            const attributeClasses = attribute => {
                const classes = [];
                if(props.isSource.value && !attribute.isDisabled) {
                    classes.push('copy-handle');
                }
                if(attribute.isDisabled) {
                    classes.push('not-allowed-handle', 'text-muted');
                }
                return classes;
            };
            const expandedClasses = i => {
                let expClasses = {};

                if(state.hideLabels || state.expansionStates[i]) {
                    expClasses['col-md-12'] = true;
                } else {
                    expClasses['col-md-9'] = true;
                }

                return expClasses;
            };
            const onAttributeExpand = (e, i) => {
                state.expansionStates[i] = !state.expansionStates[i];
            };
            const isInModeration = aid => {
                const attr = state.attributeValues[aid];
                // system attributes have no attribute value and are not (yet) moderated
                if(!attr) {
                    return false;
                }
                return attr.moderation_state && attr.moderation_state.startsWith('pending');
            };
            const isDisabledInModeration = aid => {
                const attr = state.attributeValues[aid];
                return isInModeration(aid) && attr.moderation_edit_state != 'active';
            };
            const addModerationStateClasses = aid => {
                const classes = [];

                if(isInModeration(aid)) {
                    classes.push('bg-danger');
                    classes.push('py-2');
                }

                return classes;
            };
            const toggleAttributeValue = aid => {
                const tmpVal = state.attributeValues[aid].value;
                state.attributeValues[aid].value = state.attributeValues[aid].original_value;
                state.attributeValues[aid].original_value = tmpVal;
            };
            const handleModeration = (aid, e, overwrite_value = null) => {
                const action = e.action;
                const entity_id = e.entity_id;
                const active = e.active;
                if(
                    (action == 'accept' && active == 'original') ||
                    (action == 'deny' && active == 'moderation')
                ) {
                    toggleAttributeValue(aid);
                }
                handleModerationApi(action, entity_id, aid, overwrite_value);
            };
            const handleEditModeration = (aid, e) => {
                const attr = state.attributeValues[aid];
                const action = e.action;
                if(action == 'enable') {
                    attr.moderation_edit_state = 'active';
                } else if(action == 'reset') {
                    attrRefs.value[aid].resetFieldState();
                } else if(action == 'cancel') {
                    delete attr.moderation_edit_state;
                    attrRefs.value[aid].resetFieldState();
                } else if(action == 'accept') {
                    attrRefs.value[aid].undirtyField();
                    const editValue = attrRefs.value[aid].v.value;
                    const data = {
                        action: action,
                        entity_id: e.entity_id,
                    };
                    handleModeration(aid, data, editValue);
                }
            };

            const getSeparatorTitle = element => {
                if(element.pivot && element.pivot.metadata) {
                    return element.pivot.metadata.title;
                } else {
                    return null;
                }
            };
            const onEnter = i => {
                state.hoverStates[i] = state.isHoveringPossible;
            };
            const onLeave = i => {
                state.hoverStates[i] = false;
            };
            const handleMove = (e, orgE) => {
                // Preview does not allow dragging
                if(preview.value) return false;

                const draggedAid = e.draggedContext.element.id;
                return !(showHidden.value && Object.keys(state.hiddenAttributeList).some(aid => aid == draggedAid)) && !disableDrag.value;
            };
            const handleUpdate = (e) => {
                if(!!e.moved) {
                    // only handle event if position changed
                    if(e.moved.oldIndex != e.moved.newIndex) {
                        onReorderHandler({
                            element: e.moved.element,
                            from: e.moved.oldIndex,
                            to: e.moved.newIndex,
                        });
                    }
                } else if(!!e.added) {
                    context.emit('add-element', {
                        element: e.added.element,
                        to: e.added.newIndex,
                    });
                } else if(!!e.removed) {
                    context.emit('remove-element', {
                        element: e.removed.element,
                        from: e.removed.oldIndex,
                        modal: false,
                    });
                }
            };
            const getDirtyValues = _ => {
                const values = {};
                for(let k in attrRefs.value) {
                    const curr = attrRefs.value[k];
                    let currValue = null;
                    // curr is e.g. null if attribute is hidden
                    if(!!curr && !!curr.v && curr.v.meta.dirty && curr.v.meta.valid) {
                        currValue = curr.v.value;
                        if(currValue !== null) {
                            // filter out deleted table rows
                            if(getAttribute(k).datatype == 'table') {
                                currValue = currValue.filter(cv => !cv.mark_deleted);
                            }
                            values[k] = currValue;
                        } else {
                            // null is allowed for date, string-sc, entity
                            if(
                                getAttribute(k).datatype == 'date' ||
                                getAttribute(k).datatype == 'string-sc' ||
                                getAttribute(k).datatype == 'entity'
                            ) {
                                values[k] = currValue;
                            }
                        }
                    }
                }
                return values;
            };
            const updateDirtyState = (e, aid) => {
                // Do not update dirty state if attribute is currently in moderation edit mode
                if(state.attributeValues[aid].moderation_edit_state == 'active') {
                    return;
                }

                context.emit('dirty', {
                    ...e,
                    attribute_id: aid,
                });
            };
            const resetListValues = _ => {
                for(let k in attrRefs.value) {
                    // skip all attributes currently in moderation edit mode
                    if(state.attributeValues[k].moderation_edit_state == 'active') {
                        continue;
                    }
                    const curr = attrRefs.value[k];
                    if(!!curr && !!curr.resetFieldState) {
                        curr.resetFieldState();
                    }
                }
            };
            const undirtyList = _ => {
                for(let k in attrRefs.value) {
                    // skip all attributes currently in moderation edit mode
                    if(state.attributeValues[k].moderation_edit_state == 'active') {
                        continue;
                    }
                    const curr = attrRefs.value[k];
                    if(!!curr && !!curr.undirtyField) {
                        curr.undirtyField();
                    }
                }
            };
            const setRef = (el, id) => {
                attrRefs.value[id] = el;
            };
            const checkDependency = id => {

            };
            const onReorderHandler = data => {
                context.emit('reorder-list', data);
            };
            const onEditHandler = element => {
                context.emit('edit-element', {
                    element: element
                });
            };
            const onRemoveHandler = element => {
                context.emit('remove-element', {
                    element: element,
                    modal: true,
                });
            };
            const onDeleteHandler = element => {
                context.emit('delete-element', {
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
            const handleLabelClick = (e, attrType) => {
                if(attrType == 'boolean') {
                    e.preventDefault();
                }
            };
            const convertEntityValue = (value, isMultiple) => {
                let actValue = null;
                if(value == '' || !value.value) {
                    if(isMultiple) {
                        actValue = {
                            value: [],
                            name: [],
                        };
                    } else {
                        actValue = {};
                    }
                } else {
                    actValue = value;
                }

                if(isMultiple) {
                    return actValue.value.map((v, i) => {
                        return {
                            id: v,
                            name: actValue.name ? actValue.name[i] : '',
                        };
                    });
                } else {
                    return {
                        id: actValue.value,
                        name: actValue.name,
                    };
                }
            };

            const attrs = context.attrs;
            // DATA
            const attrRefs = ref({});
            const state = reactive({
                attributeList: attributes,
                attributeValues: values,
                rootAttributeValues: {},
                entity: computed(_ => store.getters.entity),
                dynamicSelectionList: computed(_ => {
                    const list = [];
                    state.attributeList.forEach(a => {
                        if(a.root_attribute_id) {
                            list.push(a.root_attribute_id);
                        }
                    });
                    return list;
                }),
                hoverStates: new Array(attributes.value.length).fill(false),
                expansionStates: new Array(attributes.value.length).fill(false),
                componentLoaded: computed(_ => state.attributeValues),
                isHoveringPossible: computed(_ => {
                    return !!attrs.onReorderList || !!attrs.onEditElement || !!attrs.onRemoveElement || !!attrs.onDeleteElement;
                }),
                hiddenAttributeList: computed(_ => {
                    if(!state.componentLoaded) return {};

                    const list = {};
                    for(let i = 0; i < hiddenAttributes.value.length; i++) {
                        const disId = hiddenAttributes.value[i];
                        list[disId] = true;
                    }
                    return list;
                }),
                hideLabels: computed(_ => options.value.hide_labels),
                hideEntityLink: computed(_ => options.value.hide_entity_link),
                ignoreMetadata: computed(_ => options.value.ignore_metadata),
            });

            // ON MOUNTED
            onMounted(_ => {
                state.dynamicSelectionList.forEach(rootId => {
                    const attrValue = state.attributeValues[rootId].value;
                    if(attrValue) {
                        handleSelectionUpdate(rootId, attrValue.id);
                    }
                });
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
                handleSelectionUpdate,
                clFromMetadata,
                attributeClasses,
                expandedClasses,
                onAttributeExpand,
                isInModeration,
                isDisabledInModeration,
                addModerationStateClasses,
                toggleAttributeValue,
                handleModeration,
                handleEditModeration,
                getSeparatorTitle,
                onEnter,
                onLeave,
                handleMove,
                handleUpdate,
                getDirtyValues,
                updateDirtyState,
                resetListValues,
                undirtyList,
                setRef,
                checkDependency,
                onEditHandler,
                onRemoveHandler,
                onDeleteHandler,
                onMetadataHandler,
                hasEmitter,
                handleLabelClick,
                convertEntityValue,
                // STATE
                attrRefs,
                state,
            };
        },
    };
</script>
