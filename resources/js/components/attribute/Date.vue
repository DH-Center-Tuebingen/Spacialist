 
<template>
    <date-picker
        class="w-100"
        input-class="form-control"
        value-type="date"
        :id="name"
        :name="name"
        :disabled="disabled"
        :disabled-date="(date) => date > new Date()"
        :max-date="new Date()"
        :show-week-number="true"
        v-model="state.value">
        <template v-slot:icon-calendar>
            <i class="fas fa-fw fa-calendar-alt"></i>
        </template>
        <template v-slot:icon-clear>
            <i class="fas fa-fw fa-times"></i>
        </template>
    </date-picker>
</template>

<script>
    import {
        reactive,
        toRefs,
    } from 'vue';

    export default {
        props: {
            name: {
                type: String,
                required: true,
            },
            disabled: {
                type: Boolean,
                required: false,
                default: false,
            },
            value: {
                type: Number,
                required: true,
            },
        },
        setup(props, context) {
            const {
                name,
                disabled,
                value,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS

            // DATA
            const state = reactive({
                value: value.value,
            });

            // RETURN
            return {
                // HELPERS
                // LOCAL
                // PROPS
                name,
                disabled,
                value,
                // STATE
                state,
            }
        },
    }
</script>
