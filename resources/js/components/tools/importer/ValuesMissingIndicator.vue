<template>
    <div class="value-missing-indicator">
        <span
            v-if="total == 0"
            :title="t('main.importer.indicator.missing_value')"
        >
            <i
                class="fas fa-fw fa-exclamation-circle"
                :class="required ? 'text-danger' : 'text-body-tertiary'"
            />
        </span>
        <template v-else-if="missing > 0">
            <span
                v-if="allowEmpty"
                :title="t('main.importer.indicator.missing_non_required_values', {
                    miss: missing,
                    total: total,
                }, total)"
            >
                <i class="fas fa-fw fa-exclamation-circle text-warning" />
            </span>
            <span
                v-else
                :title="t('main.importer.indicator.missing_required_values', {
                    miss: missing,
                    total: total,
                }, total)"
            >
                <i class="fas fa-fw fa-exclamation-circle text-danger" />
            </span>
        </template>
        <span
            v-else
            :title="t('main.importer.indicator.no_missing_values')"
        >
            <i class="fas fa-fw fa-check-circle text-success" />
        </span>
    </div>
</template>

<script>
    import { useI18n } from 'vue-i18n';

    import {
        translateConcept,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            required: {
                type: Boolean,
                default: false,
            },
            allowEmpty: {
                type: Boolean,
                default: true,
            },
            missing: {
                type: Number,
                required: true,
            },
            total: {
                type: Number,
                required: true,
            }
        },
        setup(props) {
            return {
                t: useI18n().t,
                translateConcept,
            };
        },
    };
</script>