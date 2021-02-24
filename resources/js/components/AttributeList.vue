<template>
        <draggable v-if="state.componentLoaded"
            class="h-100 pe-1"
            v-model="state.attributeList"
            item-key="id"
            :group="group">
                <template #item="{element, index}">
                    <div class="mb-3 row" @mouseenter="onEnter(index)" @mouseleave="onLeave(index)">
                        <label
                            class="col-form-label col-md-3 d-flex flex-row justify-content-between text-break"
                            :for="`attr-${element.id}`"
                            :class="attributeClasses(element)">
                            <div v-show="!!state.hoverStates[index]">
                                <a v-show="hasEmitter('reorder')" href="" @click.prevent="" class="reorder-handle" data-bs-toggle="popover" :data-content="t('global.resort')" data-trigger="hover" data-placement="bottom">
                                    <i class="fas fa-fw fa-sort text-secondary"></i>
                                </a>
                                <button v-show="hasEmitter('edit')" class="btn btn-info btn-fab rounded-circle" @click="onEdit(element)" data-bs-toggle="popover" :data-content="t('global.edit')" data-trigger="hover" data-placement="bottom">
                                    <i class="fas fa-fw fa-xs fa-edit" style="vertical-align: 0;"></i>
                                </button>
                                <button v-show="hasEmitter('remove')" class="btn btn-danger btn-fab rounded-circle" @click="onRemove(element)" data-bs-toggle="popover" :data-content="t('global.remove')" data-trigger="hover" data-placement="bottom">
                                    <i class="fas fa-fw fa-xs fa-times" style="vertical-align: 0;"></i>
                                </button>
                                <button v-show="hasEmitter('delete')" class="btn btn-danger btn-fab rounded-circle" @click="onDelete(element)" data-bs-toggle="popover" :data-content="t('global.delete')" data-trigger="hover" data-placement="bottom">
                                    <i class="fas fa-fw fa-xs fa-trash" style="vertical-align: 0;"></i>
                                </button>
                            </div>
                            <span class="text-end col">
                                {{ translateConcept(element.thesaurus_url) }}:
                            </span>
                            <sup class="clickable" v-if="hasEmitter('metadata')" @click="onMetadata(element)">
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
                            <input class="form-control" :disabled="element.isDisabled" v-if="element.datatype == 'string'" type="text" :id="`attr-${element.id}`" :name="`attr-${element.id}`" v-model="state.attributeValues[element.id].value" @blur="checkDependency(element.id)" />

                            <input class="form-control-plaintext" v-else-if="element.datatype == 'serial'" type="text" :id="`attr-${element.id}`" :name="`attr-${element.id}`" readonly v-model="state.attributeValues[element.id].value" @blur="checkDependency(attribute.id)" />

                            <input class="form-control" :disabled="element.isDisabled" v-else-if="element.datatype == 'double'" type="number" step="any" min="0" placeholder="0.0" :id="`attr-${element.id}`" :name="`attr-${element.id}`" v-model.number="state.attributeValues[element.id].value" @blur="checkDependency(element.id)" />

                            <input class="form-control" :disabled="element.isDisabled" v-else-if="element.datatype == 'integer'" type="number" step="1" placeholder="0" :id="`attr-${element.id}`" :name="`attr-${element.id}`" v-model.number="state.attributeValues[element.id].value" @blur="checkDependency(element.id)" />

                            <input class="form-check-input" :disabled="element.isDisabled" v-else-if="element.datatype == 'boolean'" type="checkbox" :id="`attr-${element.id}`" :name="`attr-${element.id}`" v-model="state.attributeValues[element.id].value" @change="checkDependency(element.id)" />

                            <textarea class="form-control" :disabled="element.isDisabled" v-else-if="element.datatype == 'stringf'" :id="`attr-${element.id}`" :name="`attr-${element.id}`" v-model="state.attributeValues[element.id].value" @blur="checkDependency(element.id)"></textarea>

                            <div v-else-if="element.datatype == 'percentage'" class="d-flex">
                                <input class="form-range" :disabled="element.isDisabled" type="range" step="1" min="0" max="100" :id="`attr-${element.id}`" :name="`attr-${element.id}`" v-model="state.attributeValues[element.id].value" @mouseup="checkDependency(element.id)"/>
                                <span class="ms-3">{{ state.attributeValues[element.id].value }}%</span>
                            </div>
                            <div v-else-if="element.datatype == 'geography'">
                                <input class="form-control" :disabled="element.isDisabled" type="text" :id="`attr-${element.id}`" :name="`attr-${element.id}`" :placeholder="t('main.entity.attributes.add-wkt')" v-model="state.attributeValues[element.id].value" @blur="checkDependency(element.id)" />
                                <button type="button" class="btn btn-outline-secondary mt-2" :disabled="element.isDisabled" @click="openGeographyModal(element.id)">
                                    <i class="fas fa-fw fa-map-marker-alt"></i> {{ t('main.entity.attributes.open-map') }}
                                </button>
                            </div>
                            <div v-else-if="element.datatype == 'entity'">
                                IMPLEMENT ENTITY SEARCH
                                <!-- <entity-search
                                   
                                    :id="'attribute-'+attribute.id"
                                    :name="'attribute-'+attribute.id"
                                    :on-select="selection => setEntitySearchResult(selection, attribute.id)"
                                    :value="localValues[attribute.id].name">
                                </entity-search> -->
                            </div>
                            <date-picker
                                class="w-100"
                                v-else-if="element.datatype == 'date'"
                                :id="`attr-${element.id}`"
                                :disabled="element.isDisabled"
                                :disabled-date="(date) => date > new Date()"
                                :input-class="'form-control'"
                                :max-date="new Date()"
                                :name="`attr-${element.id}`"
                                :show-week-number="true"
                                :value="state.attributeValues[element.id].value"
                                :value-type="'date'"
                                @input="setDateValue($event, element.id)">
                                <template v-slot:icon-calendar>
                                    <i class="fas fa-fw fa-calendar-alt"></i>
                                </template>
                                <template v-slot:icon-clear>
                                    <i class="fas fa-fw fa-times"></i>
                                </template>
                            </date-picker>
                            <div v-else-if="element.datatype == 'string-mc'">
                                <multiselect
                                    :valueProp="'id'"
                                    :label="'thesaurus_url'"
                                    :track-by="'thesaurus_url'"
                                    :mode="'tags'"
                                    v-model="state.attributeValues[element.id].value"
                                    :disabled="element.isDisabled"
                                    :options="state.selectionLists[element.id] || []"
                                    :name="`attr-${element.id}`"
                                    :placeholder="t('global.select.placehoder')"
                                    @input="(value, id) => checkDependency(element.id)">
                                </multiselect>
                            </div>
                            <div v-else-if="element.datatype == 'string-sc'">
                                <!-- <multiselect
                                    :label="'thesaurus_url'"
                                    :track-by="'thesaurus_url'"
                                    :mode="'single'"
                                    v-model="state.attributeValues[element.id].value"
                                    :disabled="element.isDisabled"
                                    :loading="dd.loading[element.id]"
                                    :options="dd.selections[element.id] || []"
                                    :name="`attr-${element.id}`"
                                    :placeholder="t('global.select.placehoder')"
                                    @open="getOptions(element)"
                                    @input="(value, id) => checkDependency(element.id)">
                                </multiselect> -->
                            </div>
                            <div v-else-if="element.datatype == 'list'">
                                <list :entries="state.attributeValues[element.id].value" :disabled="element.isDisabled" :on-change="value => onChange(null, value, element.id)" :name="`attr-${element.id}`" />
                            </div>
                            <div v-else-if="element.datatype == 'epoch' || element.datatype == 'timeperiod'">
                                <epoch :name="`attr-${element.id}`" :on-change="(field, value) => onChange(field, value, element.id)" :value="state.attributeValues[element.id].value" :epochs="state.selectionLists[element.id]" :type="element.datatype" :disabled="element.isDisabled"/>
                            </div>
                            <div v-else-if="element.datatype == 'dimension'">
                                <dimension :name="`attr-${element.id}`" :on-change="(field, value) => onChange(field, value, element.id)" :value="state.attributeValues[element.id].value" :disabled="element.isDisabled"/>
                            </div>
                            <tabular v-else-if="element.datatype == 'table'" :name="`attr-${element.id}`" :on-change="(field, value) => onChange(field, value, element.id)" :value="state.attributeValues[element.id].value" :selections="state.selectionLists" :attribute="element" :disabled="element.isDisabled" @expanded="e => onAttributeExpand(e, index)"/>
                            <div v-else-if="element.datatype == 'sql'">
                                <div v-if="isArray(state.attributeValues[element.id].value)">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hovered table-sm">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th v-for="(columnNames, index) in state.attributeValues[element.id].value[0]" :key="index">
                                                        {{ translateConcept(index) }}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(row, index) in state.attributeValues[element.id].value" :key="index">
                                                    <td v-for="(column, colIndex) in row" :key="colIndex">
                                                        {{ translateConcept(column) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div v-else>
                                    {{ state.attributeValues[element.id].value }}
                                </div>
                            </div>
                            <iconclass v-else-if="element.datatype == 'iconclass'" :name="`attr-${element.id}`" @input="updateValue($event, element.id)" :value="state.attributeValues[element.id].value" :attribute="element" :disabled="element.isDisabled"></iconclass>
                            <input class="form-control" :disabled="element.isDisabled" v-else type="text" :id="`attr-${element.id}`" v-model="state.attributeValues[element.id].value"  :name="`attr-${element.id}`" @blur="checkDependency(element.id)"/>
                        </div>
                    </div>
                </template>
        </draggable>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        getCertaintyClass,
        translateConcept,
    } from '../helpers/helpers.js';

    import Dimension from './Dimension.vue';
    import Epoch from './Epoch.vue';
    import List from './List.vue';
    import Tabular from './Tabular.vue';
    import Iconclass from './Iconclass.vue';

    export default {
        props: {
            attributes: {
                required: true,
                type: Array
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
            }
        },
        components: {
            'dimension': Dimension,
            'epoch': Epoch,
            'list': List,
            'tabular': Tabular,
            'iconclass': Iconclass,
        },
        emits: ['edit', 'remove', 'delete', 'reorder', 'metadata', 'dirty'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                attributes,
                group,
                metadataAddon,
                selections,
                values,
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

                if(state.expansionStates[i]) {
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
            const updateValue = (eventValue, aid) => {
                state.attributeValues[aid].value = eventValue;
            };
            const checkDependency = id => {

            };
            const openGeographyModal = id => {

            };
            const onReorder = element => {
                context.emit('reorder', {
                    element: element
                });
            };
            const onEdit = element => {
                context.emit('edit', {
                    element: element
                });
            };
            const onRemove = element => {
                context.emit('remove', {
                    element: element
                });
            };
            const onDelete = element => {
                context.emit('delete', {
                    element: element
                });
            };
            const onMetadata = element => {
                context.emit('metadata', {
                    element: element
                });
            };
            const hasEmitter = which => {
                return !!attrs[which];
            };

            const attrs = context.attrs;
            // DATA
            const state = reactive({
                attributeList: attributes,
                attributeValues: values,
                selectionLists: selections,
                hoverStates: new Array(attributes.value.length).fill(false),
                expansionStates: new Array(attributes.value.length).fill(false),
                componentLoaded: computed(_ => state.attributeList.length > 0 && state.attributeValues),
                isHoveringPossible: computed(_ => {
                    return !!attrs.reorder || !!attrs.edit || !!attrs.remove || !!attrs.delete;
                }),
            });

            // ON MOUNTED
            onMounted(_ => {
                console.log(values, "values raw");
                console.log(state.attributeValues.value, "state values");
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
                updateValue,
                checkDependency,
                openGeographyModal,
                onReorder,
                onEdit,
                onRemove,
                onDelete,
                onMetadata,
                hasEmitter,
                // PROPS
                group,
                metadataAddon,
                // STATE
                state,
            }
        },
    }
</script>
