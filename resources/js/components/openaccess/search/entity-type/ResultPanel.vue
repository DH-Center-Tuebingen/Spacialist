<template>
    <div class="col-8 h-100 overflow-hidden d-flex flex-column">
        <Pagination
            class="pb-2"
            :data="pagination"
            :hide-navigation="true"
        />
        <div class="overflow-y-auto">
            <LoadingSpinner
                v-if="loading"
                class="m-auto my-5"
                style="width: fit-content;"
                size="3x"
            />
            <template v-else>
                <p
                    v-if="data.length == 0"
                    class="alert alert-warning"
                >
                    No results found for the selected filters.
                </p>
                <Card
                    v-for="entry in data"
                    :key="entry.id"
                    class="bg-primary text-dark bg-opacity-10"
                    :entity="entry"
                />
            </template>
        </div>
        <Pagination
            class="mt-2"
            :data="pagination"
            :hide-metadata="true"
            size="sm"
            @goto="$emit('goto', $event)"
        />
    </div>
</template>

<script>
    import { LoadingSpinner, Pagination } from 'dhc-components';

    import Card from '@/components/openaccess/Card.vue';

    export default {
        components: {
            Card,
            Pagination,
            LoadingSpinner,
        },
        props: {
            data: {
                type: Array,
                default: () => [],
            },
            pagination: {
                type: Object,
                default: () => ({}),
            },
            loading: {
                type: Boolean,
                default: false,
            },
        },
        emits: ['goto'],
    };
</script>