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
                    label="datatype"
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
                    @select="typeSelected">
                </multiselect>
            </div>
        </div>
        <div class="form-group" v-show="needsRootElement">
            <label class="col-form-label col-md-3">
                {{ $t('global.root-element') }}:
            </label>
            <div class="col-md-9">
                <label-search
                    :on-select="setAttributeRoot"
                ></label-search>
            </div>
        </div>
        <div class="form-group" v-show="needsTextElement">
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
                this.$emit('created', {
                    attribute: {...this.newAttribute}
                });
                this.newAttribute = {};
            },
            typeSelected(type, id) {
                this.$emit('selected-type', {
                    type: type.datatype
                });
            },
            setAttributeLabel(label) {
                Vue.set(this.newAttribute, 'label', label);
            },
            setAttributeRoot(label) {
                Vue.set(this.newAttribute, 'root', label);
            }
        },
        data() {
            return {
                newAttribute: {}
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
                        this.newAttribute.type.datatype == 'sql'
                    );
            },
            validated: function() {
                let isValid = this.newAttribute.label &&
                    this.newAttribute.type &&
                    this.newAttribute.label.id > 0 &&
                    this.newAttribute.type.datatype.length > 0 &&
                    (
                        !this.needsRootElement ||
                        (
                            this.needsRootElement &&
                            this.newAttribute.root &&
                            this.newAttribute.root.id > 0
                        )
                    ) &&
                    (
                        !this.needsTextElement ||
                        (
                            this.needsTextElement &&
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
