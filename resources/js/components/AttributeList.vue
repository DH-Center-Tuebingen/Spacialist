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
                class="mt-3 px-2"
                :class="additionalRowClasses(element)"
                @mouseenter="onEnter(index)"
                @mouseleave="onLeave(index)"
            >
                <div class="d-flex align-items-center gap-2">
                    <div
                        class="row flex-fill"
                        :class="addModerationStateClasses(element.id)"
                    >
                        <label
                            v-if="!state.hideLabels"
                            class="col-form-label col-md-3 d-flex flex-row justify-content-between text-break align-self-start gap-1 position-relative"
                            :for="`attr-${element.id}`"
                            :class="attributeClasses(element)"
                            @click="e => handleLabelClick(e, element.datatype)"
                        >
                            <slot
                                name="before"
                                :attribute="element"
                            />
                            <div
                                v-if="hasAttributeChangeIndicator(element)"
                                class="d-flex align-items-center"
                                :title="getAttributeChangeIndicatorDescription(element)"
                            >
                                <DotIndicator :type="getAttributeChangeIndicator(element)" />
                            </div>
                            <div
                                v-show="!!state.hoverStates[index]"
                                class="btn-fab-list btn-fab-list-md position-absolute start-0"
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
                            <div
                                class="text-end col"
                            >
                                <span v-if="element.is_system">
                                    &nbsp;
                                </span>
                                <span v-else>
                                    {{ translateConcept(element.thesaurus_url) }} [{{ element.id }}]
                                </span>
                            </div>
                            <sup
                                v-if="hasEmitter('onMetadata')"
                                class="clickable d-flex flex-row align-items-start top-0"
                                @click="onMetadataHandler(element)"
                            >
                                <validity-indicator :state="certainty(element)" />
                                <span v-if="hasComment(element)">
                                    <i class="fas fa-fw fa-comment" />
                                </span>
                                <span v-if="hasBookmarks(element)">
                                    <i class="fas fa-fw fa-bookmark" />
                                </span>
                            </sup>
                            <sup
                                v-if="hasEmitter('onEditElement') && !!element.pivot.depends_on"
                                :title="t('global.dependency.depends_on.desc')"
                            >
                                <i class="fas fa-diagram-next text-warning fa-rotate-180" />
                            </sup>
                        </label>
                        <div :class="expandedClasses(index, element)">
                            <Attribute
                                :ref="el => setRef(el, element.id)"
                                :data="element"
                                :value-wrapper="state.attributeValues[element.id]"
                                :disabled="state.hiddenAttributeList[element.id] || isDisabledInModeration(element.id)"
                                :react-to="state.rootAttributeValues[element.root_attribute_id]"
                                :hide-links="state.hideEntityLink"
                                :preview="preview"
                                :preview-data="previewData"
                                @change="updateDirtyState"
                                @update-selection="handleSelectionUpdate"
                                @expanded="e => onAttributeExpand(e, index)"
                            />

                            <ModerationPanel
                                v-if="isInModeration(element.id)"
                                :element="element"
                                :value="state.attributeValues[element.id]"
                                @toggle-data="e => toggleAttributeValue(element.id)"
                                @moderate="e => handleModeration(element.id, e)"
                                @edit="e => handleEditModeration(element.id, e)"
                            />
                        </div>
                    </div>
                    <slot
                        name="after"
                        :attribute="element"
                    />
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

    import useAttributeStore from '@/bootstrap/stores/attribute.js';
    import useEntityStore from '@/bootstrap/stores/entity.js';

    import {
        translateConcept,
    } from '@/helpers/helpers.js';

    import ModerationPanel from '@/components/moderation/Panel.vue';
    import ValidityIndicator from '@/components/forms/indicators/ValidityIndicator.vue';
    import DotIndicator from '@/components/indicators/DotIndicator.vue';

    export default {
        components: {
            ModerationPanel,
            ValidityIndicator,
            DotIndicator,
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
            group: {
                required: false,
                type: Object,
                default: _ => new Object(),
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
            const attributeStore = useAttributeStore();
            const entityStore = useEntityStore();
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
            const handleSelectionUpdate = e => {
                const elemId = e.elemId;
                const conceptId = e.conceptId;
                if(state.dynamicSelectionList.includes(elemId)) {
                    state.rootAttributeValues[elemId] = conceptId;
                }
            };

            const additionalRowClasses = elem => {
                const classes = [];
                if(!state.ignoreMetadata && elem.pivot && elem.pivot.metadata && elem.pivot.metadata.width) {
                    const width = elem.pivot.metadata.width;
                    switch(width) {
                        case 50:
                            classes.push('col-6');
                        default:
                            classes.push('col-12');
                    }
                } else {
                    classes.push('col-12');
                }
                return classes;
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
            const expandedClasses = (i, element) => {
                let expClasses = {
                    ['attribute-' + element.id]: true,
                };

                if(state.hideLabels || state.expansionStates[i]) {
                    expClasses['col-md-12'] = true;
                } else {
                    expClasses['col-md-9'] = true;
                }

                if(state.itemClasses) {
                    const itmCls = state.itemClasses.split(' ');
                    itmCls.forEach(itm => {
                        expClasses[itm] = true;
                    });
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
                entityStore.patchEntityDataModerations(action, entity_id, aid, overwrite_value);
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
                const excludedDatatypes = ['sql', 'serial'];
                for(let k in attrRefs.value) {
                    const datatype = attributeStore.getAttribute(k).datatype;
                    const curr = attrRefs.value[k];
                    let currValue = null;
                    // curr is e.g. null if attribute is hidden
                    if(excludedDatatypes.includes(datatype)) {
                        continue;
                    }
                    if(!!curr && !!curr.v && curr.v.meta.dirty && curr.v.meta.valid) {
                        currValue = curr.v.value;
                        if(currValue !== null) {
                            // filter out deleted table rows
                            if(datatype == 'table') {
                                currValue = currValue.filter(cv => !cv.mark_deleted);
                            } else if(datatype == 'entity') {
                                if(Object.keys(currValue).length == 0) {
                                    currValue = null;
                                }
                            }
                            values[k] = currValue;
                        } else {
                            // null is allowed for date, string-sc
                            if(
                                datatype == 'date' ||
                                datatype == 'string-sc'
                            ) {
                                values[k] = currValue;
                            }
                        }
                    }
                }
                return values;
            };
            const updateDirtyState = e => {
                // state.changeTracker.local[e.attribute_id] = true;
                state.changeTracker.local[e.attribute_id] = e.dirty;
                // Do not update dirty state if attribute is currently in moderation edit mode
                if(state.attributeValues[e.attribute_id].moderation_edit_state == 'active') {
                    return;
                }
                const dirtyValues = getDirtyValues();
                const isDirty = Object.keys(dirtyValues).length > 0;
                context.emit('dirty', e, isDirty);
            };
            const resetListValues = _ => {
                state.changeTracker.local = {};
                state.changeTracker.external = {};
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
                state.changeTracker.local = {};
                state.changeTracker.external = {};
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
            const broadcastAttributeChanges = changes => {
                for(let k in changes) {
                    if(attrRefs.value[k]) {
                        // Broadcast changes to Attribute component...
                        state.attributeValues[k].value = changes[k].value;
                        attrRefs.value[k].handleExternalChange(changes[k]);
                        // ... but also display info
                        state.changeTracker.external[k] = changes[k];
                    }
                }
            };
            const hasAttributeChangeIndicator = attribute => {
                return state.changeTracker.local[attribute.id] || state.changeTracker.external[attribute.id];
            };
            const getAttributeChangeIndicator = attribute => {
                let externalChange = false;
                let localChange = false;
                if(state.changeTracker.local[attribute.id]) {
                    localChange = true;
                }
                if(state.changeTracker.external[attribute.id]) {
                    externalChange = true;
                }
                if(externalChange && localChange) {
                    return 'error';
                } else if(externalChange) {
                    return 'primary';
                } else if(localChange) {
                    return 'warning';
                }
            };
            const getAttributeChangeIndicatorDescription = attribute => {
                const type = getAttributeChangeIndicator(attribute);
                if(type == 'error') {
                    return t('main.entity.attributes.change_indicator.both');
                } else if(type == 'primary') {
                    return t('main.entity.attributes.change_indicator.external_only');
                } else if(type == 'warning') {
                    return t('main.entity.attributes.change_indicator.local_only');
                }
            };
            const setRef = (el, id) => {
                attrRefs.value[id] = el;
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

            const certainty = attribute => {
                return state.attributeValues?.[attribute.id]?.certainty ?? null;
            };

            const hasComment = attribute => {
                return state.attributeValues[attribute.id]?.comments_count > 0;
            };
            const hasBookmarks = attribute => {
                return metadataAddon.value && metadataAddon.value(attribute.thesaurus_url);
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
                changeTracker: {
                    local: {},
                    external: {},
                },
                entity: computed(_ => entityStore.selectedEntity),
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
                        console.log(hiddenAttributes.value[i]);
                        const disId = hiddenAttributes.value[i];
                        list[disId] = true;
                    }
                    return list;
                }),
                hideLabels: computed(_ => options.value.hide_labels),
                hideEntityLink: computed(_ => options.value.hide_entity_link),
                ignoreMetadata: computed(_ => options.value.ignore_metadata),
                itemClasses: computed(_ => options.value.item_classes),
            });

            // ON MOUNTED
            onMounted(_ => {
                state.dynamicSelectionList.forEach(rootId => {
                    const attrValue = state.attributeValues[rootId].value;
                    if(attrValue) {
                        handleSelectionUpdate({
                            elemId: rootId,
                            conceptId: attrValue.id,
                        });
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
                translateConcept,
                // LOCAL
                certainty,
                handleSelectionUpdate,
                additionalRowClasses,
                attributeClasses,
                expandedClasses,
                onAttributeExpand,
                isInModeration,
                isDisabledInModeration,
                addModerationStateClasses,
                toggleAttributeValue,
                handleModeration,
                handleEditModeration,
                onEnter,
                onLeave,
                handleMove,
                handleUpdate,
                getDirtyValues,
                updateDirtyState,
                resetListValues,
                undirtyList,
                broadcastAttributeChanges,
                hasAttributeChangeIndicator,
                getAttributeChangeIndicator,
                getAttributeChangeIndicatorDescription,
                setRef,
                onEditHandler,
                onRemoveHandler,
                onDeleteHandler,
                onMetadataHandler,
                hasEmitter,
                hasComment,
                hasBookmarks,
                handleLabelClick,
                // STATE
                attrRefs,
                state,
            };
        },
    };
</script>
