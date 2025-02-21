<template>
    <span
        v-if="value"
        class="me-1"
    >
        <i class="fas fa-fw fa-circle-notch fa-spin" />
    </span>
</template>

<script>
    export default {
        props: {
            value: {
                type: Boolean,
                required: true,
            },
        }
    };
</script>

<style lang='scss' scoped></style>