<template>
    <span :title="value.author">
        {{ formatAuthors(value.author) }} ({{ value.year }}).
        <cite :title="value.title">
            {{ value.title }}.
        </cite>
        <a
            href="#"
            class="ms-1"
            @click.prevent="()=>showLiteratureInfo(value.id)"
        >
            <i class="fas fa-fw fa-info-circle" />
        </a>
    </span>
</template>

<script>

    import { formatAuthors } from '@/helpers/bibliography';
    import { showLiteratureInfo } from '@/helpers/modal';

    export default {
        props: {
            value: {
                type: Object,
                required: true
            }
        },
        setup(props) {

            return {
                formatAuthors,
                showLiteratureInfo,
            };
        }
    };
</script>

<style
    lang='scss'
    scoped
></style>