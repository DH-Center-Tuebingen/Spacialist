<template>
    <div>
        <form
            v-if="content"
            class="row align-items-center"
        >
            <div class="col-auto">
                <label
                    for="delimiter"
                    class="visually-hidden"
                >
                    {{ t('main.csv.uploader.delimiter') }}
                </label>
                <input
                    id="delimiter"
                    v-model="state.delimiter"
                    type="text"
                    class="form-control"
                    :placeholder="t('main.csv.uploader.delimiter_with_info')"
                >
            </div>
            <div class="col-auto form-check form-switch">
                <input
                    id="has-header"
                    v-model="state.hasHeaderRow"
                    class="form-check-input"
                    type="checkbox"
                >
                <label
                    class="form-check-label"
                    for="has-header"
                >
                    {{ t('main.csv.uploader.has_header') }}
                </label>
            </div>
            <div
                v-if="linenumbers"
                class="col-auto form-check form-switch"
            >
                <input
                    id="show-linenumbers"
                    v-model="state.showLinenumbers"
                    class="form-check-input"
                    type="checkbox"
                >
                <label
                    class="form-check-label"
                    for="show-linenumbers"
                >
                    {{ t('main.csv.uploader.show_linenumbers') }}
                </label>
            </div>
            <div class="col-auto">
                <label
                    for="row-count"
                    class="visually-hidden"
                >
                    {{ t('main.csv.uploader.nr_of_shown_rows') }}
                </label>
                <input
                    id="row-count"
                    v-model.number="state.showCount"
                    type="number"
                    class="form-control"
                    min="0"
                    :max="state.maxRows"
                    step="1"
                    :placeholder="t('main.csv.uploader.nr_of_shown_rows')"
                >
            </div>
            <div class="col-auto">
                <label
                    for="skipped-row-count"
                    class="visually-hidden"
                >
                    {{ t('main.csv.uploader.nr_of_skipped_rows') }}
                </label>
                <input
                    id="skipped-row-count"
                    v-model.number="state.skippedCount"
                    type="number"
                    class="form-control"
                    min="0"
                    :max="state.maxSkippedRows"
                    step="1"
                    :placeholder="t('main.csv.uploader.nr_of_skipped_rows')"
                >
            </div>
            <div class="col-auto">
                <a
                    href="#"
                    class="text-reset"
                    @click.prevent="toggleShowPreview()"
                >
                    <span v-if="state.showPreview">
                        <i class="fas fa-fw fa-eye-slash" />
                    </span>
                    <span v-else>
                        <i class="fas fa-fw fa-eye" />
                    </span>
                </a>
            </div>
        </form>
        <div
            v-show="state.showPreview"
            class="table-responsive"
        >
            <table
                class="table table-striped table-hover"
                :class="{ 'table-sm': small }"
            >
                <thead class="table-light sticky-top">
                    <tr>
                        <th v-if="state.showLinenumbers">
                            #
                        </th>
                        <th
                            v-for="(header, i) in state.computedRows.header"
                            :key="i"
                        >
                            {{ ucfirst(header) }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, i) in state.computedRows.striped_data"
                        :key="`csv-preview-row-${i}`"
                    >
                        <td v-if="state.showLinenumbers">
                            <span class="fw-bold">
                                {{ i + 1 + state.skippedCount }}
                            </span>
                        </td>
                        <td
                            v-for="(column, j) in row"
                            :key="`csv-preview-col-${i}-${j}`"
                            @mouseenter="setHover(i, j, true)"
                            @mouseleave="setHover(i, j, false)"
                        >
                            <span v-if="state.hover[`${i}_${j}`]">
                                {{ column }}
                            </span>
                            <span v-else>
                                {{ truncate(column) }}
                            </span>
                        </td>
                    </tr>
                </tbody>
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
        truncate,
    } from '@/helpers/filters.js';

    export default {
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

            const {
                content,
                small,
                linenumbers,
            } = toRefs(props);

            // FETCH

            // FUNCTIONS
            const toggleShowPreview = _ => {
                state.showPreview = !state.showPreview;
            };
            const recomputeRows = (internal = false) => {
                if(!content.value || !state.dsv) {
                    state.computedRows = {};
                    return;
                }
                const res = {
                    header: null,
                    data: null,
                    striped_data: null,
                    delimiter: state.delimiter || ',',
                };
                const headerRow = content.value.split('\n')[0];
                const header = state.dsv.parseRows(headerRow)[0];
                if(state.hasHeaderRow) {
                    res.header = header;
                    res.data = state.dsv.parse(content.value);
                } else {
                    const headerPlaceholder = [];
                    for(let i=0; i<header.length; i++) {
                        headerPlaceholder.push(`#${i+1}`);
                    }
                    res.data = state.dsv.parseRows(content.value);
                    res.header = headerPlaceholder;
                }
                state.computedRows = res;
                state.computedRows.striped_data = res.data.slice(state.stripedStart, state.stripedEnd);

                if(!internal) {
                    context.emit('parse', state.computedRows);
                }
            };
            const setHover = (rowIdx, colIdx, status) => {
                state.hover[`${rowIdx}_${colIdx}`] = status;
            };

            // DATA
            const state = reactive({
                delimiter: ',',
                hasHeaderRow: true,
                showLinenumbers: false,
                showCount: 10,
                skippedCount: 0,
                showPreview: true,
                computedRows: {},
                hover: {},
                dsv: computed(_ => d3.dsvFormat(state.delimiter || ',')),
                rows: computed(_ => state.computedRows.data ? state.computedRows.data.length : 0),
                maxRows: computed(_ => state.rows - state.skippedCount),
                maxSkippedRows: computed(_ => state.rows > 0 ?  state.rows - 1 : 0),
                stripedStart: computed(_ => state.skippedCount || 0),
                stripedEnd: computed(_ => {
                    return Math.min(
                        (state.skippedCount || 0) + (state.showCount || 10),
                        state.rows
                    );
                }),
            });

            onMounted(_ => {
                recomputeRows();
            });
            watch(_ => content.value, _ => {
                recomputeRows();
            });
            watch(_ => state.dsv, _ => {
                recomputeRows();
            });
            watch(_ => state.hasHeaderRow, _ => {
                recomputeRows();
            });
            watch(_ => state.showCount, _ => {
                recomputeRows(true);
            });
            watch(_ => state.skippedCount, _ => {
                recomputeRows(true);
            });

            // RETURN
            return {
                t,
                // HELPERS
                ucfirst,
                truncate,
                // LOCAL
                toggleShowPreview,
                setHover,
                // STATE
                state,
            }
        },
    }
</script>
