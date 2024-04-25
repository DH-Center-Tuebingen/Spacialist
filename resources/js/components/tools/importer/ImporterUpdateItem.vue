<template>
    <div class="importer-update-item d-flex gap-1 align-items-center">
        <i :class="icon" />
        <span>
            {{ value }}
        </span>
    </div>
</template>

<script>
    export default {
        props: {
            value: {
                type: Number,
                required: true
            },
            icon: {
                type: String,
                default: 'fas fa-exclamation-circle'
            }
        },
    };
</script>