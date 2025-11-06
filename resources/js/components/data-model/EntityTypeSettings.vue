<template>
    <form
        role="form"
        @submit.prevent="updateEntityType"
    >
        <div class="row mb-3">
            <div class="offset-3 col row align-items-center">
                <div class="form-check form-switch">
                    <input
                        id="entity-type-root-toggle"
                        v-model="properties.is_root"
                        class="form-check-input"
                        type="checkbox"
                    >
                    <label
                        class="form-check-label"
                        for="entity-type-root-toggle"
                    >
                        {{ t('main.datamodel.detail.properties.top_level') }}
                    </label>
                </div>
            </div>
            <div class="col align-items-center">
                <div class="row align-items-center">
                    <label
                        for="entity-color"
                        style="width: min-content;"
                    >
                        {{ t('global.color') }}
                    </label>
                    <div class="col align-items-center">
                        <input
                            v-model="properties.color"
                            type="color"
                            class="form-control form-control-color w-100"
                        >
                    </div>
                </div>
            </div>
        </div>
        <!-- TODO: This should be handled using a @PluginHook from within the map plugin! -->
        <div
            v-if="properties?.layer?.type"
            class="mb-3 row"
        >
            <label
                for="entity-geometrytype-ro"
                class="col-form-label col-md-3 text-end"
            >
                {{ t('global.geometry_type') }}
            </label>
            <div class="col-md-9 d-flex align-items-center">
                <span>
                    {{ properties?.layer?.type ?? "" }}
                </span>
                <!-- <router-link :to="{name: 'ldetail', params: { id: entityType.layer.id }}">
                            {{ t('main.datamodel.detail.manage_layer') }}
                        </router-link> -->
            </div>
        </div>
        <div class="mb-2 row">
            <label
                for="dme-allowed-sub-entity-types-select"
                class="col-form-label col-md-3 text-end"
            >
                {{ t('main.datamodel.detail.properties.sub_types') }}
            </label>
            <div class="col-md-9">
                <multiselect
                    id="dme-allowed-sub-entity-types-select"
                    v-model="properties.sub_entity_types"
                    :object="true"
                    :mode="'tags'"
                    :label="'thesaurus_url'"
                    :track-by="'thesaurus_url'"
                    :value-prop="'id'"
                    :options="state.minimalEntityTypes"
                    :close-on-select="false"
                    :close-on-deelect="false"
                    :placeholder="t('global.select.placeholder')"
                >
                    <template #option="{ option }">
                        {{ translateConcept(option.thesaurus_url) }}
                    </template>
                    <template #tag="{ option, handleTagRemove, disabled }">
                        <div class="multiselect-tag">
                            {{ translateConcept(option.thesaurus_url) }}
                            <span
                                v-if="!disabled"
                                class="multiselect-tag-remove"
                                @click.prevent
                                @mousedown.prevent.stop="handleTagRemove(option, $event)"
                            >
                                <span class="multiselect-tag-remove-icon" />
                            </span>
                        </div>
                    </template>
                </multiselect>
                <div class="mt-2 d-flex flex-row gap-2">
                    <div
                        class="btn-group"
                        role="group"
                    >
                        <button
                            type="button"
                            class="btn btn-outline-success btn-sm"
                            @click="addAll"
                        >
                            <i class="fas fa-fw fa-tasks" /> {{ t('global.select_all') }}
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-danger btn-sm"
                            @click="removeAll"
                        >
                            <i class="fas fa-fw fa-times" /> {{ t('global.select_none') }}
                        </button>
                    </div>
                </div>
            </div>
            <header class="d-flex justify-content-end mt-4">
                <LoadingButton
                    :loading="state.saving"
                    :icon="faSave"
                    :disabled="!state.dirty || state.saving"
                    color="success"
                >
                    {{ t('global.save') }}
                </LoadingButton>
            </header>
        </div>
    </form>
</template>

<script>
    import {
        computed,
        reactive,
        watch
    } from 'vue';
    import { faSave } from '@fortawesome/free-regular-svg-icons';
    import { LoadingButton } from 'dhc-components';
    import { useI18n } from 'vue-i18n';

    import {
        translateConcept,
    } from '@/helpers/helpers.js';

    import { useToast } from '@/plugins/toast.js';
    import useEntityStore from '@/bootstrap/stores/entity.js';
    


    export default {
        components: {
            LoadingButton,
        },
        props: {
            entityType: {
                type: Object,
                required: true,
            },
        },
        setup(props) {
            const { t } = useI18n();
            const entityStore = useEntityStore();
            const toast = useToast();

            const properties = reactive({
                color: props.entityType.color,
                is_root: props.entityType.is_root,
                sub_entity_types: props.entityType.sub_entity_types,
            });
            
            watch(() => props.entityType, (newVal) => {
                properties.color = newVal.color;
                properties.is_root = newVal.is_root;
                properties.sub_entity_types = newVal.sub_entity_types;
            });

            const state = reactive({
                saving: false,
                dirty: computed(_ => {
                    if(!props.entityType) return false;
                    const rootDirty = props.entityType.is_root !== properties.is_root;
                    const colorDirty = props.entityType.color !== properties.color;
                    const subTypesDirty = props.entityType.sub_entity_types.length !== properties.sub_entity_types.length ||
                        properties.sub_entity_types.every((v, i) => v.id !== props.entityType.sub_entity_types[i].id);

                    return rootDirty || colorDirty || subTypesDirty;
                }),
                minimalEntityTypes: computed(_ => {
                    return Object.values(entityStore.entityTypes).map(et => ({
                        id: et.id,
                        thesaurus_url: et.thesaurus_url
                    }));
                }),
            });

            // FUNCTIONS
            const updateEntityType = async _ => {
                if(!props.entityType.id) return;

                const et = props.entityType;

                try {
                    state.saving = true;
                    await entityStore.updateEntityType(et.id, properties);
                    const name = translateConcept(props.entityType.thesaurus_url);
                    toast.$toast(
                        t('main.datamodel.toasts.updated_type.msg', {
                            name: name
                        }),
                        t('main.datamodel.toasts.updated_type.title'),
                        {
                            channel: 'success',
                        }
                    );
                } catch(error) {
                    toast.$toast(
                        error?.message ?? error,
                        "Error",
                        {
                            channel: 'danger',
                        }
                    );
                } finally {
                    state.saving = false;
                }
            }

            const addAll = _ => {
                entityType.sub_entity_types = state.minimalEntityTypes.slice();
            };

            const removeAll = _ => {
                entityType.sub_entity_types = [];
            };

            return {
                t,
                state,
                properties,
                faSave: faSave,
                addAll,
                updateEntityType,
                removeAll,
                translateConcept,
            };

        }
    };
</script>