<template>
    <div>
        <div class="bg-secondary bg-opacity-10 p-3 rounded mt-2">
            <span v-if="loading">
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
                            v-if="creatorId"
                            class="d-flex flex-row gap-2 align-items-center"
                        >
                            <UserLabel
                                :user="creator"
                                :special-user-id="creatorId"
                            />
                        </div>
                    </div>
                    <div class="flex-fill flex-wrap">
                        <h6 class="mb-1">
                            {{ t('global.editors') }}
                        </h6>
                        <div class="d-flex flex-row gap-2 align-items-center">
                            <UserLabel
                                v-for="editor in editors"
                                :key="editor.user_id"
                                :user="editor"
                                :special-user-id="creatorId"
                                href="#"
                            />
                        </div>
                    </div>
                    <div>
                        <h6 class="mb-1">
                            {{ t('global.licence') }}
                        </h6>
                        <input
                            v-model="metadata.licence"
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
                            :value="metadata.summary"
                            @change="updateEntitySummary"
                        />
                    </div>
                </form>
            </div>

            <button
                type="button"
                class="d-block ms-auto btn btn-sm btn-outline-success mt-2"
                :disabled="!isDirty"
                @click="saveMetadata"
            >
                <i class="fas fa-fw fa-save" />
                {{ t('main.entity.metadata.save') }}
            </button>
        </div>
    </div>
</template>

<script>
    import {computed, onMounted, reactive, ref, watch} from 'vue';
    import {useI18n} from 'vue-i18n';
    import {useToast} from '@/plugins/toast.js';
    import store from '@/bootstrap/store';

    import {
        getUserBy,
    } from '@/helpers/helpers';

    import {
        fetchEntityMetadata,
        patchEntityMetadata,
    } from '@/api.js';

    import UserLabel from '@/components/UserLabel.vue';

    export default {
        components: {
            UserLabel,
        },
        setup() {
            const toast = useToast();
            const loading = ref(false);

            onMounted(_ => {
                updateMetaData();
            });

            const entity = computed(_ => store.getters.entity);

            const loadedMetadata = reactive({
                summary: '',
                licence: '',
            });

            const metadata = reactive({
                summary: '',
                licence: '',
            });

            const creatorId = computed(_ => {
                if(!entity.value?.metadata?.creator) return 0;
                return entity.value.metadata.creator;
            });

            const creator = computed(_ => {
                if(creatorId.value == -1) return {name: 'N/A', id: -1, avatar: null};
                const user = getUserBy(creatorId.value);
                return user || {name: 'N/A', id: 0, avatar: null};
            });

            const editors = computed(_ => {
                if(!entity.value?.metadata?.editors) return [];
                return entity.value.metadata.editors.map(({user_id}) => {
                    const user = getUserBy(user_id);
                    return user || {name: 'N/A', id: 0, avatar: null};
                });
            });

            const changedMetadata = computed(_ => {
                let changedMetadata = {};
                for(const key in metadata) {
                    if(metadata[key] != loadedMetadata[key]) {
                        changedMetadata[key] = metadata[key];
                    }
                }
                return changedMetadata;
            });

            const isDirty = computed(_ => {
                return Object.keys(changedMetadata.value).length > 0;
            });


            const saveMetadata = async _ => {
                if(isDirty.value) {
                    patchEntityMetadata(entity.value.id, changedMetadata.value).then(data => {

                        setMetadata({...metadata});

                        store.dispatch('updateEntityMetadata', {
                            eid: entity.value.id,
                            data: data,
                        });

                        toast.$toast(
                            t('main.entity.toasts.updated_metadata.msg', {
                                name: data.name
                            }),
                            t('main.entity.toasts.updated_metadata.title'), {
                            channel: 'success',
                            autohide: true,
                            icon: true,
                        });
                    }).catch(console.error);
                } else console.error('No changes to save');
            };

            const setMetadata = (data) => {
                loadedMetadata.summary = data.summary || '';
                loadedMetadata.licence = data.licence || '';
                metadata.summary = data.summary || '';
                metadata.licence = data.licence || '';
            };

            const updateEntitySummary = e => {
                metadata.summary = e.value;
            };

            const updateMetaData = _ => {
                if(!entity.value?.id) return;
                loading.value = true;
                fetchEntityMetadata(entity.value.id).then((value) => {
                    loading.value = false;
                    setMetadata(value.metadata);
                });
            };


            watch(entity, (newVal) => {
                loading.value = false;
                if(newVal?.id) {
                    updateMetaData();
                }
            });


            return {
                changedMetadata,
                creator,
                creatorId,
                editors,
                entity,
                isDirty,
                metadata,
                saveMetadata,
                t: useI18n().t,
                updateEntitySummary,
            };
        }
    };
</script>