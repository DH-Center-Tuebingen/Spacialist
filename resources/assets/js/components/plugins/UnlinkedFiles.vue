<template>
    <div v-show="activeComp == 'unlinked'">
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
            }
        },
        mounted() {
        },
        data() {
            return {
                fileApi: '/file/unlinked/'
            }
        }
    }
</script>
