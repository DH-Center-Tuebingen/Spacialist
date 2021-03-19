<template>
    <div>
        <table class="table table-striped table-hovered table-sm">
            <thead class="thead-light">
                <tr>
                    <th v-for="(column, i) in attribute.columns" :key="i">
                        {{ translateConcept(column.thesaurus_url) }}
                    </th>
                    <th>
                        <button type="button" class="btn btn-outline-secondary btn-sm" @click="emitExpandToggle()">
                            <i class="fas fa-fw fa-expand"></i>
                        </button>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, $index) in value" :key="$index">
                    <td v-for="(column, i) in attribute.columns" :key="i">
                        <input class="form-control form-control-sm" v-if="column.datatype == 'string'" type="text" :disabled="disabled" v-model="row[column.id]" @input="onInput($index, $event.target.value)"/>
                        <input class="form-control form-control-sm" v-else-if="column.datatype == 'double'" type="number" :disabled="disabled" step="any" min="0" placeholder="0.0" v-model.number="row[column.id]" @input="onInput($index, $event.target.value)"/>
                        <input class="form-control form-control-sm" v-else-if="column.datatype == 'integer'" type="number" :disabled="disabled" step="1" placeholder="0" v-model.number="row[column.id]" @input="onInput($index, $event.target.value)"/>
                        <input class="form-control form-control-sm" v-else-if="column.datatype == 'boolean'" type="checkbox" :disabled="disabled" v-model="row[column.id]" @input="onInput($index, $event.target.value)"/>
                        <div v-else-if="column.datatype == 'string-sc'">
                            <multiselect
                                class="multiselect-sm"
                                v-model="row[column.id]"
                                :mode="'tags'"
                                :label="'concept_url'"
                                :track-by="'concept_url'"
                                :valueProp="'id'"
                                :disabled="disabled"
                                :options="selections[column.id] || []"
                                :placeholder="t('global.select.placeholder')"
                                @input="onInput($index, $event.target)">
                            </multiselect>
                        </div>
                        <div v-else-if="column.datatype == 'entity'">
                            <entity-search
                                :id="`attr-${column.id}`"
                                :name="`attr-${column.id}`"
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
                    <td v-for="(column, i) in attribute.columns" :key="i">
                        <input class="form-control form-control-sm" v-if="column.datatype == 'string'" type="text" :disabled="disabled" v-model="state.newTableCols[column.id]"/>
                        <input class="form-control form-control-sm" v-else-if="column.datatype == 'double'" type="number" :disabled="disabled" step="any" min="0" placeholder="0.0" v-model.number="state.newTableCols[column.id]"/>
                        <input class="form-control form-control-sm" v-else-if="column.datatype == 'integer'" type="number" :disabled="disabled" step="1" placeholder="0" v-model.number="state.newTableCols[column.id]"/>
                        <input class="form-control form-control-sm" v-else-if="column.datatype == 'boolean'" type="checkbox" :disabled="disabled" v-model="state.newTableCols[column.id]"/>
                        <div v-else-if="column.datatype == 'string-sc'">
                            <multiselect
                                class="multiselect-sm"
                                v-model="state.newTableCols[column.id]"
                                :label="'concept_url'"
                                :track-by="'concept_url'"
                                :mode="'tags'"
                                :disabled="disabled"
                                :options="selections[column.id] || []"
                                :placeholder="t('global.select.placeholder')">
                            </multiselect>
                        </div>
                        <div v-else-if="column.datatype == 'entity'">
                            <entity-search
                                :id="`attr-${column.id}`"
                                :name="`attr-${column.id}`"
                                :on-select="selection => setEntitySearchResult(selection, state.newTableCols, column.id)"
                                :value="state.newTableCols[column.id] ? state.newTableCols[column.id].name : ''">
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
                    <td v-for="(column, i) in attribute.columns" :key="i">
                        <form class="d-flex flex-column" v-if="state.chartShown">
                            <div class="form-check" v-show="column.datatype == 'integer'">
                                <input class="form-check-input" type="checkbox" :id="`include-cb-${column.id}`" v-model="state.chartSet[column.id]" @change="updateChart()" />
                                <label class="form-check-label" :for="`include-cb-${column.id}`">
                                    {{ t('main.entity.attributes.table.chart.include_in') }}
                                </label>
                            </div>
                            <div class="form-check" v-show="column.datatype == 'integer'">
                                <input class="form-check-input" type="checkbox" :id="`difference-cb-${column.id}`" v-model="state.chartAcc[column.id]" @change="updateChart()" />
                                <label class="form-check-label" :for="`difference-cb-${column.id}`">
                                    {{ t('main.entity.attributes.table.chart.use_difference') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" :id="`label-radio-${column.id}`" :value="column.id" v-model="state.chartLabel" @change="updateChart()" />
                                <label class="form-check-label" :for="`label-radio-${column.id}`">
                                    {{ t('main.entity.attributes.table.chart.use_as_label') }}
                                </label>
                            </div>
                        </form>
                    </td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm" @click="toggleChart()">
                            <i class="fas fa-fw fa-chart-bar"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div v-show="state.chartShown">
            <div class="d-flex flex-row">
                <input type="range" class="form-range" min="1" :max="value.length" step="1" v-model.number="state.chartSetLength" @change="updateChart()" />
                <span>
                    {{ t('main.entity.attributes.table.chart.number_sets', {cnt: state.chartSetLength}) }}
                </span>
            </div>
            <canvas :id="state.chartId"></canvas>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
    } from 'vue';

    import {
        Chart,
        LinearScale,
    } from 'chart.js';

    import { useI18n } from 'vue-i18n';

    import {
        translateConcept,
    } from '../../helpers/helpers.js';

    export default {
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
        emits: ['expanded'],
        setup(props, context) {
            const { t } = useI18n();

            Chart.register(LinearScale);

            const {
                name,
                value,
                selections,
                attribute,
                disabled,
                onChange,
            } = toRefs(props);

            // FETCH

            // FUNCTIONS
            const emitExpandToggle = _ => {
                state.expanded = !state.expanded;
                context.emit('expanded', {
                    id: attribute.value.id,
                    state: state.expanded,
                });
            };
            const toggleChart = _ => {
                if(state.chartShown) {
                    state.chartShown = false;
                    if(thstateis.chartCtx) state.chartCtx.destroy();
                    state.chartCtx = null;
                } else {
                    state.chartShown = true;
                }
            };
            const updateChart = _ => {
                if(state.chartCtx) state.chartCtx.destroy();
                state.chartCtx = new Chart(state.chartId, {
                    type: 'bar',
                    data: state.chartData,
                    options: {
                        scales: {
                            y: {
                                ticks: {
                                    beginAtZero: true
                                }
                            },
                        }
                    }
                });
            };
            const onInput = (field, value) => {
                this.$emit('input', value);
                if(field != null) {
                    // an entry in an existing row has been changed
                    // replacing a single value is not supported
                    // therefore the whole row will be replaced
                    value = this.value[field];
                }
                this.onChange(field, value);
            };
            const setEntitySearchResult = (result, row, column, field) => {
                if(result) {
                    row[column] = result;
                } else {
                    delete row[column];
                }
                if(field) {
                    onInput(field, result);
                }
            };
            const addTableRow = row => {
                this.value.push(_clone(row));
                for(let k in row) {
                    delete row[k];
                }
                onInput(null, this.value);
            };
            const deleteTableRow = index => {
                this.value.splice(index, 1);
                onInput(null, this.value);
            };

            // DATA
            const state = reactive({
                locValue: value,
                newTableCols: {},
                expanded: false,
                chartShown: false,
                chartId: 'chart-container',
                chartCtx: null,
                chartLabel: -1,
                chartSet: {},
                chartAcc: {},
                chartSetLength: Math.min(7, value.value.length),
                chartData: computed(_ => {
                    if(!state.chartShown) return;
                    if(state.chartLabel === -1) return;
                    let lastValues = value.value.slice(-(state.chartSetLength+1));
                    let datasets = [];
                    for(let i=1; i<lastValues.length; i++) {
                        let j = 0;
                        for(let k in state.chartSet) {
                            if(!state.chartSet[k]) continue;
                            const label = attribute.value.columns[k];
                            if(!datasets[j]) {
                                datasets[j] = {
                                    data: [],
                                    backgroundColor: `rgba(${Math.floor(Math.random()*256)}, ${Math.floor(Math.random()*256)}, ${Math.floor(Math.random()*256)}, 0.25)`,
                                    label: `${translateConcept(label.thesaurus_url)}`
                                }
                            }
                            let value;
                            if(state.chartAcc[k]) {
                                value = lastValues[i][k] - lastValues[i-1][k];
                            } else {
                                value = lastValues[i][k];
                            }
                            datasets[j].data.push(value);
                            j++;
                        }
                    }
                    const labels = lastValues.slice(1).map(v => v[state.chartLabel]);
                    return {
                        labels: labels,
                        datasets: datasets
                    };
                }),
            });

            // ON MOUNTED
            onMounted(_ => {

            });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                // LOCAL
                emitExpandToggle,
                toggleChart,
                updateChart,
                setEntitySearchResult,
                // PROPS
                disabled,
                // STATE
                state,
            }
        },
        // mounted () {
        //     this.$el.value = this.value;
        //     for(let k in this.attribute.columns) {
        //         const c = this.attribute.columns[k];
        //         Vue.set(this.newTableCols, c.id, null);
        //     }
        // },
    }
</script>
