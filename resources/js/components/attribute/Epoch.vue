<template>
    <div>
        <div class="input-group">
            <div class="input-group-prepend" uib-dropdown>
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" :disabled="disabled" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span v-if="v.startLabel.value">
                        {{ t(`main.entity.attributes.${v.startLabel.value}`) }}
                    </span>
                    <span v-else>
                    </span>
                </button>
                <ul class="dropdown-menu">
                    <a class="dropdown-item" href="#" v-for="(label, i) in timeLabels" @click.prevent="setLabel('startLabel', label)" :key="i">
                        {{ t(`main.entity.attributes.${label}`) }}
                    </a>
                </ul>
            </div>
            <input type="number" step="1" min="0" pattern="[0-9]+" class="form-control text-center" :disabled="disabled" aria-label="" @input="v.start.handleInput" v-model.number="v.start.value">
                <span class="input-group-text">-</span>
            <input type="number" step="1" min="0" pattern="[0-9]+" class="form-control text-center" :disabled="disabled" aria-label="" @input="v.end.handleInput" v-model.number="v.end.value">
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" :disabled="disabled" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span v-if="v.endLabel.value">
                        {{ t(`main.entity.attributes.${v.endLabel.value}`) }}
                    </span>
                    <span v-else>
                    </span>
                </button>
                <ul uib-dropdown-menu class="dropdown-menu">
                    <a class="dropdown-item" href="#" v-for="(label, i) in timeLabels" @click.prevent="setLabel('endLabel', label)" :key="i">
                        {{ t(`main.entity.attributes.${label}`) }}
                    </a>
                </ul>
            </div>
        </div>
        <!-- <multiselect class="pt-2"
            label="concept_url"
            track-by="id"
            @input="onInput('epoch', epoch)"
            v-if="state.hasEpochList"
            v-model="epoch"
            :allowEmpty="true"
            :closeOnSelect="true"
            :customLabel="translateLabel"
            :disabled="disabled"
            :hideSelected="true"
            :multiple="false"
            :options="epochs"
            :placeholder="$t('global.select.placeholder')"
            :select-label="$t('global.select.select')"
            :deselect-label="$t('global.select.deselect')">
        </multiselect> -->
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
        translateLabel,
    } from '../../helpers/helpers.js';

    export default {
        props: {
            name: String,
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
            };
            const setLabel = (field, value) => {
                v[field].handleChange(value);
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
            const state = reactive({
                epoch: value.epoch,
                hasEpochList: computed(_ => type.value !== 'timeperiod'),
            });
            const v = reactive({
                value: computed(_ => {
                    return {
                        start: v.start.value,
                        startLabel: v.startLabel.value,
                        end: v.end.value,
                        endLabel: v.endLabel.value,
                    };
                }),
                meta: computed(_ => {
                    return {
                        dirty: v.start.meta.dirty || v.startLabel.meta.dirty || v.end.meta.dirty || v.endLabel.meta.dirty,
                        valid: ((v.start.meta.dirty && v.start.meta.valid) || !v.start.meta.dirty) &&
                               ((v.startLabel.meta.dirty && v.startLabel.meta.valid) || !v.startLabel.meta.dirty) &&
                               ((v.end.meta.dirty && v.end.meta.valid) || !v.end.meta.dirty) &&
                               ((v.endLabel.meta.dirty && v.endLabel.meta.valid) || !v.endLabel.meta.dirty),
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
            });

            watch(_ => v.meta, (newValue, oldValue) => {
                console.log("epoch new value", newValue, v.meta);
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                });
            });

            // RETURN
            return {
                t,
                // HELPERS
                translateLabel,
                // LOCAL
                resetFieldState,
                undirtyField,
                setLabel,
                timeLabels,
                // PROPS
                name,
                epochs,
                disabled,
                // STATE
                state,
                v,
            }
        },
        // mounted () {
        //     this.$el.value = this.value;
        // },
    }
</script>
