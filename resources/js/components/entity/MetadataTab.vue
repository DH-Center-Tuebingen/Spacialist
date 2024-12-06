<template>
    <div>
        <div class="bg-secondary bg-opacity-10 p-3 rounded mt-2">
            <span v-if="state.loading">
                <i class="fas fa-fw fa-spinner fa-spin" />
            </span>
            <div
                v-else
                class="d-flex flex-column h-100"
            >
                <div class="d-flex flex-row gap-2 justify-content-between">
                    <div class="flex-fill">
                        <h6 class="mb-1">
                            {{ t('global.creator') }}
                        </h6>

                        <div
                            v-if="state.creatorId"
                            class="d-flex flex-row gap-2 align-items-center"
                        >
                            <UserLabel
                                :user="state.creator"
                                :special-user-id="state.creator.id"
                            />
                        </div>
                    </div>
                    <div class="flex-fill flex-wrap">
                        <h6 class="mb-1">
                            {{ t('global.editors') }}
                        </h6>
                        <div class="d-flex flex-row gap-2 align-items-center">
                            <UserLabel
                                v-for="editor in state.editors"
                                :key="editor.user_id"
                                :user="editor"
                                :special-user-id="state.creatorId"
                            />
                        </div>
                    </div>
                    <div>
                        <h6 class="mb-1">
                            {{ t('global.licence') }}
                        </h6>
                        <input
                            v-model="state.metadata.licence"
                            type="text"
                            class="form-control"
                            placeholder="CC-BY 4.0, ..."
                        >
                    </div>
                </div>
                <form>
                    <div>
                        <label
                            for="entity-metadata-summary"
                            class="form-label mb-1"
                        >
                            <h6 class="mb-0">
                                {{ t('global.summary') }}
                            </h6>
                        </label>
                        <richtext
                            id="entity-metadata-summary"
                            class="bg-white rounded"
                            :value="state.metadata.summary"
                            @change="updateEntitySummary"
                        />
                    </div>
                </form>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-warning mt-2"
                    :disabled="!state.isDirty"
                    @click="resetForm"
                >
                    <i class="fas fa-fw fa-undo" />
                    {{ t('global.reset') }}
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-success mt-2"
                    :disabled="!state.isDirty"
                    @click="save"
                >
                    <i class="fas fa-fw fa-save" />
                    {{ t('main.entity.metadata.save') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        ref,
        watch,
    } from 'vue';

    import {
        useI18n,
    } from 'vue-i18n';

    import {
        useToast,
    } from '@/plugins/toast.js';

    import useEntityStore from '@/bootstrap/stores/entity.js';
    import useUserStore from '@/bootstrap/stores/user.js';

    import {
        patchEntityMetadata,
    } from '@/api.js';

    import UserLabel from '@/components/UserLabel.vue';

    export default {
        components: {
            UserLabel,
        },
        setup() {
            const { t } = useI18n();
            const entityStore = useEntityStore();
            const userStore = useUserStore();
            const toast = useToast();

            const defaultUser = {
                name: 'N/A',
                id: 0,
                avatar: null,
            };
            const state = reactive({
                loading: false,
                loadedMetadata: {
                    summary: '',
                    licence: '',
                },
                metadata: {
                    summary: '',
                    licence: '',
                },
                entity: computed(_ => entityStore.selectedEntity),
                creatorId: computed(_ => {
                    if(!state.entity?.metadata?.creator) return 0;
                    return state.entity.metadata.creator;
                }),
                creator: computed(_ => {
                    if(state.creatorId == -1) {
                        return defaultUser;
                    }

                    const user = userStore.getUserBy(state.creatorId);
                    return user || defaultUser;
                }),
                editors: computed(_ => {
                    if(!state.entity?.metadata?.editors) return [];
                    return state.entity.metadata.editors.map(u => {
                        const user = userStore.getUserBy(u.user_id);
                        return user || defaultUser;
                    });
                }),
                changedMetadata: computed(_ => {
                    let changedMetadata = {};
                    for(const key in state.metadata) {
                        if(state.metadata[key] != state.loadedMetadata[key]) {
                            changedMetadata[key] = state.metadata[key];
                        }
                    }
                    return changedMetadata;
                }),
                isDirty: computed(_ => Object.keys(state.changedMetadata).length > 0),
            });

            const save = async _ => {
                if(state.isDirty) {
                    patchEntityMetadata(state.entity.id, state.changedMetadata).then(data => {

                        setMetadata({...metadata});

                        entityStore.updateEntityMetadata(state.entity.id, data);

                        toast.$toast(
                            t('main.entity.toasts.updated_metadata.msg', {
                                name: data.name
                            }),
                            t('main.entity.toasts.updated_metadata.title'), {
                            channel: 'success',
                            autohide: true,
                            icon: true,
                        });
                    }).catch(e => {
                        console.error(e);
                    });
                } else {
                    console.error('No changes to save');
                }
            };

            const resetForm = _ => {
                state.metadata.summary = state.loadedMetadata.summary;
                state.metadata.licence = state.loadedMetadata.licence;
            };

            // TODO can data be loaded from store?
            const setMetadata = data => {
                state.loadedMetadata.summary = data.summary || '';
                state.loadedMetadata.licence = data.licence || '';
                state.metadata.summary = data.summary || '';
                state.metadata.licence = data.licence || '';
            };

            const updateEntitySummary = e => {
                state.metadata.summary = e.value;
            };

            const updateMetadata = _ => {
                if(!state.entity?.id) return;
                state.loading = true;
                entityStore.fetchEntityMetadata(state.entity.id).then(value => {
                    state.loading = false;
                    setMetadata({...value.metadata});
                });
            };

            onMounted(_ => {
                updateMetadata();
            });

            watch(_ => state.entity, (newVal) => {
                state.loading = false;
                if(newVal?.id) {
                    updateMetadata();
                }
            });

            return {
                t,
                // HELPERS
                // LOCAL
                save,
                resetForm,
                updateEntitySummary,
                // STATE
                state,
            };
        }
    };
</script>