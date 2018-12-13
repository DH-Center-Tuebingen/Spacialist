<template>
    <div class="table-responsive">
        <table class="table table-striped table-hover" :class="{ 'table-sm': small }">
            <thead class="thead-light sticky-top">
                <tr>
                    <th v-for="header in rows.header">
                        {{ header }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in rows.data">
                    <td v-for="column in row">
                        {{ column }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    let d3 = require('d3-dsv');

    export default {
        props: {
            content: {
                required: true,
                type: String
            },
            delimiter: {
                required: false,
                type: String,
                default: ','
            },
            header: {
                required: false,
                type: Boolean,
                default: true
            },
            length: {
                required: false,
                type: Number,
                default: 10
            },
            small: {
                required: false,
                type: Boolean
            }
        },
        mounted() {},
        methods: {
        },
        data() {
            return {
            }
        },
        computed: {
            dsv: function() {
                let delimiter = this.delimiter || ',';
                return d3.dsvFormat(delimiter);
            },
            rows: function() {
                if(!this.dsv) return {};
                let length = this.length || 10;
                let dataRows;
                let row = this.content.split('\n')[0];
                let headerRow = this.dsv.parseRows(row)[0];
                if(this.header) {
                    dataRows = this.dsv.parse(this.content);
                } else {
                    let columnCount = headerRow.length;
                    headerRow = [];
                    for(let i=0; i<columnCount; i++) {
                        headerRow.push('Column ' + (i+1));
                    }
                    dataRows = this.dsv.parseRows(this.content);
                }
                let count = Math.min(length, dataRows.length);
                return {
                    header: headerRow,
                    data: dataRows.slice(0, count)
                };
            }
        }
    }
</script>
