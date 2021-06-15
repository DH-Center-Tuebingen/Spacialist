<template>
    <div class="row mb-3">
        <label for="left-column" class="col-md-2 col-form-label text-end">{{ t('main.preference.key.columns.left') }}:</label>
        <div class="col-md">
            <input class="form-control" id="left-column" type="number" min="0" :max="state.maxLeft" v-model="data.left" :readonly="readonly" @input="onChange" />
        </div>
    </div>
    <div class="row mb-3">
        <label for="center-column" class="col-md-2 col-form-label text-end">{{ t('main.preference.key.columns.center') }}:</label>
        <div class="col-md">
            <input class="form-control" id="center-column" type="number" min="0" :max="state.maxCenter" v-model="data.center" :readonly="readonly" @input="onChange" />
        </div>
    </div>
    <div class="row mb-3">
        <label for="right-column" class="col-md-2 col-form-label text-end">{{ t('main.preference.key.columns.right') }}:</label>
        <div class="col-md">
            <input class="form-control" id="right-column" type="number" min="0" :max="state.maxRight" v-model="data.right" :readonly="readonly" @input="onChange" />
        </div>
    </div>
    <div class="row">
        <div class="col-md-10 offset-md-2">
            <div class="alert bg-info mb-0 w-50" role="alert">
                {{ t('main.preference.info.columns') }}
            </div>
        </div>
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
        _debounce
    } from '../../helpers/helpers.js';
    
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

            // FUNCTIONS
            const onChange = _debounce(e => {
                if(readonly.value) return;
                context.emit('changed', {
                    value: {
                        left: data.value['left'],
                        center: data.value['center'],
                        right: data.value['right'],
                    }
                });
            }, 250);

            // DATA
            const state = reactive({
                maxLeft: computed(_ => 12 - data.value['center'] - data.value['right']),
                maxCenter: computed(_ => 12 - data.value['left'] - data.value['right']),
                maxRight: computed(_ => 12 - data.value['left'] - data.value['center']),
            });

            // RETURN
            return {
                t,
                // LOCAL
                onChange,
                // PROPS
                data,
                readonly,
                // STATE
                state,
            };
        }
    }
</script>