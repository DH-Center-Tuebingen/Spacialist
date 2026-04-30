<template>
    <div class="row">
        <h3>
            Single Search
        </h3>
    </div>
    <div class="row flex-grow-1 overflow-hidden">
        <div class="col-12 h-100 overflow-hidden d-flex flex-column">
            <p class="lead mb-0">
                Please select an entity type first to search through the database
            </p>
            <EntityTypeSelector
                :entity-types="availableEntityTypes"
                @select="selectEntityType"
            />
        </div>
        <router-view />
    </div>
</template>

<script>
    import {
        computed,
    } from 'vue';

    import { useRouter } from 'vue-router';

    import useEntityStore from '@/bootstrap/stores/entity.js';

    import { useI18n } from 'vue-i18n';

    import {
        sortTranslated,
    } from '@/helpers/helpers.js';

    import EntityTypeSelector from '@/components/openaccess/search/entity-type/TypeSelector.vue';

    export default {
        components: {
            EntityTypeSelector,
        },
        setup(props) {
            const { t } = useI18n();
            const router = useRouter();
            const entityStore = useEntityStore();

            // DATA
            const availableEntityTypes = computed(() =>  {
                if(!entityStore.entityTypes) {
                    return [];
                }
                const filtered = Object.values(entityStore.entityTypes).filter(entityType => entityType.entities_count > 0)
                return filtered.sort(sortTranslated());
            });

            const selectEntityType = entityType => {
                router.push({
                    name: 'entity-type-view',
                    params: {
                        entityTypeId: entityType.id,
                    },
                });
            };

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                availableEntityTypes,
                selectEntityType,
                // PROPS
                // STATE
            };
        }
    };
</script>
