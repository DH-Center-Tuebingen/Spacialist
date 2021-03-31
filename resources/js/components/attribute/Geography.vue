<template>
    <div>
        <input
            class="form-control"
            :disabled="disabled"
            type="text"
            :id="name"
            :name="name"
            :placeholder="t('main.entity.attributes.add-wkt')"
            v-model="v.fields.geo.value"
            @input="v.fields.geo.handleInput" />

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
        watch,
    } from 'vue';

    import { useField } from 'vee-validate';

    import * as yup from 'yup';

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
        emits: ['change'],
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
            const resetFieldState = _ => {
                v.fields.geo.resetField({
                    value: value.value
                });
            };
            const undirtyField = _ => {
                v.fields.geo.resetField({
                    value: v.fields.geo.value,
                });
            };

            // DATA
            const {
                handleInput,
                value: fieldValue,
                meta,
                resetField,
            } = useField(`geo_${name.value}`, yup.string(), {
                initialValue: value.value,
            });
            const state = reactive({

            });
            const v = reactive({
                fields: {
                    geo: {
                        value: fieldValue,
                        handleInput,
                        meta,
                        resetField,
                    },
                },
            });

            watch(v.fields.geo.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.fields.geo.meta.dirty,
                    valid: v.fields.geo.meta.valid,
                });
            });

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                openGeographyModal,
                resetFieldState,
                undirtyField,
                // PROPS
                name,
                disabled,
                value,
                attribute,
                // STATE
                state,
                v,
            }
        },
    }
</script>
