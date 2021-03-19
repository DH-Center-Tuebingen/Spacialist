<template>
    <form :id="formId" name="newAttributeForm" role="form" @submit.prevent="create">
        <div class="mb-3">
            <label class="col-form-label col-md-3">
                {{ t('global.label') }}:
            </label>
            <div class="col-md-9">
                <label-search
                    :on-select="setAttributeLabel"
                ></label-search>
            </div>
        </div>
        <div class="mb-3">
            <label class="col-form-label col-md-3">
                {{ t('global.type') }}:
            </label>
            <div class="col-md-9">
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
        <div class="mb-3 col-md-12" v-if="isStringSc">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="root-type-toggle" v-model="state.attribute.differRoot">
                <label class="form-check-label" for="root-type-toggle">
                    {{ t('global.root-attribute-toggle') }}
                </label>
            </div>
        </div>
        <div class="form-group" v-show="isStringSc && rootTypeToggle">
            <label class="col-form-label col-md-3">
                {{ t('global.root-attribute') }}
            </label>
            <div class="col-md-9">
                <attribute-search
                    :on-select="setAttributeRootId">
                </attribute-search>
            </div>
        </div>
        <div class="form-group" v-show="needsRootElement && !rootTypeToggle">
            <label class="col-form-label col-md-3">
                {{ t('global.root-element') }}:
            </label>
            <div class="col-md-9">
                <label-search
                    :on-select="setAttributeRoot"
                ></label-search>
            </div>
        </div>
        <div class="form-group" v-show="allowsRestriction && !rootTypeToggle">
            <label class="col-form-label px-3" for="allow-restrictions">
                {{ t('global.recursive') }}:
            </label>
            <input type="checkbox" id="allow-restrictions" v-model="state.attribute.recursive" />
        </div>
        <div class="form-group" v-show="needsTextElement">
            <div class="alert alert-info" role="alert" v-if="state.attribute.type.datatype == 'serial'">
                <span v-html="t('global.attributes.serial-info')"></span>
            </div>
            <label class="col-form-label col-md-3">
                {{ t('global.content') }}:
            </label>
            <div class="col-md-9">
                <input type="text" class="form-control" v-model="state.attribute.textContent" />
            </div>
        </div>
        <div class="form-group" v-show="needsTextareaElement">
            <label class="col-form-label col-md-3">
                {{ t('global.content') }}:
            </label>
            <div class="col-md-9">
                <textarea class="form-control" v-model="state.attribute.textContent"></textarea>
            </div>
        </div>
        <button v-show="!external" type="submit" class="btn btn-outline-success" :disabled="!validated">
            {{ t('global.create') }}
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
            }
        },
        setup(props, context) {
            const { t } = useI18n();
            const {
                type,
                external,
            } = toRefs(props);

            // FUNCTIONS
            const create = _ => {
                if(!this.needsRootElement) {
                    Vue.delete(this.newAttribute, 'root');
                    Vue.delete(this.newAttribute, 'root_id');
                }
                if(!this.needsTextElement && !this.needsTextareaElement) {
                    Vue.delete(this.newAttribute, 'textContent');
                }
                this.$emit('created', {
                    attribute: {...this.newAttribute}
                });
                this.newAttribute = {
                    recursive: true
                };
            };
            const typeSelected = (type, id) => {
                this.$emit('selected-type', {
                    type: type.datatype
                });
            };
            const setAttributeLabel = label => {
                Vue.set(this.newAttribute, 'label', label);
            };
            const setAttributeRoot = label => {
                Vue.set(this.newAttribute, 'root', label);
            };
            const setAttributeRootId = label => {
                Vue.set(this.newAttribute, 'root_id', label.id);
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
                    type: {},
                    differRoot: false,
                },
                formId: external.value || 'create-attribute-form',
                attributeTypes: types,
                allowsRestriction: function() {
                    return this.newAttribute.type &&
                        (
                            this.newAttribute.type.datatype == 'string-sc' ||
                            this.newAttribute.type.datatype == 'string-mc' ||
                            this.newAttribute.type.datatype == 'epoch'
                        );
                },
                isStringSc() {
                    return this.newAttribute.type && this.newAttribute.type.datatype == 'string-sc';
                },
                needsRootElement: function() {
                    return this.newAttribute.type &&
                        (
                            this.newAttribute.type.datatype == 'string-sc' ||
                            this.newAttribute.type.datatype == 'string-mc' ||
                            this.newAttribute.type.datatype == 'epoch'
                        );
                },
                needsTextElement: function() {
                    return this.newAttribute.type &&
                        (
                            this.newAttribute.type.datatype == 'serial'
                        );
                },
                needsTextareaElement: function() {
                    return this.newAttribute.type &&
                        (
                            this.newAttribute.type.datatype == 'sql'
                        );
                },
                hasRootElement() {
                    if(!this.needsRootElement) return true;
                    return (
                        this.newAttribute.root &&
                        this.newAttribute.root.id > 0
                    ) || (
                        this.newAttribute.type.datatype == 'string-sc' &&
                        this.newAttribute.root_id
                    );
                },
                validated: function() {
                    let isValid = this.newAttribute.label &&
                        this.newAttribute.type &&
                        this.newAttribute.label.id > 0 &&
                        this.newAttribute.type.datatype.length > 0 &&
                        this.hasRootElement &&
                        (
                            !this.needsTextareaElement ||
                            (
                                this.needsTextareaElement &&
                                this.newAttribute.textContent &&
                                this.newAttribute.textContent.length > 0
                            )
                        ) &&
                        (
                            !this.needsColumns ||
                            (
                                this.needsColumns &&
                                this.newAttribute.columns &&
                                this.newAttribute.columns.length > 0
                            )
                        );
                    this.$emit('validation', {
                        state: isValid
                    });
                    return isValid;
                }
            });

            // ON MOUNTED
            onMounted(_ => {
            });

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                external,
                // LOCAL
                // STATE
                state,
            }
        },
    }
</script>
