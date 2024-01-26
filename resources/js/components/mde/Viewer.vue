<template>
    <md-editor
        :data="source"
        :readonly="true"
        :classes="classes"
    />
</template>

<script>
    export default {
        props: {
            id: {
                required: true,
                type: String,
            },
            source: {
                required: true,
                type: String,
            },
            classes: {
                required: false,
                type: String,
                default: 'milkdown-wrapper p-3 mt-1 h-100',
            },
        }
    };
</script>