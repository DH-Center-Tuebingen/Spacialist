<template>
    <div>
        <form class="row" v-if="content">
            <div class="col-auto">
                <label for="delimiter" class="visually-hidden">
                    {{ t('main.csv.uploader.delimiter') }}
                </label>
                <input type="text" class="form-control" id="delimiter" v-model="state.delimiter" :placeholder="t('main.csv.uploader.delimiter_with_info')" />
            </div>
            <div class="col-auto form-check form-switch">
                <input class="form-check-input" type="checkbox" id="has-header" v-model="state.hasHeaderRow">
                <label class="form-check-label" for="has-header">
                    {{ t('main.csv.uploader.has_header') }}
                </label>
            </div>
            <div class="col-auto">
                <label for="row-count" class="visually-hidden">
                    {{ t('main.csv.uploader.nr_of_shown_rows') }}
                </label>
                <input type="text" class="form-control" id="row-count" v-model.number="state.showCount" :placeholder="t('main.csv.uploader.nr_of_shown_rows')" />
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-hover" :class="{ 'table-sm': small }">
                <thead class="thead-light sticky-top">
                    <tr>
                        <th v-for="(header, i) in state.computedRows.header" :key="i">
                            {{ ucfirst(header) }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, i) in state.computedRows.striped_data" :key="i">
                        <td v-for="(column, j) in row" :key="j">
                            {{ column }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    let d3 = require('d3-dsv');

    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        ucfirst,
    } from '../helpers/filters.js';

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
        },
        emits: ['parse'],
        setup(props, context) {
            const { t } = useI18n();

            const {
                content,
                small,
            } = toRefs(props);

            // FETCH

            // FUNCTIONS

            // DATA
            const state = reactive({
                delimiter: ',',
                hasHeaderRow: true,
                showCount: 10,
                dsv: computed(_ => d3.dsvFormat(state.delimiter || ',')),
                computedRows: computed(_ => {
                    if(!content.value) return {};
                    if(!state.dsv) return {};
                    const res = {
                        header: null,
                        data: null,
                        striped_data: null,
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
                    res.striped_data = res.data.slice(
                        0,
                        Math.min(state.showCount || 10, res.data.length)
                    );
                    context.emit('parse', res);
                    return res;
                }),
            });

            // RETURN
            return {
                t,
                // HELPERS
                ucfirst,
                // LOCAL
                // PROPS
                content,
                small,
                // STATE
                state,
            }
        },
    }
</script>
