<template>
    <form :id="formId" name="newAttributeForm" role="form" v-on:submit.prevent="create">
        <div class="form-group">
            <label class="col-form-label col-md-3">
                {{ $t('global.label') }}:
            </label>
            <div class="col-md-9">
                <label-search
                    :on-select="setAttributeLabel"
                ></label-search>
            </div>
        </div>
        <div class="form-group">
            <label class="col-form-label col-md-3">
                {{ $t('global.type') }}:
            </label>
            <div class="col-md-9">
                <multiselect
                    track-by="datatype"
                    v-model="newAttribute.type"
                    :allowEmpty="false"
                    :closeOnSelect="true"
                    :hideSelected="true"
                    :multiple="false"
                    :options="attributeTypes"
                    :placeholder="$t('global.select.placehoder')"
                    :select-label="$t('global.select.select')"
                    :deselect-label="$t('global.select.deselect')"
                    :custom-label="translateDatatype"
                    @select="typeSelected">
                </multiselect>
            </div>
        </div>
        <div class="form-group col-md-12" v-if="isStringSc">
            <div class="custom-control custom-switch">
                <!-- <label class="custom-control-label" for="root-type-toggle">Select static element</label> -->
                <input type="checkbox" class="custom-control-input" id="root-type-toggle" v-model="rootTypeToggle" />
                <label class="custom-control-label" for="root-type-toggle">
                    {{ $t('global.root-attribute-toggle') }}
                </label>
            </div>
        </div>
        <div class="form-group" v-show="isStringSc && rootTypeToggle">
            <label class="col-form-label col-md-3">
                {{ $t('global.root-attribute') }}
            </label>
            <div class="col-md-9">
                <attribute-search
                    :on-select="setAttributeRootId">
                </attribute-search>
            </div>
        </div>
        <div class="form-group" v-show="needsRootElement && !rootTypeToggle">
            <label class="col-form-label col-md-3">
                {{ $t('global.root-element') }}:
            </label>
            <div class="col-md-9">
                <label-search
                    :on-select="setAttributeRoot"
                ></label-search>
            </div>
        </div>
        <div class="form-group" v-show="allowsRestriction && !rootTypeToggle">
            <label class="col-form-label px-3" for="allow-restrictions">
                {{ $t('global.recursive') }}:
            </label>
            <input type="checkbox" id="allow-restrictions" v-model="newAttribute.recursive" />
        </div>
        <div class="form-group" v-show="needsTextElement">
            <p class="alert alert-info" v-if="newAttribute.type && newAttribute.type.datatype == 'serial'">
                <span v-html="$t('global.attributes.serial-info')"></span>
            </p>
            <label class="col-form-label col-md-3">
                {{ $t('global.content') }}:
            </label>
            <div class="col-md-9">
                <input type="text" class="form-control" v-model="newAttribute.textContent" />
            </div>
        </div>
        <div class="form-group" v-show="needsTextareaElement">
            <label class="col-form-label col-md-3">
                {{ $t('global.content') }}:
            </label>
            <div class="col-md-9">
                <textarea class="form-control" v-model="newAttribute.textContent"></textarea>
            </div>
        </div>
        <button v-show="!externalSubmit" type="submit" class="btn btn-outline-success" :disabled="!validated">
            {{ $t('global.create') }}
        </button>
    </form>
</template>

<script>
    export default {
        props: {
            attributeTypes: {
                required: true,
                type: Array
            },
            externalSubmit: {
                required: false,
                type: String
            }
        },
        mounted() {},
        methods: {
            create() {
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
            },
            typeSelected(type, id) {
                this.$emit('selected-type', {
                    type: type.datatype
                });
            },
            translateDatatype(option) {
                return this.$t(`global.attributes.${option.datatype}`);
            },
            setAttributeLabel(label) {
                Vue.set(this.newAttribute, 'label', label);
            },
            setAttributeRoot(label) {
                Vue.set(this.newAttribute, 'root', label);
            },
            setAttributeRootId(label) {
                Vue.set(this.newAttribute, 'root_id', label.id);
            }
        },
        data() {
            return {
                newAttribute: {
                    recursive: true
                },
                rootTypeToggle: false
            }
        },
        computed: {
            formId: function() {
                if(this.externalSubmit) {
                    return this.externalSubmit;
                } else {
                    return 'create-attribute-form';
                }
            },
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
        }
    }
</script>
