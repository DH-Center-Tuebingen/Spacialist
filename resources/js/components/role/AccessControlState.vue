<template>
    <div>
        <span v-show="hasUnknownState">
            <i class="fas fa-fw fa-minus text-muted" />
        </span>
        <span v-show="hasAcceptState">
            <i class="fas fa-fw fa-check text-success" />
        </span>
        <span v-show="hasNonAcceptState">
            <i class="fas fa-fw fa-ban text-danger" />
        </span>
    </div>
</template>

<script>
    import {
        computed,
    } from 'vue';

    export default {
        props: {
            status: {
                type: Number,
                required: true,
            }
        },
        setup(props) {
            const hasUnknownState = computed(_ => props.status > 1);
            const hasAcceptState = computed(_ => props.status === 1);
            const hasNonAcceptState = computed(_ => props.status === 0);

            return {
                hasUnknownState,
                hasAcceptState,
                hasNonAcceptState,
            };
        },
    };
</script>