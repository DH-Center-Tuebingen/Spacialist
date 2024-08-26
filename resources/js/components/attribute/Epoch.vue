<template>
    <div>
        <div class="input-group">
            <button
                type="button"
                class="btn btn-outline-secondary dropdown-toggle"
                :disabled="disabled"
                data-bs-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
            >
                <span v-if="v.startLabel.value">
                    {{ t(`main.entity.attributes.${v.startLabel.value}`) }}
                </span>
                <span v-else>
                    <!-- TODO: Check if this else is required -->
                </span>
            </button>
            <ul class="dropdown-menu">
                <a
                    v-for="(label, i) in timeLabels"
                    :key="i"
                    class="dropdown-item"
                    href="#"
                    @click.prevent="setLabel('startLabel', label)"
                >
                    {{ t(`main.entity.attributes.${label}`) }}
                </a>
            </ul>
            <input
                v-model.number="v.start.value"
                type="number"
                step="1"
                min="0"
                pattern="[0-9]+"
                class="form-control text-center"
                :disabled="disabled"
                aria-label=""
                @input="v.start.handleInput"
            >
            <span class="input-group-text">-</span>
            <input
                v-model.number="v.end.value"
                type="number"
                step="1"
                min="0"
                pattern="[0-9]+"
                class="form-control text-center"
                :disabled="disabled"
                aria-label=""
                @input="v.end.handleInput"
            >
            <button
                type="button"
                class="btn btn-outline-secondary dropdown-toggle"
                :disabled="disabled"
                data-bs-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
            >
                <span v-if="v.endLabel.value">
                    {{ t(`main.entity.attributes.${v.endLabel.value}`) }}
                </span>
                <span v-else>
                    <!-- TODO: Check if this else is required -->
                </span>
            </button>
            <ul
                uib-dropdown-menu
                class="dropdown-menu"
            >
                <a
                    v-for="(label, i) in timeLabels"
                    :key="i"
                    class="dropdown-item"
                    href="#"
                    @click.prevent="setLabel('endLabel', label)"
                >
                    {{ t(`main.entity.attributes.${label}`) }}
                </a>
            </ul>
        </div>
        <multiselect
            v-if="state.hasEpochList"
            v-model="v.epoch.value"
            class="mt-2"
            :classes="multiselectResetClasslist"
            :value-prop="'concept_url'"
            :label="'concept_url'"
            :track-by="'concept_url'"
            :object="true"
            :mode="'single'"
            :disabled="disabled"
            :options="epochs"
            :name="name"
            :placeholder="t('global.select.placeholder')"
            @change="handleEpochChange"
        >
            <template #option="{ option }">
                {{ translateConcept(option.concept_url) }}
            </template>
            <template #singlelabel="{ value: singlelabelValue }">
                <div class="multiselect-single-label">
                    {{ translateConcept(singlelabelValue.concept_url) }}
                </div>
            </template>
        </multiselect>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
        watch,
    } from 'vue';

    import { useField } from 'vee-validate';

    import * as yup from 'yup';

    import { useI18n } from 'vue-i18n';

    import {
        translateConcept,
        multiselectResetClasslist,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            name: {
                type: String,
                required: true,
            },
            value: {
                required: false,
                type: Object,
                default: _ => new Object(),
            },
            epochs: {
                required: false,
                type: Array,
                default: _ => new Array(),
            },
            type: {
                required: false,
                type: String,
                default: 'epoch',
            },
            disabled: {
                type: Boolean,
            },
        },
        emits: ['change'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                name,
                value,
                epochs,
                type,
                disabled,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS
            const resetFieldState = _ => {
                v.start.resetField({
                    value: value.value.start
                });
                v.end.resetField({
                    value: value.value.end
                });
                v.startLabel.resetField({
                    value: value.value.startLabel
                });
                v.endLabel.resetField({
                    value: value.value.endLabel
                });
                v.epoch.resetField({
                    value: value.value.epoch
                });
            };
            const undirtyField = _ => {
                v.start.resetField({
                    value: v.start.value
                });
                v.end.resetField({
                    value: v.end.value
                });
                v.startLabel.resetField({
                    value: v.startLabel.value
                });
                v.endLabel.resetField({
                    value: v.endLabel.value
                });
                v.epoch.resetField({
                    value: v.epoch.value
                });
            };
            const setLabel = (field, value) => {
                v[field].handleChange(value);
            };
            const handleEpochChange = option => {
                v.epoch.handleChange(option);
            };

            // DATA
            const timeLabels = ['BC', 'AD'];
            const {
                handleInput: his,
                value: vs,
                meta: ms,
                resetField: rfs,
            } = useField(`start_${name.value}`, yup.number().positive(), {
                initialValue: value.value.start,
            });
            const {
                handleInput: hie,
                value: ve,
                meta: me,
                resetField: rfe,
            } = useField(`end_${name.value}`, yup.number().positive(), {
                initialValue: value.value.end,
            });
            const {
                handleInput: hisl,
                handleChange: hcsl,
                value: vsl,
                meta: msl,
                resetField: rfsl,
            } = useField(`startlabel_${name.value}`, yup.string().matches(/(BC|AD)/), {
                initialValue: value.value.startLabel,
            });
            const {
                handleInput: hiel,
                handleChange: hcel,
                value: vel,
                meta: mel,
                resetField: rfel,
            } = useField(`endlabel_${name.value}`, yup.string().matches(/(BC|AD)/), {
                initialValue: value.value.endLabel,
            });
            const {
                handleInput: hiep,
                handleChange: hcep,
                value: vep,
                meta: mep,
                resetField: rfep,
            } = useField(`epoch_${name.value}`, yup.mixed(), {
                initialValue: value.value.epoch,
            });
            const state = reactive({
                hasEpochList: computed(_ => type.value !== 'timeperiod'),
            });
            const v = reactive({
                value: computed(_ => {
                    return {
                        start: v.start.value,
                        startLabel: v.startLabel.value,
                        end: v.end.value,
                        endLabel: v.endLabel.value,
                        epoch: v.epoch.value,
                    };
                }),
                meta: computed(_ => {
                    return {
                        dirty: v.start.meta.dirty || v.startLabel.meta.dirty || v.end.meta.dirty || v.endLabel.meta.dirty || v.epoch.meta.dirty,
                        valid: ((v.start.meta.dirty && v.start.meta.valid) || !v.start.meta.dirty) &&
                               ((v.startLabel.meta.dirty && v.startLabel.meta.valid) || !v.startLabel.meta.dirty) &&
                               ((v.end.meta.dirty && v.end.meta.valid) || !v.end.meta.dirty) &&
                               ((v.endLabel.meta.dirty && v.endLabel.meta.valid) || !v.endLabel.meta.dirty) &&
                               ((v.epoch.meta.dirty && v.epoch.meta.valid) || !v.epoch.meta.dirty),
                    };
                }),
                start: {
                    value: vs,
                    meta: ms,
                    resetField: rfs,
                    handleInput: his,
                },
                startLabel: {
                    value: vsl,
                    meta: msl,
                    resetField: rfsl,
                    handleInput: hisl,
                    handleChange: hcsl,
                },
                end: {
                    value: ve,
                    meta: me,
                    resetField: rfe,
                    handleInput: hie,
                },
                endLabel: {
                    value: vel,
                    meta: mel,
                    resetField: rfel,
                    handleInput: hiel,
                    handleChange: hcel,
                },
                epoch: {
                    value: vep,
                    meta: mep,
                    resetField: rfep,
                    handleInput: hiep,
                    handleChange: hcep,
                },
            });


            watch(_ => value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(_ => v.value, (newValue, oldValue) => {
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
                translateConcept,
                multiselectResetClasslist,
                // LOCAL
                resetFieldState,
                undirtyField,
                setLabel,
                timeLabels,
                handleEpochChange,
                // STATE
                state,
                v,
            };
        },
    };
</script>
