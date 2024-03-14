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
                    :default-value="state.searchResetValue"
                    @selected="e => labelSelected(e, 'label')"
                />
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
                    :value-prop="'datatype'"
                    :track-by="'datatype'"
                    :placeholder="t('global.select.placeholder')"
                    :hide-selected="true"
                    :search-filter="searchInAttributeTypes"
                    @select="typeSelected"
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
                    @selected="e => labelSelected(e, 'rootAttributeLabel')"
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
                    :default-value="state.attribute.label"
                    @selected="e => labelSelected(e, 'rootLabel')"
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

    import store from '@/bootstrap/store.js';

    import {
        searchAttribute,
        searchLabel,
    } from '@/api.js';

    import {
        translateConcept,
        getConceptLabel,
        getTs,
    } from '@/helpers/helpers.js';

    export default {
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
                if(!state.needsRootElement) {
                    state.attribute.rootLabel = null;
                }
                if(!state.needsTextElement && !state.needsTextareaElement) {
                    state.attribute.textContent = '';
                }
                if(!state.canRestrictTypes || state.attribute.restrictedTypes.length == 0) {
                    state.attribute.restrictedTypes = null;
                }
                context.emit('created', {...state.attribute});
                reset();
            };
            const emitUpdate = _ => {
                context.emit('updated', state.attribute);
            };
            const labelSelected = (e, key) => {
                const {
                    added,
                    removed,
                    ...label
                } = e;
                if(removed) {
                    state.attribute[key] = null;
                } else if(added) {
                    state.attribute[key] = label;
                }
                emitUpdate();
            };
            const typeSelected = e => {
                state.attribute.type = e;
                emitUpdate();
            };
            const getAttributeLabel = attribute => {
                return translateConcept(attribute.thesaurus_url);
            };
            const searchInAttributeTypes = (option, query) => {
                if(query) {
                    const tq = query.toLowerCase().trim();
                    return option.datatype.indexOf(tq) !== -1 || t(`global.attributes.${option.datatype}`).toLowerCase().indexOf(tq) !== -1;
                } else {
                    return true;
                }
            };

            // DATA
            let types = [];
            switch(type.value) {
                case 'table':
                    types = store.getters.attributeTableTypes;
                    break;
                default:
                    types = store.getters.attributeTypes;
                    break;
            }
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
                },
                searchResetValue: null,
                formId: external.value || 'create-attribute-form',
                attributeTypes: types,
                minimalEntityTypes: computed(_ => {
                    return Object.values(store.getters.entityTypes).map(et => ({
                        id: et.id,
                        thesaurus_url: et.thesaurus_url
                    }));
                }),
                label: computed(_ => {
                    return createText.value || t('global.create');
                }),
                validated: computed(_ =>  {
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
                        );
                    context.emit('validation', isValid);
                    return isValid;
                }),
                allowsRestriction: computed(_ => {
                    return  state.attribute.type == 'string-sc' ||
                            state.attribute.type == 'string-mc' ||
                            state.attribute.type == 'epoch';
                }),
                isStringSc: computed(_ => {
                    return state.attribute.type == 'string-sc';
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

            // ON MOUNTED
            onMounted(_ => {
            });

            // RETURN
            return {
                t,
                // HELPERS
                searchAttribute,
                searchLabel,
                getConceptLabel,
                translateConcept,
                // LOCAL
                create,
                labelSelected,
                typeSelected,
                getAttributeLabel,
                searchInAttributeTypes,
                // STATE
                state,
            };
        },
    };
</script>
