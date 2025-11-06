<template>
    <div
        v-dcan="'entity_data_write|entity_data_read'"
        class="row d-flex flex-row overflow-hidden h-100 pt-3"
    >
        <EntityTypeList
            class="col-md-2 h-100 d-flex flex-column"
            :data="state.entityTypes"
            :selected-id="state.selectedEntityTypeId"
            @select="setEntityType"
        >
            <template #header>
                <div class="d-flex flex-row gap-2 align-items-center justify-content-between mb-2">
                    <h2 class="fs-heading fw-bold m-0">
                        {{ t('main.datamodel.entity.title') }}
                    </h2>
                    <div class="toolbar d-flex gap-1 align-items-center">
                        <button
                            type="button"
                            class="btn btn-outline-success btn-sm"
                            @click="showAddEntityType()"
                        >
                            <i class="fas fa-fw fa-plus" />
                        </button>
                    </div>
                </div>
            </template>
        </EntityTypeList>
        <div class="col-md-6 h-100 px-3 pb-3">
            <router-view />
        </div>
        <AttributeSelectionList :selectedEntityType="state.selectedEntityType">
            <template #header>
                <h2 class="fs-heading fw-bold m-0">
                    {{ t('main.datamodel.attribute.title') }}
                </h2>
            </template>
        </AttributeSelectionList>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        useRoute,
    } from 'vue-router';

    import useEntityStore from '@/bootstrap/stores/entity.js';
    import router from '%router';

    import {
        getEntityTypeAttributes,
    } from '@/helpers/helpers.js';

    import {
        getEntityTypeOccurrenceCount,
    } from '@/api.js';

    import {
        showAddEntityType,
    } from '@/helpers/modal.js';


    import AttributeSelectionList from '@/components/attribute/AttributeSelectionList.vue';

    export default {
        components: {
            AttributeSelectionList
        },
        setup(props, context) {
            const { t } = useI18n();
            const currentRoute = useRoute();
            const entityStore = useEntityStore();

            // FETCH

            // FUNCTIONS
            const setEntityType = event => {
                router.push({
                    name: 'dmdetail',
                    params: {
                        id: event.type.id
                    }
                });
            };

            // DATA
            const state = reactive({
                entityTypes: computed(_ => Object.values(entityStore.entityTypes)),
                selectedEntityTypeId: computed(_ => parseInt(currentRoute.params.id)),
                selectedEntityType: computed(_ => {
                    return getEntityTypeAttributes(state.selectedEntityTypeId)
                }),
            });

            // RETURN
            return {
                t,
                state,
                // LOCAL
                setEntityType,
                showAddEntityType,
            };
        },
    };
</script>
