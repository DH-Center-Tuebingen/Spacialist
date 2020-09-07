<template>
    <div>
        <table class="table table-striped table-hovered table-sm">
            <thead class="thead-light">
                <tr>
                    <th v-for="column in attribute.columns">
                        {{ $translateConcept(column.thesaurus_url) }}
                    </th>
                    <th>
                        <button type="button" class="btn btn-outline-secondary btn-sm" @click="emitExpandToggle()">
                            <i class="fas fa-fw fa-expand"></i>
                        </button>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, $index) in value">
                    <td v-for="column in attribute.columns">
                        <input class="form-control form-control-sm" v-if="column.datatype == 'string'" type="text" :disabled="disabled" v-model="row[column.id]" @input="onInput($index, $event.target.value)"/>
                        <input class="form-control form-control-sm" v-else-if="column.datatype == 'double'" type="number" :disabled="disabled" step="any" min="0" placeholder="0.0" v-model.number="row[column.id]" @input="onInput($index, $event.target.value)"/>
                        <input class="form-control form-control-sm" v-else-if="column.datatype == 'integer'" type="number" :disabled="disabled" step="1" placeholder="0" v-model.number="row[column.id]" @input="onInput($index, $event.target.value)"/>
                        <input class="form-control form-control-sm" v-else-if="column.datatype == 'boolean'" type="checkbox" :disabled="disabled" v-model="row[column.id]" @input="onInput($index, $event.target.value)"/>
                        <div v-else-if="column.datatype == 'string-sc'">
                            <multiselect
                                class="multiselect-sm"
                                label="concept_url"
                                track-by="id"
                                v-model="row[column.id]"
                                :allowEmpty="true"
                                :closeOnSelect="true"
                                :customLabel="translateLabel"
                                :disabled="disabled"
                                :hideSelected="true"
                                :multiple="false"
                                :options="selections[column.id] || []"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')"
                                @input="onInput($index, $event.target)">
                            </multiselect>
                        </div>
                        <div v-else-if="column.datatype == 'entity'">
                            <entity-search
                                :id="`attribute-${column.id}`"
                                :name="`attribute-${column.id}`"
                                :on-select="selection => setEntitySearchResult(selection, row, column.id, $index)"
                                :value="row[column.id] ? row[column.id].name : ''">
                            </entity-search>
                        </div>
                    </td>
                    <td>
                        <button type="button" :disabled="disabled" class="btn btn-danger btn-sm" @click="deleteTableRow($index)">
                            <i class="fas fa-fw fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td v-for="column in attribute.columns">
                        <input class="form-control form-control-sm" v-if="column.datatype == 'string'" type="text" :disabled="disabled" v-model="newTableCols[column.id]"/>
                        <input class="form-control form-control-sm" v-else-if="column.datatype == 'double'" type="number" :disabled="disabled" step="any" min="0" placeholder="0.0" v-model.number="newTableCols[column.id]"/>
                        <input class="form-control form-control-sm" v-else-if="column.datatype == 'integer'" type="number" :disabled="disabled" step="1" placeholder="0" v-model.number="newTableCols[column.id]"/>
                        <input class="form-control form-control-sm" v-else-if="column.datatype == 'boolean'" type="checkbox" :disabled="disabled" v-model="newTableCols[column.id]"/>
                        <div v-else-if="column.datatype == 'string-sc'">
                            <multiselect
                                class="multiselect-sm"
                                label="concept_url"
                                track-by="id"
                                v-model="newTableCols[column.id]"
                                :allowEmpty="true"
                                :closeOnSelect="true"
                                :customLabel="translateLabel"
                                :disabled="disabled"
                                :hideSelected="true"
                                :multiple="false"
                                :options="selections[column.id] || []"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                            </multiselect>
                        </div>
                        <div v-else-if="column.datatype == 'entity'">
                            <entity-search
                                :id="`attribute-${column.id}`"
                                :name="`attribute-${column.id}`"
                                :on-select="selection => setEntitySearchResult(selection, newTableCols, column.id)"
                                :value="newTableCols[column.id] ? newTableCols[column.id].name : ''">
                            </entity-search>
                        </div>
                    </td>
                    <td>
                        <button type="button" :disabled="disabled" class="btn btn-success btn-sm" @click="addTableRow(newTableCols, attribute.columns)">
                            <i class="fas fa-fw fa-plus"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td v-for="column in attribute.columns">
                        <form class="d-flex flex-column" v-if="chartShown">
                            <div class="form-check" v-show="column.datatype == 'integer'">
                                <input class="form-check-input" type="checkbox" :id="`include-cb-${column.id}`" v-model="chartSet[column.id]" @change="updateChart()" />
                                <label class="form-check-label" :for="`include-cb-${column.id}`">
                                    {{ $t('main.entity.attributes.table.chart.include_in') }}
                                </label>
                            </div>
                            <div class="form-check" v-show="column.datatype == 'integer'">
                                <input class="form-check-input" type="checkbox" :id="`difference-cb-${column.id}`" v-model="chartAcc[column.id]" @change="updateChart()" />
                                <label class="form-check-label" :for="`difference-cb-${column.id}`">
                                    {{ $t('main.entity.attributes.table.chart.use_difference') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" :id="`label-radio-${column.id}`" :value="column.id" v-model="chartLabel" @change="updateChart()" />
                                <label class="form-check-label" :for="`label-radio-${column.id}`">
                                    {{ $t('main.entity.attributes.table.chart.use_as_label') }}
                                </label>
                            </div>
                        </form>
                    </td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm" @click="toggleChart">
                            <i class="fas fa-fw fa-chart-bar"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div v-show="chartShown">
            <div class="d-flex flex-row">
                <input type="range" class="form-control-range" min="1" :max="value.length" step="1" v-model.number="chartSetLength" @change="updateChart()" />
                <span>
                    {{ $t('main.entity.attributes.table.chart.number_sets', {cnt: chartSetLength}) }}
                </span>
            </div>
            <canvas :id="chartId"></canvas>
        </div>
    </div>
</template>

<script>
    import Chart from 'chart.js';

    export default {
        $_veeValidate: {
            // value getter
            value () {
                return this.$el.value;
            },
            // name getter
            name () {
                return this.name;
            }
        },
        props: {
            name: String,
            value: {
                type: Array,
                default: _ => new Array(),
            },
            selections: {
                type: Object,
            },
            attribute: {
                type: Object,
            },
            disabled: {
                type: Boolean,
            },
            onChange: {
                type: Function,
                required: true,
            }
        },
        mounted () {
            this.$el.value = this.value;
            for(let k in this.attribute.columns) {
                const c = this.attribute.columns[k];
                Vue.set(this.newTableCols, c.id, null);
            }
        },
        methods: {
            emitExpandToggle() {
                this.expanded = !this.expanded;
                this.$emit('expanded', {
                    id: this.attribute.id,
                    state: this.expanded
                });
            },
            toggleChart() {
                if(this.chartShown) {
                    this.chartShown = false;
                    if(this.chartCtx) this.chartCtx.destroy();
                    this.chartCtx = null;
                } else {
                    this.chartShown = true;
                }
            },
            updateChart() {
                if(this.chartCtx) this.chartCtx.destroy();
                this.chartCtx = new Chart(this.chartId, {
                    type: 'bar',
                    data: this.chartData,
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });
            },
            onInput(field, value) {
                this.$emit('input', value);
                if(field != null) {
                    // an entry in an existing row has been changed
                    // replacing a single value is not supported
                    // therefore the whole row will be replaced
                    value = this.value[field];
                }
                this.onChange(field, value);
            },
            setEntitySearchResult(result, row, column, field) {
                if(result) {
                    Vue.set(row, column, result);
                } else {
                    Vue.delete(row, column);
                }
                if(field) {
                    this.onInput(field, result);
                }
            },
            addTableRow(row) {
                this.value.push(_clone(row));
                for(let k in row) {
                    Vue.delete(row, k);
                }
                this.onInput(null, this.value);
            },
            deleteTableRow(index) {
                this.value.splice(index, 1);
                this.onInput(null, this.value);
            },
            translateLabel(element, prop) {
                return this.$translateLabel(element, prop);
            },
        },
        data() {
            return {
                newTableCols: {},
                expanded: false,
                chartShown: false,
                chartId: 'chart-container',
                chartCtx: null,
                chartLabel: -1,
                chartSet: {},
                chartAcc: {},
                chartSetLength: Math.min(7, this.value.length),
            }
        },
        computed: {
            chartData() {
                if(!this.chartShown) return;
                if(this.chartLabel === -1) return;
                let lastValues = this.value.slice(-(this.chartSetLength+1));
                let datasets = [];
                for(let i=1; i<lastValues.length; i++) {
                    let j = 0;
                    for(let k in this.chartSet) {
                        if(!this.chartSet[k]) continue;
                        const label = this.attribute.columns[k];
                        if(!datasets[j]) {
                            datasets[j] = {
                                data: [],
                                backgroundColor: `rgba(${Math.floor(Math.random()*256)}, ${Math.floor(Math.random()*256)}, ${Math.floor(Math.random()*256)}, 0.25)`,
                                label: `${this.$translateConcept(label.thesaurus_url)}`
                            }
                        }
                        let value;
                        if(this.chartAcc[k]) {
                            value = lastValues[i][k] - lastValues[i-1][k];
                        } else {
                            value = lastValues[i][k];
                        }
                        datasets[j].data.push(value);
                        j++;
                    }
                }
                const labels = lastValues.slice(1).map(v => v[this.chartLabel]);
                return {
                    labels: labels,
                    datasets: datasets
                };
            }
        }
    }
</script>
