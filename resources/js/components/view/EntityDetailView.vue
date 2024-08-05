<template>
    <div>
        <h1>Entity: {{ entity.id }}</h1>
        <EntityDetail
            v-if="entity"
            :entity="entity"
            :user="user"
        />
    </div>
</template>

<script>
    import store from '@/bootstrap/store';
    import {
        useRoute,
    } from 'vue-router';
    import EntityDetail from '@/components/EntityDetail.vue';

    import {
        getAttribute,
        getEntityTypeAttributeSelections,
        getEntityTypeDependencies,
    } from '@/helpers/helpers.js';

    import { computed, ref, watch } from 'vue';

    export default {
        components: {
            EntityDetail,
        },
        setup() {

            const route = useRoute();

            const entity = computed(() => store.getters.entity);
            const entityAttributes = computed(_ => store.getters.entityTypeAttributes(entity.value.entity_type_id));
            const entityTypeDependencies = computed(_ => getEntityTypeDependencies(entity.value.entity_type_id));

            const loading = ref(false);

            watch(_ => route.params,
                async (newParams, oldParams) => {
                    if(newParams.id == oldParams.id) return;
                    if(!newParams.id) return;
                    loading.value = true;
                    store.dispatch('getEntity', newParams.id).then(_ => {
                        getEntityTypeAttributeSelections();
                        loading.value = false;
                        updateAllDependencies();
                    });
                });

            const updateDependencyState = (aid, value) => {
                const attrDeps = entityTypeDependencies.value[aid];
                if(!attrDeps) return;
                const type = getAttribute(aid).datatype;
                attrDeps.forEach(ad => {
                    let matches = false;
                    switch(ad.operator) {
                        case '=':
                            if(type == 'string-sc') {
                                matches = value?.id == ad.value;
                            } else if(type == 'string-mc') {
                                matches = value && value.some(mc => mc.id == ad.value);
                            } else {
                                matches = value == ad.value;
                            }
                            break;
                        case '!=':
                            if(type == 'string-sc') {
                                matches = value?.id != ad.value;
                            } else if(type == 'string-mc') {
                                matches = value && value.every(mc => mc.id != ad.value);
                            } else {
                                matches = value != ad.value;
                            }
                            break;
                        case '<':
                            matches = value < ad.value;
                            break;
                        case '>':
                            matches = value > ad.value;
                            break;
                    }
                    state.hiddenAttributes[ad.dependant] = {
                        hide: !matches,
                        hide: matches,
                        by: aid,
                    };
                });
            };

            const updateAllDependencies = _ => {
                if(!entityAttributes.value) return;

                for(let i = 0; i < entityAttributes.value.length; i++) {
                    const curr = entityAttributes.value[i];
                    updateDependencyState(curr.id, entity.value.data[curr.id].value);
                }
            };

            // FETCH
            store.dispatch('getEntity', route.params.id).then(_ => {
                getEntityTypeAttributeSelections();
                // initFinished = true;
                updateAllDependencies();
            });

            return {
                entity
            };
        },
    };
</script>