<template>
    <div class="col-md-6 px-0 h-100 d-flex flex-column" v-if="initFinished">
        <div class="col scroll-y-auto">
            <form role="form" name="layerStylingForm" id="layerStylingForm" @submit.prevent="apply">
                <div class="form-group row">
                    <label class="col-form-label col-md-6 text-right">
                        Active:
                    </label>
                    <div class="col-md-6">
                        <label class="cb-toggle mx-0 my-auto align-middle">
                            <input type="checkbox" id="font-active-toggle" v-model="isActive" />
                            <span class="slider slider-rounded slider-primary"></span>
                        </label>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-6 text-right" for="style-style">
                        Style:
                    </label>
                    <div class="col-md-6">
                        <multiselect
                            id="style-style"
                            label="label"
                            name="style-style"
                            v-model="selectedStyle"
                            :allowEmpty="false"
                            :closeOnSelect="true"
                            :hideSelected="false"
                            :multiple="false"
                            :options="styles">
                        </multiselect>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-6 text-right" for="style-attribute">
                        Attribute:
                    </label>
                    <div class="col-md-6">
                        <multiselect
                            id="style-attribute"
                            name="style-attribute"
                            label="thesaurus_url"
                            v-model="selectedAttribute"
                            :allowEmpty="false"
                            :closeOnSelect="true"
                            :custom-label="translateLabel"
                            :hideSelected="false"
                            :multiple="false"
                            :options="attributeList">
                        </multiselect>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-6 text-right" for="style-colors">
                        Color Ramp:
                    </label>
                    <div class="col-md-6">
                        <multiselect
                            id="style-colors"
                            name="style-colors"
                            v-model="selectedColorRamp"
                            :allowEmpty="false"
                            :closeOnSelect="true"
                            :hideSelected="false"
                            :multiple="false"
                            :options="colorRamps"
                            :show-labels="false">
                            <template slot="singleLabel" slot-scope="props">
                                <color-gradient
                                    :from="props.option.from"
                                    :to="props.option.to"
                                    :label="props.option.label">
                                </color-gradient>
                            </template>
                            <template slot="option" slot-scope="props">
                                <color-gradient
                                    :from="props.option.from"
                                    :to="props.option.to"
                                    :label="props.option.label">
                                </color-gradient>
                            </template>
                        </multiselect>
                    </div>
                </div>
                <div class="form-group row" v-if="selectedStyle.id == 'graduated'">
                    <label class="col-form-label col-md-6 text-right" for="style-classes">
                        Classes:
                    </label>
                    <div class="col-md-6">
                        <input class="form-control" type="number" id="style-classes" name="style-classes" min="1" v-model.number="numberOfClasses" />
                    </div>
                </div>
                <div class="form-group row" v-if="selectedStyle.id == 'graduated'">
                    <label class="col-form-label col-md-6 text-right" for="style-graduated-mode">
                        Mode:
                    </label>
                    <div class="col-md-6">
                        <multiselect
                            id="style-graduated-mode"
                            label="label"
                            name="style-graduated-mode"
                            v-model="selectedMode"
                            :allowEmpty="false"
                            :closeOnSelect="true"
                            :hideSelected="false"
                            :multiple="false"
                            :options="graduatedModes">
                        </multiselect>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-6 text-right" for="style-size">
                        Size:
                    </label>
                    <div class="col-md-6">
                        <input class="form-control" type="number" id="style-size" name="style-size" min="1" v-model.number="size" />
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-6 text-right" for="style-transparency">
                        Transparency:
                    </label>
                    <div class="col-md-6 d-flex">
                        <input class="form-control" type="range" id="style-transparency" name="style-transparency" min="0" max="1" step="0.01" v-model="transparency" />
                        <span class="ml-3">{{ transparency }}</span>
                    </div>
                </div>
            </form>
        </div>

        <button type="submit" form="layerStylingForm" class="btn btn-outline-success mt-2">
            <i class="fas fa-fw fa-check"></i>
            Apply Styling
        </button>
    </div>
</template>

<script>
    export default {
        props: {
            attributes: {
                required: true,
                type: Array
            },
            isEntityLayer: {
                required: true,
                type: Boolean
            },
            layer: {
                required: true,
                type: Object
            },
            onUpdate: {
                required: false,
                type: Function
            }
        },
        mounted() {
            this.init();
        },
        methods: {
            init() {
                this.initFinished = false;
                this.selectedColorRamp = this.colorRamps[0];
                this.selectedStyle = this.styles[0];
                this.selectedMode = this.graduatedModes[0];
                this.initFinished = true;
            },
            apply() {
                let options = {};
                if(this.isActive) {
                    options = {
                        attribute_id: this.selectedAttribute.id,
                        style: this.selectedStyle,
                        classes: this.numberOfClasses,
                        mode: this.selectedMode,
                        color: this.selectedColorRamp,
                        size: this.size,
                        transparency: this.transparency
                    };
                }
                if(this.onUpdate) {
                    this.onUpdate(this.layer, {
                        type: 'styling',
                        data: options
                    });
                }
            },
            translateLabel(element, prop) {
                return this.$translateLabel(element, prop);
            }
        },
        data() {
            return {
                initFinished: false,
                isActive: false,
                styles: [
                    {
                        label: 'Categorized',
                        id: 'categorized'
                    },
                    {
                        label: 'Graduated',
                        id: 'graduated'
                    }
                ],
                colorRamps: [
                    {
                        label: 'Blues',
                        from: '#FFFFFF',
                        to: '#0000FF'
                    },
                    {
                        label: 'Greens',
                        from: '#FFFFFF',
                        to: '#00FF00'
                    },
                    {
                        label: 'Reds',
                        from: '#FFFFFF',
                        to: '#FF0000'
                    },
                    {
                        label: 'GreenBlue',
                        from: '#0000FF',
                        to: '#00FF00'
                    }
                ],
                selectedStyle: {},
                selectedColorRamp: {},
                selectedAttribute: null,
                numberOfClasses: 5,
                graduatedModes: [
                    {
                        label: 'Equal Interval',
                        id: 'equal_interval'
                    },
                    {
                        label: 'Quantile (Equal Count)',
                        id: 'quantile'
                    }
                ],
                selectedMode: {},
                size: 2,
                transparency: 0
            }
        },
        computed: {
            attributeList: function() {
                switch(this.selectedStyle.id) {
                    case 'categorized':
                        return this.attributes;
                    case 'graduated':
                        return this.attributes.filter(a => {
                            switch(a.datatype) {
                                case 'integer':
                                case 'double':
                                case 'boolean':
                                case 'percentage':
                                    return true;
                                default:
                                    return false;
                            }
                        });
                    default:
                        return [];
                }
            }
        }
    }
</script>
