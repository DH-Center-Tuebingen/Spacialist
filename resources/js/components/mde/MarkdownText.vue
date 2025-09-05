<template>
    <div class="markdown-text">
        <VueMarkdownIt :source="value" />
    </div>
</template>

<script>
    import VueMarkdownIt from '@f3ve/vue-markdown-it';

    export default {
        components: {
            VueMarkdownIt,
        },
        props: {
            value: {
                type: String,
                required: true,
            },
        },
    };
</script>