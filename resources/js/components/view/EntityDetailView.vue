<template>
    <div class="h-100 d-flex flex-column">
        <entity-breadcrumbs
            v-if="showBreadcrumb"
            class="mb-2 small"
            :entity="entity"
        />
        <EntityDetail
            v-if="entity"
            :entity="entity"
            :user="user"
            @view-change="setView"
        />
    </div>
</template>

<script>
    import store from '@/bootstrap/store';
    import router from '%router';
    import {
        useRoute,
    } from 'vue-router';
    import EntityDetail from '@/components/EntityDetail.vue';

    import {
        getAttribute,
        getEntityTypeAttributeSelections,
        getEntityTypeDependencies,
    } from '@/helpers/helpers.js';

    import { computed, onMounted, reactive, watch } from 'vue';

    export default {
        components: {
            EntityDetail,
        },
        setup(props) {

            const route = useRoute();

            const entity = computed(() => store.getters.entity);
            const entityAttributes = computed(_ => store.getters.entityTypeAttributes(entity.value.entity_type_id));
            const entityTypeDependencies = computed(_ => getEntityTypeDependencies(entity.value.entity_type_id));


            const state = reactive({
                loading: false,
                hiddenAttributes: {},
                initialized: false,
            });

            watch(_ => route.params,
                async (newParams, oldParams) => {
                    if(newParams.id == oldParams.id) return;
                    if(!newParams.id) return;
                    state.loading = true;
                    store.dispatch('getEntity', newParams.id).then(_ => {
                        getEntityTypeAttributeSelections();
                        state.loading = false;
                        updateAllDependencies();
                    });
                });

            const setView = view => {
                const query = {
                    view,
                };

                router.push({
                    query: {
                        ...route.query,
                        ...query,
                    }
                });
            };

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

            const showBreadcrumb = computed(_ => {
                return entity.value.parentIds && entity.value.parentIds.length > 1;
            });

            async function initializeIfNecessary(id = null) {
                if(id == null) return;

                if(!state.initialized) {
                    const entity = await store.dispatch('getEntity', id);
                    await store.commit('setEntity', entity);
                    state.initialized = true;
                    updateAllDependencies();
                }
            }

            const updateAllDependencies = _ => {
                if(!entityAttributes.value) return;

                for(let i = 0; i < entityAttributes.value.length; i++) {
                    const curr = entityAttributes.value[i];
                    //TODO REIMPLEMENT
                    // updateDependencyState(curr.id, entity.value.data[curr.id].value);
                }
            };

            // FETCH
            store.dispatch('getEntity', route.params.id).then(_ => {
                getEntityTypeAttributeSelections();
                state.initialized = true;
                updateAllDependencies();
            });

            onMounted(() => {
                initializeIfNecessary(route.params.id);
            });

            return {
                entity,
                setView,
                showBreadcrumb,
                state,
            };
        },
    };
</script>