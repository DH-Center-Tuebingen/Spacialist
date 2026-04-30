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
                        :src="`https://picsum.photos/400?random=${entityType.id}`"
                        class="card-img-top rounded-top-4"
                        :alt="`${translateConcept(entityType.thesaurus_url)} Image missing`"
                    >
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center justify-content-between">
                            <span>
                                {{ translateConcept(entityType.thesaurus_url) }}
                            </span>
                            <span class="text-secondary fw-bold">
                                {{ entityType.entities_count }}
                            </span>
                        </h5>
                        <p class="card-text text-truncate">
                            This is a longer card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.
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