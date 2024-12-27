<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content"
        name="edit-attribute-modal"
    >
        <div class="sp-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <template v-if="state.attribute.is_system">
                        {{
                            t('main.entity.modals.edit.title_attribute', {
                                name: t('global.attributes.system-separator')
                            })
                        }}
                    </template>
                    <template v-else>
                        {{
                            t('main.entity.modals.edit.title_attribute', {
                                name: translateConcept(state.attribute.thesaurus_url)
                            })
                        }}
                    </template>
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body overflow-hidden d-flex flex-column">
                <template v-if="state.attribute.is_system">
                    <div class="mb-3 row">
                        <label class="col-form-label text-end col-md-2">
                            {{ t('global.text') }}:
                        </label>
                        <div class="col-md-10">
                            <simple-search
                                :endpoint="searchLabel"
                                :key-fn="getConceptLabel"
                                @selected="handleSeparatorRename"
                            />
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="mb-3 row">
                        <label class="col-form-label text-end col-md-2">
                            {{ t('global.label') }}:
                        </label>
                        <div class="col-md-10">
                            <input
                                type="text"
                                class="form-control"
                                :value="translateConcept(state.attribute.thesaurus_url)"
                                disabled
                            >
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-form-label text-end col-md-2">
                            {{ t('global.type') }}:
                        </label>
                        <div class="col-md-10">
                            <input
                                type="text"
                                class="form-control"
                                :value="t(`global.attributes.${state.attribute.datatype}`)"
                                disabled
                            >
                        </div>
                    </div>
                </template>
                <hr>
                <h5 class="text-center">
                    {{ t('global.dependency.title') }}
                </h5>
                <div
                    v-if="state.dependency.groups.length > 1"
                    class="mb-2 row d-flex flex-row justify-content-end"
                >
                    <div class="input-group input-group-sm w-auto">
                        <button
                            id="dependency-mode-toggle-btn"
                            class="btn btn-sm btn-outline-secondary"
                            type="button"
                            @click="state.dependency.union = !state.dependency.union"
                        >
                            <span
                                v-show="state.dependency.union"
                                :title="t('global.dependency.modes.union_desc')"
                            >
                                <i class="fas fa-fw fa-object-ungroup" />
                                {{ t('global.dependency.modes.union') }}
                            </span>
                            <span
                                v-show="!state.dependency.union"
                                :title="t('global.dependency.modes.intersect_desc')"
                            >
                                <i class="fas fa-fw fa-object-group" />
                                {{ t('global.dependency.modes.intersect') }}
                            </span>
                        </button>
                        <button
                            class="btn btn-sm btn-outline-danger"
                            type="button"
                            :title="t('global.dependency.remove_group')"
                            @click="removeGroup(state.currentDependencyGroupId)"
                        >
                            <i class="fas fa-fw fa-minus" />
                        </button>
                        <button
                            class="btn btn-sm btn-outline-success"
                            type="button"
                            :disabled="state.lastGroupEmpty"
                            :title="t('global.dependency.add_group')"
                            @click="addGroup"
                        >
                            <i class="fas fa-fw fa-plus" />
                        </button>
                    </div>
                    <div class="input-group input-group-sm w-auto">
                        <button
                            class="btn btn-sm btn-outline-secondary"
                            type="button"
                            @click="gotoPrevGroup"
                        >
                            <i class="fas fa-fw fa-chevron-left" />
                        </button>
                        <div class="input-group-text d-flex flex-row gap-2">
                            {{ t('global.dependency.group') }}
                            {{ state.currentDependencyGroupId + 1 }} / {{ state.dependency.groups.length }}
                        </div>
                        <button
                            class="btn btn-sm btn-outline-secondary"
                            type="button"
                            @click="gotoNextGroup"
                        >
                            <i class="fas fa-fw fa-chevron-right" />
                        </button>
                    </div>
                </div>
                <div
                    v-else
                    class="mb-2 d-flex flex-row justify-content-end"
                >
                    <button
                        class="btn btn-sm btn-outline-success"
                        type="button"
                        :disabled="state.lastGroupEmpty"
                        @click="addGroup"
                    >
                        <i class="fas fa-fw fa-plus" />
                        {{ t('global.dependency.add_group') }}
                    </button>
                </div>
                <Alert
                    v-if="state.dependency.groups[state.currentDependencyGroupId].rules.length == 0"
                    class="mb-2"
                    :message="t('global.dependency.no_rules_defined')"
                    :type="'info'"
                    :noicon="true"
                />
                <div class="overflow-y-auto overflow-x-hidden">
                    <div
                        v-for="(itm, i) in state.dependency.groups[state.currentDependencyGroupId].rules"
                        :key="`dependency-group-${state.currentDependencyGroupId}-item-${i}`"
                        class="mb-2 row g-2"
                    >
                        <div class="col-5">
                            <multiselect
                                v-model="itm.attribute"
                                :classes="{
                                    ...multiselectResetClasslist,
                                    'dropdown': 'multiselect-dropdown multiselect-modal-dropdown'
                                }"
                                :append-to-body="true"
                                :value-prop="'id'"
                                :label="'thesaurus_url'"
                                :track-by="'id'"
                                :object="true"
                                :mode="'single'"
                                :hide-selected="true"
                                :options="state.selection"
                                :placeholder="t('global.select.placeholder')"
                                @change="dependantSelected"
                            >
                                <template #option="{ option }">
                                    {{ translateConcept(option.thesaurus_url) }}
                                </template>
                                <template #singlelabel="{ value }">
                                    <div class="multiselect-single-label">
                                        {{ translateConcept(value.thesaurus_url) }}
                                    </div>
                                </template>
                            </multiselect>
                        </div>
                        <div class="col-2">
                            <multiselect
                                v-if="itm.attribute?.id"
                                v-model="itm.operator"
                                :classes="{
                                    ...multiselectResetClasslist,
                                    'dropdown': 'multiselect-dropdown multiselect-modal-dropdown'
                                }"
                                :append-to-body="true"
                                :value-prop="'id'"
                                :label="'label'"
                                :track-by="'id'"
                                :mode="'single'"
                                :object="true"
                                :hide-selected="true"
                                :options="getOperatorList(itm.attribute.datatype)"
                                :placeholder="t('global.select.placeholder')"
                                @change="operatorSelected"
                            />
                        </div>
                        <div class="col-4">
                            <div v-if="itm.attribute?.id && itm.operator?.id && !itm.operator.no_parameter">
                                <div
                                    v-if="getInputTypeClass(itm.attribute.datatype) == 'boolean'"
                                    class="form-check form-switch"
                                >
                                    <input
                                        id="dependency-boolean-value"
                                        v-model="itm.value"
                                        type="checkbox"
                                        class="form-check-input"
                                    >
                                </div>
                                <input
                                    v-else-if="getInputTypeClass(itm.attribute.datatype) == 'number'"
                                    v-model.number="itm.value"
                                    type="number"
                                    class="form-control"
                                    :step="itm.attribute.datatype == 'double' ? 0.01 : 1"
                                >
                                <multiselect
                                    v-else-if="getInputTypeClass(itm.attribute.datatype) == 'select'"
                                    v-model="itm.value"
                                    :classes="{
                                        ...multiselectResetClasslist,
                                        'dropdown': 'multiselect-dropdown multiselect-modal-dropdown'
                                    }"
                                    :append-to-body="true"
                                    :value-prop="'id'"
                                    :label="'concept_url'"
                                    :track-by="'id'"
                                    :hide-selected="true"
                                    :mode="'single'"
                                    :options="getDependantOptions(itm.attribute.id, itm.attribute.datatype)"
                                    :placeholder="t('global.select.placeholder')"
                                >
                                    <template #option="{ option }">
                                        {{ translateConcept(option.concept_url) }}
                                    </template>
                                    <template #singlelabel="{ value }">
                                        <div class="multiselect-single-label">
                                            {{ translateConcept(value.concept_url) }}
                                        </div>
                                    </template>
                                </multiselect>
                                <input
                                    v-else
                                    v-model="itm.value"
                                    type="text"
                                    class="form-control"
                                >
                            </div>
                        </div>
                        <div
                            class="col-1 d-flex flex-row justify-content-center align-items-center"
                            :title="t('global.dependency.remove_rule')"
                        >
                            <a
                                href="#"
                                class="text-danger text-decoration-none"
                                @click.prevent="removeItem(state.currentDependencyGroupId, i)"
                            >
                                <i class="fas fa-fw fa-trash" />
                            </a>
                        </div>
                    </div>
                </div>
                <div class="input-group input-group-sm">
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-success"
                        @click="addItem"
                    >
                        <i class="fas fa-fw fa-plus" />
                        {{ t('global.dependency.add_rule') }}
                    </button>
                    <button
                        id="dependency-mode-ingroup-toggle-btn"
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        @click="state.dependency.groups[state.currentDependencyGroupId].union = !state.dependency.groups[state.currentDependencyGroupId].union"
                    >
                        <span
                            v-show="state.dependency.groups[state.currentDependencyGroupId].union"
                            :title="t('global.dependency.modes.union_desc')"
                        >
                            <i class="fas fa-fw fa-object-ungroup" />
                            {{ t('global.dependency.modes.union') }}
                        </span>
                        <span
                            v-show="!state.dependency.groups[state.currentDependencyGroupId].union"
                            :title="t('global.dependency.modes.intersect_desc')"
                        >
                            <i class="fas fa-fw fa-object-group" />
                            {{ t('global.dependency.modes.intersect') }}
                        </span>
                    </button>
                </div>
                <hr>
                <div class="row">
                    <h5 class="text-center">
                        {{ t('global.width') }}
                    </h5>
                    <div class="d-flex justify-content-between">
                        <span>50%</span>
                        <span>100%</span>
                    </div>
                    <input
                        id="attribute-width-slider"
                        v-model.number="state.width"
                        type="range"
                        class="form-range px-3"
                        min="50"
                        max="100"
                        step="50"
                    >
                </div>
            </div>
            <div class="modal-footer">
                <button
                    type="submit"
                    class="btn btn-outline-success"
                    :disabled="!state.isValid"
                    @click="confirmEdit()"
                >
                    <i class="fas fa-fw fa-save" /> {{ t('global.update') }}
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                >
                    <i class="fas fa-fw fa-times" /> {{ t('global.cancel') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import store from '@/bootstrap/store.js';

    import {
        searchLabel,
    } from '@/api.js';

    import {
        getAttribute,
        getConceptLabel,
        getEntityTypeAttribute,
        getEntityTypeDependencies,
        translateConcept,
        multiselectResetClasslist,
    } from '@/helpers/helpers.js';

    import {
        mod,
    } from '@/helpers/math.js';

    export default {
        props: {
            attributeId: {
                required: true,
                type: Number,
            },
            entityTypeId: {
                required: true,
                type: Number,
            },
            attributeSelection: {
                required: true,
                type: Array,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                attributeId,
                entityTypeId,
                attributeSelection,
            } = toRefs(props);

            // FUNCTIONS
            const validateDependencyRule = rule => {
                return rule.attribute?.id &&
                    rule.operator?.id &&
                    (
                        (!rule.operator.no_parameter && !!rule.value)
                        ||
                        (rule.operator.no_parameter && !rule.value)
                    );
            };
            const formatDependency = dependencyRules => {
                const formattedRules = {};
                formattedRules.union = !!dependencyRules?.union;
                if(dependencyRules.groups) {
                    formattedRules.groups = dependencyRules.groups.map(group => {
                        const formattedGroup = {};
                        formattedGroup.union = group.union;
                        formattedGroup.rules = group.rules.map(rule => {
                            const converted = {
                                attribute: null,
                                operator: null,
                                value: null,
                            };
                            converted.attribute = getAttribute(rule.on);
                            converted.operator = operators.find(o => o.operator == rule.operator);
                            converted.value = rule.value;
                            return converted;
                        });
                        return formattedGroup;
                    });
                } else if(Object.keys(dependencyRules).length == 0) {
                    formattedRules.union = true;
                    formattedRules.groups = [{
                        union: false,
                        rules: [],
                    }];
                }
                return formattedRules;
            };
            const confirmEdit = _ => {
                const data = {
                    dependency: state.dependency,
                };

                // Check for changes in width metadata
                if(state.attribute.pivot &&
                    (!state.attribute.pivot.metadata || state.width != state.attribute.pivot.metadata.width)
                ) {
                    data.metadata = {
                        width: state.width,
                    };
                }
                if(state.attribute.is_system && state.separatorTitle && (!state.attribute.pivot.metadata?.title || state.separatorTitle != state.attribute.pivot.metadata.title)
                ) {
                    data.metadata = {
                        title: state.separatorTitle,
                    };
                }

                context.emit('confirm', data);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };
            const handleSeparatorRename = label => {
                if(label === null){
                    state.separatorTitle = null;
                } else if(label.concept_url) {
                    state.separatorTitle = label.concept_url;
                } else {
                    console.error('Invalid separator label', label);
                }
            };
            const dependantSelected = e => {
            };
            const operatorSelected = e => {
            };
            const getInputTypeClass = datatype => {
                switch(datatype) {
                        case 'string':
                        case 'stringf':
                        case 'richtext':
                        case 'geography':
                        case 'iconclass':
                        case 'rism':
                        case 'serial':
                            return 'text';
                        case 'double':
                        case 'integer':
                        case 'percentage':
                            return 'number';
                        case 'boolean':
                            return 'boolean';
                        case 'date':
                            return 'date';
                        case 'string-sc':
                        case 'string-mc':
                            return 'select';
                        // TODO handle entity attributes
                        case 'entity':
                        case 'entity-mc':
                            // return 'entity';
                        case 'userlist':
                        case 'epoch':
                        case 'timeperiod':
                        case 'dimension':
                        case 'list':
                        case 'table':
                        case 'sql':
                        default:
                            return 'unsupported';
                    }
            };
            const getOperatorList = datatype => {
                const list = defaultOperators.slice();
                switch(datatype) {
                    case 'epoch':
                    case 'timeperiod':
                    case 'dimension':
                    case 'list':
                    case 'table':
                    case 'sql':
                        break;
                    // TODO handle entity attributes
                    case 'entity':
                    case 'entity-mc':
                    case 'userlist':
                        break;
                    case 'string':
                    case 'stringf':
                    case 'richtext':
                    case 'string-sc':
                    case 'string-mc':
                    case 'geography':
                    case 'iconclass':
                    case 'rism':
                    case 'serial':
                        operators.forEach(o => {
                            switch(o.id) {
                                case 1:
                                case 2:
                                    list.push(o);
                                    break;
                            }
                        });
                        break;
                    case 'double':
                    case 'integer':
                    case 'date':
                    case 'percentage':
                        operators.forEach(o => {
                            switch(o.id) {
                                case 1:
                                case 2:
                                case 3:
                                case 4:
                                    list.push(o);
                                    break;
                            }
                        });
                        break;
                    case 'boolean':
                        operators.forEach(o => {
                            switch(o.id) {
                                case 1:
                                    list.push(o);
                                    break;
                            }
                        });
                        break;
                    default:
                        throw new Error(`Unsupported datatype ${datatype}`);
                }
                return list;
            };
            const getDependantOptions = (aid, datatype) => {
                if(getInputTypeClass(datatype) == 'select') {
                    return store.getters.attributeSelections[aid];
                } else {
                    return [];
                }
            };
            const gotoGroup = id => {
                if(id >= state.dependency.groups.length) return;

                state.currentDependencyGroupId = id;
            };
            const gotoPrevGroup = _ => {
                const id = mod(state.currentDependencyGroupId - 1, state.dependency.groups.length);
                gotoGroup(id);
            };
            const gotoNextGroup = _ => {
                const id = mod(state.currentDependencyGroupId + 1, state.dependency.groups.length);
                gotoGroup(id);
            };
            const addGroup = _ => {
                if(state.lastGroupEmpty) return;

                state.dependency.groups.push({
                    rules: [],
                    union: false,
                });
                gotoGroup(state.dependency.groups.length - 1);
            };
            const removeGroup = idx => {
                if(idx >= state.dependency.groups.length) return;

                state.dependency.groups.splice(idx, 1);
                if(state.currentDependencyGroupId == idx) {
                    gotoPrevGroup();
                }
            };
            const addItem = _ => {
                state.dependency.groups[state.currentDependencyGroupId].rules.push({});
            };
            const removeItem = (grpIdx, idx) => {
                if(grpIdx >= state.dependency.groups.length) return;
                if(idx >= state.dependency.groups[grpIdx].rules.length) return;

                state.dependency.groups[grpIdx].rules.splice(idx, 1);
            };

            // DATA
            const operators = [
                {
                    id: 1,
                    operator: '=',
                    label: t('global.dependency.operators.equal'),
                },
                {
                    id: 2,
                    operator: '!=',
                    label: t('global.dependency.operators.not_equal'),
                },
                {
                    id: 3,
                    operator: '<',
                    label: t('global.dependency.operators.less'),
                },
                {
                    id: 4,
                    operator: '>',
                    label: t('global.dependency.operators.greater'),
                },
                {
                    id: 5,
                    operator: '?',
                    label: t('global.dependency.operators.set'),
                    no_parameter: true,
                },
                {
                    id: 6,
                    operator: '!?',
                    label: t('global.dependency.operators.not_set'),
                    no_parameter: true,
                },
            ];
            const defaultOperators = operators.filter(o => o.operator == '?' || o.operator == '!?');
            const state = reactive({
                currentDependencyGroupId: 0,
                dependency: {
                    union: true,
                    groups: [{
                        union: false,
                        rules: [],
                    }],
                },
                separatorTitle: '',
                width: 100,
                lastGroupEmpty: computed(_ => state.dependency.groups[state.dependency.groups.length - 1].rules.length == 0),
                isValid: computed(_ => {
                    return state.dependency.groups.every(group => {
                        return group.rules.length == 0 || group.rules.every(rule => validateDependencyRule(rule));
                    });
                }),
                attribute: {},
                selection: computed(_ => {
                    return attributeSelection.value.filter(a => {
                        return a.id != attributeId.value && getInputTypeClass(a.datatype) != 'unsupported';
                    });
                }),
            });

            // ON MOUNTED
            onMounted(_ => {
                state.attribute = getEntityTypeAttribute(entityTypeId.value, attributeId.value);
                const currentDependency = getEntityTypeDependencies(entityTypeId.value, attributeId.value);
                if(currentDependency) {
                    state.dependency = formatDependency(currentDependency);
                }
                if(state.attribute?.pivot?.metadata) {
                    state.width = state.attribute.pivot.metadata.width || 100;
                    if(state.attribute.is_system) {
                        state.separatorTitle = state.attribute.pivot.metadata.title;
                    }
                }
            });

            // RETURN
            return {
                t,
                // HELPERS
                searchLabel,
                getConceptLabel,
                translateConcept,
                multiselectResetClasslist,
                // PROPS
                // LOCAL
                getInputTypeClass,
                confirmEdit,
                closeModal,
                handleSeparatorRename,
                dependantSelected,
                operatorSelected,
                getOperatorList,
                getDependantOptions,
                gotoPrevGroup,
                gotoNextGroup,
                addGroup,
                removeGroup,
                addItem,
                removeItem,
                // STATE
                state,
            };
        },
    };
</script>