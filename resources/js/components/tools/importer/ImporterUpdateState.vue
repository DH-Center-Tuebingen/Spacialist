<template>
    <div class="importer-update-state">
        <div class="input-group mb-1">
            <ImporterUpdateItem
                :value="create"
                icon="fas fa-fw fa-circle-plus"
                class="input-group-text flex-grow-1 text-success"
                :title="t('global.create')"
            />
            <ImporterUpdateItem
                :value="update"
                icon="fas fa-fw fa-circle-arrow-up"
                class="input-group-text flex-grow-1 text-warning"
                :title="t('global.update')"
            />
            <ImporterUpdateItem
                :value="conflict"
                icon="fas fa-fw fa-circle-exclamation"
                class="input-group-text flex-grow-1 text-danger"
                :title="t('global.conflict')"
            />
        </div>
        <Alert
            v-if="errors.length == 0"
            :message="t(`main.importer.validation.${activeOption.text}`)"
            :noicon="false"
            :type="activeOption.type"
        />
        <ErrorList
            v-for="(error, index) in errors"
            v-else
            :key="index"
            class="alert alert-danger mb-1 py-1 px-2"
            :value="error"
        />
    </div>
</template>

<script>
    import { computed } from 'vue';
    import { useI18n } from 'vue-i18n';

    import ImporterUpdateItem from './ImporterUpdateItem.vue';
    import ErrorList from '@/components/error/ErrorList.vue';

    export default {
        components: {
            ErrorList,
            ImporterUpdateItem,
        },
        props: {
            conflict: {
                type: Number,
                required: true,
            },
            update: {
                type: Number,
                required: true,
            },
            create: {
                type: Number,
                required: true,
            },
            imported: {
                type: Boolean,
                required: true,
            },
            errors: {
                type: Array,
                default: () => [],
            },
        },
        setup(props) {
            const { t } = useI18n();

            const options = {
                conflict: {
                    text: 'multiple_error',
                    type: 'error',
                },
                create: {
                    text: 'create',
                    type: 'success',
                },
                mixed: {
                    text: 'mixed',
                    type: 'warning',
                },
                no_items: {
                    text: 'no_items',
                    type: 'error',
                },
                update: {
                    text: 'update',
                    type: 'warning',
                },
                imported: {
                    text: 'imported',
                    type: 'success',
                },
            };

            const activeOption = computed(() => {
                if(props.imported) {
                    return options.imported;
                }
                if(props.conflict > 0) {
                    return options.conflict;
                }
                if(props.update > 0 && props.create > 0) {
                    return options.mixed;
                }
                if(props.update > 0) {
                    return options.update;
                }
                if(props.create > 0) {
                    return options.create;
                }

                return options.no_items;
            });

            const splitLines = string => {
                return string.split(', ');
            };

            return {
                t,
                activeOption,
                splitLines,
            };
        }
    };
</script>