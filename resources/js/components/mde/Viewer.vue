<template>
    <md-editor
        :data="source"
        :readonly="true"
        :classes="classes"
    />
</template>

<script>
    import {
        toRefs,
    } from 'vue';

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
                default: 'milkdown-wrapper h-100',
            },
        },
        setup(props) {
            const {
                source,
                classes,
            } = toRefs(props);

            return {
            }
        },
    };
</script>


<style scoped>
pre {
    background-color: red;
}
</style>