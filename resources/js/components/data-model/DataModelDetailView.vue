<template>
    <div
        v-if="state.entityAvailable"
        class="h-100 d-flex flex-column bg-light-dark rounded border border-1 border-gray h-100 overflow-y-auto"
    >
        <header class="d-flex flex-row align-items-center justify-content-between gap-3 border-bottom py-2 px-3">
            <div class="heading d-flex flex-row align-items-center gap-3 h-100">
                <div
                    class="rounded"
                    :style="{
                        aspectRatio: '1 / 1',
                        height: '100%',
                        backgroundColor: `${color} !important` || '#666666',
                    }"
                ></div>
                <h2 class="m-0 fs-regular fw-bold">
                    {{ translateConcept(state.entityType.thesaurus_url) }}
                </h2>
            </div>
            <div class="toolbar d-flex flex-row align-items-center gap-4">
                <IconStatsGroup :value="stats" />

                <button
                    type="button"
                    class="btn btn-sm p-0"
                >
                    <i class="fas fa-fw fa-sliders" />
                </button>
            </div>
        </header>
        <div
            v-if="state.entityType.id"
            class="col d-flex flex-column position-relative p-3"
        >
            <div class="col overflow-hidden flex-grow-1 position-relative">
                <div
                    v-if="state.entityAttributes.length == 0"
                    class="position-absolute d-flex justify-content-center align-items-center h-100 w-100 rounded text-muted bg-light-dark border border-3 border-secondary border-dashed"
                >
                    <span class="fw-bold fs-heading">Drag attributes here</span>
                </div>
                <attribute-list
                    class="h-100 overflow-y-auto overflow-x-hidden pt-0"
                    style="padding-bottom: 10rem;"
                    :disabled="true"
                    :group="{ name: 'attribute-selection', pull: false, put: true }"
                    :attributes="state.entityAttributes"
                    :values="state.entityValues"
                    :disable-drag="false"
                    :selections="{}"
                    :show-info="true"
                    @add-element="addAttributeToEntityType"
                    @reorder-list="reorderEntityAttribute"
                >
                    <template #before-container="{ attribute, dragging, hovered }">
                        <EntityAttributeListControls
                            v-if="hovered && !dragging"
                            :attribute="attribute"
                            class="top-50 translate-middle-y"
                            @edit="() => onEditEntityAttribute(attribute)"
                            @require="() => onRequireEntityAttribute(attribute)"
                            @remove="() => onRemoveAttributeFromEntityType(attribute, true)"
                        />
                    </template>
                </attribute-list>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        watch,
    } from 'vue';

    import {
        useRoute,
    } from 'vue-router';

    import { IconStatsGroup } from 'dhc-components'
    import { useI18n } from 'vue-i18n';
    import { useToast } from '@/plugins/toast.js';
    import {
        faCodeMerge,
        faCubes,
        faLevelDown,
        faLevelUp,
    } from '@fortawesome/free-solid-svg-icons';

    import useEntityStore from '@/bootstrap/stores/entity.js';

    import {
        getInitialAttributeValue,
        getEntityTypeAttributes,
        translateConcept,
        _cloneDeep,
    } from '@/helpers/helpers.js';

    import {
        getAttributeOccurrenceCount,
    } from '@/api.js';

    import {
        showEditAttribute,
        showRemoveAttribute,
    } from '@/helpers/modal.js';

    import EntityAttributeListControls from '@/components/attribute/EntityAttributeListControls.vue';
    import {
        isRequired as isAttributeRequired,
        setRequired as setAttributeRequired
    } from '@/helpers/attribute.js';

    import EntityTypeSettings from '@/components/data-model/EntityTypeSettings.vue';



    export default {
        components: {
            EntityAttributeListControls,
            EntityTypeSettings,
            IconStatsGroup,
        },
        setup(props, context) {
            const { t } = useI18n();
            const entityStore = useEntityStore();
            const currentRoute = useRoute();
            const toast = useToast();

            const addAttributeToEntityType = async e => {
                try {
                    const data = await entityStore.addEntityTypeAttribute(state.entityType.id, e.element.id, e.to + 1);
                    if(e.element.is_system && e.element.datatype == 'system-separator') {
                        showEditAttribute(data.id, state.entityType.id, {
                            is_system: e.element.is_system,
                            datatype: data.datatype,
                            pivot: data.pivot,
                        });
                    }
                } catch(e) {
                    console.error(e);
                    const errorMessage = e?.response?.data?.error || 'Unknown error occured!';
                    toast.$toast(
                        errorMessage,
                        t('global.error.alert_title'),
                        {
                            channel: 'danger',
                        }
                    );
                }
            };
            const onEditEntityAttribute = attribute => {
                showEditAttribute(attribute.id, state.entityType.id, {
                    is_system: attribute.is_system,
                    datatype: attribute.datatype,
                    pivot: attribute.pivot,
                });
            };

            const onRequireEntityAttribute = async attribute => {
                if(attribute) {
                    const isRequired = !isAttributeRequired(attribute);
                    const metadata = {
                        required: isRequired,
                    };
                    await entityStore.patchEntityMetadata(
                        state.entityType.id,
                        attribute.id,
                        attribute.pivot.id,
                        metadata,
                    )

                    console.log(attribute.pivot.metadata);
                    if(!attribute.pivot.metadata) {
                        attribute.pivot.metadata = {};
                    }

                    setAttributeRequired(attribute, isRequired);
                }
            };
            const onRemoveAttributeFromEntityType = (attribute, modal = false) => {
                const entityTypeId = state.entityType.id;
                const pivotId = attribute?.pivot?.id;
                if(modal) {
                    if(attribute.is_system && attribute.datatype == 'system-separator') {
                        showRemoveAttribute(entityTypeId, attribute.id, pivotId, {
                            is_system: attribute.is_system,
                            datatype: attribute.datatype,
                            pivot: attribute.pivot,
                        });
                    } else {
                        getAttributeOccurrenceCount(attribute.id, entityTypeId).then(cnt => {
                            showRemoveAttribute(entityTypeId, attribute.id, pivotId, {
                                cnt: cnt
                            });
                        });
                    }
                } else {
                    entityStore.removeEntityTypeAttribute(pivotId, entityTypeId);
                }
            };
            const reorderEntityAttribute = ({ element, from, to }) => {
                entityStore.reorderAttributes(currentRoute.params.id, element, from, to);
            };

            const getDefaultPropertyValues = function () {
                return {
                    id: null,
                    is_root: undefined,
                    sub_entity_types: [],
                    color: undefined,
                };
            };

            // DATA
            const state = reactive({
                entityType: computed(_ => {
                    const entityType = _cloneDeep(entityStore.getEntityType(currentRoute.params.id));

                    // We need to ensure that sub_entity_types is set
                    // otherwise the dirties calculation may fail.
                    if(!entityType.sub_entity_types)
                        entityType.sub_entity_types = [];
                    return entityType;
                }),

                entityAttributes: computed(_ => getEntityTypeAttributes(currentRoute.params.id)),
                entityValues: computed(_ => {
                    let data = {};
                    if(!state.entityAttributes) return data;
                    for(let i = 0; i < state.entityAttributes.length; i++) {
                        const curr = state.entityAttributes[i];
                        // several datatypes require a "valid"/non-string v-model
                        data[curr.id] = {
                            value: getInitialAttributeValue(curr, 'datatype'),
                        };
                    }
                    return data;
                }),
                entitySelections: {},
                entityDependencies: [],
                entityAvailable: computed(_ => !!state.entityType),
                selectedDependency: {
                    attribute: {},
                    operator: undefined,
                    value: undefined
                },
                depends: {
                    attributes: [],
                    values: []
                },
                openedModal: '',
                modalSelectedAttribute: {},
                modalSelectedEntityType: {},
                attributeValueCount: 0,
                dependencyOperators: computed(_ => {
                    if(!state.selectedDependency.attribute) return [];
                    switch(state.selectedDependency.attribute.datatype) {
                        case 'boolean':
                            return [
                                { id: '=' }
                            ];
                        case 'double':
                        case 'integer':
                        case 'date':
                        case 'percentage':
                            return [
                                { id: '<' },
                                { id: '>' },
                                { id: '=' },
                            ];
                        default:
                            return [
                                { id: '=' }
                            ];
                    }
                }),
                dependencyType: computed(_ => {
                    if(!state.selectedDependency.attribute) return '';
                    switch(state.selectedDependency.attribute.datatype) {
                        case 'boolean':
                            return 'boolean';
                        case 'double':
                            return 'double';
                        case 'integer':
                        case 'date':
                        case 'percentage':
                            return 'integer';
                        case 'string-sc':
                        case 'string-mc':
                            return 'select';
                        default:
                            return 'string';
                    }
                }),
                editEntityAttributeDisabled: computed(_ => {
                    return !state.modalSelectedAttribute ||
                        // Either all or none of the deps must be set to be valid
                        !(
                            (
                                state.selectedDependency.attribute &&
                                state.selectedDependency.attribute.id &&
                                state.selectedDependency.operator &&
                                state.selectedDependency.operator.id &&
                                state.selectedDependency.value
                            )
                            ||
                            (
                                (
                                    !state.selectedDependency.attribute ||
                                    !state.selectedDependency.attribute.id
                                ) &&
                                (
                                    !state.selectedDependency.operator ||
                                    !state.selectedDependency.operator.id
                                ) &&
                                !state.selectedDependency.value
                            )
                        )
                        ;
                }),
            });

            const color = computed(() => {
                return state.entityType?.color || '#ff00ff';
            })

            const stats = computed(() => {
                if(!state.entityType) return [];

                const entityType = state.entityType;

                const stats = [
                    {
                        icon: faLevelUp,
                        text: entityType?.parents?.length || 0,
                    },
                    {
                        icon: faLevelDown,
                        text: entityType?.sub_entity_types?.length || 0,
                    },
                    {
                        icon: faCubes,
                        text: entityType?.entities_count || 0,
                    },
                ];

                if(entityType?.is_root) {
                    stats.unshift({
                        icon: faCodeMerge,
                        iconOnly: true,
                        color: 'primary',
                    });
                }

                return stats;
            })


            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                // LOCAL
                color,
                addAttributeToEntityType,
                onEditEntityAttribute,
                onRequireEntityAttribute,
                onRemoveAttributeFromEntityType,
                reorderEntityAttribute,
                // PROPS
                // STATE
                state,
                stats,
            };
        },
    };
</script>
