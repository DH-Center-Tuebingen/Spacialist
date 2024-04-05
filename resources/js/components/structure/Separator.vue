<template>
    <span
        class="separator"
        :class="{
            'text-separator': isTextSeparator,
        }"
    />
</template>

<script setup>

defineProps({
    isTextSeparator: {
        type: Boolean,
    },
});

</script>

<style>
.separator {
    border-left: 1px solid currentColor;
    height: 100%;
    margin: 0 0.5rem;
    opacity: 0.3;
}

.text-separator {
    height: 1em;
}
</style>