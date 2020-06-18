<template>
    <div class="col-md-12 px-0 h-100 d-flex flex-column">
        <div class="col scroll-y-auto">
            <form role="form" name="layerLabelingForm" id="layerLabelingForm" @submit.prevent="apply">
                <div class="form-group row">
                    <label class="col-form-label col-md-3 text-right my-auto" for="label-text">
                        {{ $t('global.text') }}:
                    </label>
                    <div class="col-md-9">
                        <input v-if="!isEntityLayer" class="form-control" type="text" id="label-text" name="label-text" v-model="label" />
                        <div v-else>
                            <div class="d-flex flex-row justify-content-between form-group mb-2">
                                <span @click="useEntityName = !useEntityName">
                                    {{ $t('plugins.map.gis.props.labels.use-entity-name') }}
                                </span>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="use-entity-name-toggle" v-model="useEntityName" />
                                    <label class="custom-control-label" for="use-entity-name-toggle">
                                    </label>
                                </div>
                            </div>
                            <multiselect
                                label="thesaurus_url"
                                track-by="id"
                                v-model="selectedAttribute"
                                :allowEmpty="true"
                                :closeOnSelect="true"
                                :customLabel="translateLabel"
                                :disabled="useEntityName"
                                :hideSelected="false"
                                :multiple="false"
                                :options="attributes"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                            </multiselect>
                        </div>
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
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="font-options-toggle" v-model="font.active" />
                        <label class="custom-control-label" for="font-options-toggle">
                        </label>
                    </div>
                </h6>
                <div v-show="displays.font">
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="font-size">
                            {{ $t('global.size') }}:
                        </label>
                        <div class="col-md-9">
                            <input class="form-control" type="number" id="font-size" name="font-size" min="1" v-model.number="font.size" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="font-color">
                            {{ $t('global.color') }}:
                        </label>
                        <div class="col-md-9">
                            <input class="form-control" type="color" id="font-color" name="font-color" min="1" v-model="font.color" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="font-style">
                            {{ $t('plugins.map.gis.props.labels.style') }}:
                        </label>
                        <div class="col-md-9">
                            <multiselect
                                id="font-style"
                                name="font-style"
                                label="label"
                                v-model="font.style"
                                :allowEmpty="true"
                                :closeOnSelect="true"
                                :hideSelected="false"
                                :multiple="false"
                                :options="font.styles"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                            </multiselect>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="font-transform">
                            {{ $t('plugins.map.gis.props.labels.transform') }}:
                        </label>
                        <div class="col-md-9">
                            <multiselect
                                id="font-transform"
                                name="font-transform"
                                label="label"
                                v-model="font.transform"
                                :allowEmpty="true"
                                :closeOnSelect="true"
                                :hideSelected="false"
                                :multiple="false"
                                :options="font.transforms"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                            </multiselect>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="font-transparency">
                            {{ $t('global.transparency') }}:
                        </label>
                        <div class="col-md-9 d-flex">
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
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="buffer-options-toggle" v-model="buffer.active" />
                        <label class="custom-control-label" for="buffer-options-toggle">
                        </label>
                    </div>
                </h6>
                <div v-show="displays.buffer">
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="buffer-size">
                            {{ $t('global.size') }}:
                        </label>
                        <div class="col-md-9">
                            <input class="form-control" type="number" id="buffer-size" name="buffer-size" min="1" v-model.number="buffer.size" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="buffer-color">
                            {{ $t('global.color') }}:
                        </label>
                        <div class="col-md-9">
                            <input class="form-control" type="color" id="buffer-color" name="buffer-color" min="1" v-model="buffer.color" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="buffer-transparency">
                            {{ $t('global.transparency') }}:
                        </label>
                        <div class="col-md-9 d-flex">
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
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="background-options-toggle" v-model="background.active" />
                        <label class="custom-control-label" for="background-options-toggle">
                        </label>
                    </div>
                </h6>
                <div v-show="displays.background">
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="background-size-x">
                            {{ $t('plugins.map.gis.props.labels.background.padding-x') }}:
                        </label>
                        <div class="col-md-9">
                            <input class="form-control" type="number" id="background-size-x" name="background-size-x" min="0" v-model.number="background.sizes.x" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="background-size-y">
                            {{ $t('plugins.map.gis.props.labels.background.padding-y') }}:
                        </label>
                        <div class="col-md-9">
                            <input class="form-control" type="number" id="background-size-y" name="background-size-y" min="0" v-model.number="background.sizes.y" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="background-color-fill">
                            {{ $t('plugins.map.gis.props.labels.fill-color') }}:
                        </label>
                        <div class="col-md-9">
                            <input class="form-control" type="color" id="background-color-fill" name="background-color-fill" min="1" v-model="background.colors.fill" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="background-color-border">
                            {{ $t('plugins.map.gis.props.labels.border-color') }}:
                        </label>
                        <div class="col-md-9">
                            <input class="form-control" type="color" id="background-color-border" name="background-color-border" min="1" v-model="background.colors.border" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="background-size-border">
                            {{ $t('plugins.map.gis.props.labels.border-size') }}:
                        </label>
                        <div class="col-md-9">
                            <input class="form-control" type="number" id="background-size-border" name="background-size-border" min="1" v-model.number="background.borderSize" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="background-transparency">
                            {{ $t('global.transparency') }}:
                        </label>
                        <div class="col-md-9 d-flex">
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
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="position-options-toggle" v-model="position.active" />
                        <label class="custom-control-label" for="position-options-toggle">
                        </label>
                    </div>
                </h6>
                <div v-show="displays.position">
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="position-offset-x">
                            {{ $t('plugins.map.gis.props.labels.position.offset-x') }}:
                        </label>
                        <div class="col-md-9">
                            <input class="form-control" type="number" id="position-offset-x" name="position-offset-x" v-model.number="position.offsets.x" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="position-offset-y">
                            {{ $t('plugins.map.gis.props.labels.position.offset-y') }}:
                        </label>
                        <div class="col-md-9">
                            <input class="form-control" type="number" id="position-offset-y" name="position-offset-y" v-model.number="position.offsets.y" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3 text-right" for="position-placement">
                            {{ $t('plugins.map.gis.props.labels.position.placement') }}:
                        </label>
                        <div class="col-md-9">
                            <multiselect
                                id="position-placement"
                                name="position-placement"
                                label="label"
                                v-model="position.placement"
                                :allowEmpty="true"
                                :closeOnSelect="true"
                                :hideSelected="false"
                                :multiple="false"
                                :options="position.placements"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
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
                if(
                    (this.selectedAttribute && this.isEntityLayer) ||
                    (!this.selectedAttribute && this.label.length) ||
                    this.useEntityName
                ) {
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
                    if(this.selectedAttribute && this.isEntityLayer) {
                        if(this.cachedData[this.cacheKey]) {
                            options.getText = feature => {
                                const eid = feature.get('entity').id;
                                const data = this.cachedData[this.cacheKey][eid];
                                return data ? data.value : '';
                            };

                            if(this.onUpdate) {
                                this.onUpdate(this.layer, {
                                    type: 'labeling',
                                    data: options
                                });
                            }
                        } else {
                            const id = this.layer.entity_type_id;
                            const aid = this.selectedAttribute.attribute_id;
                            $httpQueue.add(() => $http.get(`entity/entity_type/${id}/data/${aid}?geodata=has`).then(response => {
                                this.cachedData[this.cacheKey] = response.data;

                                options.getText = feature => {
                                    const eid = feature.get('entity').id;
                                    const data = this.cachedData[this.cacheKey][eid];
                                    return data ? data.value : '';
                                };

                                if(this.onUpdate) {
                                    this.onUpdate(this.layer, {
                                        type: 'labeling',
                                        data: options
                                    });
                                }
                            }));
                        }
                    } else if(this.useEntityName) {
                        options.getText = feature => {
                            return feature.get('entity').name;
                        };
                        if(this.onUpdate) {
                            this.onUpdate(this.layer, {
                                type: 'labeling',
                                data: options
                            });
                        }
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
                let name;
                if(this.layer.entity_type) {
                    name = this.$translateConcept(this.layer.entity_type.thesaurus_url)
                } else {
                    name = this.layer.name;
                }
                this.$showToast(
                    this.$t('plugins.map.gis.toasts.updated.labels.title'),
                    this.$t('plugins.map.gis.toasts.updated.labels.msg', {
                        name: name
                    }),
                    'success'
                );
            },
            translateLabel(element, prop) {
                return this.$translateLabel(element, prop);
            }
        },
        data() {
            return {
                cachedData: {},
                useEntityName: false,
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
        },
        computed: {
            cacheKey() {
                return `${this.layer.id}_${this.selectedAttribute.attribute_id}`;
            }
        }
    }
</script>
