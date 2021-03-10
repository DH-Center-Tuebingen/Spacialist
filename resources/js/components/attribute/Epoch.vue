<template>
    <div>
        <div class="input-group">
            <div class="input-group-prepend" uib-dropdown>
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" :disabled="disabled" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span v-if="state.startLabel">
                        {{ t(`main.entity.attributes.${state.startLabel}`) }}
                    </span>
                    <span v-else>
                    </span>
                </button>
                <ul class="dropdown-menu">
                    <a class="dropdown-item" href="#" v-for="(label, i) in state.labels" @click.prevent="setLabel('startLabel', label)" :key="i">
                        {{ t(`main.entity.attributes.${label}`) }}
                    </a>
                </ul>
            </div>
            <input type="number" step="1" min="0" pattern="[0-9]+" class="form-control text-center" :disabled="disabled" aria-label="" @input="onInput('start', state.start)" v-model.number="state.start">
                <span class="input-group-text">-</span>
            <input type="number" step="1" min="0" pattern="[0-9]+" class="form-control text-center" :disabled="disabled" aria-label="" @input="onInput('end', state.end)" v-model.number="state.end">
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" :disabled="disabled" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span v-if="state.endLabel">
                        {{ t(`main.entity.attributes.${state.endLabel}`) }}
                    </span>
                    <span v-else>
                    </span>
                </button>
                <ul uib-dropdown-menu class="dropdown-menu">
                    <a class="dropdown-item" href="#" v-for="(label, i) in state.labels" @click.prevent="setLabel('endLabel', label)" :key="i">
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
            :placeholder="$t('global.select.placehoder')"
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
    } from 'vue';

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
            onChange: {
                type: Function,
                required: true,
            }
        },
        setup(props, context) {
            const { t } = useI18n();
            const {
                name,
                value,
                epochs,
                type,
                disabled,
                onChange,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS
            const onInput = (field, value) => {
                // this.$emit('input', value);
                // this.onChange(field, value);
            };
            const setLabel = (field, value) => {
                // this[field] = value;
                // onInput(field, value);
            };

            // DATA
            const state = reactive({
                labels: ['BC', 'AD'],
                startLabel: value.startLabel,
                start: value.start,
                endLabel: value.endLabel,
                end: value.end,
                epoch: value.epoch,
                hasEpochList: computed(_ => type.value !== 'timeperiod'),
            });

            // RETURN
            return {
                t,
                // HELPERS
                translateLabel,
                // LOCAL
                onInput,
                setLabel,
                // PROPS
                name,
                epochs,
                disabled,
                onChange,
                // STATE
                state,
            }
        },
        // mounted () {
        //     this.$el.value = this.value;
        // },
    }
</script>
