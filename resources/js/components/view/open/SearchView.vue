<template>
    <h3>
        {{ title }}
    </h3>
    <LoadingIndicator
        v-if="loading"
        :value="loading"
        class="fs-1 position-absolute start-50 top-50 translate-middle"
    />
    <div
        v-else
        class="row h-100"
    >
        <div class="col-8 h-100 overflow-hidden d-flex flex-column">
            <slot name="test" />
            <hr>
            <PaginationInfo
                v-if="pagination"
                :pagination="pagination"
            />
            <div class="overflow-y-auto">
                <slot name="results" />
            </div>
            <Pagination
                v-if="pagination && pagination.last_page > 1"
                :pagination="pagination"
                @page-selected="pageSelected"
            />
        </div>
        <div class="col-4 h-100 overflow-hidden">
            <slot name="filters" />
        </div>
    </div>
</template>

<script>
    import LoadingIndicator from '@/components/indicators/LoadingIndicator.vue';
    import Pagination from '@/components/list/Pagination.vue';
    import PaginationInfo from '@/components/list/PaginationInfo.vue';

    export default {
        components: {
            LoadingIndicator,
            Pagination,
            PaginationInfo,
        },
        props: {
            title: {
                type: String,
                required: true,
            },
            loading: {
                type: Boolean,
                defaultValue: true,
            },
            pagination: {
                type: Object,
                required: false,
                default: () => null,
            },
        },
        emits: ['page-selected'],
        setup(props, { emit }) {

            const pageSelected = page => {
                emit('page-selected', page);
            };

            return {
                pageSelected,
            };
        },

    };
</script>
