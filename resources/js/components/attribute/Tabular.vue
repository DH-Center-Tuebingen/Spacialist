<template>
    <div>
        <table class="table table-striped table-hovered table-sm mb-0">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th
                        v-for="(column, i) in state.columns"
                        :key="i"
                    >
                        {{ translateConcept(column.thesaurus_url) }}
                    </th>
                    <th>
                        <a
                            class="text-body"
                            href="#"
                            @click.prevent="emitExpandToggle()"
                        >
                            <i class="fas fa-fw fa-expand" />
                        </a>
                        <a
                            class="text-body"
                            href="#"
                            @click.prevent="openCsvUpload()"
                        >
                            <i class="fas fa-fw fa-file-upload" />
                        </a>
                        <a
                            class="text-body"
                            href="#"
                            @click.prevent="downloadAs('csv')"
                        >
                            <i class="fas fa-fw fa-file-download" />
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                <template
                    v-for="(row, $index) in state.actualShow"
                    :key="`tabular-row-${uniqueRowIndex(row, $index)}`"
                >
                    <td
                        v-if="row.hidden_info"
                        class="text-muted text-center fs-5 p-2 bg-primary-subtle"
                        :colspan="state.placeholderWidth"
                        @click="state.showAll = true"
                    >
                        show {{ v.value.length - 20 }} hidden rows…
                    </td>
                    <Row
                        v-else
                        :ref="el => setRef(el, $index)"
                        :data="row"
                        :columns="state.columns"
                        :number="getActualRowIndex($index)"
                        :disabled="disabled"
                        :hide-links="hideLinks"
                        @change="e => updateDirtyState(e, $index)"
                        @delete="markTableRowForDelete($index)"
                        @reset="resetRow($index)"
                        @restore="restoreTableRow($index)"
                    />
                </template>
                <tr v-if="!disabled && !state.isPreview">
                    <td
                        class="text-center"
                        style="--bs-table-striped-bg: var(--bs-body-bg);"
                        :colspan="state.placeholderWidth"
                    >
                        <button
                            type="button"
                            class="btn btn-outline-success btn-sm w-100"
                            @click="addTableRow()"
                        >
                            <i class="fas fa-fw fa-plus" />
                            {{ t('main.entity.attributes.table.add_row') }}
                        </button>
                    </td>
                </tr>
                <tr
                    v-if="!state.isPreview"
                    class="border-0"
                >
                    <td>&nbsp;</td>
                    <td
                        v-for="(column, i) in state.columns"
                        :key="i"
                    >
                        <form
                            v-if="state.chartShown"
                            class="d-flex flex-column"
                        >
                            <div
                                v-show="['integer', 'double'].includes(column.datatype)"
                                class="form-check"
                            >
                                <input
                                    :id="`include-cb-${column.id}`"
                                    v-model="state.chartSet[column.id]"
                                    class="form-check-input"
                                    type="checkbox"
                                    @change="updateChart()"
                                >
                                <label
                                    class="form-check-label"
                                    :for="`include-cb-${column.id}`"
                                >
                                    {{ t('main.entity.attributes.table.chart.include_in') }}
                                </label>
                            </div>
                            <div
                                v-show="['integer', 'double'].includes(column.datatype)"
                                class="form-check"
                            >
                                <input
                                    :id="`difference-cb-${column.id}`"
                                    v-model="state.chartAcc[column.id]"
                                    class="form-check-input"
                                    type="checkbox"
                                    @change="updateChart()"
                                >
                                <label
                                    class="form-check-label"
                                    :for="`difference-cb-${column.id}`"
                                >
                                    {{ t('main.entity.attributes.table.chart.use_difference') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input
                                    :id="`label-radio-${column.id}`"
                                    v-model="state.chartLabel"
                                    class="form-check-input"
                                    type="radio"
                                    :value="column.id"
                                    @change="updateChart()"
                                >
                                <label
                                    class="form-check-label"
                                    :for="`label-radio-${column.id}`"
                                >
                                    {{ t('main.entity.attributes.table.chart.use_as_label') }}
                                </label>
                            </div>
                        </form>
                    </td>
                    <td class="text-center">
                        <button
                            type="button"
                            class="btn btn-primary btn-sm"
                            @click="toggleChart()"
                        >
                            <i class="fas fa-fw fa-chart-bar" />
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div v-show="state.chartShown">
            <div class="d-flex flex-row gap-2">
                <input
                    v-model.number="state.chartSetLength"
                    type="range"
                    class="form-range"
                    min="1"
                    :max="value.length"
                    step="1"
                    @change="updateChart()"
                >
                <span>
                    {{ t('main.entity.attributes.table.chart.number_sets', { cnt: state.chartSetLength }) }}
                </span>
            </div>
            <canvas :id="state.chartId" />
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
        Colors,
        BarController,
        CategoryScale,
        LinearScale,
        BarElement,
        Legend,
        Tooltip,
    } from 'chart.js';

    import { useI18n } from 'vue-i18n';
    import store from '@/bootstrap/store.js';

    import {
        createDownloadLink,
        getTs,
        getAttribute,
        hash,
        slugify,
        translateConcept,
        _cloneDeep,
    } from '@/helpers/helpers.js';

    import {
        showCsvPreviewer,
        showCsvColumnPicker,
    } from '@/helpers/modal.js';

    import Row from '@/components/attribute/TabularRow.vue';

    import * as d3 from 'd3-dsv';

    export default {
        components: {
            Row,
        },
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
            attribute: {
                type: Object,
                default: null
            },
            hideLinks: {
                type: Boolean,
                required: false,
                default: false,
            },
            previewColumns: {
                required: false,
                type: Object,
                default: _ => new Object(),
            },
        },
        emits: ['change', 'expanded'],
        setup(props, context) {
            const { t } = useI18n();

            Chart.register(
                Colors,
                BarController,
                BarElement,
                CategoryScale,
                LinearScale,
                Legend,
                Tooltip,
            );

            const {
                name,
                disabled,
                value,
                selections,
                attribute,
                hideLinks,
                previewColumns,
            } = toRefs(props);

            // FETCH

            const CUT_SIZE = 10;
            const CUT_THRES = (CUT_SIZE * 2) + Math.floor(CUT_SIZE / 2);
            // FUNCTIONS
            const uniqueRowIndex = (row, index) => {
                const idx = getActualRowIndex(index);
                return idx;
                // return hash(JSON.stringify(row)) + idx;
            };
            const resetFieldState = _ => {
                v.resetField({
                    value: value.value
                });
                for(let k in rowRefs.value) {
                    const curr = rowRefs.value[k];
                    if(curr?.v?.meta?.dirty && !!curr.resetFieldState) {
                        curr.resetFieldState();
                    }
                }
                state.deletedRows = {};
            };
            const undirtyField = _ => {
                v.resetField({
                    value: v.value.filter(cv => !cv.mark_deleted && Object.keys(cv).length > 0),
                });
                for(let k in rowRefs.value) {
                    const curr = rowRefs.value[k];
                    if(curr?.v?.meta?.dirty && !!curr.undirtyField) {
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
                    case 'daterange':
                    case 'iconclass':
                    case 'entity':
                    case 'entity-mc':
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
                for(let i = 0; i < data.length; i++) {
                    const rowValue = {};
                    const curr = data[i];
                    let colIdx = 0;
                    for(let column in state.columns) {
                        let value = curr[columns[colIdx]];
                        if(column.datatype == 'si-unit') {
                            rowValue[column] = curr[columns[colIdx]];
                            colIdx++;
                            continue;
                        }
                        rowValue[column] = value;
                        colIdx++;
                        // If less columns selected than exist, stop adding new/non-existing column data
                        if(colIdx == columns.length) break;
                    }
                    rows.push(rowValue);
                }
                v.handleChange(v.value.concat(rows));
            };
            const addTableRow = _ => {
                v.handleChange(v.value.concat([{}]));
            };
            const restoreTableRow = rowIdx => {
                const actualRow = getActualRowIndex(rowIdx);
                const currentValue = v.value;
                delete currentValue[actualRow].mark_deleted;
                v.handleChange(currentValue);
            };
            const markTableRowForDelete = rowIdx => {
                const actualRow = getActualRowIndex(rowIdx);
                const currentValue = _cloneDeep(v.value);
                currentValue[actualRow].mark_deleted = true;
                v.handleChange(currentValue);
            };
            const resetRow = rowIdx => {
                const actualRow = getRowRefIndex(rowIdx);

                const curr = rowRefs.value[actualRow];
                if(curr?.resetFieldState) {
                    curr.resetFieldState();
                }
                if(v.value[actualRow].mark_deleted) {
                    restoreTableRow(rowIdx);
                }
            };
            const getActualRowIndex = idx => {
                if(state.showAll || !state.needsCut) return idx;
                if(idx < CUT_SIZE) return idx;

                // add hidden cut length (total length - size of first and last cut)
                // minus 1 (because hidden row info is added as element)
                return idx + (v.value.length - (CUT_SIZE * 2) - 1);
            };
            const getRowRefIndex = idx => {
                return `row-${getActualRowIndex(idx)}`;
            };
            const updateDirtyState = (e, rowIdx) => {
                const actualRow = getActualRowIndex(rowIdx);
                const currentValue = _cloneDeep(v.value);
                currentValue[actualRow] = e.value;
                v.handleChange(currentValue);
                context.emit('change', e);
            };
            const setRef = (el, idx) => {
                const actualRow = getRowRefIndex(idx);
                rowRefs.value[actualRow] = el;
            };

            // DATA
            const rowRefs = ref({});
            const {
                handleChange,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`tabular_${name.value}`, yup.array(), {
                initialValue: value.value,
            });
            const state = reactive({
                isPreview: computed(_ => previewColumns.value && Object.keys(previewColumns.value).length > 0),
                columns: computed(_ => state.isPreview ? previewColumns.value : getAttribute(attribute.value.id).columns),
                placeholderWidth: computed(_ => {
                    const colCnt = state.columns ? Object.keys(state.columns).length : 0;
                    return colCnt + 2;
                }),
                selections: computed(_ => {
                    const list = {};
                    if(!state.columns || state.isPreview) return list;

                    for(let k in state.columns) {
                        const curr = state.columns[k];
                        if(curr.datatype == 'string-sc' || curr.datatype == 'string-mc') {
                            list[curr.id] = store.getters.attributeSelections[curr.id];
                        }
                    }
                    return list;
                }),
                showAll: false,
                actualShow: computed(_ => {
                    if(state.needsCut && !state.showAll) {
                        return [
                            ...state.firstCut,
                            {
                                hidden_info: true,
                            },
                            ...state.lastCut,
                        ];
                    } else {
                        return v.value;
                    }
                }),
                needsCut: computed(_ => v.value.length > CUT_THRES),
                firstCut: computed(_ => v.value.slice(0, CUT_SIZE)),
                lastCut: computed(_ => v.value.slice(-CUT_SIZE)),
                newRowColumns: {},
                deletedRows: {},
                expanded: false,
                chartShown: false,
                chartId: `chart-container-${attribute.value.id}-${getTs()}`,
                chartCtx: null,
                chartLabel: -1,
                chartSet: {},
                chartAcc: {},
                chartSetLength: Math.min(7, value.value.length),
                chartData: computed(_ => {
                    if(!state.chartShown) return { labels: [], datasets: [] };
                    if(state.chartLabel === -1) return { labels: [], datasets: [] };
                    const lastValues = value.value.slice(-state.chartSetLength);
                    const datasets = [];
                    for(let i = 0; i < lastValues.length; i++) {
                        let j = 0;
                        for(let k in state.chartSet) {
                            if(!state.chartSet[k]) continue;
                            const label = state.columns[k];
                            if(!datasets[j]) {
                                datasets[j] = {
                                    data: [],
                                    backgroundColor: `rgba(${Math.floor(Math.random() * 256)}, ${Math.floor(Math.random() * 256)}, ${Math.floor(Math.random() * 256)}, 0.25)`,
                                    label: `${translateConcept(label.thesaurus_url)}`
                                };
                            }
                            let value;
                            if(state.chartAcc[k]) {
                                value = i > 0 ? lastValues[i][k] - lastValues[i - 1][k] : lastValues[i][k];
                            } else {
                                value = lastValues[i][k];
                            }
                            datasets[j].data.push(value);
                            j++;
                        }
                    }
                    const labels = lastValues.map(v => {
                        let lbl = v[state.chartLabel];
                        if(typeof lbl == 'object') {
                            if(!!lbl.concept_url) {
                                lbl = translateConcept(lbl.concept_url);
                            } else {
                                lbl = 'TODO: Handle Object in Label!';
                            }
                        }
                        return lbl;
                    });
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

            watch(_ => value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(_ => v.value, (newValue, oldValue) => {
                // only emit @change event if field is validated (required because Entity.vue components)
                // trigger this watcher several times even if another component is updated/validated
                if(!v.meta.validated) return;
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
                uniqueRowIndex,
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
                getActualRowIndex,
                updateDirtyState,
                setRef,
                // PROPS
                // STATE
                state,
                v,
            };
        },
    };
</script>
