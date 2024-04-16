<template>
    <button
        class="btn"
        role="button"
    >
        <i
            v-if="loading"
            class="fas fa-fw fa-notch fa-spin"
        />
        <slot />
    </button>
</template>

<script>
    export default {
        props: {
            loading: {
                type: Boolean,
                required: true,
            },
        },
    };
</script>