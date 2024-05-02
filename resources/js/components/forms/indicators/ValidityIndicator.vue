<template>
    <div class="validity-indicator">
        <span v-show="isNone">
            <i class="fas fa-fw fa-question opacity-50" />
        </span>
        <span v-show="isOk">
            <i class="fas fa-fw fa-check text-success" />
        </span>
        <span v-show="isBad">
            <i class="fas fa-fw fa-exclamation text-danger" />
        </span>
    </div>
</template>

<script>
    import { computed } from 'vue';

    export default {
        props: {
            state: {
                type: [Boolean, null],
                required: true,
            },
        },
        setup(props) {
            const isNone = computed(() => props.state === null);
            const isOk = computed(() => props.state === true);
            const isBad = computed(() => props.state === false);

            return {
                isNone,
                isOk,
                isBad,
            };
        },
    };
</script>