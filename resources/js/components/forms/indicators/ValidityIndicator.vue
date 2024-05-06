<template>
    <div class="validity-indicator">
        <template
            v-for="certainty in certainties"
            :key="certainty"
        >
            <span v-show="certainty.rangeFunction(state)">
                <i :class="`${certainty.icon} text-${certainty.type}`" />
            </span>
        </template>
    </div>
</template>

<script>
    import {
        getCertainties
    } from '../../../helpers/helpers';

    export default {
        props: {
            state: {
                type: [Number, null],
                required: true,
                validator: (value) => {
                    return value === null || (value >= 0 && value <= 100);
                },
            },
        },
        setup(props) {

            const certainties = getCertainties();

            return {
                certainties,
            };
        },
    };
</script>