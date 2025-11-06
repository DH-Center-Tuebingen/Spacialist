<template>
    <draggable
        :model-value="attributes"
        item-key="id"
        class="attribute-list-container align-content-start"
        :class="classes"
        ghost-class="vue-draggable-ghost"
        :disabled="disableDrag || preview"
        :group="group"
        :move="handleMove"
        @change="handleUpdate"
        @start="state.dragging = true"
        @stop="state.dragging = false"
        @drop="state.dragging = false"
    >
        <template #item="{ element: attribute, index }">
            <div
                v-if="!state.hiddenAttributeList[attribute.id] || showHidden"
                class="px-2"
                :class="additionalRowClasses(attribute, index)"
                @mouseenter="onEnter(index)"
                @mouseleave="onLeave(index)"
            >
                <div class="d-flex align-items-center gap-2 position-relative">
                    <slot
                        name="before-container"
                        :attribute="attribute"
                        :index="index"
                        :hovered="isHovered(index)"
                        :dragging="state.dragging"
                    />
                    <div
                        class="row gx-3 flex-fill"
                        :class="addModerationStateClasses(attribute)"
                    >
                        <label
                            v-if="!state.hideLabels"
                            class="col-form-label col-md-3 d-flex flex-row justify-content-between text-break align-self-start gap-1 position-relative"
                            :for="`attr-${attribute.id}`"
                            :class="attributeClasses(attribute)"
                            @click="e => handleLabelClick(e, attribute.datatype)"
                        >
                            <slot
                                name="before"
                                :attribute="attribute"
                                :hovered="isHovered(index)"
                                :dragging="state.dragging"
                            />
                            <div
                                v-if="hasAttributeChangeIndicator(attribute)"
                                class="d-flex align-items-center"
                                :title="getAttributeChangeIndicatorDescription(attribute)"
                            >
                                <DotIndicator :type="getAttributeChangeIndicator(attribute)" />
                            </div>
                            <div class="text-end col d-inline-block text-truncate">
                                <span v-if="attribute.is_system">
                                    &nbsp;
                                </span>
                                <span
                                    v-else
                                    :title="translateConcept(attribute.thesaurus_url)"
                                >
                                    {{ translateConcept(attribute.thesaurus_url) }}
                                    <span
                                        v-if="attribute.pivot?.metadata?.required"
                                        class="text-danger"
                                    >
                                        <i class="fas fa-fw fa-xs fa-asterisk align-top" />
                                    </span>
                                </span>
                            </div>
                            <a
                                v-if="getConceptNote(attribute.thesaurus_url)"
                                tabindex="0"
                                class="text-decoration-none text-secondary ms-1 position-relative"
                                data-bs-toggle="popover"
                                data-bs-trigger="focus"
                                data-bs-placement="top"
                                :data-bs-content="getConceptNote(attribute.thesaurus_url)"
                                href="#"
                                @click.prevent
                            >
                                <i class="fas fa-fw fa-circle-info" />
                            </a>
                            <sup
                                v-if="hasEmitter('onEditElement') && !!attribute.pivot.depends_on && Object.keys(attribute.pivot.depends_on).length > 0"
                                :title="t('global.dependency.depends_on.desc')"
                            >
                                <i class="fas fa-diagram-next text-warning fa-rotate-180" />
                            </sup>
                        </label>
                        <div :class="expandedClasses(index, attribute)">
                            <Attribute
                                :ref="el => setRef(el, attribute.id)"
                                :data="attribute"
                                :value-wrapper="values[attribute.id]"
                                :disabled="isAttributeDisabled(attribute)"
                                :react-to="state.rootAttributeValues[attribute.root_attribute_id]"
                                :hide-links="state.hideEntityLink"
                                :preview="preview"
                                :preview-data="previewData"
                                @change="attributeChanged"
                                @update-selection="handleSelectionUpdate"
                                @expanded="e => onAttributeExpand(e, index)"
                            />

                            <ModerationPanel
                                v-if="isInModeration(attribute)"
                                :element="attribute"
                                :value="values[attribute.id]"
                                @toggle-data="e => toggleAttributeValue(attribute)"
                                @moderate="e => handleModeration(attribute, e)"
                                @edit="e => handleEditModeration(attribute, e)"
                            />
                        </div>
                    </div>
                    <!-- <div
                        v-if="hasEmitter('onMetadata')"
                        class="pt-2 fs-1r clickable d-flex flex-row align-items-start justify-content-center align-self-start gap-1"
                        @click="onMetadataHandler(attribute)"
                    >
                        <ValidityIndicator
                            class="col h-10"
                            :class="getCertaintyStyle(certainty(attribute))"
                            :center="true"
                            :state="certainty(attribute)"
                        />
                        <span
                            class="col text-center"
                            :class="inactiveMetadataClass(!hasComment(attribute))"
                        >
                            <i class="fas fa-fw fa-comment" />
                        </span>
                        <span
                            class="col text-center"
                            :class="inactiveMetadataClass(!hasBookmarks(attribute))"
                        >
                            <i class="fas fa-fw fa-bookmark" />
                        </span>
                    </div> -->
                    <slot
                        name="after"
                        :attribute="attribute"
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
        toRef,
        toRefs,
        useAttrs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import { Popover } from 'bootstrap';

    import useAttributeStore from '@/bootstrap/stores/attribute.js';
    import useEntityStore from '@/bootstrap/stores/entity.js';

    import {
        translateConcept,
        getConceptNote,
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
                default: 'h-100',
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
            disabled: {
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
            //Todo: This is kinda read-only, consider ream
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
        emits: ['dirty', 'change'],
        setup(props, context) {
            const { t } = useI18n();
            const attributeStore = useAttributeStore();
            const entityStore = useEntityStore();

            const handleSelectionUpdate = e => {
                const elemId = e.elemId;
                const conceptId = e.conceptId;
                if(state.dynamicSelectionList.includes(elemId)) {
                    state.rootAttributeValues[elemId] = conceptId;
                }
            };

            const additionalRowClasses = (elemement, index) => {
                const classes = [];
                if(!state.ignoreMetadata && elemement.pivot && elemement.pivot.metadata && elemement.pivot.metadata.width) {
                    const width = elemement.pivot.metadata.width;
                    switch(width) {
                        case 50:
                            classes.push('col-6');
                            break;
                        default:
                            classes.push('col-12');
                            break;
                    }
                } else {
                    classes.push('col-12');
                }

                if(index != 0) {
                    classes.push('mt-3');
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
            const expandedClasses = (i, attribute) => {
                let expClasses = {
                    ['attribute-' + attribute.id]: true,
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
            const isInModeration = attribute => {
                // system attributes have no attribute value and are not (yet) moderated
                if(!attribute) {
                    return false;
                }
                return attribute.moderation_state && attribute.moderation_state.startsWith('pending');
            };
            const isDisabledInModeration = attribute => {
                return isInModeration(attribute) && attribute.moderation_edit_state != 'active';
            };
            const addModerationStateClasses = attribute => {
                const classes = [];

                if(isInModeration(attribute)) {
                    classes.push('bg-danger');
                    classes.push('py-2');
                }

                return classes;
            };
            const toggleAttributeValue = attribute => {
                const tmpVal = attribute.value;
                attribute.value = attribute.original_value;
                attribute.original_value = tmpVal;
            };
            const handleModeration = (attribute, e, overwrite_value = null) => {
                const action = e.action;
                const entity_id = e.entity_id;
                const active = e.active;
                if(
                    (action == 'accept' && active == 'original') ||
                    (action == 'deny' && active == 'moderation')
                ) {
                    toggleAttributeValue(attribute);
                }
                entityStore.patchEntityDataModerations(action, entity_id, aid, overwrite_value);
            };
            const handleEditModeration = (attribute, e) => {
                const action = e.action;
                if(action == 'enable') {
                    attribute.moderation_edit_state = 'active';
                } else if(action == 'reset') {
                    attrRefs.value[attribute.id].resetFieldState();
                } else if(action == 'cancel') {
                    delete attribute.moderation_edit_state;
                    attrRefs.value[attribute.id].resetFieldState();
                } else if(action == 'accept') {
                    attrRefs.value[attribute.id].undirtyField();
                    const editValue = attrRefs.value[attribute.id].v.value;
                    const data = {
                        action: action,
                        entity_id: e.entity_id,
                    };
                    handleModeration(attribute, data, editValue);
                }
            };

            const onEnter = i => {
                state.hovered = i;
            };
            const onLeave = i => {
                if(state.hovered == i) state.hovered = -1;
            };
            const handleMove = (e) => {
                // Preview does not allow dragging
                const draggedAid = e.draggedContext?.element?.id;
                if(props.preview || !draggedAid) return false;

                return !(props.showHidden && Object.keys(state.hiddenAttributeList).some(aid => aid == draggedAid)) && !props.disableDrag;
            };
            const handleUpdate = (e) => {
                if(!!e.moved) {
                    // only handle event if position changed
                    if(e.moved.oldIndex != e.moved.newIndex) {
                        context.emit('reorder-list', {
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

            const attributeChanged = e => {
                updateDirtyState(e);
                context.emit('change', e);
            };

            const updateDirtyState = e => {
                // state.changeTracker.local[e.attribute_id] = true;
                state.changeTracker.local[e.attribute_id] = e.dirty;
                // Do not update dirty state if attribute is currently in moderation edit mode
                if(props.values[e.attribute_id].moderation_edit_state == 'active') {
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
                    if(props.values[k].moderation_edit_state == 'active') {
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
                    if(props.values[k].moderation_edit_state == 'active') {
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
                        props.values[k].value = changes[k].value;
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
            const onMetadataHandler = element => {
                context.emit('metadata', {
                    element: element
                });
            };
            const hasEmitter = which => {
                return !!attrs[which];
            };

            const certainty = attribute => {
                return props.values?.[attribute.id]?.certainty ?? null;
            };

            const hasComment = attribute => {
                return props.values[attribute.id]?.comments_count > 0;
            };
            const hasBookmarks = attribute => {
                return props.metadataAddon && props.metadataAddon(attribute.thesaurus_url);
            };

            const inactiveMetadataClass = inactive => {
                if(inactive) {
                    return ['opacity-25'];
                }
            };

            const handleLabelClick = (e, attrType) => {
                if(attrType == 'boolean') {
                    e.preventDefault();
                }
            };

            const isHovered = index => {
                return state.hovered === index;
            };

            const isAttributeDisabled = attribute => {
                if(props.disabled) {
                    return true;
                }
                return state.hiddenAttributeList[attribute.id] || isDisabledInModeration(attribute.id);
            };

            const attrs = useAttrs();
            // DATA
            const attrRefs = ref({});
            const state = reactive({
                rootAttributeValues: {},
                visibleAttributeNotes: {},
                changeTracker: {
                    local: {},
                    external: {},
                },
                dragging: false,
                entity: computed(_ => entityStore.selectedEntity),
                dynamicSelectionList: computed(_ => {
                    const list = [];
                    props.attributes.forEach(a => {
                        if(a.root_attribute_id) {
                            list.push(a.root_attribute_id);
                        }
                    });
                    return list;
                }),
                hovered: -1,
                expansionStates: new Array(props.attributes.length).fill(false),
                hiddenAttributeList: computed(_ => {
                    const list = {};
                    for(let i = 0; i < props.hiddenAttributes.length; i++) {
                        const disId = props.hiddenAttributes[i];
                        list[disId] = true;
                    }
                    return list;
                }),
                hideLabels: computed(_ => props.options.hide_labels),
                hideEntityLink: computed(_ => props.options.hide_entity_link),
                ignoreMetadata: computed(_ => props.options.ignore_metadata),
                itemClasses: computed(_ => props.options.item_classes),
            });

            const initializeTooltips = _ => {
                document.querySelectorAll('[data-bs-toggle="popover"]')
                    .forEach(popoverElement => new Popover(popoverElement));
            };

            const getCertaintyStyle = certainty => {
                if(certainty === null) {
                    return 'opacity-25';
                }

                return '';
            };

            // ON MOUNTED
            onMounted(_ => {
                state.dynamicSelectionList.forEach(rootId => {
                    const attrValue = props.values[rootId].value;
                    if(attrValue) {
                        handleSelectionUpdate({
                            elemId: rootId,
                            conceptId: attrValue.id,
                        });
                    }
                });

                initializeTooltips();
            });
            onBeforeUpdate(_ => {
                attrRefs.value = {};
            });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                getConceptNote,
                // LOCAL
                additionalRowClasses,
                addModerationStateClasses,
                attributeChanged,
                attributeClasses,
                broadcastAttributeChanges,
                certainty,
                expandedClasses,
                getAttributeChangeIndicator,
                getAttributeChangeIndicatorDescription,
                getCertaintyStyle,
                getDirtyValues,
                handleEditModeration,
                handleLabelClick,
                handleModeration,
                handleMove,
                handleSelectionUpdate,
                handleUpdate,
                hasAttributeChangeIndicator,
                hasBookmarks,
                hasComment,
                hasEmitter,
                inactiveMetadataClass,
                isAttributeDisabled,
                isDisabledInModeration,
                isHovered,
                isInModeration,
                onAttributeExpand,
                onEnter,
                onLeave,
                onMetadataHandler,
                resetListValues,
                setRef,
                toggleAttributeValue,
                undirtyList,
                updateDirtyState,
                // STATE
                attrRefs,
                state,
            };
        },
    };
</script>
