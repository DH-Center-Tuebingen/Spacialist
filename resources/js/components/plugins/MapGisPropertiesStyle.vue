<template>
    <div class="col-md-6 px-0 h-100 d-flex flex-column" v-if="initFinished">
        <div class="col scroll-y-auto">
            <form role="form" name="layerStylingForm" id="layerStylingForm" @submit.prevent="apply">
                <div class="form-group row">
                    <label class="col-form-label col-md-6 text-right" for="style-style">
                        {{ $t('plugins.map.gis.props.style.title') }}:
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
                            :options="styles"
                            :placeholder="$t('global.select.placehoder')"
                            :select-label="$t('global.select.select')"
                            :deselect-label="$t('global.select.deselect')">
                        </multiselect>
                    </div>
                </div>
                <div v-if="styleActive">
                    <div class="form-group row" v-if="isAttributeBased">
                        <label class="col-form-label col-md-6 text-right" for="style-attribute">
                            {{ $t('global.attribute') }}:
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
                                :options="attributeList"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                            </multiselect>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="style-colors">
                            {{ $t('plugins.map.gis.props.style.color-ramp') }}:
                        </label>
                        <div class="col-md-6" v-if="isAttributeBased">
                            <multiselect
                                id="style-colors"
                                name="style-colors"
                                v-model="selectedColorRamp"
                                :allowEmpty="false"
                                :closeOnSelect="true"
                                :hideSelected="false"
                                :multiple="false"
                                :options="colorRamps"
                                :show-labels="false"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
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
                        <div class="col-md-6" v-else-if="isColor">
                            <input type="color" class="form-control" @change="convertColorToRamp" />
                        </div>
                    </div>
                    <div class="form-group row" v-if="isGraduated">
                        <label class="col-form-label col-md-6 text-right" for="style-classes">
                            {{ $t('plugins.map.gis.props.style.classes') }}:
                        </label>
                        <div class="col-md-6">
                            <input class="form-control" type="number" id="style-classes" name="style-classes" min="1" v-model.number="numberOfClasses" />
                        </div>
                    </div>
                    <div class="form-group row" v-if="isGraduated">
                        <label class="col-form-label col-md-6 text-right" for="style-graduated-mode">
                            {{ $t('global.mode') }}:
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
                                :options="graduatedModes"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                            </multiselect>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="style-size">
                            {{ $t('global.size') }}:
                        </label>
                        <div class="col-md-6">
                            <input class="form-control" type="number" id="style-size" name="style-size" min="1" v-model.number="size" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="style-transparency">
                            {{ $t('global.transparency') }}:
                        </label>
                        <div class="col-md-6 d-flex">
                            <input class="form-control" type="range" id="style-transparency" name="style-transparency" min="0" max="1" step="0.01" v-model="transparency" />
                            <span class="ml-3">{{ transparency }}</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <button type="submit" form="layerStylingForm" class="btn btn-outline-success mt-2">
            <i class="fas fa-fw fa-check"></i>
            {{ $t('plugins.map.gis.props.style.apply') }}
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
                if(this.styleActive) {
                    options = {
                        style: this.selectedStyle,
                        classes: this.numberOfClasses,
                        mode: this.selectedMode,
                        color: this.selectedColorRamp,
                        size: this.size,
                        transparency: this.transparency
                    };
                    if(this.selectedAttribute) {
                        options.attribute_id = this.selectedAttribute.id;
                    }
                }
                if(this.onUpdate) {
                    this.onUpdate(this.layer, {
                        type: 'styling',
                        data: options
                    });
                }
                let name;
                if(this.layer.entity_type) {
                    name = this.$translateConcept(this.layer.entity_type.thesaurus_url)
                } else {
                    name = this.layer.name;
                }
                this.$showToast(
                    this.$t('plugins.map.gis.toasts.updated.style.title'),
                    this.$t('plugins.map.gis.toasts.updated.style.msg', {
                        name: name
                    }),
                    'success'
                );
            },
            convertColorToRamp(event) {
                const c = event.target.value;
                this.selectedColorRamp = {
                    from: c,
                    to: c
                };
            },
            translateLabel(element, prop) {
                return this.$translateLabel(element, prop);
            }
        },
        data() {
            return {
                initFinished: false,
                styles: [
                    {
                        label: this.$t('plugins.map.gis.props.style.none'),
                        id: 'none'
                    },
                    {
                        label: this.$t('plugins.map.gis.props.style.categorized'),
                        id: 'categorized'
                    },
                    {
                        label: this.$t('plugins.map.gis.props.style.graduated'),
                        id: 'graduated'
                    },
                    {
                        label: this.$t('plugins.map.gis.props.style.color'),
                        id: 'color'
                    }
                ],
                colorRamps: [
                    {
                        label: this.$t('plugins.map.gis.props.style.colors.blues'),
                        from: '#FFFFFF',
                        to: '#0000FF'
                    },
                    {
                        label: this.$t('plugins.map.gis.props.style.colors.greens'),
                        from: '#FFFFFF',
                        to: '#00FF00'
                    },
                    {
                        label: this.$t('plugins.map.gis.props.style.colors.reds'),
                        from: '#FFFFFF',
                        to: '#FF0000'
                    },
                    {
                        label: this.$t('plugins.map.gis.props.style.colors.blue-green'),
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
                        label: this.$t('plugins.map.gis.props.style.equal-interval'),
                        id: 'equal_interval'
                    },
                    {
                        label: this.$t('plugins.map.gis.props.style.quantile'),
                        id: 'quantile'
                    }
                ],
                selectedMode: {},
                size: 2,
                transparency: 0
            }
        },
        computed: {
            attributeList() {
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
            },
            styleActive() {
                if(!this.selectedStyle || this.selectedStyle.id == 'none') {
                    return false;
                }
                return true;
            },
            isGraduated() {
                return this.selectedStyle && this.selectedStyle.id == 'graduated';
            },
            isCategorized() {
                return this.selectedStyle && this.selectedStyle.id == 'categorized';
            },
            isColor() {
                return this.selectedStyle && this.selectedStyle.id == 'color';
            },
            isAttributeBased() {
                return this.selectedStyle &&
                    (
                        this.selectedStyle.id == 'graduated' ||
                        this.selectedStyle.id == 'categorized'
                    );
            }
        }
    }
</script>
