<template>
    <div>
        IMPLEMENT ENTITY SEARCH COMPONENT
        <!-- <entity-search
            
            :id="'attribute-'+attribute.id"
            :name="'attribute-'+attribute.id"
            :on-select="selection => setEntitySearchResult(selection, attribute.id)"
            :value="localValues[attribute.id].name">
        </entity-search> -->
    </div>
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
