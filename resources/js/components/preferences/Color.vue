<template>
    <div class="row">
        <label class="col-md-2 form-label"></label>
        <div class="col-md-10">
            <multiselect
                v-model="data"
                id="color-search"
                :trackBy="'id'"
                :label="'id'"
                :valueProp="'key'"
                :hideSelected="true"
                :mode="'single'"
                :filterResults="true"
                :options="state.colorList"
                :searchable="true"
                :readonly="readonly"
                :placeholder="t('global.select.placeholder')"
                @change="onChange">
                <template v-slot:option="{ option }">
                    {{ t(`main.preference.key.color.style.${option.id}`) }}
                </template>
                <template v-slot:singlelabel="{ value }">
                    <div class="multiselect-single-label">
                        {{ t(`main.preference.key.color.style.${value.id}`) }}
                    </div>
                </template>
            </multiselect>
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
        getSupportedColorSets,
    } from '@/bootstrap/color.js';

    export default {
        props: {
            data: {
                required: true,
                type: String,
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
            const onChange = value => {
                if(readonly.value) return;
                context.emit('changed', {
                    value: value
                });
            };

            // DATA
            const state = reactive({
                colorList: getSupportedColorSets(),
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