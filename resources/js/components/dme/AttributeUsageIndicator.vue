<template>
    <span 
        :class="state.classes"
        :title="t('main.datamodel.attribute.indicator_info', { cnt: count }, count)"
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
            count: {
                required: true,
                type: Number,
            },
            size: {
                required: false,
                type: Number,
                default: 8,
            },
        },
        setup(props, context) {
            const { t } = useI18n();
            
            const state = reactive({
                classes: computed(_ => {
                    const classes = [];
                    if(props.count > 0) {
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
                state,
            };
        },
    };
</script>