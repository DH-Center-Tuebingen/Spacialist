<template>
    <div>
        <input
            :id="name"
            v-model="v.value"
            class="form-control"
            :disabled="disabled"
            type="text"
            :name="name"
            :placeholder="t('main.entity.attributes.add_wkt')"
        >

        <button
            type="button"
            class="btn btn-outline-secondary mt-2"
            :disabled="disabled"
            @click="openGeographyModal()"
        >
            <i class="fas fa-fw fa-map-marker-alt" />
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
                value: fieldValue,
                meta,
                resetField,
            });


            watch(_ => value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(_ => v.value, (newValue, oldValue) => {
                // only emit @change event if field is validated (required because Entity.vue components)
                // trigger this watcher several times even if another component is updated/validated
                if(!v.meta.validated) return;
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
                // STATE
                state,
                v,
            };
        },
    };
</script>
