<template>
    <div>
        <input
            class="form-control"
            :disabled="disabled"
            type="text"
            :id="name"
            :name="name"
            :placeholder="t('main.entity.attributes.add_wkt')"
            v-model="v.value"
            @input="v.handleInput" />

            <button type="button" class="btn btn-outline-secondary mt-2" :disabled="disabled" @click="openGeographyModal()">
                <i class="fas fa-fw fa-map-marker-alt"></i>
                {{ t('main.entity.attributes.open_map') }}
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

    import {
        showMapPicker
    } from '@/helpers/modal.js';

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
                type: String,
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
            const updateValue = newValue => {
                v.handleChange(newValue);
            };
            const openGeographyModal = _ => {
                showMapPicker({
                    value: v.value,
                }, updateValue);
            };
            const resetFieldState = _ => {
                v.resetField({
                    value: value.value
                });
            };
            const undirtyField = _ => {
                v.resetField({
                    value: v.value,
                });
            };

            // DATA
            const {
                handleChange,
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
                handleChange,
                handleInput,
                value: fieldValue,
                meta,
                resetField,
            });

            watch(v.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: v.value,
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
