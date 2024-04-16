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
                :value="entityType"
                :classes="multiselectResetClasslist"
                :value-prop="'id'"
                :label="'thesaurus_url'"
                :track-by="'id'"
                :object="true"
                :mode="'single'"
                :options="availableEntityTypes"
                :placeholder="t('global.select.placeholder')"
                :hide-selected="true"
                :searchable="true"
                @change="value => $emit('update:entityType', value)"
            >
                <template #option="{ option }">
                    {{ translateConcept(option.thesaurus_url) }}
                </template>
                <template #singlelabel="{ value }">
                    <div class="multiselect-single-label">
                        {{ translateConcept(value.thesaurus_url) }}
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
                :value="entityName"
                :classes="multiselectResetClasslist"
                :object="false"
                :mode="'single'"
                :options="availableColumns"
                :placeholder="t('global.select.placeholder')"
                :hide-selected="true"
                :searchable="true"
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
                :value="entityParent"
                :classes="multiselectResetClasslist"
                :object="false"
                :mode="'single'"
                :options="availableColumns"
                :placeholder="t('global.select.placeholder')"
                :hide-selected="true"
                :searchable="true"
                @change="value => $emit('update:entityParent', value)"
            />
        </div>
    </div>
</template>

<script>
    import { useI18n } from 'vue-i18n';

    import {
        multiselectResetClasslist,
        translateConcept,
    } from '@/helpers/helpers.js';

    import Multiselect from '@vueform/multiselect';
    import ValuesMissingIndicator from './ValuesMissingIndicator.vue';

    export default {
        components: {
            Multiselect,
            ValuesMissingIndicator
        },
        props: {
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

            return {
                t,
                multiselectResetClasslist,
                translateConcept,
                getTotal,
                getMissing,
            };
        },
    };
</script>