<template>
    <img :src="file.url" class="modal-image" />
</template>

<script>
    export default {
        props: {
            file: {
                required: true,
                type: Object
            }
        },
        data() {
            return {
            }
        }
    }
</script>
