<template>
    <ol class="chain breadcrumb m-0 p-0 bg-none small">
        <li
            v-for="chainLink in chain"
            :key="chainLink"
            class="chain-link breadcrumb-item text-secondary small"
        >
            <span>{{ chainLink }}</span>
        </li>
    </ol>
</template>

<script>
    export default {
        props: {
            chain: {
                type: Array,
                required: true,
            },
        },
    };
</script>