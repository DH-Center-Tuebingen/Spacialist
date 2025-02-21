<template>
    <nav class="sp-pagination">
        <ul class="pagination pagination-sm justify-content-center mb-0">
            <li
                class="page-item"
                :class="pageClass('first')"
            >
                <a
                    class="page-link"
                    href="#"
                    aria-label="First"
                    @click.prevent="selectPage(1)"
                >
                    <span aria-hidden="true">
                        <i class="fas fa-fw fa-angle-double-left" />
                    </span>
                </a>
            </li>
            <li
                class="page-item"
                :class="pageClass('previous')"
            >
                <a
                    class="page-link"
                    href="#"
                    aria-label="Previous"
                    @click.prevent="selectPage(pagination.current_page - 1)"
                >
                    <span aria-hidden="true">
                        <i class="fas fa-fw fa-chevron-left" />
                    </span>
                </a>
            </li>
            <li
                v-for="page in cleanLinks"
                :key="`page-${page.label}`"
                class="page-item"
                :class="pageClass(page.label)"
            >
                <a
                    class="page-link"
                    href="#"
                    @click.prevent="selectPage(page.label)"
                >
                    {{ page.label }}
                </a>
            </li>
            <li
                class="page-item"
                :class="pageClass('next')"
            >
                <a
                    class="page-link"
                    href="#"
                    aria-label="Next"
                    @click.prevent="selectPage(pagination.current_page + 1)"
                >
                    <span aria-hidden="true">
                        <i class="fas fa-fw fa-chevron-right" />
                    </span>
                </a>
            </li>
            <li
                class="page-item"
                :class="pageClass('last')"
            >
                <a
                    class="page-link"
                    href="#"
                    aria-label="Last"
                    @click.prevent="selectPage(pagination.last_page)"
                >
                    <span aria-hidden="true">
                        <i class="fas fa-fw fa-angle-double-right" />
                    </span>
                </a>
            </li>
        </ul>
    </nav>
</template>

<script>
    import { computed } from 'vue';

    export default {
        props: {
            pagination: {
                type: Object,
                required: true,
            }
        },
        emits: ['page-selected'],
        setup(props, { emits }) {

            const pageClass = label => {
                const list = [];
                switch(label) {
                    case 'first':
                        if(props.pagination.current_page == 1) {
                            list.push('disabled');
                        }
                        break;
                    case 'previous':
                        if(!props.pagination.prev_page_url) {
                            list.push('disabled');
                        }
                        break;
                    case 'last':
                        if(props.pagination.current_page == props.pagination.last_page) {
                            list.push('disabled');
                        }
                        break;
                    case 'next':
                        if(!props.pagination.next_page_url) {
                            list.push('disabled');
                        }
                        break;
                    case '...':
                        list.push('disabled');
                        break;
                    default:
                        if(props.pagination.current_page == label) {
                            list.push('active');
                        }
                        break;
                }
                return list;
            };

            const selectPage = page => {
                emits('page-selected', page);
            };

            const cleanLinks = computed(() => {
                if(!props?.pagination?.links) {
                    return [];
                }
                return props.pagination.links.slice(1, -1);
            });

            return {
                cleanLinks,
                pageClass,
                selectPage,
            };
        }
    };
</script>