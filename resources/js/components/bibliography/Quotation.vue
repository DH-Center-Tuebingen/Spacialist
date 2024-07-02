<template>
    <div class="quotation">
        <blockquote class="blockquote fs-09 mb-4">
            <p class="text-muted">
                {{ value.description }}
            </p>
        </blockquote>
        <figcaption class="blockquote-footer fw-medium mb-0 d-flex gap-1 ">
            <Citation :value="value.bibliography" />
        </figcaption>
    </div>
</template>

<script>
    import Citation from '@/components/bibliography/Citation.vue';
    export default {
        components: {
            Citation
        },
        props: {
            value: {
                type: Object,
                required: true
            }
        },
    };
</script>