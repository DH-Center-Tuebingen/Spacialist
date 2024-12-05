<template>
    <div class="entity-importer-settings d-flex flex-column gap-2">
        <div>
            <label
                for="import-entity-type"
                class="form-label"
            >
                {{ t('main.importer.selected_entity_type') }}
            </label>
            <multiselect
                id="import-entity-type"
                :classes="multiselectResetClasslist"
                :disabled="disabled"
                :hide-selected="true"
                :label="'thesaurus_url'"
                :object="true"
                :options="sortedAvailableEntityTypes"
                :placeholder="t('global.select.placeholder')"
                :searchable="true"
                :track-by="'id'"
                :value-prop="'id'"
                :value="entityType"
                :append-to-body="true"
                @change="value => $emit('update:entityType', value)"
            >
                <template #option="{ option }">
                    {{ option._label }}
                </template>
                <template #singlelabel="{ value }">
                    <div class="multiselect-single-label">
                        {{ value._label }}
                    </div>
                </template>
            </multiselect>
        </div>
        <div>
            <label
                for="import-entity-name"
                class="form-label d-flex"
            >
                {{ t('main.importer.column_entity_name') }}

                <ValuesMissingIndicator
                    class="ms-2"
                    :required="true"
                    :allow-empty="false"
                    :missing="getMissing('entityName')"
                    :total="getTotal('entityName')"
                />
            </label>
            <multiselect
                id="import-entity-name"
                :classes="multiselectResetClasslist"
                :disabled="disabled"
                :hide-selected="true"
                :options="sortedAvailableColumns"
                :placeholder="t('global.select.placeholder')"
                :searchable="true"
                :value="entityName"
                :append-to-body="true"
                @change="value => $emit('update:entityName', value)"
            />
        </div>
        <div>
            <label
                for="import-entity-parent"
                class="form-label d-flex"
            >
                {{ t('main.importer.column_entity_parent') }}
                <ValuesMissingIndicator
                    class="ms-2"
                    :missing="getMissing('entityParent')"
                    :total="getTotal('entityParent')"
                />
            </label>
            <multiselect
                id="import-entity-parent"
                :classes="multiselectResetClasslist"
                :disabled="disabled"
                :hide-selected="true"
                :value="entityParent"
                :options="sortedAvailableColumns"
                :placeholder="t('global.select.placeholder')"
                :searchable="true"
                :append-to-body="true"
                @change="(value) => $emit('update:entityParent', value, entityParent)"
            />
        </div>
    </div>
</template>

<script>
    import { useI18n } from 'vue-i18n';
    import { computed } from 'vue';

    import {
        multiselectResetClasslist,
        translateConcept,
    } from '@/helpers/helpers.js';

    import ValuesMissingIndicator from './ValuesMissingIndicator.vue';

    export default {
        components: {
            ValuesMissingIndicator
        },
        props: {
            disabled: {
                type: Boolean,
                default: false,
            },
            stats: {
                type: Object,
                required: true,
            },
            entityParent: {
                type: String,
                default: '',
            },
            entityName: {
                type: String,
                default: '',
            },
            entityType: {
                type: Object,
                default: null,
            },
            availableEntityTypes: {
                type: Array,
                required: true,
            },
            availableColumns: {
                type: Array,
                required: true,
            },
        },
        emits: ['update:entityType', 'update:entityName', 'update:entityParent'],
        setup(props) {

            const { t } = useI18n();

            function getTotal(attr) {
                let val = 0;
                if(props.stats[attr]?.total != undefined)
                    val = props.stats[attr].total;
                return val;
            }

            function getMissing(attr) {
                let val = 0;
                if(props.stats[attr]?.missing != undefined)
                    val = props.stats[attr].missing;
                return val;
            }

            const sortedAvailableEntityTypes = computed(() => {
                const options = props.availableEntityTypes;
                for(const option of options) {
                    option._label = translateConcept(option.thesaurus_url);
                }

                return options.sort((a, b) => {
                    return a._label.localeCompare(b._label);
                });
            });

            const sortedAvailableColumns = computed(() => {
                const options = [];
                for(const key in props.availableColumns) {
                    options.push(props.availableColumns[key]);
                }

                return Object.values(options).sort((a, b) => {
                    return a.localeCompare(b);
                });
            });

            return {
                t,
                multiselectResetClasslist,
                translateConcept,
                getTotal,
                getMissing,
                sortedAvailableColumns,
                sortedAvailableEntityTypes,
            };
        },
    };
</script>