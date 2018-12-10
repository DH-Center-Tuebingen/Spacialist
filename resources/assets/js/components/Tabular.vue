<template>
    <table class="table table-striped table-hovered table-sm">
        <thead class="thead-light">
            <tr>
                <th v-for="column in attribute.columns">
                    {{ $translateConcept(column.thesaurus_url) }}
                </th>
                <th>
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
                            @input="onInput($index, $event.target.value)">
                        </multiselect>
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
                </td>
                <td>
                    <button type="button" :disabled="disabled" class="btn btn-success btn-sm" @click="addTableRow(newTableCols, attribute.columns)">
                        <i class="fas fa-fw fa-plus"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
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
        },
        methods: {
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
            addTableRow(row) {
                this.value.push(_.clone(row));
                for(let k in row) delete row[k];
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
        computed: {
            newTableCols: _ => new Object()
        }
    }
</script>
