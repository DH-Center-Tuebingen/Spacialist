<template>
    <div class="col-md-4 h-100 d-flex flex-column">
        <header class="d-flex flex-row justify-content-between align-items-center">
            <slot name="header">
                <div></div>
            </slot>
            <div class="toolbar d-flex gap-1 align-items-center">
                <button
                    type="button"
                    class="btn btn-outline-success btn-sm"
                    @click="createAttribute()"
                >
                    <i class="fas fa-fw fa-plus" />
                </button>
                <div class="dropdown">
                    <span
                        id="dme-attribute-list-options-dropdown"
                        class="clickable text-body align-middle"
                        data-bs-toggle="dropdown"
                        role="button"
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        <i class="fas fa-fw fa-ellipsis-vertical" />
                    </span>
                    <div
                        class="dropdown-menu"
                        aria-labelledby="dme-attribute-list-options-dropdown"
                    >
                        <a
                            href="#"
                            class="dropdown-item"
                            @click.prevent="state.showHiddenAttributes = !state.showHiddenAttributes"
                        >
                            <span v-show="state.showHiddenAttributes">
                                <i class="fas fa-fw fa-eye-slash" />
                                {{ t('main.datamodel.attribute.hide_hidden') }}
                            </span>
                            <span v-show="!state.showHiddenAttributes">
                                <i class="fas fa-fw fa-eye" />
                                {{ t('main.datamodel.attribute.show_hidden') }}
                            </span>
                        </a>
                        <a
                            href="#"
                            class="dropdown-item"
                            @click.prevent="state.showAttributesInGroups = !state.showAttributesInGroups"
                        >
                            <span v-show="state.showAttributesInGroups">
                                <i class="fas fa-fw fa-list" />
                                {{ t('main.datamodel.attribute.separated') }}
                            </span>
                            <span v-show="!state.showAttributesInGroups">
                                <i class="fas fa-fw fa-table-list" />
                                {{ t('main.datamodel.attribute.in_groups') }}
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </header>
        <div class="col overflow-hidden mt-2 d-flex flex-column">
            <attribute-list
                :group="{ name: 'attribute-selection', pull: true, put: false }"
                :classes="'mx-2 px-2 py-3 rounded-3 bg-secondary bg-opacity-10'"
                :attributes="state.systemAttributeList"
                :values="[]"
                :options="{ 'hide_labels': true }"
                :selections="{}"
                :is-source="true"
            />
            <hr>
            <div class="d-flex flex-row justify-content-between align-items-center mb-2 gap-2">
                <div class="input-group w-50 flex-fill">
                    <span
                        id="dme-attribute-search"
                        class="input-group-text"
                    >
                        <i class="fas fa-fw fa-search" />
                    </span>
                    <input
                        v-model="state.attributeQuery"
                        :placeholder="t('global.search')"
                        type="text"
                        class="form-control"
                    >
                </div>
                <div
                    class="btn-group btn-group-sm"
                    role="group"
                >
                    <button
                        v-if="state.showAttributesInGroups"
                        type="button"
                        class="btn btn-outline-primary"
                        :title="t('main.datamodel.expand_groups')"
                        @click="setAttributeGroupExpand(true)"
                    >
                        <i class="fas fa-fw fa-angles-down" />
                    </button>
                    <button
                        v-if="state.showAttributesInGroups"
                        type="button"
                        class="btn btn-outline-primary"
                        :title="t('main.datamodel.collapse_groups')"
                        @click="setAttributeGroupExpand(false)"
                    >
                        <i class="fas fa-fw fa-angles-up" />
                    </button>
                    <button
                        type="button"
                        class="btn btn-outline-primary"
                        :class="{ 'active': state.sortAttributes }"
                        @click="state.sortAttributes = !state.sortAttributes"
                    >
                        <span
                            v-if="state.sortAttributes"
                            :title="t('main.datamodel.sort_by_name')"
                        >
                            <i class="fas fa-fw fa-arrow-down-a-z" />
                        </span>
                        <span
                            v-if="!state.sortAttributes"
                            :title="t('main.datamodel.sort_by_creation_date')"
                        >
                            <i class="fas fa-fw fa-arrow-down-1-9" />
                        </span>
                    </button>
                </div>
            </div>
            <div
                v-if="state.showAttributesInGroups && state.attributeList.length > 0"
                class="col overflow-hidden d-flex flex-column"
            >
                <div
                    id="dme-attribute-list-accordion"
                    ref="accordionRef"
                    class="accordion accordion-flush flex-grow-1 overflow-y-auto overflow-x-hidden pe-2"
                >
                    <template
                        v-for="([type, attrGrp], index) in state.sortedAttributeListGroups"
                        :key="`dme-attribute-list-${type}-grp`"
                    >
                        <div
                            v-show="attributeGroupHasItems(attrGrp)"
                            class="accordion-item"
                            :class="{
                                'border-bottom': index == state.sortedAttributeListGroups.length - 1,
                            }"
                        >
                            <h2 class="accordion-header">
                                <button
                                    class="accordion-button collapsed"
                                    :class="{ 'text-muted': attributeGroupItemCount(attrGrp) == 0 }"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    :data-bs-target="`#dme-attribute-list-${type}-grp-container`"
                                    aria-expanded="false"
                                    :aria-controls="`dme-attribute-list-${type}-grp-container`"
                                >
                                    <span class="flex-fill">
                                        <span v-if="isFromPlugin(type)">
                                            {{ t(getPluginLabel(type)) }}
                                            <i class="fas fa-fw fa-puzzle-piece" />
                                        </span>
                                        <span v-else>
                                            {{ t(`global.attributes.${type}`) }}
                                        </span>
                                    </span>
                                    <span
                                        class="badge bg-primary mx-2 d-flex flex-row"
                                        :class="{ 'bg-opacity-50': attributeGroupItemCount(attrGrp) == 0 }"
                                    >
                                        <span>{{ attributeGroupItemCount(attrGrp) }}</span>
                                        <span>/</span>
                                        <span>{{ attrGrp.length }}</span>
                                    </span>
                                </button>
                                <!--
                                        This was really useful, when adding multiple attributes of the same type
                                        But with the bootstrap accordion, it could not be put on the header.
                                        It would be nice to put it on the header of a custom accordion component. [SO]
                                    -->
                                <!--
                                    <button
                                        class="btn btn-sm btn-outline-success"
                                        @click.stop="createAttribute(type)"
                                    >
                                        +
                                    </button> -->
                            </h2>
                            <div
                                :id="`dme-attribute-list-${type}-grp-container`"
                                class="accordion-collapse collapse"
                            >
                                <div class="accordion-body px-2 py-3">
                                    <attribute-list
                                        :group="{ name: `attribute-selection-${type}`, pull: true, put: false }"
                                        :attributes="attrGrp"
                                        :hidden-attributes="state.selectedEntityTypeAttributeIds"
                                        :show-hidden="state.showHiddenAttributes"
                                        :values="state.attributeListValues"
                                        :selections="{}"
                                        :is-source="true"
                                        :show-info="true"
                                        @delete-element="onDeleteAttribute"
                                    >
                                        <template #before-container="{ attribute, hovered, dragging }">
                                            <AttributeListControls
                                                v-if="hovered && !dragging"
                                                class="position-absolute start-0 top-50 translate-middle-y"
                                                :attribute="attribute"
                                                @delete="onDeleteAttribute(attribute)"
                                            />
                                        </template>
                                        <template #after="{ attribute }">
                                            <AttributeUsageIndicator :count="attribute.entity_types_count" />
                                        </template>
                                    </attribute-list>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <attribute-list
                v-else-if="!state.showAttributesInGroups"
                :classes="'pe-2 col overflow-y-auto overflow-x-hidden'"
                :group="{ name: 'attribute-selection', pull: true, put: false }"
                :attributes="state.attributeList"
                :hidden-attributes="state.selectedEntityTypeAttributeIds"
                :show-hidden="state.showHiddenAttributes"
                :values="state.attributeListValues"
                :selections="{}"
                :is-source="true"
                :show-info="true"
            >
                <template #after="{ attribute, hovered, dragging }">
                    <AttributeUsageIndicator
                        v-if="hovered && !dragging"
                        :count="attribute.entity_types_count"
                        @delete="onDeleteAttribute"
                    />
                </template>
            </attribute-list>
            <Alert
                v-if="state.attributeList.length == 0"
                class="mb-0"
                :message="`${t('global.search_no_results_for')} ${state.attributeQuery}`"
                :type="'info'"
                :noicon="false"
                :icontext="t('global.information')"
            />
        </div>
    </div>
