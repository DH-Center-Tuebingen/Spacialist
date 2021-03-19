<template>
    <div class="col-md-12 px-0 h-100 d-flex flex-column">
        <div class="col scroll-y-auto">
            <form role="form" name="layerLabelingForm" id="layerLabelingForm" @submit.prevent="apply">
                <div class="form-group row">
                    <label class="col-form-label col-md-3 text-end my-auto" for="label-text">
                        {{ $t('global.text') }}:
                    </label>
                    <div class="col-md-9">
                        <multiselect
                            id="label-text"
                            label="thesaurus_url"
                            track-by="id"
                            v-model="selectedAttribute"
                            :allowEmpty="true"
                            :closeOnSelect="true"
                            :customLabel="translateLabel"
                            :hideSelected="false"
                            :multiple="false"
                            :options="attributes"
                            :placeholder="$t('global.select.placeholder')"
                            :select-label="$t('global.select.select')"
                            :deselect-label="$t('global.select.deselect')"
                            @input="attributeSwitched">
                        </multiselect>
                    </div>
                </div>
                <h6>{{ $t('plugins.map.gis.props.diagrams.data.title') }}</h6>
                <div class="form-group row">
                    <label class="col-form-label col-md-3 text-end" for="row-or-columns">
                        {{ $t('plugins.map.gis.props.diagrams.data.order') }}:
                    </label>
                    <div class="col-md-9">
                        <multiselect
                                id="row-or-columns"
                                label="label"
                                track-by="id"
                                v-model="data.order"
                                :closeOnSelect="true"
                                :hideSelected="false"
                                :multiple="false"
                                :options="orderTypes"
                                :placeholder="$t('global.select.placeholder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                        </multiselect>
                    </div>
                </div>
                <div class="form-group row" v-if="!dataInitNeeded">
                    <label class="col-form-label col-md-3 text-end" for="selected-columns">
                        {{ $t('plugins.map.gis.props.diagrams.data.columns') }}:
                    </label>
                    <div class="col-md-9">
                        <multiselect
                                id="selected-columns"
                                label="thesaurus_url"
                                track-by="id"
                                v-model="data.columns"
                                :closeOnSelect="data.order.id !== 'row'"
                                :customLabel="translateLabel"
                                :hideSelected="data.order.id === 'row'"
                                :multiple="data.order.id === 'row'"
                                :options="availableColumns"
                                :placeholder="$t('global.select.placeholder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                        </multiselect>
                    </div>
                </div>
                <div v-else>
                    <p class="alert alert-warning">
                        {{ $t('plugins.map.gis.props.diagrams.missing_sql_data') }}
                        <br/>
                        <button type="button" class="btn btn-secondary btn-sm mt-2" @click="fetchSqlData()">
                            {{ $t('plugins.map.gis.props.diagrams.fetch_sql_data') }}
                        </button>
                    </p>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-3 text-end" for="min-values">
                        {{ $t('plugins.map.gis.props.diagrams.data.min_n_values') }}:
                    </label>
                    <div class="col-md-9">
                        <input class="form-control" type="number" id="min-values" name="min-values" min="0" v-model.number="data.min_n_values" />
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-3 text-end" for="last-n-elements">
                        {{ $t('plugins.map.gis.props.diagrams.data.last_n_elements') }}:
                    </label>
                    <div class="col-md-9">
                        <input class="form-control" type="number" id="last-n-elements" name="last-n-elements" min="2" v-model.number="data.last_n_elements" />
                    </div>
                </div>
                <hr />
                <h6>{{ $t('plugins.map.gis.props.diagrams.properties.title') }}</h6>
                <div class="form-group row">
                    <label class="col-form-label col-md-3 text-end" for="radius">
                        {{ $t('plugins.map.gis.props.diagrams.properties.radius') }}:
                    </label>
                    <div class="col-md-9">
                        <input class="form-control" type="number" id="radius" name="font-size" min="5" v-model.number="properties.radius" />
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-3 text-end" for="radius">
                        {{ $t('plugins.map.gis.props.diagrams.properties.type') }}:
                    </label>
                    <div class="col-md-9">
                        <multiselect
                                track-by="id"
                                label="label"
                                v-model="properties.type"
                                :allowEmpty="false"
                                :closeOnSelect="true"
                                :hideSelected="false"
                                :multiple="false"
                                :options="chartTypes"
                                :placeholder="$t('global.select.placeholder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                        </multiselect>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-3 text-end" for="stroke-color">
                        {{ $t('plugins.map.gis.props.diagrams.properties.stroke_color') }}:
                    </label>
                    <div class="col-md-9">
                        <input class="form-control" type="color" id="stroke-color" name="stroke-color" v-model.number="properties.stroke.color" />
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-3 text-end" for="stroke-width">
                        {{ $t('plugins.map.gis.props.diagrams.properties.stroke_width') }}:
                    </label>
                    <div class="col-md-9">
                        <input class="form-control" type="number" id="stroke-width" name="stroke-width" min="0" v-model.number="properties.stroke.width" />
                    </div>
                </div>
            </form>
        </div>

        <button type="submit" form="layerLabelingForm" class="btn btn-outline-success mt-2" :disabled="applyDisabled">
            <i class="fas fa-fw fa-check"></i>
            {{ $t('plugins.map.gis.props.diagrams.apply') }}
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
        mounted() {},
        methods: {
            attributeSwitched(value) {
                this.dataInitNeeded = value.datatype === 'sql' && !this.cachedData[this.cacheKey];
            },
            apply() {
                if(this.applyDisabled) return;

                const id = this.layer.entity_type_id;
                const aid = this.selectedAttribute.attribute_id;

                if(this.cachedData[this.cacheKey]) {
                    this.sendData(this.cachedData[this.cacheKey]);
                } else {
                    $httpQueue.add(() => $http.get(`entity/entity_type/${id}/data/${aid}?geodata=has`).then(response => {
                        this.cachedData[this.cacheKey] = response.data;
                        this.sendData(this.cachedData[this.cacheKey]);
                    }));
                }
            },
            fetchSqlData() {
                if(!this.dataInitNeeded) return;

                const id = this.layer.entity_type_id;
                const aid = this.selectedAttribute.attribute_id;

                $httpQueue.add(() => $http.get(`entity/entity_type/${id}/data/${aid}?geodata=has`).then(response => {
                    this.cachedData[this.cacheKey] = response.data;
                    this.dataInitNeeded = false;
                }));
            },
            sendData(data) {
                if(this.applyDisabled) return;
                let layerData = {};
                let featureOpts = {};

                for(let eid in data) {
                    if(!data.hasOwnProperty(eid)) continue;
                    const value = data[eid];
                    if(!value || !value.value || value.value.length === 0) {
                        continue;
                        // layerData[eid] = [];
                    } else {
                        let curr = [];
                        if(this.data.order.id === 'row') {
                            const row = value.value.slice(-1)[0];
                            for(let i=0; i<this.data.columns.length; i++) {
                                const col = this.data.columns[i];
                                curr.push(Number.parseFloat(row[col.id]));
                            }
                        } else {
                            const colId = this.data.columns.id;
                            for(let i=0; i<value.value.length; i++) {
                                curr.push(Number.parseFloat(value.value[i][colId]));
                            }
                        }
                        // if sum of values is below desired min value, omit dataset
                        const sum = curr.reduce((total, curr) => total + curr, 0);
                        if(sum < this.data.min_n_values) {
                            continue;
                            // layerData[eid] = [];
                        }
                        featureOpts[eid] = {};
                        featureOpts[eid].max = curr.sort((a, b) => a-b).slice(-1)[0];
                        // only select last n entries
                        layerData[eid] = curr.slice(-this.data.last_n_elements);
                    }
                }

                let options = {
                    type: this.properties.type.id,
                    radius: this.properties.radius,
                    stroke: {
                        color: this.properties.stroke.width === 0 ? 'rgba(0,0,0,0)' : this.properties.stroke.color,
                        width: this.properties.stroke.width,
                    },
                    data: layerData,
                    feature_options: featureOpts
                };
                if(this.onUpdate) {
                    this.onUpdate(this.layer, {
                        type: 'charts',
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
                cachedData: {},
                dataInitNeeded: false,
                selectedAttribute: null,
                orderTypes: [
                    {
                        id: 'row',
                        label: this.$t('plugins.map.gis.props.diagrams.data.order_row')
                    },
                    {
                        id: 'column',
                        label: this.$t('plugins.map.gis.props.diagrams.data.order_columns')
                    },
                ],
                chartTypes: [
                    {
                        id: 'pie',
                        label: this.$t('plugins.map.gis.props.diagrams.properties.type_pie')
                    },
                    {
                        id: 'pie3D',
                        label: this.$t('plugins.map.gis.props.diagrams.properties.type_pie3d')
                    },
                    {
                        id: 'donut',
                        label: this.$t('plugins.map.gis.props.diagrams.properties.type_donut')
                    },
                    {
                        id: 'bar',
                        label: this.$t('plugins.map.gis.props.diagrams.properties.type_bar')
                    },
                ],
                data: {
                    order: {},
                    columns: [],
                    min_n_values: 1,
                    last_n_elements: 10,
                },
                properties: {
                    type: {},
                    radius: 10,
                    stroke: {
                        color: '#ffffff',
                        width: 1
                    }
                },
            }
        },
        computed: {
            applyDisabled() {
                return (!this.selectedAttribute || !this.selectedAttribute.id) ||
                    (!this.properties.type || !this.properties.type.id) ||
                    !this.properties.radius ||
                    (!this.data.order || !this.data.order.id) ||
                    (!this.data.columns ||
                        (this.data.order.id === 'row' && !this.data.columns.length > 0) ||
                        (this.data.order.id === 'column' && !this.data.columns.id)
                    ) ||
                    (!this.data.min_n_values && this.data.min_n_values !== 0) ||
                    (!this.data.last_n_elements && this.data.last_n_elements !== 0);
            },
            availableColumns() {
                if(!this.selectedAttribute) return [];
                if(this.selectedAttribute.datatype === 'sql') {
                    if(this.dataInitNeeded) return [];
                    const data = this.cachedData[this.cacheKey];
                    let i = 0;
                    let first;
                    let keys = Object.keys(data);
                    while(!first || !first.value) {
                        first = data[keys[i++]];
                    }
                    let colKeys = Object.keys(first.value[0]);
                    let avCols = [];
                    for(let i=0; i<colKeys.length; i++) {
                        avCols.push({
                            id: colKeys[i],
                            thesaurus_url: colKeys[i],
                            label: colKeys[i]
                        });
                    }
                    return avCols;
                } else {
                    return Object.values(this.selectedAttribute.columns);
                }
            },
            cacheKey() {
                return `${this.layer.id}_${this.selectedAttribute.attribute_id}`;
            }
        }
    }
</script>
