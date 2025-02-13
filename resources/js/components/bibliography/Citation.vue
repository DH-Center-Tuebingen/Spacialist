<template>
    <span>
        <span :title="value.author">
            {{ formatAuthors(value.author, 3) }} ({{ value.year }}).
        </span>
        <cite :title="value.title">
            {{ shortenedTitle }}.
        </cite>
        <a
            href="#"
            class="ms-1"
            @click.prevent="() => showLiteratureInfo(value.id)"
        >
            <i class="fas fa-fw fa-info-circle" />
        </a>
    </span>
</template>

<script>
    import { computed } from 'vue';
    import { truncate } from '@/helpers/filters';
    import {
        formatAuthors,
        formatBibtexText,
    } from '@/helpers/bibliography';
    import { showLiteratureInfo } from '@/helpers/modal';
    export default {
        props: {
            value: {
                type: Object,
                required: true
            }
        },
        setup(props) {
            const shortenedTitle = computed(() => {
                const formattedText = formatBibtexText(props.value.title);
                return truncate(formattedText, 60);
            });
            return {
                formatAuthors,
                showLiteratureInfo,
                shortenedTitle,
            };
        }
    };
</script>