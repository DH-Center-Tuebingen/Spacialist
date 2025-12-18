<template>
    <!-- In epoch we can decide if we use BC/AD or CE/BCE -->
    <div class="epoch-attribute-template">
        <label
            :title="t('global.attribute-template.epoch-format')"
            class="form-label text-truncate"
        >
            {{ t('global.attribute-template.epoch-format') }}
        </label>
        <div class="col">
            <select
                v-model="epochFormat"
                class="form-select"
                style="min-width: max-content;"
            >
                <option
                    v-for="option in getEpochTemplateOptions()"
                    :value="option.key"
                >{{ option.templateLabel }}</option>
            </select>
        </div>
    </div>
</template>

<script>
    import {
        computed,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import {
        defaultEpochFormat,
        getEpochTemplateOptions,
    } from '@/helpers/attributes/epoch.js';

    export default {
        props: {
            modelValue: {
                type: Object,
                required: true,
            },
        },
        emits: ['update:modelValue'],
        setup(props, context) {
            const t = useI18n().t;

            // FUNCTIONS
            const updateModelValue = (updates) => {
                context.emit('update:modelValue', {
                    ...props.modelValue,
                    ...updates,
                });
            };

            // DATA
            const epochFormat = computed({
                get: _ => props.modelValue?.metadata?.epoch_format ?? defaultEpochFormat,
                set: value => {
                    updateModelValue({
                        metadata: {
                            ...props.modelValue?.metadata,
                            epoch_format: value,
                        },
                    });
                },
            });

            // RETURN
            return {
                t,
                epochFormat,
                getEpochTemplateOptions
            };
        },
    };
</script>

<style lang='scss' scoped></style>