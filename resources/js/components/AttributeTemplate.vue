<template>
    <form :id="state.formId" :name="state.formId" role="form" @submit.prevent="create()">
        <div class="mb-3">
            <label class="col-form-label col-3">
                {{ t('global.label') }}:
            </label>
            <div class="col">
                <simple-search
                    :endpoint="searchLabel"
                    :key-fn="getConceptLabel"
                    @selected="e => labelSelected(e, 'label')" />
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
                    :valueProp="'datatype'"
                    :trackBy="'datatype'"
                    :placeholder="t('global.select.placeholder')"
                    :hideSelected="true"
                    @select="typeSelected">
                        <template v-slot:option="{ option }">
                            {{ t(`global.attributes.${option.datatype}`) }}
                        </template>
                        <template v-slot:singlelabel="{ value }">
                            <div class="px-2">
                                {{ t(`global.attributes.${value.datatype}`) }}
                            </div>
                        </template>
                </multiselect>
            </div>
        </div>
        <div class="mb-3" v-if="state.isStringSc">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="root-type-toggle" v-model="state.attribute.differRoot">
                <label class="form-check-label" for="root-type-toggle">
                    {{ t('global.root-attribute-toggle') }}
                </label>
            </div>
        </div>
        <div class="mb-3" v-show="state.isStringSc && state.attribute.differRoot">
            <label class="col-form-label col-3">
                {{ t('global.root-attribute') }}:
            </label>
            <div class="col">
                <simple-search
                    :endpoint="searchAttribute"
                    :key-fn="getAttributeLabel"
                    @selected="e => labelSelected(e, 'rootAttributeLabel')" />
            </div>
        </div>
        <div class="mb-3" v-show="state.needsRootElement && !state.attribute.differRoot">
            <label class="col-form-label col-3">
                {{ t('global.root-element') }}:
            </label>
            <div class="col">
                <simple-search
                    :endpoint="searchLabel"
                    :key-fn="getConceptLabel"
                    @selected="e => labelSelected(e, 'rootLabel')" />
            </div>
        </div>
        <div class="mb-3 form-check form-switch" v-show="state.allowsRestriction && !state.attribute.differRoot">
            <input class="form-check-input" type="checkbox" id="recursive-attribute-toggle" v-model="state.attribute.recursive">
            <label class="form-check-label" for="recursive-attribute-toggle">
                {{ t('global.recursive') }}
            </label>
        </div>
        <div class="mb-3" v-show="state.needsTextElement">
            <alert
                v-if="state.attribute.type == 'serial'"
                :message="t('global.attributes.serial_info')"
                :type="'note'"
                :noicon="false" />
            <label class="col-form-label col-3">
                {{ t('global.content') }}:
            </label>
            <div class="col">
                <input type="text" class="form-control" v-model="state.attribute.textContent" />
            </div>
        </div>
        <div class="mb-3" v-show="state.needsTextareaElement">
            <label class="col-form-label col-3">
                {{ t('global.content') }}:
            </label>
            <div class="col">
                <alert
                    :message="t('global.attributes.sql_info')"
                    :type="'note'"
                    :noicon="false" />
                <textarea class="form-control" v-model="state.attribute.textContent"></textarea>
            </div>
        </div>
        <button v-show="!external" type="submit" class="btn btn-outline-success" :disabled="!state.validated">
            <i class="fas fa-fw fa-plus"></i>
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

    import store from '../bootstrap/store.js';

    import {
        searchAttribute,
        searchLabel,
    } from '../api.js';

    import {
        translateConcept,
        getConceptLabel,
    } from '../helpers/helpers.js';

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
                },
                formId: external.value || 'create-attribute-form',
                attributeTypes: types,
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
                // PROPS
                external,
                // LOCAL
                create,
                labelSelected,
                typeSelected,
                getAttributeLabel,
                // STATE
                state,
            }
        },
    }
</script>
