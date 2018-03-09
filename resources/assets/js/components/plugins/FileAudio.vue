<template>
    <audio controls class="w-100">
        <source :src="file.url" :type="file.mime_type">
    </audio>
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
