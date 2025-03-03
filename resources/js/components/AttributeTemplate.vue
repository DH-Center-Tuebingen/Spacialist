<template>
    <form
        :id="state.formId"
        :name="state.formId"
        role="form"
        @submit.prevent="create()"
    >
        <div class="mb-3">
            <label class="col-form-label col-3">
                {{ t('global.label') }}:
            </label>
            <div class="col">
                <simple-search
                    :endpoint="searchLabel"
                    :key-fn="getConceptLabel"
                    :value="state.searchResetValue"
                    @selected="label => labelSelected(label, 'label')"
                >
                    <template #chain="{ option }">
                        <ChainList
                            :lists="option.parent_path"
                            :max-length="3"
                        />
                    </template>
                </simple-search>
            </div>
        </div>
        <div class="mb-3">
            <label class="col-form-label col-3">
                {{ t('global.type') }}:
            </label>
            <div class="col">
                <multiselect
                    v-model="state.attribute.type"
                    :mode="'single'"
                    :options="state.attributeTypes"
                    :searchable="true"
                    :filter-results="false"
                    :value-prop="'datatype'"
                    :track-by="'datatype'"
                    :placeholder="t('global.select.placeholder')"
                    :hide-selected="true"
                    @select="typeSelected"
                    @search-change="searchInAttributeTypes"
                >
                    <template #option="{ option }">
                        {{ t(`global.attributes.${option.datatype}`) }}
                    </template>
                    <template #singlelabel="{ value }">
                        <div class="multiselect-single-label">
                            {{ t(`global.attributes.${value.datatype}`) }}
                        </div>
                    </template>
                </multiselect>
            </div>
        </div>
        <div
            v-if="state.isStringSc"
            class="mb-3"
        >
            <div class="form-check form-switch">
                <input
                    id="root-type-toggle"
                    v-model="state.attribute.differRoot"
                    class="form-check-input"
                    type="checkbox"
                >
                <label
                    class="form-check-label"
                    for="root-type-toggle"
                >
                    {{ t('global.root_attribute_toggle') }}
                </label>
            </div>
        </div>
        <div
            v-show="state.isStringSc && state.attribute.differRoot"
            class="mb-3"
        >
            <label class="col-form-label col-3">
                {{ t('global.root_attribute') }}:
            </label>
            <div class="col">
                <simple-search
                    :endpoint="searchAttribute"
                    :key-fn="getAttributeLabel"
                    @selected="label => labelSelected(label, 'rootAttributeLabel')"
                />
            </div>
        </div>
        <div
            v-show="state.needsRootElement && !state.attribute.differRoot"
            class="mb-3"
        >
            <label class="col-form-label col-3">
                {{ t('global.root_element') }}:
            </label>
            <div class="col">
                <simple-search
                    :endpoint="searchLabel"
                    :key-fn="getConceptLabel"
                    :value="state.attribute.label"
                    @selected="label => labelSelected(label, 'rootLabel')"
                />
            </div>
        </div>
        <div
            v-show="state.allowsRestriction && !state.attribute.differRoot"
            class="mb-3 form-check form-switch"
        >
            <input
                id="recursive-attribute-toggle"
                v-model="state.attribute.recursive"
                class="form-check-input"
                type="checkbox"
            >
            <label
                class="form-check-label"
                for="recursive-attribute-toggle"
            >
                {{ t('global.recursive') }}
            </label>
        </div>
        <div
            v-show="state.canRestrictTypes"
            class="mb-3"
        >
            <label class="col-form-label col">
                {{ t('global.attributes.restrictions.entity_type') }}:
            </label>
            <multiselect
                v-model="state.attribute.restrictedTypes"
                class="col"
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
        </div>
        <div
            v-show="state.needsTextElement"
            class="mb-3"
        >
            <alert
                v-if="state.attribute.type == 'serial'"
                :message="t('global.attributes.serial_info')"
                :type="'note'"
                :noicon="false"
            />
            <label class="col-form-label col-3">
                {{ t('global.content') }}:
            </label>
            <div class="col">
                <input
                    v-model="state.attribute.textContent"
                    type="text"
                    class="form-control"
                >
            </div>
        </div>
        <div
            v-show="state.needsTextareaElement"
            class="mb-3"
        >
            <label class="col-form-label col-3">
                {{ t('global.content') }}:
            </label>
            <div class="col">
                <alert
                    :message="t('global.attributes.sql_info')"
                    :type="'note'"
                    :noicon="false"
                />
                <textarea
                    v-model="state.attribute.textContent"
                    class="form-control"
                />
            </div>
        </div>
        <template v-if="state.isSiUnit">
            <div class="mb-3">
                <label class="col-form-label col-3">
                    {{ t('global.attributes.si_units.unit_type') }}:
                </label>
                <div class="col">
                    <multiselect
                        v-model="state.attribute.siGroup"
                        :mode="'single'"
                        :options="state.siGroups"
                        :searchable="true"
                        :filter-results="false"
                        :object="false"
                        :placeholder="t('global.select.placeholder')"
                        :hide-selected="true"
                        @select="siGroupSelected"
                        @search-change="searchInSiGroups"
                    >
                        <template #option="{ option }">
                            {{ t(`global.attributes.si_units.${option.label}.label`) }}
                        </template>
                        <template #singlelabel="{ value }">
                            <div class="multiselect-single-label">
                                {{ t(`global.attributes.si_units.${value.label}.label`) }}
                            </div>
                        </template>
                    </multiselect>
                </div>
            </div>
            <div
                v-show="state.attribute.siGroup"
                class="mb-3"
            >
                <label class="col-form-label col-3">
                    {{ t('global.attributes.si_units.base_unit') }}:
                </label>
                <div class="col">
                    <multiselect
                        v-model="state.attribute.siGroupUnit"
                        :classes="multiselectResetClasslist"
                        :mode="'single'"
                        :options="state.siGroupUnits"
                        :track-by="'label'"
                        :value-prop="'label'"
                        :object="false"
                        :placeholder="t('global.select.placeholder')"
                        :hide-selected="true"
                        @select="siGroupUnitSelected"
                    >
                        <template #option="{ option }">
                            <div class="d-flex flex-row justify-content-between gap-3">
                                <span>
                                    {{ siSymbolToStr(option.symbol) }}
                                </span>
                                <span>
                                    {{ t(`global.attributes.si_units.${state.attribute.siGroup}.units.${option.label}`) }}
                                </span>
                            </div>
                        </template>
                        <template #singlelabel="{ value }">
                            <div class="multiselect-single-label">
                                <div class="d-flex flex-row justify-content-between gap-3">
                                    <span>
                                        {{ siSymbolToStr(value.symbol) }}
                                    </span>
                                    <span>
                                        {{ t(`global.attributes.si_units.${state.attribute.siGroup}.units.${value.label}`)
                                        }}
                                    </span>
                                </div>
                            </div>
                        </template>
                    </multiselect>
                </div>
            </div>
        </template>
        <button
            v-show="!external"
            type="submit"
            class="btn btn-outline-success"
            :disabled="!state.validated"
        >
            <i class="fas fa-fw fa-plus" />
            {{ state.label }}
        </button>
    </form>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import useAttributeStore from '@/bootstrap/stores/attribute.js';
    import useEntityStore from '@/bootstrap/stores/entity.js';
    import useSystemStore from '@/bootstrap/stores/system.js';

    import {
        searchAttribute,
        searchLabel,
    } from '@/api.js';

    import {
        translateConcept,
        getConceptLabel,
        getTs,
        siSymbolToStr,
        multiselectResetClasslist,
    } from '@/helpers/helpers.js';

    import ChainList from './chain/ChainList.vue';

    export default {
        components: {
            ChainList,
        },
        props: {
            type: {
                required: false,
                type: String,
                default: 'default',
            },
            external: {
                required: false,
                type: String,
                default: '',
            },
            createText: {
                required: false,
                type: String,
                default: '',
            },
        },
        emits: ['created', 'updated', 'validation'],
        setup(props, context) {
            const { t } = useI18n();
            const attributeStore = useAttributeStore();
            const entityStore = useEntityStore();
            const systemStore = useSystemStore();
            const {
                type,
                external,
                createText,
            } = toRefs(props);

            // FUNCTIONS
            const reset = _ => {
                state.attribute.recursive = false;
                state.attribute.type = '';
                state.attribute.label = null;
                state.attribute.rootLabel = null;
                state.attribute.rootAttributeLabel = null;
                state.attribute.differRoot = false;
                state.attribute.textContent = '';
                state.attribute.restrictedTypes = [];
                state.attribute.siGroup = null;
                state.attribute.siGroupUnit = null;

                state.searchResetValue = {
                    reset: true,
                    ts: getTs(),
                };
            };
            const create = _ => {
                if(!state.attribute.differRoot) {
                    state.attribute.rootAttributeLabel = null;
                }
                if(!state.allowsRestriction || state.attribute.differRoot) {
                    state.attribute.recursive = false;
                }
                if(!state.needsRootElement || state.attribute.rootAttributeLabel) {
                    state.attribute.rootLabel = null;
                }
                if(!state.needsTextElement && !state.needsTextareaElement) {
                    state.attribute.textContent = '';
                }
                if(!state.canRestrictTypes || state.attribute.restrictedTypes.length == 0) {
                    state.attribute.restrictedTypes = null;
                }
                context.emit('created', { ...state.attribute });
                reset();
            };
            const emitUpdate = _ => {
                context.emit('updated', state.attribute);
            };
            const labelSelected = (label, key) => {
                state.attribute[key] = label;
                emitUpdate();
            };
            const typeSelected = e => {
                state.attribute.type = e;
                emitUpdate();
            };
            const getAttributeLabel = attribute => {
                return translateConcept(attribute.thesaurus_url);
            };
            const searchInAttributeTypes = query => {
                state.query = query ? query.toLowerCase().trim() : null;
            };
            const siGroupSelected = e => {
                state.attribute.siGroup = e;

                if(!state.attribute.siGroup) {
                    state.attribute.siGroupUnit = null;
                    state.siGroupUnits = null;
                } else {
                    const grp = systemStore.getDatatypeDataOf('si-unit')[state.attribute.siGroup];
                    const matchUnit = grp.units.find(u => grp.default == u.symbol);
                    if(matchUnit) {
                        state.attribute.siGroupUnit = matchUnit.label;
                    } else {
                        state.attribute.siGroupUnit = null;
                    }
                    state.siGroupUnits = grp.units;
                }
                emitUpdate();
            };
            const siGroupUnitSelected = e => {
                state.attribute.siGroupUnit = e;
                emitUpdate();
            };
            const searchInSiGroups = query => {
                state.siQuery = query ? query.toLowerCase().trim() : null;
            };

            // DATA
            let types = [];
            switch(type.value) {
                case 'table':
                    types = attributeStore.getTableAttributeTypes;
                    break;
                default:
                    types = attributeStore.attributeTypes;
                    break;
            }
            types = types.slice().sort((a, b) => {
                return t(`global.attributes.${a.datatype}`) > t(`global.attributes.${b.datatype}`);
            });

            const state = reactive({
                attribute: {
                    recursive: false,
                    type: '',
                    label: null,
                    rootLabel: null,
                    rootAttributeLabel: null,
                    differRoot: false,
                    textContent: '',
                    restrictedTypes: [],
                    siGroup: null,
                    siGroupUnit: null,
                },
                query: null,
                siQuery: null,
                siGroupUnits: null,
                attributeTypes: computed(_ => {
                    if(!state.query) return types;

                    return types.filter(type => {
                        return type.datatype.indexOf(state.query) !== -1 || t(`global.attributes.${type.datatype}`).toLowerCase().indexOf(state.query) !== -1;
                    });
                }),
                siGroups: computed(_ => {
                    if(!state.isSiUnit) return null;

                    const keys = Object.keys(systemStore.getDatatypeDataOf('si-unit'));
                    if(!state.siQuery) return keys;

                    return keys.filter(grp => {
                        return grp.indexOf(state.siQuery) !== -1 || t(`global.attributes.si_units.${grp}.label`).toLowerCase().indexOf(state.siQuery) !== -1;
                    });
                }),
                searchResetValue: null,
                formId: external.value || 'create-attribute-form',
                minimalEntityTypes: computed(_ => {
                    return Object.values(entityStore.entityTypes).map(et => ({
                        id: et.id,
                        thesaurus_url: et.thesaurus_url
                    }));
                }),
                label: computed(_ => {
                    return createText.value || t('global.create');
                }),
                validated: computed(_ => {
                    let isValid = state.attribute.label &&
                        state.attribute.label.id > 0 &&
                        state.attribute.type &&
                        state.hasRootElement &&
                        (
                            !state.needsTextareaElement ||
                            (
                                state.needsTextareaElement &&
                                state.attribute.textContent.length > 0
                            )
                        ) && (
                            (state.isSiUnit && state.attribute.siGroup && state.attribute.siGroupUnit) || !state.isSiUnit
                        );
                    context.emit('validation', isValid);
                    return isValid;
                }),
                allowsRestriction: computed(_ => {
                    return state.attribute.type == 'string-sc' ||
                        state.attribute.type == 'string-mc' ||
                        state.attribute.type == 'epoch';
                }),
                isStringSc: computed(_ => {
                    return state.attribute.type == 'string-sc';
                }),
                isSiUnit: computed(_ => {
                    return state.attribute.type == 'si-unit';
                }),
                needsRootElement: computed(_ => {
                    return state.attribute.type == 'string-sc' ||
                        state.attribute.type == 'string-mc' ||
                        state.attribute.type == 'epoch';
                }),
                canRestrictTypes: computed(_ => {
                    return state.attribute.type == 'entity' || state.attribute.type == 'entity-mc';
                }),
                needsTextElement: computed(_ => {
                    return state.attribute.type == 'serial';
                }),
                needsTextareaElement: computed(_ => {
                    return state.attribute.type == 'sql';
                }),
                hasRootElement: computed(_ => {
                    if(!state.needsRootElement) return true;
                    return (
                        !state.attribute.differRoot &&
                        state.attribute.rootLabel &&
                        state.attribute.rootLabel.id > 0
                    ) || (
                            state.attribute.type == 'string-sc' &&
                            state.attribute.differRoot &&
                            state.attribute.rootAttributeLabel &&
                            state.attribute.rootAttributeLabel.id > 0
                        );
                }),
            });

            // RETURN
            return {
                t,
                // HELPERS
                searchAttribute,
                searchLabel,
                getConceptLabel,
                siSymbolToStr,
                multiselectResetClasslist,
                translateConcept,
                // LOCAL
                create,
                labelSelected,
                typeSelected,
                getAttributeLabel,
                searchInAttributeTypes,
                siGroupSelected,
                searchInSiGroups,
                siGroupUnitSelected,
                // STATE
                state,
            };
        },
    };
</script>
