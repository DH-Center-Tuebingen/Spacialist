<template>
    <div>
        <table class="table table-striped table-hovered table-sm">
            <col style="width: 33%"/>
            <col style="width: 33%"/>
            <col style="width: 33%"/>
            <col style="width: 1%"/>
            <thead class="thead-light">
                <tr>
                    <th v-for="(column, i) in state.columns" :key="i">
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
                <tr v-for="(row, $index) in state.locValue" :key="$index">
                    <td v-for="(column, i) in state.columns" :key="i">
                        <string-attribute
                            v-if="column.datatype == 'string'"
                            :ref="el => setRef(el, `${$index}_${i}`)"
                            :disabled="disabled || state.deletedRows[$index]"
                            :name="`attr-${column.id}`"
                            :value="row[column.id]"
                            @change="updateDirtyState" />

                        <integer-attribute
                            v-else-if="column.datatype == 'integer'"
                            :ref="el => setRef(el, `${$index}_${i}`)"
                            :disabled="disabled || state.deletedRows[$index]"
                            :name="`attr-${column.id}`"
                            :value="row[column.id]"
                            @change="updateDirtyState" />

                        <float-attribute
                            v-else-if="column.datatype == 'double'"
                            :ref="el => setRef(el, `${$index}_${i}`)"
                            :disabled="disabled || state.deletedRows[$index]"
                            :name="`attr-${column.id}`"
                            :value="row[column.id]"
                            @change="updateDirtyState" />

                        <bool-attribute
                            v-else-if="column.datatype == 'boolean'"
                            :ref="el => setRef(el, `${$index}_${i}`)"
                            :disabled="disabled || state.deletedRows[$index]"
                            :name="`attr-${column.id}`"
                            :value="row[column.id]"
                            @change="updateDirtyState" />

                        <iconclass-attribute
                            v-else-if="column.datatype == 'iconclass'"
                            :ref="el => setRef(el, `${$index}_${i}`)"
                            :disabled="disabled || state.deletedRows[$index]"
                            :name="`attr-${column.id}`"
                            :value="row[column.id]"
                            :attribute="element"
                            @change="updateDirtyState" />

                        <entity-attribute v-else-if="column.datatype == 'entity'"
                            :ref="el => setRef(el, `${$index}_${i}`)"
                            :disabled="disabled || state.deletedRows[$index]"
                            :name="`attr-${column.id}`"
                            :value="row[column.id]"
                            @change="updateDirtyState" />

                        <date-attribute
                            v-else-if="column.datatype == 'date'"
                            :ref="el => setRef(el, `${$index}_${i}`)"
                            :disabled="disabled || state.deletedRows[$index]"
                            :name="`attr-${column.id}`"
                            :value="row[column.id]"
                            @change="updateDirtyState" />

                        <singlechoice-attribute
                            v-else-if="column.datatype == 'string-sc'"
                            :ref="el => setRef(el, `${$index}_${i}`)"
                            :disabled="disabled || state.deletedRows[$index]"
                            :name="`attr-${column.id}`"
                            :value="row[column.id]"
                            @change="updateDirtyState" />

                        <multichoice-attribute
                            v-else-if="column.datatype == 'string-mc'"
                            :ref="el => setRef(el, `${$index}_${i}`)"
                            :disabled="disabled || state.deletedRows[$index]"
                            :name="`attr-${column.id}`"
                            :value="row[column.id]"
                            :selections="{}"
                            @change="updateDirtyState" />
                    </td>
                    <td>
                        <button v-if="state.deletedRows[$index]" type="button" class="btn btn-warning btn-sm" @click="restoreTableRow($index)">
                            <i class="fas fa-fw fa-undo"></i>
                        </button>
                        <button v-else type="button" :disabled="disabled" class="btn btn-danger btn-sm" @click="deleteTableRow($index)">
                            <i class="fas fa-fw fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr v-if="!disabled">
                    <td v-for="(column, i) in state.columns" :key="i">
                        <string-attribute
                            v-if="column.datatype == 'string'"
                            :name="`attr-${column.id}`"
                            :value="state.newTableCols[column.id]" />

                        <integer-attribute
                            v-else-if="column.datatype == 'integer'"
                            :name="`attr-${column.id}`"
                            :value="state.newTableCols[column.id]" />

                        <float-attribute
                            v-else-if="column.datatype == 'double'"
                            :name="`attr-${column.id}`"
                            :value="state.newTableCols[column.id]" />

                        <bool-attribute
                            v-else-if="column.datatype == 'boolean'"
                            :name="`attr-${column.id}`"
                            :value="state.newTableCols[column.id]" />

                        <iconclass-attribute
                            v-else-if="column.datatype == 'iconclass'"
                            :name="`attr-${column.id}`"
                            :value="state.newTableCols[column.id]"
                            :attribute="column" />

                        <entity-attribute v-else-if="column.datatype == 'entity'"
                            :name="`attr-${column.id}`"
                            :value="state.newTableCols[column.id]" />

                        <date-attribute
                            v-else-if="column.datatype == 'date'"
                            :name="`attr-${column.id}`"
                            :value="state.newTableCols[column.id]" />

                        <singlechoice-attribute
                            v-else-if="column.datatype == 'string-sc'"
                            :name="`attr-${column.id}`"
                            :value="state.newTableCols[column.id]" />

                        <multichoice-attribute
                            v-else-if="column.datatype == 'string-mc'"
                            :name="`attr-${column.id}`"
                            :value="state.newTableCols[column.id]"
                            :selections="{}" />
                    </td>
                    <td>
                        <button type="button" class="btn btn-success btn-sm" @click="addTableRow(newTableCols, state.columns)">
                            <i class="fas fa-fw fa-plus"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td v-for="(column, i) in state.columns" :key="i">
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
        ref,
        toRefs,
    } from 'vue';

    import {
        Chart,
        LinearScale,
    } from 'chart.js';

    import { useI18n } from 'vue-i18n';

    import {
        getAttribute,
        translateConcept,
    } from '../../helpers/helpers.js';

    import StringAttr from './String.vue';
    import IntegerAttr from './Integer.vue';
    import FloatAttr from './Float.vue';
    import Bool from './Bool.vue';
    import Iconclass from './Iconclass.vue';
    import Entity from './Entity.vue';
    import DateAttr from './Date.vue';
    import SingleChoice from './SingleChoice.vue';
    import MultiChoice from './MultiChoice.vue';

    export default {
        props: {
            name: {
                type: String,
                required: true,
            },
            disabled: {
                type: Boolean,
                required: false,
                default: false,
            },
            value: {
                type: Array,
                required: false,
                default: _ => new Array(),
            },
            selections: {
                type: Object,
            },
            attribute: {
                type: Object,
            },
        },
        components: {
            'string-attribute': StringAttr,
            'integer-attribute': IntegerAttr,
            'float-attribute': FloatAttr,
            'bool-attribute': Bool,
            'iconclass-attribute': Iconclass,
            'entity-attribute': Entity,
            'date-attribute': DateAttr,
            'singlechoice-attribute': SingleChoice,
            'multichoice-attribute': MultiChoice,
        },
        emits: ['change', 'expanded'],
        setup(props, context) {
            const { t } = useI18n();

            Chart.register(LinearScale);

            const {
                name,
                disabled,
                value,
                selections,
                attribute,
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
                    if(state.chartCtx) state.chartCtx.destroy();
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
            const setEntitySearchResult = (result, row, column, field) => {
                if(result) {
                    row[column] = result;
                } else {
                    delete row[column];
                }
            };
            const addTableRow = row => {
                this.value.push(_clone(row));
                for(let k in row) {
                    delete row[k];
                }
            };
            const restoreTableRow = index => {
                delete state.deletedRows[index];
                console.log("would restore", state.locValue[index]);
            };
            const deleteTableRow = index => {
                state.deletedRows[index] = true;
                console.log("would delete", state.locValue[index]);
            };
            const storeData = _ => {
                state.locValue = state.locValue.filter((v, i) => {
                    return !state.deletedRows[i];
                });
                state.deletedRows = {};
            };
            const updateDirtyState = e => {
                context.emit('change', e);
            };
            const setRef = (el, idx) => {
                columnRefs[idx] = el;
            };

            // DATA
            const columnRefs = ref({});
            const state = reactive({
                locValue: value.value.slice(),
                columns: computed(_ => getAttribute(attribute.value.id).columns),
                newTableCols: {},
                deletedRows: {},
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
                addTableRow,
                restoreTableRow,
                deleteTableRow,
                storeData,
                updateDirtyState,
                setRef,
                // PROPS
                disabled,
                value,
                attribute,
                selections,
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