</template>

<script>
    import { useI18n } from 'vue-i18n';
    import {
        computed,
        reactive,
        ref,
    } from 'vue';

    import {
        getAttributeOccurrenceCount,
    } from '@/api.js';

    import {
        getInitialAttributeValue,
        translateConcept,
    } from '@/helpers/helpers.js';

    import {
        showAddAttribute,
        showDeleteAttribute,
    } from '@/helpers/modal.js';

    import useAttributeStore from '@/bootstrap/stores/attribute.js';

    import AttributeUsageIndicator from '@/components/data-model/AttributeUsageIndicator.vue';
    import AttributeListControls from '@/components/attribute/AttributeListControls.vue';

    export default {
        components: {
            AttributeListControls,
            AttributeUsageIndicator,
        },
        props: {
            selectedEntityType: {
                type: Object,
                required: false,
                default: null,
            },
        },
        setup(props) {
            const { t } = useI18n();

            const accordionRef = ref(null);
            const attributeStore = useAttributeStore();
            const attributeGroupItemCount = (items, onlyVisible = true) => {
                if(!onlyVisible) {
                    return items.length;
                } else {
                    return items.filter(itm => !state.selectedEntityTypeAttributeIds.includes(itm.id)).length;
                }
            };

            const attributeGroupHasItems = items => {
                if(state.showHiddenAttributes && items.length > 0) return true;

                return attributeGroupItemCount(items) > 0;
            };

            const createAttribute = _ => {
                showAddAttribute(null);
            };
            const onDeleteAttribute = attribute => {
                getAttributeOccurrenceCount(attribute.id).then(data => {
                    const metadata = {
                        attributeCount: data,
                    };
                    showDeleteAttribute(attribute, metadata);
                });
            };
            const setAttributeGroupExpand = (expand = true) => {
                const parent = accordionRef.value;
                const buttons = parent.querySelectorAll('.accordion-button');
                const containers = parent.querySelectorAll('.accordion-collapse');
                if(expand) {
                    buttons.forEach(btn => btn.classList.remove('collapsed'));
                    containers.forEach(btn => btn.classList.add('show'));
                } else {
                    buttons.forEach(btn => btn.classList.add('collapsed'));
                    containers.forEach(btn => btn.classList.remove('show'));
                }
            };

            const isFromPlugin = datatype => attributeStore.isFromPlugin(datatype);
            const getPluginLabel = datatype => attributeStore.getPluginAttributeLabel(datatype);

            const state = reactive({
                attributeList: computed(_ => {
                    if(!state.sortAttributes) return state.filteredAttributeList;

                    return state.filteredAttributeList.toSorted((a, b) => {
                        const conceptA = translateConcept(a.thesaurus_url);
                        const conceptB = translateConcept(b.thesaurus_url);
                        return conceptA.localeCompare(conceptB);
                    });
                }),
                // set values for all attributes to '', so values in <attribute-list> are existant
                attributeListValues: computed(_ => {
                    if(!state.attributeList) return;
                    let data = {};
                    for(let i = 0; i < state.attributeList.length; i++) {
                        let a = state.attributeList[i];
                        data[a.id] = {
                            value: getInitialAttributeValue(a, 'datatype'),
                        };
                    }
                    return data;
                }),
                attributeQuery: '',
                filteredAttributeList: computed(_ => {
                    if(!state.attributeQuery) return state.userAttributeList;

                    return state.userAttributeList.filter(attribute => {
                        return translateConcept(attribute.thesaurus_url).toLowerCase().indexOf(state.attributeQuery.toLowerCase()) > -1;
                    });
                }),
                attributeListGroups: computed(_ => {
                    const grps = {};
                    state.attributeList.forEach(a => {
                        if(!grps[a.datatype]) {
                            grps[a.datatype] = [];
                        }
                        grps[a.datatype].push(a);
                    });
                    return grps;
                }),
                selectedEntityTypeAttributeIds: computed(_ => props.selectedEntityType ? props.selectedEntityType.map(a => a.id) : []),
                showHiddenAttributes: false,
                sortedAttributeListGroups: computed(_ => {
                    const groupList = Object.entries(state.attributeListGroups);

                    return groupList.sort((a, b) => {
                        const labelA = isFromPlugin(a[0]) ?
                            t(getPluginLabel(a[0])) :
                            t(`global.attributes.${a[0]}`);
                        const labelB = isFromPlugin(b[0]) ?
                            t(getPluginLabel(b[0])) :
                            t(`global.attributes.${b[0]}`);
                        return labelA.localeCompare(labelB);
                    });
                }),
                showAttributesInGroups: true,
                sortAttributes: true,
                systemAttributeList: computed(_ => attributeStore.getAttributeListBy('system')),
                userAttributeList: computed(_ => attributeStore.getAttributeListBy()),

            })


            return {
                t,
                state,
                accordionRef,
                attributeGroupItemCount,
                attributeGroupHasItems,
                createAttribute,
                getPluginLabel,
                isFromPlugin,
                onDeleteAttribute,
                setAttributeGroupExpand,
            };
        },
    }
</script>