<template>
    <div class="entity-attribute-mapping d-flex flex-column gap-2">
        <div
            v-if="!Array.isArray(availableAttributes) || availableAttributes.length == 0"
            class="alert alert-primary"
        >
            {{ t('main.importer.info.entity_type_has_no_attributes') }}
        </div>
        <div
            v-for="(attr, i) in availableAttributes"
            :key="i"
        >
            <div class="d-flex align-items-centered">
                <label
                    class="form-label"
                    :for="`input-data-column-${attr.id}`"
                >
                    {{ translateConcept(attr.thesaurus_url) }}

                    <span class="ms-2 opacity-50 small">
                        {{ t(`global.attributes.${attr.datatype}`) }}
                    </span>
                </label>
                <ValuesMissingIndicator
                    class="ms-2"
                    :missing="getMissing(attr)"
                    :total="getTotal(attr)"
                />
            </div>
            <multiselect
                :id="`input-data-column-${attr.id}`"
                :value="attributeMapping[attr.id]"
                :classes="multiselectResetClasslist"
                :object="false"
                :mode="'single'"
                :options="availableColumns"
                :placeholder="t('global.select.placeholder')"
                :hide-selected="true"
                :searchable="true"
                @select="(value) => updateAttributeMapping(attr.id, value)"
                @change="(value) => deselectWorkAround(attr.id, value)"
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
            attributeMapping: {
                type: Object,
                required: true,
            },
            availableAttributes: {
                type: Array,
                required: true,
            },
            stats: {
                type: Object,
                required: true,
            },
            availableColumns: {
                type: Object,
                required: true,
            },
        },
        emits: [
            'row-changed',
            'update:attributeMapping',
        ], setup(props, context) {
            const {
                t
            } = useI18n();

            function updateAttributeMapping(id, option) {
                const newMapping = Object.assign({}, props.attributeMapping);
                newMapping[id] = option;

                context.emit('row-changed', id, option);
                context.emit('update:attributeMapping', newMapping);
            }

            function deselectWorkAround(id, value) {
                /**
                 * There is a deselect function for multiselect.
                 * But for some reason it's not fired when the user deselects an option.
                 * So we use the change listener and only call the update function 
                 * when the value is set to 'null'.
                 */
                if(value === null) {
                    updateAttributeMapping(id, null);
                }
            }

            function getTotal(attr) {
                let val = 0;
                const stat = props.stats.attributes[attr.id];
                if(stat && stat.total) {
                    val = stat.total;
                }
                return val;
            }

            function getMissing(attr) {
                let val = 0;
                const stat = props.stats.attributes[attr.id];
                if(stat && stat.total) {
                    val = stat.missing;
                }
                return val;
            }



            return {
                t,
                translateConcept,
                multiselectResetClasslist,
                updateAttributeMapping,
                deselectWorkAround,
                getTotal,
                getMissing,
            };
        },
    };
</script>