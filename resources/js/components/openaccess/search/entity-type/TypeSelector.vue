<template>
    <div class="col mt-4 overflow-y-auto overflow-x-hidden">
        <div class="row row-cols-1 row-cols-md-4 row-cols-sm-2 g-2 me-2">
            <div
                v-for="entityType in entityTypes"
                :key="entityType.id"
                class="col"
            >
                <div
                    class="card rounded-4 clickable h-100"
                    @click.prevent="$emit('select', entityType)"
                >
                    <img
                        :src="`api/v1/open/download/entity_type?path=${entityType.metadata.image}`"
                        class="card-img-top rounded-4"
                        :alt="`${translateConcept(entityType.thesaurus_url)} Image missing`"
                    >
                    <div class="card-body position-absolute bottom-0 start-0 w-100 bg-white bg-opacity-75 rounded-bottom-4 p-2">
                        <h5 class="card-title d-flex align-items-center justify-content-between">
                            <span>
                                {{ translateConcept(entityType.thesaurus_url) }}
                            </span>
                            <span class="text-secondary fw-bold">
                                {{ entityType.entities_count }}
                            </span>
                        </h5>
                        <p
                            v-if="entityType.metadata?.open_access_summary"
                            class="card-text text-truncate"
                        >
                            {{ entityType.metadata.open_access_summary }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        translateConcept,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            entityTypes: {
                type: Array,
                required: true,
            },
        },
        emits: ['select'],
        setup() {
            return {
                translateConcept,
            };
        },
    };
</script>