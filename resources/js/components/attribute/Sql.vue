<template>
    <div>
        <div v-if="isArray(value)">
            <div class="table-responsive">
                <table class="table table-striped table-hovered table-light table-sm">
                    <thead class="sticky-top">
                        <tr>
                            <th
                                v-for="(columnNames, i) in value[0]"
                                :key="i"
                            >
                                {{ translateConcept(index) }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(row, i) in value"
                            :key="i"
                        >
                            <td
                                v-for="(column, ci) in row"
                                :key="ci"
                            >
                                {{ translateConcept(column) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-else>
            {{ value }}
        </div>
    </div>
</template>

<script>
    import {
        reactive,
        toRefs,
    } from 'vue';

    import {
        isArray,
        translateConcept,
    } from '@/helpers/helpers.js';

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
                type: [Array, String],
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

            });

            // RETURN
            return {
                // HELPERS
                isArray,
                translateConcept,
                // LOCAL
                // STATE
                state,
            };
        },
    };
</script>
