<template>
    <video controls class="h-100">
        <source :src="file.url" :type="file.mime_type">
    </video>
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
