<template>
    <div class="col-4 h-100 pe-0 overflow-hidden d-flex flex-column">
        <h4>Filters</h4>
        <div class="d-flex flex-column pe-3 overflow-y-auto flex-grow-1">
            <div
                v-for="attribute in attributes"
                :key="attribute.id"
                class="mb-3"
            >
                <h5>
                    {{ translateConcept(attribute.attribute.thesaurus_url) }}
                    <!-- <small>
                        <span class="badge bg-primary">
                            {{ getAttributeCount(attribute.attribute_id) }}
                        </span>
                    </small> -->
                </h5>
                <multiselect
                    :model-value="filters[attribute.attribute_id]"
                    :disabled="getAttributeCount(attribute.attribute_id) == 0"
                    :label="'key'"
                    :value-prop="'key'"
                    :track-by="'key'"
                    :object="true"
                    :mode="'tags'"
                    :hide-selected="false"
                    :close-on-select="false"
                    :options="attributeValues[attribute.attribute_id] ?? []"
                    @update:model-value="options => $emit('change-filter', attribute.attribute_id, options)"
                >
                    <!-- <template #beforelist>
                        <a
                            href="#"
                            class="py-2 text-reset text-decoration-none"
                            style="padding-left: 0.75rem; padding-right: 0.75rem;"
                            @click.prevent="$emit('reset-filter', attribute.attribute_id)"
                        >
                            All Values
                        </a>
                    </template> -->
                    <template #option="{option}">
                        <div class="d-flex flex-row w-100 justify-content-between">
                            <span v-if="attribute.attribute.datatype == 'string-sc'">
                                {{ translateConcept(option.key) }}
                            </span>
                            <span v-else>
                                {{ option.key }}
                            </span>
                            <!-- <span class="fw-bold">
                                [{{ option.count }}]
                            </span> -->
                        </div>
                    </template>
                    <template #tag="{option, handleTagRemove, disabled}">
                        <div class="multiselect-tag">
                            <span v-if="attribute.attribute.datatype == 'string-sc'">
                                {{ translateConcept(option.key) }}
                            </span>
                            <span v-else>
                                {{ option.key }}
                            </span>
                            <span
                                v-if="!disabled"
                                class="multiselect-tag-remove"
                                @click.prevent
                                @mousedown.prevent.stop="handleTagRemove(option, $event)"
                            >
                                <span class="multiselect-tag-remove-icon" />
                            </span>
                        </div>
                    </template>
                </multiselect>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {
        translateConcept,
    } from '@/helpers/helpers.js';

    const props = defineProps({
        filters: {
            type: Object,
            required: true,
        },
        filterableAttributes: {
            type: Object,
            required: true,
        },
        attributes: {
            type: Object,
            required: true,
        },
        attributeValues: {
            type: Object,
            required: true,
        },
    });

    const emit = defineEmits(['change-filter', 'reset-filter']);


    const getAttributeCount = attributeId => {
        if(!props.attributeValues[attributeId]) {
            return 0;
        }
        return props.attributeValues[attributeId].count;
    }; 
</script>
