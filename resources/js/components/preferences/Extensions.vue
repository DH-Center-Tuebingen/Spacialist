<template>
    <div class="row" v-for="(extension, key) in data" :key="key">
        <div class="col-md-2"></div>
        <div class="col-md-10">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" :id="`ext-${key}`" v-model="data[key]" :readonly="readonly" :disabled="readonly">
                <label class="form-check-label" :for="`ext-${key}`">
                    {{ key }}
                </label>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';
    
    export default {
        props: {
            data: {
                required: true,
                type: Object,
            },
            readonly: {
                required: false,
                type: Boolean,
                default: false,
            },
        },
        emits: ['changed'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                data,
                readonly,
            } = toRefs(props);

            // DATA

            // RETURN
            return {
                t,
                // PROPS
                data,
                readonly,
                // STATE
            };
        }
    }
</script>