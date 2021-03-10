<template>
    <div>
        <input
            class="form-control"
            :disabled="disabled"
            type="text"
            :id="name"
            :name="name"
            :placeholder="t('main.entity.attributes.add-wkt')"
            v-model="value" />

            <button type="button" class="btn btn-outline-secondary mt-2" :disabled="disabled" @click="openGeographyModal(attribute.id)">
                <i class="fas fa-fw fa-map-marker-alt"></i>
                {{ t('main.entity.attributes.open-map') }}
            </button>
    </div>
</template>

<script>
    import {
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

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
            attribute: {
                type: Object,
                required: true,
            },
        },
        setup(props, context) {
            const { t } = useI18n();
            const {
                name,
                disabled,
                value,
                attribute,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS
            const openGeographyModal = id => {

            };

            // DATA
            const state = reactive({

            });

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                openGeographyModal,
                // PROPS
                name,
                disabled,
                value,
                attribute,
                // STATE
                state,
            }
        },
    }
</script>
