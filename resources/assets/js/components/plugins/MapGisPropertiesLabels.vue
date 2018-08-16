<template>
    <div class="col-md-6 px-0 h-100 d-flex flex-column">
        <div class="col scroll-y-auto">
            <form role="form" name="layerLabelingForm" id="layerLabelingForm" @submit.prevent="apply">
                <div class="form-group row">
                    <label class="col-form-label col-md-6 text-right" for="label-text">
                        {{ $t('global.text') }}:
                    </label>
                    <div class="col-md-6">
                        <input v-if="!isEntityLayer" class="form-control" type="text" id="label-text" name="label-text" v-model="label" />
                        <multiselect
                            label="thesaurus_url"
                            track-by="id"
                            v-else
                            v-model="selectedAttribute"
                            :allowEmpty="true"
                            :closeOnSelect="true"
                            :customLabel="translateLabel"
                            :hideSelected="false"
                            :multiple="false"
                            :options="attributes">
                        </multiselect>
                    </div>
                </div>
                <h6 class="d-flex flex-row justify-content-between">
                    <div>
                        {{ $t('global.font') }}
                        <span class="clickable" @click.prevent="toggle('font')">
                            <span v-show="displays.font">
                                <i class="fas fa-fw fa-caret-up"></i>
                            </span>
                            <span v-show="!displays.font">
                                <i class="fas fa-fw fa-caret-down"></i>
                            </span>
                        </span>
                    </div>
                    <label class="cb-toggle mx-0 my-auto align-middle">
                        <input type="checkbox" id="font-active-toggle" v-model="font.active" />
                        <span class="slider slider-rounded slider-primary"></span>
                    </label>
                </h6>
                <div v-show="displays.font">
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="font-size">
                            {{ $t('global.size') }}:
                        </label>
                        <div class="col-md-6">
                            <input class="form-control" type="number" id="font-size" name="font-size" min="1" v-model.number="font.size" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="font-color">
                            {{ $t('global.color') }}:
                        </label>
                        <div class="col-md-6">
                            <input class="form-control" type="color" id="font-color" name="font-color" min="1" v-model="font.color" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="font-style">
                            {{ $t('plugins.map.gis.props.labels.style') }}:
                        </label>
                        <div class="col-md-6">
                            <multiselect
                                id="font-style"
                                name="font-style"
                                label="label"
                                v-model="font.style"
                                :allowEmpty="true"
                                :closeOnSelect="true"
                                :hideSelected="false"
                                :multiple="false"
                                :options="font.styles">
                            </multiselect>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="font-transform">
                            {{ $t('plugins.map.gis.props.labels.transform') }}:
                        </label>
                        <div class="col-md-6">
                            <multiselect
                                id="font-transform"
                                name="font-transform"
                                label="label"
                                v-model="font.transform"
                                :allowEmpty="true"
                                :closeOnSelect="true"
                                :hideSelected="false"
                                :multiple="false"
                                :options="font.transforms">
                            </multiselect>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="font-transparency">
                            {{ $t('global.transparency') }}:
                        </label>
                        <div class="col-md-6 d-flex">
                            <input class="form-control" type="range" id="font-transparency" name="font-transparency" min="0" max="1" step="0.01" v-model="font.transparency" />
                            <span class="ml-3">{{ font.transparency }}</span>
                        </div>
                    </div>
                </div>
                <hr />
                <h6 class="d-flex flex-row justify-content-between">
                    <div>
                        {{ $t('plugins.map.gis.props.labels.buffer') }}
                        <span class="clickable" @click.prevent="toggle('buffer')">
                            <span v-show="displays.buffer">
                                <i class="fas fa-fw fa-caret-up"></i>
                            </span>
                            <span v-show="!displays.buffer">
                                <i class="fas fa-fw fa-caret-down"></i>
                            </span>
                        </span>
                    </div>
                    <label class="cb-toggle mx-0 my-auto align-middle">
                        <input type="checkbox" id="buffer-active-toggle" v-model="buffer.active" />
                        <span class="slider slider-rounded slider-primary"></span>
                    </label>
                </h6>
                <div v-show="displays.buffer">
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="buffer-size">
                            {{ $t('global.size') }}:
                        </label>
                        <div class="col-md-6">
                            <input class="form-control" type="number" id="buffer-size" name="buffer-size" min="1" v-model.number="buffer.size" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="buffer-color">
                            {{ $t('global.color') }}:
                        </label>
                        <div class="col-md-6">
                            <input class="form-control" type="color" id="buffer-color" name="buffer-color" min="1" v-model="buffer.color" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="buffer-transparency">
                            {{ $t('global.transparency') }}:
                        </label>
                        <div class="col-md-6 d-flex">
                            <input class="form-control" type="range" id="buffer-transparency" name="buffer-transparency" min="0" max="1" step="0.01" v-model="buffer.transparency" />
                            <span class="ml-3">{{ buffer.transparency }}</span>
                        </div>
                    </div>
                </div>
                <hr />
                <h6 class="d-flex flex-row justify-content-between">
                    <div>
                        {{ $t('plugins.map.gis.props.labels.background.title') }}
                        <span class="clickable" @click.prevent="toggle('background')">
                            <span v-show="displays.background">
                                <i class="fas fa-fw fa-caret-up"></i>
                            </span>
                            <span v-show="!displays.background">
                                <i class="fas fa-fw fa-caret-down"></i>
                            </span>
                        </span>
                    </div>
                    <label class="cb-toggle mx-0 my-auto align-middle">
                        <input type="checkbox" id="background-active-toggle" v-model="background.active" />
                        <span class="slider slider-rounded slider-primary"></span>
                    </label>
                </h6>
                <div v-show="displays.background">
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="background-size-x">
                            {{ $t('plugins.map.gis.props.labels.background.padding-x') }}:
                        </label>
                        <div class="col-md-6">
                            <input class="form-control" type="number" id="background-size-x" name="background-size-x" min="0" v-model.number="background.sizes.x" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="background-size-y">
                            {{ $t('plugins.map.gis.props.labels.background.padding-y') }}:
                        </label>
                        <div class="col-md-6">
                            <input class="form-control" type="number" id="background-size-y" name="background-size-y" min="0" v-model.number="background.sizes.y" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="background-color-fill">
                            {{ $t('plugins.map.gis.props.labels.fill-color') }}:
                        </label>
                        <div class="col-md-6">
                            <input class="form-control" type="color" id="background-color-fill" name="background-color-fill" min="1" v-model="background.colors.fill" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="background-color-border">
                            {{ $t('plugins.map.gis.props.labels.border-color') }}:
                        </label>
                        <div class="col-md-6">
                            <input class="form-control" type="color" id="background-color-border" name="background-color-border" min="1" v-model="background.colors.border" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="background-size-border">
                            {{ $t('plugins.map.gis.props.labels.border-size') }}:
                        </label>
                        <div class="col-md-6">
                            <input class="form-control" type="number" id="background-size-border" name="background-size-border" min="1" v-model.number="background.borderSize" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="background-transparency">
                            {{ $t('global.transparency') }}:
                        </label>
                        <div class="col-md-6 d-flex">
                            <input class="form-control" type="range" id="background-transparency" name="background-transparency" min="0" max="1" step="0.01" v-model="background.transparency" />
                            <span class="ml-3">{{ background.transparency }}</span>
                        </div>
                    </div>
                </div>
                <hr />
                <h6 class="d-flex flex-row justify-content-between">
                    <div>
                        {{ $t('plugins.map.gis.props.labels.position.title') }}
                        <span class="clickable" @click.prevent="toggle('position')">
                            <span v-show="displays.position">
                                <i class="fas fa-fw fa-caret-up"></i>
                            </span>
                            <span v-show="!displays.position">
                                <i class="fas fa-fw fa-caret-down"></i>
                            </span>
                        </span>
                    </div>
                    <label class="cb-toggle mx-0 my-auto align-middle">
                        <input type="checkbox" id="position-active-toggle" v-model="position.active" />
                        <span class="slider slider-rounded slider-primary"></span>
                    </label>
                </h6>
                <div v-show="displays.position">
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="position-offset-x">
                            {{ $t('plugins.map.gis.props.labels.position.offset-x') }}:
                        </label>
                        <div class="col-md-6">
                            <input class="form-control" type="number" id="position-offset-x" name="position-offset-x" v-model.number="position.offsets.x" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="position-offset-y">
                            {{ $t('plugins.map.gis.props.labels.position.offset-y') }}:
                        </label>
                        <div class="col-md-6">
                            <input class="form-control" type="number" id="position-offset-y" name="position-offset-y" v-model.number="position.offsets.y" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-6 text-right" for="position-placement">
                            {{ $t('plugins.map.gis.props.labels.position.placement') }}:
                        </label>
                        <div class="col-md-6">
                            <multiselect
                                id="position-placement"
                                name="position-placement"
                                label="label"
                                v-model="position.placement"
                                :allowEmpty="true"
                                :closeOnSelect="true"
                                :hideSelected="false"
                                :multiple="false"
                                :options="position.placements">
                            </multiselect>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <button type="submit" form="layerLabelingForm" class="btn btn-outline-success mt-2">
            <i class="fas fa-fw fa-check"></i>
            {{ $t('plugins.map.gis.props.labels.apply') }}
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
            this.font.style = this.font.styles[0];
            this.position.placement = this.position.placements[0];
        },
        methods: {
            toggle(section) {
                this.displays[section] = !this.displays[section];
            },
            apply() {
                let options = {};
                // Only set options (set active) if attribute is selected
                if(this.selectedAttribute) {
                    if(this.font.active) {
                        options.font = this.font;
                    }
                    if(this.buffer.active) {
                        options.buffer = this.buffer;
                    }
                    if(this.background.active) {
                        options.background = this.background;
                    }
                    if(this.shadow.active) {
                        options.shadow = this.shadow;
                    }
                    if(this.position.active) {
                        options.position = this.position;
                    }
                    let callback;
                    if(this.isEntityLayer) {
                        const id = this.layer.context_type_id;
                        const aid = this.selectedAttribute.attribute_id;
                        $http.get(`context/entity_type/${id}/data/${aid}`).then(response => {
                            options.getText = feature => {
                                const eid = feature.get('entity').id;
                                const data = response.data[eid];
                                return data ? data.value : '';
                            };
                            if(this.onUpdate) {
                                this.onUpdate(this.layer, {
                                    type: 'labeling',
                                    data: options
                                });
                            }
                        });
                    } else {
                        options.getText = feature => {
                            return this.label;
                        };
                        if(this.onUpdate) {
                            this.onUpdate(this.layer, {
                                type: 'labeling',
                                data: options
                            });
                        }
                    }
                } else {
                    if(this.onUpdate) {
                        this.onUpdate(this.layer, {
                            type: 'labeling',
                            data: options
                        });
                    }
                }
            },
            translateLabel(element, prop) {
                return this.$translateLabel(element, prop);
            }
        },
        data() {
            return {
                selectedAttribute: null,
                label: 'Text',
                displays: {
                    font: false,
                    buffer: false,
                    background: false,
                    shadow: false,
                    position: false
                },
                font: {
                    active: false,
                    size: 12,
                    color: '#000000',
                    styles: [
                        {
                            id: 'normal',
                            label: this.$t('plugins.map.gis.props.labels.normal')
                        },
                        {
                            id: 'bold',
                            label: this.$t('plugins.map.gis.props.labels.bold')
                        },
                        {
                            id: 'italic',
                            label: this.$t('plugins.map.gis.props.labels.italic')
                        },
                        {
                            id: 'oblique',
                            label: this.$t('plugins.map.gis.props.labels.oblique')
                        },
                        {
                            id: 'bold-italic',
                            label: this.$t('plugins.map.gis.props.labels.bold-italic')
                        },
                        {
                            id: 'bold-oblique',
                            label: this.$t('plugins.map.gis.props.labels.bold-oblique')
                        }
                    ],
                    style: {},
                    transforms: [
                        {
                            id: 'uppercase',
                            label: this.$t('plugins.map.gis.props.labels.uppercase')
                        },
                        {
                            id: 'lowercase',
                            label: this.$t('plugins.map.gis.props.labels.lowercase')
                        },
                        {
                            id: 'capitalize',
                            label: this.$t('plugins.map.gis.props.labels.capitalize')
                        }
                    ],
                    transform: {},
                    transparency: 0
                },
                buffer: {
                    active: false,
                    size: 1,
                    color: '#ffffff',
                    transparency: 0
                },
                background: {
                    active: false,
                    sizes: {
                        x: 0,
                        y: 0
                    },
                    colors: {
                        fill: '#ffffff',
                        border: '#000000'
                    },
                    borderSize: 1,
                    transparency: 0
                },
                shadow: {
                    active: false,
                    offsets: {
                        x: 0,
                        y: 0
                    },
                    blur: 0,
                    spread: 0,
                    color: '#000000',
                    transparency: 0
                },
                position: {
                    active: false,
                    offsets: {
                        x: 0,
                        y: 0
                    },
                    placements: [
                        {
                            id: 'top',
                            label: this.$t('plugins.map.gis.props.labels.top')
                        },
                        {
                            id: 'right',
                            label: this.$t('plugins.map.gis.props.labels.right')
                        },
                        {
                            id: 'bottom',
                            label: this.$t('plugins.map.gis.props.labels.bottom')
                        },
                        {
                            id: 'left',
                            label: this.$t('plugins.map.gis.props.labels.left')
                        },
                        {
                            id: 'center',
                            label: this.$t('plugins.map.gis.props.labels.center')
                        },
                        {
                            id: 'top-right',
                            label: this.$t('plugins.map.gis.props.labels.top-right')
                        },
                        {
                            id: 'top-left',
                            label: this.$t('plugins.map.gis.props.labels.top-left')
                        },
                        {
                            id: 'bottom-right',
                            label: this.$t('plugins.map.gis.props.labels.bottom-right')
                        },
                        {
                            id: 'bottom-left',
                            label: this.$t('plugins.map.gis.props.labels.bottom-left')
                        }
                    ],
                    placement: {}
                }
            }
        }
    }
</script>
