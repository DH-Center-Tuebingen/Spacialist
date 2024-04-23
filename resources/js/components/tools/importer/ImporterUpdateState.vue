<template>
    <div class="importer-update-state">
        <div
            class="alert d-flex align-items-center gap-4"
            :class="activeOption.class"
        >
            <i :class="activeOption.icon" />
            <span>
                {{ t(`main.importer.validation.${activeOption.text}`) }}
            </span>
        </div>

        <div class="input-group">
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
    </div>
</template>

<script>
    import { computed } from 'vue';
    import { useI18n } from 'vue-i18n';

    import ImporterUpdateItem from './ImporterUpdateItem.vue';

    export default {
        components: {
            ImporterUpdateItem
        },
        props: {
            conflict: {
                type: Number,
                required: true
            },
            update: {
                type: Number,
                required: true
            },
            create: {
                type: Number,
                required: true
            },

        },
        setup(props) {

            const { t } = useI18n();

            const options = {
                conflict: {
                    text: 'multiple_error',
                    class: 'alert-danger',
                    icon: 'fas fa-exclamation-circle',
                },
                create: {
                    text: 'create',
                    class: 'alert-success',
                    icon: 'fas fa-circle-check',
                },
                mixed: {
                    text: 'mixed',
                    class: 'alert-warning',
                    icon: 'fas fa-exclamation-circle',
                },
                no_items: {
                    text: 'no_items',
                    class: 'alert-danger',
                    icon: 'fas fa-exclamation-circle',
                },
                update: {
                    text: 'update',
                    class: 'alert-warning',
                    icon: 'fas fa-exclamation-circle',
                },
            };

            const activeOption = computed(() => {
                if(props.conflict > 0)
                    return options.value.conflict;
                if(props.update > 0 && props.create > 0)
                    return options.value.mixed;
                if(props.update > 0)
                    return options.value.update;
                if(props.create > 0)
                    return options.value.create;

                return options.value.no_items;
            });

            return {
                t,
                activeOption
            };
        }
    };
</script>