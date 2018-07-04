<template>
    <h3 class="text-danger mb-3 text-left">Not Yet Implemented</h3>
</template>

<script>
    export default {
        props: {
            options: {
                required: true,
                type: Object
            },
            selections: {
                required: true,
                type: Array
            }
        },
        beforeMount() {
            const defaults = {};
            for(let k in defaults) {
                Vue.set(this.options, k, defaults[k]);
            }
        },
        methods: {
        }
    }
</script>
