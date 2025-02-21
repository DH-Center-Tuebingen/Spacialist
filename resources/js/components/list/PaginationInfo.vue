<template>
    <p>
        Displaying results
        <span class="fw-bold">
            {{ pagination.from }} - {{ pagination.to }}
        </span>
        of
        <span class="fw-bold">{{ pagination.total }}</span>
        in total.
    </p>
</template>

<script>
    export default {
        props: {
            pagination: {
                type: Object,
                required: true,
            },
        }
    };
</script>