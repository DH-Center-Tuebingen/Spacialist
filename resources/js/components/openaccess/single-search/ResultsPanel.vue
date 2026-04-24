<template>
    <div class="col-8 h-100 overflow-hidden d-flex flex-column">
        <Pagination
            class="pb-2"
            :data="pagination"
            :hide-navigation="true"
        />
        <div class="overflow-y-auto">
            <result-card
                v-for="entry in data"
                :key="entry.id"
                class="bg-primary text-dark bg-opacity-25"
                :entity="entry"
            />
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
    import { Pagination } from 'dhc-components';

    export default {
        components: {
            Pagination,
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
        },
        emits: ['goto'],
    };
</script>
