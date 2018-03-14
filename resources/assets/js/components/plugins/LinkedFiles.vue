<template>
    <file-list :api-url="fileApi" :on-click="onClick"></file-list>
</template>

<script>
    export default {
        props: {
            onClick: {
                required: true,
                type: Function
            },
            context: {
                required: true,
                type: Object
            }
        },
        mounted() {
        },
        data() {
            return {
                fileApi: '/file/linked/' + this.context.id
            }
        }
    }
</script>
