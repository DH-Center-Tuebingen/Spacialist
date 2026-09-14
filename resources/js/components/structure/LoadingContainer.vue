<template>
    <div class="loading-container">
        <span v-if="loading">
            <i class="fas fa-fw fa-spinner fa-spin" />
        </span>
        <alert v-else-if="error"
            :message="error"
            :type="'danger'"
            :noicon="false"
        />
        <slot v-else />
    </div>
</template>

<script setup>
    defineProps({
        loading: {
            type: Boolean,
            required: true,
        },
        error: {
            type: String,
            required: false,
        },
    });
</script>