<template>
    <div
        v-if="state.entityAvailable"
        class="h-100 d-flex flex-column"
    >
        <h4>
            {{ t('main.datamodel.detail.properties.title') }}
            <small>
                {{ translateConcept(state.entityType.thesaurus_url) }}
            </small>
        </h4>
        <div
            v-if="state.entityType.id"
            class="col d-flex flex-column"
        >
            <form
                role="form"
                @submit.prevent="updateEntityType"
            >
                <div class="mb-3 offset-3 ps-2">
                    <div class="form-check form-switch">
                        <input
                            id="entity-type-root-toggle"
                            v-model="state.entityType.is_root"
                            class="form-check-input"
                            type="checkbox"
                        >
                        <label
                            class="form-check-label"
                            for="entity-type-root-toggle"
                        >
                            {{ t('main.datamodel.detail.properties.top_level') }}
                        </label>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label
                        for="entity-geometrytype-ro"
                        class="col-form-label col-md-3 text-end"
                    >{{ t('global.geometry_type') }}</label>
                    <div class="col-md-9 d-flex align-items-center">
                        <!-- This should be handled using a Plugin Slot from within the map plugin. -->
                        <span v-if="state.entityType.layer">
                            {{ state.entityType.layer.type }}
                        </span>
                        <!-- <router-link :to="{name: 'ldetail', params: { id: state.entityType.layer.id }}">
                            {{ t('main.datamodel.detail.manage_layer') }}
                        </router-link> -->
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-md-3 text-end">{{ t('main.datamodel.detail.properties.sub_types') }}</label>
                    <div class="col-md-9">
                        <multiselect
                            v-model="state.entityType.sub_entity_types"
                            :object="true"
                            :mode="'tags'"
                            :label="'thesaurus_url'"
                            :track-by="'thesaurus_url'"
                            :value-prop="'id'"
                            :options="state.minimalEntityTypes"
                            :close-on-select="false"
                            :close-on-deelect="false"
                            :placeholder="t('global.select.placeholder')"
                        >
                            <template #option="{ option }">
                                {{ translateConcept(option.thesaurus_url) }}
                            </template>
                            <template #tag="{ option, handleTagRemove, disabled }">
                                <div class="multiselect-tag">
                                    {{ translateConcept(option.thesaurus_url) }}
                                    <span
                                        v-if="!disabled"
                                        class="multiselect-tag-remove"
                                        @click.prevent
                                        @mousedown.prevent.stop="handleTagRemove(option, $event)"
                                    >
                                        <span class="multiselect-tag-remove-icon" />
                                    </span>
                                </div>
                            </template>
                        </multiselect>
                        <div class="mt-2 d-flex flex-row gap-2">
                            <button
                                type="button"
                                class="btn btn-outline-success btn-sm"
                                @click="addAllEntityTypes"
                            >
                                <i class="fas fa-fw fa-tasks" /> {{ t('global.select_all') }}
                            </button>
                            <button
                                type="button"
                                class="btn btn-outline-danger btn-sm"
                                @click="removeAllEntityTypes"
                            >
                                <i class="fas fa-fw fa-times" /> {{ t('global.select_none') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="offset-3 ps-2">
                    <button
                        type="submit"
                        class="btn btn-success"
                    >
                        <i class="fas fa-fw fa-save" /> {{ t('global.save') }}
                    </button>
                </div>
                <hr>
            </form>
            <h4>{{ t('main.datamodel.detail.attribute.title') }}</h4>
            <div class="col overflow-hidden flex-grow-1 position-relative">
                <div
                    v-if="state.entityAttributes.length == 0"
                    class="position-absolute d-flex justify-content-center align-items-center h-100 w-100 rounded text-muted bg-light-dark border border-3 border-secondary border-dashed"
                >
                    <h4>Drag attributes here</h4>
                </div>
                <attribute-list
                    class="h-100 overflow-y-auto overflow-x-hidden"
                    :group="{name: 'attribute-selection', pull: false, put: true}"
                    :attributes="state.entityAttributes"
                    :values="state.entityValues"
                    :disable-drag="false"
                    :selections="{}"
                    :show-info="true"
                    @add-element="addAttributeToEntityType"
                    @edit-element="onEditEntityAttribute"
                    @remove-element="onRemoveAttributeFromEntityType"
                    @reorder-list="reorderEntityAttribute"
                />
            </div>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import {
        useRoute,
    } from 'vue-router';

    import { useI18n } from 'vue-i18n';

    import store from '@/bootstrap/store.js';

    import { useToast } from '@/plugins/toast.js';

    import {
        getInitialAttributeValue,
        getEntityType,
        getEntityTypeAttributes,
        translateConcept,
        _cloneDeep,
    } from '@/helpers/helpers.js';

    import {
        reorderEntityAttributes,
        addEntityTypeAttribute,
        updateEntityTypeRelation,
        getAttributeOccurrenceCount,
        removeEntityTypeAttribute,
    } from '@/api.js';

    import {
      showEditAttribute,
        showRemoveAttribute,
    } from '@/helpers/modal.js';

    export default {
        setup(props, context) {
            const { t } = useI18n();
            const currentRoute = useRoute();
            const toast = useToast();
            // FETCH

            // FUNCTIONS
            const updateEntityType = _ => {
                if(!state.entityType.id) return;

                const et = state.entityType;
                const data = {
                    'is_root': et.is_root || false,
                    'sub_entity_types': et.sub_entity_types || [],
                };

                updateEntityTypeRelation(et.id, data).then(_ => {
                    const name = translateConcept(state.entityType.thesaurus_url);
                    toast.$toast(
                        t('main.datamodel.toasts.updated_type.msg', {
                            name: name
                        }),
                        t('main.datamodel.toasts.updated_type.title'),
                        {
                            channel: 'success',
                        }
                    );
                });
            };
            const addAllEntityTypes = _ => {
                state.entityType.sub_entity_types = [];
                state.entityType.sub_entity_types = state.minimalEntityTypes.slice();
            };
            const removeAllEntityTypes = _ => {
                state.entityType.sub_entity_types = [];
            };
            const addAttributeToEntityType = e => {
                addEntityTypeAttribute(currentRoute.params.id, e.element.id, e.to).then(data => {
                    if(e.element.is_system && e.element.datatype == 'system-separator') {
                        showEditAttribute(data.id, currentRoute.params.id, {
                            is_system: e.element.is_system,
                            datatype: data.datatype,
                            pivot: data.pivot,
                        });
                    }
                });
            };
            const onEditEntityAttribute = e => {
                showEditAttribute(e.element.id, currentRoute.params.id, {
                    is_system: e.element.is_system,
                    datatype: e.element.datatype,
                    pivot: e.element.pivot,
                });
            };
            const onRemoveAttributeFromEntityType = e => {
                const etid = currentRoute.params.id;
                const aid = e.element.id;
                const id = e.element.pivot.id;
                if(e.modal) {
                    if(e.element.is_system && e.element.datatype == 'system-separator') {
                        showRemoveAttribute(etid, aid, id, {
                            is_system: e.element.is_system,
                            datatype: e.element.datatype,
                            pivot: e.element.pivot,
                        });
                    } else {
                        getAttributeOccurrenceCount(aid, etid).then(cnt => {
                            showRemoveAttribute(etid, aid, id, {
                                cnt: cnt
                            });
                        });
                    }
                } else {
                    removeEntityTypeAttribute(id).then(_ => {
                        store.dispatch('removeEntityTypeAttribute', {
                            entity_type_id: etid,
                            attribute_id: id,
                        });
                    });
                }
            };
            const reorderEntityAttribute = e => {
                reorderEntityAttributes(currentRoute.params.id, e.element.id, e.from, e.to);
            };

            // DATA
            const state = reactive({
                entityType: computed(_ => _cloneDeep(getEntityType(currentRoute.params.id))),
                entityAttributes: computed(_ => getEntityTypeAttributes(currentRoute.params.id)),
                entityValues: computed(_ => {
                    let data = {};
                    if(!state.entityAttributes) return data;
                    for(let i=0; i<state.entityAttributes.length; i++) {
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
                minimalEntityTypes: computed(_ => {
                    return Object.values(store.getters.entityTypes).map(et => ({
                        id: et.id,
                        thesaurus_url: et.thesaurus_url
                    }));
                }),
                openedModal: '',
                modalSelectedAttribute: {},
                modalSelectedEntityType: {},
                attributeValueCount: 0,
                dependencyOperators: computed(_ => {
                    if(!state.selectedDependency.attribute) return [];
                    switch(state.selectedDependency.attribute.datatype) {
                        case 'boolean':
                            return [
                                {id: '='}
                            ];
                        case 'double':
                        case 'integer':
                        case 'date':
                        case 'percentage':
                            return [
                                {id: '<'},
                                {id: '>'},
                                {id: '='},
                            ];
                        default:
                            return [
                                {id: '='}
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

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                // LOCAL
                updateEntityType,
                addAllEntityTypes,
                removeAllEntityTypes,
                addAttributeToEntityType,
                onEditEntityAttribute,
                onRemoveAttributeFromEntityType,
                reorderEntityAttribute,
                // PROPS
                // STATE
                state,
            };
        },
        // methods: {
        //     editEntityAttribute(attribute, options) {
        //         const vm = this;
        //         if(vm.editEntityAttributeDisabled) return;
        //         const aid = attribute.id;
        //         const ctid = attribute.entity_type_id;
        //         let data = {
        //             d_attribute: options.attribute.id,
        //             d_operator: options.operator.id
        //         };
        //         data.d_value = vm.getDependencyValue(options.value, options.attribute.datatype);
        //         $httpQueue.add(() => vm.$http.patch(`/editor/dm/entity_type/${ctid}/attribute/${aid}/dependency`, data).then(function(response) {
        //             vm.hideEditEntityAttributeModal();
        //         }));
        //     },
        //     onEditEntityAttribute(attribute) {
        //         const ctid = this.entityType.id;
        //         this.depends.attributes = this.entityAttributes.filter(function(a) {
        //             return a.id != attribute.id;
        //         });
        //         let attrDependency = {};
        //         for(let k in this.entityDependencies) {
        //             const attrDeps = this.entityDependencies[k];
        //             const dep = attrDeps.find(function(ad) {
        //                 return ad.dependant == attribute.id;
        //             });
        //             if(dep) {
        //                 attrDependency[k] = dep;
        //             }
        //         }
        //         this.setModalSelectedAttribute(attribute);
        //         if(Object.keys(attrDependency).length) {
        //             this.setSelectedDependency(attrDependency);
        //         }
        //         this.openedModal = 'edit-entity-attribute-modal';
        //         this.$modal.show('edit-entity-attribute-modal');
        //     },
        //     dependencyAttributeSelected(attribute) {
        //         const vm = this;
        //         if(!attribute) {
        //             vm.depends.values = [];
        //             return;
        //         }
        //         const id = attribute.id;
        //         switch(attribute.datatype) {
        //             case 'string-sc':
        //             case 'string-mc':
        //                 $httpQueue.add(() => vm.$http.get(`/editor/attribute/${id}/selection`).then(function(response) {
        //                     vm.depends.values = [];
        //                     const selections = response.data;
        //                     if(selections) {
        //                         for(let i=0; i<selections.length; i++) {
        //                             vm.depends.values.push(selections[i]);
        //                         }
        //                     }
        //                 }));
        //                 break;
        //             default:
        //                 vm.depends.values = [];
        //                 break;
        //         }
        //     },
        //     getDependencyValue(valObject, type) {
        //         switch(type) {
        //             case 'string-sc':
        //             case 'string-mc':
        //                 return valObject.concept_url;
        //             default:
        //                 return valObject;
        //         }
        //     },
        //     // Modal Methods
        //     setSelectedDependency(values) {
        //         if(!values) return;
        //         let aid;
        //         // We have an object with only one key
        //         // Hacky way to get that key
        //         for(let k in values) {
        //             aid = k;
        //             break;
        //         }
        //         this.selectedDependency.attribute = this.entityAttributes.find(function(a) {
        //             return a.id == aid;
        //         });
        //         this.selectedDependency.operator = {id: values[aid].operator};
        //         if(this.selectedDependency.attribute) {
        //             switch(this.selectedDependency.attribute.datatype) {
        //                 case 'string-sc':
        //                 case 'string-mc':
        //                     this.selectedDependency.value = {
        //                         concept_url: values[aid].value
        //                     };
        //                     break;
        //                 default:
        //                     this.selectedDependency.value = values[aid].value;
        //                     break;
        //             }
        //         }
        //     },
        // },
    };
</script>
