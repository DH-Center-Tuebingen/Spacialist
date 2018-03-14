<template>
    <div v-show="activeComp == 'linked'">
        <file-list :api-url="fileApi" :on-click="onClick"></file-list>
    </div>
</template>

<script>
    export default {
        props: {
            onClick: {
                required: true,
                type: Function
            },
            activeComp: {
                required: true,
                type: String
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
