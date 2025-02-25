<template>
    <div class="validity-indicator">
        <template
            v-for="certainty in certainties"
            :key="certainty"
        >
            <template v-if="certainty.rangeFunction(state) && Array.isArray(certainty.icon)">
                <div
                    class="position-relative mt-1 h-100 w-100"
                    :class="addOnCenter('h-100 w-100')"
                >
                    <i
                        v-for="icon in certainty.icon"
                        :key="icon"
                        :class="`position-absolute ${addOnCenter('w-100')} ${icon}`"
                    />
                </div>
            </template>
            <div
                v-show="certainty.rangeFunction(state) && !Array.isArray(certainty.icon)"
                :class="addOnCenter('text-center')"
            >
                <i :class="`${certainty.icon} text-${certainty.type}`" />
            </div>
        </template>
    </div>
</template>

<script>
    import {
        getCertainties
    } from '@/helpers/helpers.js';

    export default {
        props: {
            state: {
                type: [Number, null],
                required: true,
                validator: (value) => {
                    return value === null || (value >= 0 && value <= 100);
                },
            },
            center: {
                type: Boolean,
                default: false,
                required: false,
            },
        },
        setup(props) {
            const certainties = getCertainties();

            const addOnCenter = classes => {
                return props.center ? classes : '';
            };

            return {
                certainties,
                addOnCenter,
            };
        },
    };
</script>