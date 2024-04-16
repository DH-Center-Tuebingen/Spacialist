<template>
    <div class="d-flex flex-column ">
        <header class="d-flex gap-3 align-items-center overflow-visible mb-3">
            <CsvSettings
                v-model:delimiter="csvSettings.delimiter"
                v-model:has-header-row="csvSettings.hasHeaderRow"
                v-model:show-linenumbers="csvSettings.showLinenumbers"
                v-model:show-count="csvSettings.showCount"
                v-model:skipped-count="csvSettings.skippedCount"
                v-model:useCustomDelimiter="csvSettings.useCustomDelimiter"
                v-model:show-preview="csvSettings.showPreview"
                class="d-flex"
                :total="state.rows"
            />
        </header>
        <div class="table-responsive position-relative flex-grow-1 overflow-y-auto overflow-x-auto">
            <table
                v-show="csvSettings.showPreview"
                class="table table-bordered table-striped table-hover "
                :class="{ 'table-sm': small }"
            >
                <thead class="table-light sticky-top">
                    <tr>
                        <th
                            v-if="csvSettings.showLinenumbers"
                            :class="cellClass"
                        >
                            #
                        </th>
                        <th
                            v-for="(header, i) in state.computedRows.header"
                            :key="i"
                            :class="cellClass"
                        >
                            {{ ucfirst(header) }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, i) in state.computedRows.striped_data"
                        :key="i"
                    >
                        <td
                            v-if="csvSettings.showLinenumbers"
                            :class="cellClass"
                        >
                            <span class="fw-bold">
                                {{ i + 1 + csvSettings.skippedCount }}
                            </span>
                        </td>
                        <td
                            v-for="(column, j) in row"
                            :key="j"
                            :class="cellClass"
                        >
                            {{ column }}
                        </td>
                    </tr>
                </tbody>
                <caption
                    v-if="state.endVisible"
                    class="bg-dark text-light text-center"
                >
                    <div
                        class="end-of-file"
                        style=""
                    >
                        {{ t("main.csv.uploader.eof") }}
                    </div>
                </caption>
            </table>
        </div>
    </div>
</template>

<script>
    import * as d3 from 'd3-dsv';

    import {
        computed,
        onMounted,
        reactive,
        toRefs,
        watch,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        ucfirst,
    } from '@/helpers/filters.js';
    import CsvSettings from './tools/csv/CsvSettings.vue';
    import { useLocalStorage } from '../composables/local-storage';

    export default {
        components: { CsvSettings },
        props: {
            content: {
                required: true,
                type: String
            },
            small: {
                required: false,
                type: Boolean
            },
            options: {
                type: Boolean,
                required: false,
                default: true,
            },
            linenumbers: {
                type: Boolean,
                required: false,
                default: false,
            },
        },
        emits: ['parse'],
        setup(props, context) {
            const { t } = useI18n();
            // FETCH
            // FUNCTIONS
            const toggleShowPreview = _ => {
                csvSettings.showPreview = !csvSettings.showPreview;
            };
            const recomputeRows = (internal = false) => {
                if(!props.content || !state.dsv) {
                    state.computedRows = {};
                    return;
                }
                const res = {
                    header: null,
                    data: null,
                    striped_data: null,
                    delimiter: csvSettings.delimiter,
                };
                const headerRow = props.content.split('\n')[0];
                const header = state.dsv.parseRows(headerRow)[0];
                if(csvSettings.hasHeaderRow) {
                    res.header = header;
                    res.data = state.dsv.parse(props.content);
                }
                else {
                    const headerPlaceholder = [];
                    for(let i = 0; i < header.length; i++) {
                        headerPlaceholder.push(`#${i + 1}`);
                    }
                    res.data = state.dsv.parseRows(props.content);
                    res.header = headerPlaceholder;
                }
                state.computedRows = res;
                state.computedRows.striped_data = res.data.slice(state.stripedStart, state.stripedEnd);
                if(!internal) {
                    context.emit('parse', state.computedRows);
                }
            };


            const { value: csvSettings } = useLocalStorage('csv-settings', {
                delimiter: ',',
                hasHeaderRow: true,
                showLinenumbers: false,
                showCount: 10,
                skippedCount: 0,
                showPreview: true,
                useCustomDelimiter: false,
            });

            // DATA
            const state = reactive({
                computedRows: {},
                dsv: computed(_ => d3.dsvFormat(csvSettings.delimiter || ',')),
                endVisible: computed(_ => {
                    console.log(state.stripedStart + csvSettings.showCount);
                    return state.stripedStart + csvSettings.showCount > state.rows;
                }),
                rows: computed(_ => state.computedRows.data ? state.computedRows.data.length : 0),
                maxRows: computed(_ => state.rows - props.skippedCount),
                maxSkippedRows: computed(_ => state.rows > 0 ? state.rows - 1 : 0),
                stripedStart: computed(_ => csvSettings.skippedCount || 0),
                stripedEnd: computed(_ => {
                    return Math.min((csvSettings.skippedCount || 0) + (csvSettings.showCount || 10),
                        state.rows);
                }),
            });

            onMounted(_ => {
                recomputeRows();
            });
            watch(_ => props.content.value, _ => {
                recomputeRows();
            });
            watch(_ => state.dsv, _ => {
                recomputeRows();
            });
            watch(_ => csvSettings.hasHeaderRow, _ => {
                recomputeRows();
            });
            watch(_ => csvSettings.showCount, _ => {
                recomputeRows(true);
            });
            watch(_ => csvSettings.skippedCount, _ => {
                recomputeRows(true);
            });
            const cellClass = 'px-2 py-1';
            // RETURN
            return {
                t,
                // HELPERS
                ucfirst,
                // LOCAL
                cellClass,
                toggleShowPreview,
                // STATE
                state,
                csvSettings,
            };
        }
    };
</script>
