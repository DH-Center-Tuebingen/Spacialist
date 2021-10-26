<template>
    <div>
        <table class="table table-striped table-hovered table-sm mb-0">
            <thead class="thead-light">
                <tr>
                    <th v-for="(column, i) in state.columns" :key="i">
                        {{ translateConcept(column.thesaurus_url) }}
                    </th>
                    <th>
                        <a class="text-body" href="#" @click.prevent="emitExpandToggle()">
                            <i class="fas fa-fw fa-expand"></i>
                        </a>
                        <a class="text-body" href="#" @click.prevent="openCsvUpload()">
                            <i class="fas fa-fw fa-file-upload"></i>
                        </a>
                        <a class="text-body" href="#" @click.prevent="downloadAs('csv')">
                            <i class="fas fa-fw fa-file-download"></i>
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, $index) in v.value" :key="$index">
                    <td v-for="(column, i) in state.columns" :key="i">
                        <string-attribute
                            v-if="column.datatype == 'string'"
                            :ref="el => setRef(el, `${$index}_${column.id}`)"
                            :disabled="disabled || row.mark_deleted"
                            :name="`${name}-column-attr-${column.id}`"
                            :value="row[column.id]"
                            @change="e => updateDirtyState(e, $index, column.id)" />

                        <integer-attribute
                            v-else-if="column.datatype == 'integer'"
                            :ref="el => setRef(el, `${$index}_${column.id}`)"
                            :disabled="disabled || row.mark_deleted"
                            :name="`${name}-column-attr-${column.id}`"
                            :value="row[column.id]"
                            @change="e => updateDirtyState(e, $index, column.id)" />

                        <float-attribute
                            v-else-if="column.datatype == 'double'"
                            :ref="el => setRef(el, `${$index}_${column.id}`)"
                            :disabled="disabled || row.mark_deleted"
                            :name="`${name}-column-attr-${column.id}`"
                            :value="row[column.id]"
                            @change="e => updateDirtyState(e, $index, column.id)" />

                        <bool-attribute
                            v-else-if="column.datatype == 'boolean'"
                            :ref="el => setRef(el, `${$index}_${column.id}`)"
                            :disabled="disabled || row.mark_deleted"
                            :name="`${name}-column-attr-${column.id}`"
                            :value="row[column.id]"
                            @change="e => updateDirtyState(e, $index, column.id)" />

                        <iconclass-attribute
                            v-else-if="column.datatype == 'iconclass'"
                            :ref="el => setRef(el, `${$index}_${column.id}`)"
                            :disabled="disabled || row.mark_deleted"
                            :name="`${name}-column-attr-${column.id}`"
                            :value="row[column.id]"
                            :attribute="element"
                            @change="e => updateDirtyState(e, $index, column.id)" />

                        <rism-attribute
                            v-else-if="column.datatype == 'rism'"
                            :ref="el => setRef(el, `${$index}_${column.id}`)"
                            :disabled="disabled || row.mark_deleted"
                            :name="`${name}-column-attr-${column.id}`"
                            :value="row[column.id]"
                            :attribute="element"
                            @change="e => updateDirtyState(e, $index, column.id)" />

                        <entity-attribute v-else-if="column.datatype == 'entity'"
                            :ref="el => setRef(el, `${$index}_${column.id}`)"
                            :disabled="disabled || row.mark_deleted"
                            :name="`${name}-column-attr-${column.id}`"
                            :value="row[column.id]"
                            @change="e => updateDirtyState(e, $index, column.id)" />

                        <date-attribute
                            v-else-if="column.datatype == 'date'"
                            :ref="el => setRef(el, `${$index}_${column.id}`)"
                            :disabled="disabled || row.mark_deleted"
                            :name="`${name}-column-attr-${column.id}`"
                            :value="row[column.id]"
                            @change="e => updateDirtyState(e, $index, column.id)" />

                        <singlechoice-attribute
                            v-else-if="column.datatype == 'string-sc'"
                            :ref="el => setRef(el, `${$index}_${column.id}`)"
                            :disabled="disabled || row.mark_deleted"
                            :name="`${name}-column-attr-${column.id}`"
                            :value="row[column.id]"
                            :selections="state.selections[column.id]"
                            @change="e => updateDirtyState(e, $index, column.id)" />
                    </td>
                    <td v-if="!disabled" class="text-center">
                        <div class="dropdown">
                            <span :id="`tabular-row-options-${$index}`" class="clickable" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-fw fa-ellipsis-h"></i>
                            </span>
                            <div class="dropdown-menu" :aria-labelledby="`tabular-row-options-${$index}`">
                                <a class="dropdown-item" href="#" @click.prevent="resetRow($index)">
                                    <i class="fas fa-fw fa-undo text-info"></i> {{ t('global.reset') }}
                                </a>
                                <a class="dropdown-item" href="#" v-if="row.mark_deleted" @click.prevent="restoreTableRow($index)">
                                    <i class="fas fa-fw fa-trash-restore text-warning"></i> {{ t('global.restore') }}
                                </a>
                                <a class="dropdown-item" href="#" v-else @click.prevent="markTableRowForDelete($index)">
                                    <i class="fas fa-fw fa-trash text-danger"></i> {{ t('global.delete') }}
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr v-if="!disabled">
                    <td v-for="(column, i) in state.columns" :key="i">
                        <string-attribute
                            v-if="column.datatype == 'string'"
                            :ref="el => setAddRef(el, `${column.id}`)"
                            :name="`${name}-new-column-attr-${column.id}`"
                            :value="state.newRowColumns[column.id]" />

                        <integer-attribute
                            v-else-if="column.datatype == 'integer'"
                            :ref="el => setAddRef(el, `${column.id}`)"
                            :name="`${name}-new-column-attr-${column.id}`"
                            :value="state.newRowColumns[column.id]" />

                        <float-attribute
                            v-else-if="column.datatype == 'double'"
                            :ref="el => setAddRef(el, `${column.id}`)"
                            :name="`${name}-new-column-attr-${column.id}`"
                            :value="state.newRowColumns[column.id]" />

                        <bool-attribute
                            v-else-if="column.datatype == 'boolean'"
                            :ref="el => setAddRef(el, `${column.id}`)"
                            :name="`${name}-new-column-attr-${column.id}`"
                            :value="state.newRowColumns[column.id]" />

                        <iconclass-attribute
                            v-else-if="column.datatype == 'iconclass'"
                            :ref="el => setAddRef(el, `${column.id}`)"
                            :name="`${name}-new-column-attr-${column.id}`"
                            :value="state.newRowColumns[column.id]"
                            :attribute="column" />

                        <rism-attribute
                            v-else-if="column.datatype == 'rism'"
                            :ref="el => setAddRef(el, `${column.id}`)"
                            :name="`${name}-new-column-attr-${column.id}`"
                            :value="state.newRowColumns[column.id]"
                            :attribute="column" />

                        <entity-attribute v-else-if="column.datatype == 'entity'"
                            :ref="el => setAddRef(el, `${column.id}`)"
                            :name="`${name}-new-column-attr-${column.id}`"
                            :value="state.newRowColumns[column.id]" />

                        <date-attribute
                            v-else-if="column.datatype == 'date'"
                            :ref="el => setAddRef(el, `${column.id}`)"
                            :name="`${name}-new-column-attr-${column.id}`"
                            :value="state.newRowColumns[column.id]" />

                        <singlechoice-attribute
                            v-else-if="column.datatype == 'string-sc'"
                            :ref="el => setAddRef(el, `${column.id}`)"
                            :name="`${name}-new-column-attr-${column.id}`"
                            :value="state.newRowColumns[column.id]"
                            :selections="state.selections[column.id]" />
                    </td>
                    <td>
                        <button type="button" class="btn btn-success btn-sm" @click="addTableRow()">
                            <i class="fas fa-fw fa-plus"></i>
                        </button>
                    </td>
                </tr>
                <tr class="border-0">
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
        watch,
    } from 'vue';

    import { useField } from 'vee-validate';

    import * as yup from 'yup';

    import {
        Chart,
        LinearScale,
    } from 'chart.js';

    import { useI18n } from 'vue-i18n';
    import store from '../../bootstrap/store.js';

    import {
      createDownloadLink,
        getAttribute,
        slugify,
        translateConcept,
        _cloneDeep,
    } from '../../helpers/helpers.js';

    import {
        showCsvPreviewer,
        showCsvColumnPicker,
    } from '../../helpers/modal.js';

    import StringAttr from './String.vue';
    import IntegerAttr from './Integer.vue';
    import FloatAttr from './Float.vue';
    import Bool from './Bool.vue';
    import Iconclass from './Iconclass.vue';
    import RISM from './Rism.vue';
    import Entity from './Entity.vue';
    import DateAttr from './Date.vue';
    import SingleChoice from './SingleChoice.vue';

    let d3 = require('d3-dsv');

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
            'rism-attribute': RISM,
            'entity-attribute': Entity,
            'date-attribute': DateAttr,
            'singlechoice-attribute': SingleChoice,
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
            const resetFieldState = _ => {
                v.resetField({
                    value: value.value
                });
                for(let k in columnRefs) {
                    const curr = columnRefs[k];
                    if(!!curr && !!curr.v && curr.v.meta.dirty && !!curr.resetFieldState) {
                        curr.resetFieldState();
                    }
                }
                state.deletedRows = {};
            };
            const undirtyField = _ => {
                v.resetField({
                    value: v.value.filter(cv => !cv.mark_deleted),
                });
                for(let k in columnRefs) {
                    const curr = columnRefs[k];
                    if(!!curr.v && curr.v.meta.dirty && !!curr.undirtyField) {
                        curr.undirtyField();
                    }
                }
            };
            
            const getSimpleValue = (datatype, valueObject) => {
                switch(datatype) {
                    case 'string':
                    case 'integer':
                    case 'double':
                    case 'boolean':
                    case 'date':
                    case 'iconclass':
                    case 'entity':
                        return valueObject;
                    case 'string-sc':
                        return translateConcept(valueObject.concept_url);
                }
            };
            const openCsvUpload = _ => {
                showCsvPreviewer(null, csv => {
                    showCsvColumnPicker({
                        max: Object.keys(state.columns).length,
                        force_max: false,
                        selection: csv.header,
                    }, columns => {
                        addTableRowFromCsv(columns, csv.data);
                    });
                });
            };
            const downloadAs = (type) => {
                let content = '';
                let contentType = 'text/plain';
                let filename = slugify(translateConcept(attribute.value.thesaurus_url));
                switch(type) {
                    case 'csv':
                    default:
                        const data = v.value.map(r => {
                            const newR = {};
                            for(let k in r) {
                                newR[translateConcept(state.columns[k].thesaurus_url)] = getSimpleValue(state.columns[k].datatype, r[k]);
                            }
                            return newR;
                        });
                        content = d3.csvFormat(data);
                        contentType = 'text/csv';
                        filename += '.csv';
                        break;
                }
                createDownloadLink(content, filename, false, contentType);
            };
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
            const addTableRowFromCsv = (columns, data) => {
                const rows = [];
                for(let i=0; i<data.length; i++) {
                    const rowValue = {};
                    const curr = data[i];
                    let colIdx = 0;
                    for(let k in state.columns) {
                        rowValue[k] = curr[columns[colIdx]];
                        colIdx++;
                        // If less columns selected than exist, stop adding new/non-existing column data
                        if(colIdx == columns.length) break;
                    }
                    rows.push(rowValue);
                }
                v.handleChange(v.value.concat(rows));
            };
            const addTableRow = _ => {
                const rowValue = {};
                for(let k in state.columns) {
                    const reference = newRowRefs[k];
                    if(!!reference.v.value) {
                        rowValue[k] = reference.v.value;
                        if(!!reference.resetFieldState) {
                            reference.resetFieldState();
                        }
                    }
                }
                v.handleChange(v.value.concat([rowValue]));
                state.newRowColumns = {};
            };
            const restoreTableRow = index => {
                const currentValue = v.value;
                delete currentValue[index].mark_deleted;
                v.handleChange(currentValue);
            };
            const markTableRowForDelete = index => {
                const currentValue = _cloneDeep(v.value);
                currentValue[index].mark_deleted = true;
                v.handleChange(currentValue);
            };
            const resetRow = index => {
                for(let k in state.columns) {
                    const reference = columnRefs[`${index}_${state.columns[k].id}`];
                    if(!!reference.resetFieldState) {
                        reference.resetFieldState();
                    }
                }
                restoreTableRow(index);
            };
            const updateDirtyState = (e, rowIdx, columnId) => {
                const currentValue = _cloneDeep(v.value);
                currentValue[rowIdx][columnId] = e.value;
                v.handleChange(currentValue);
                context.emit('change', e);
            };
            const setAddRef = (el, idx) => {
                newRowRefs[idx] = el;
            };
            const setRef = (el, idx) => {
                columnRefs[idx] = el;
            };

            // DATA
            const columnRefs = ref({});
            const newRowRefs = ref({});
            const {
                handleChange,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`tabular_${name.value}`, yup.array(), {
                initialValue: value.value,
            });
            const state = reactive({
                columns: computed(_ => getAttribute(attribute.value.id).columns),
                selections: computed(_ => {
                    const list = {};
                    if(!state.columns) return list;

                    for(let k in state.columns) {
                        const curr = state.columns[k];
                        if(curr.datatype == 'string-sc' || curr.datatype == 'string-mc') {
                            list[curr.id] = store.getters.attributeSelections[curr.id];
                        }
                    }
                    return list;
                }),
                newRowColumns: {},
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
            const v = reactive({
                value: fieldValue,
                handleChange,
                meta,
                resetField,
            });

            watch(v.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: v.value,
                });
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
                resetFieldState,
                undirtyField,
                openCsvUpload,
                downloadAs,
                emitExpandToggle,
                toggleChart,
                updateChart,
                setEntitySearchResult,
                addTableRow,
                restoreTableRow,
                markTableRowForDelete,
                resetRow,
                updateDirtyState,
                setAddRef,
                setRef,
                resetFieldState,
                undirtyField,
                // PROPS
                name,
                disabled,
                attribute,
                selections,
                // STATE
                state,
                v,
            }
        },
        // mounted () {
        //     this.$el.value = this.value;
        //     for(let k in this.attribute.columns) {
        //         const c = this.attribute.columns[k];
        //         Vue.set(this.newRowColumns, c.id, null);
        //     }
        // },
    }
</script>
