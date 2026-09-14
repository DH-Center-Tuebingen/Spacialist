<template>
    <span
        :class="state.classes"
        :title="title"
    >
        <!--
            The icon is not ideal, it should be a circle with outline.
            But in FontAwesome this icon is not available to us.
            When switching to a different icon library, this should be updated.
        -->
        <i class="fas fa-circle-info" />
    </span>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            list: {
                required: true,
                type: Array,
            },
            size: {
                required: false,
                type: Number,
                default: 8,
            },
        },
        setup(props, context) {
            const { t } = useI18n();

            const count = computed(() => props.list.length);

            const listToString = computed(() => props.list.join(', '));

            const title = computed(() => {
                let content = t('main.datamodel.attribute.indicator_info', { cnt: count.value }, count.value);
                if(props.list.length > 0) {
                    content += `\n${listToString.value}`;
                }
                return content;
            });

            const state = reactive({
                classes: computed(_ => {
                    const classes = [];
                    if(count.value > 0) {
                        classes.push('text-secondary');
                        classes.push('opacity-25');
                    }else {
                        classes.push('text-danger');
                    }
                    return classes;
                }),
            });

            return {
                t,
                title,
                state,
            };
        },
    };
</script>