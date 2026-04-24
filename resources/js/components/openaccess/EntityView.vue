<template>
    <div
        v-if="state.loaded"
        class="h-100 d-flex flex-column gap-3 overflow-hidden"
    >
        <div class="d-flex flex-row justify-content-between bg-primary bg-opacity-10 rounded">
            <div class="d-flex flex-row align-items-center px-3 py-2 gap-2">
                <h2 class="m-0">
                    {{ state.entity.name }}
                </h2>
                <EntityTypeLabel
                    :type="state.entity.entity_type_id"
                    :icon-only="false"
                />
            </div>
            <div class="d-flex flex-row gap-2 align-items-center px-3 py-2">
                <div>
                    <span class="fw-bold">
                        <i class="fas fa-clock-rotate-left" />
                        Created
                    </span>
                    <span :title="new Date(state.entity.created_at).toLocaleString()">
                        {{ ago(state.entity.created_at) }}
                    </span>
                </div>
                <span class="text-muted">•</span>
                <div>
                    <span class="fw-bold">
                        <i class="far fa-clock" />
                        Last updated
                    </span>
                    <span :title="new Date(state.entity.updated_at).toLocaleString()">
                        {{ ago(state.entity.updated_at) }}
                    </span>
                </div>
                <span class="text-muted">•</span>
                <div>
                    <span class="fw-bold">
                        <i class="fas fa-file-signature" />
                        Licence
                    </span>
                    <span>
                        <span v-if="state.metadata?.metadata?.licence">
                            {{ state.metadata.metadata.licence }}
                        </span>
                        <span
                            v-else
                            class="fst-italic text-danger"
                        >
                            Unlicenced
                        </span>
                    </span>
                </div>
                <span class="text-muted">•</span>
                <a
                    :ref="el => userPopoverRef = el"
                    tabindex="0"
                    data-bs-toggle="popover"
                    data-bs-trigger="focus"
                >
                    <i class="fas fa-fw fa-users" />
                </a>
                <div :ref="el => userPopoverContent = el">
                    <ul class="list-group list-group-flush popup-list-group">
                        <template
                            v-if="state.editors.length > 0"
                        >
                            <li
                                v-for="editor in state.editors"
                                :key="`user-${editor.id}-editor`"
                                class="list-group-item d-flex flex-row justify-content-between align-items-center gap-3"
                                :class="{'fst-italic': editor.deleted_at}"
                            >
                                {{ editor.name }}
                                <a
                                    v-if="editor.metadata?.orcid"
                                    :title="editor.metadata.orcid"
                                    :href="`https://orcid.org/${editor.metadata.orcid}`"
                                    target="_blank"
                                >
                                    <i class="fab fa-fw fa-orcid text-success" />
                                </a>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
        <div class="flex-grow-1 px-3 overflow-y-scroll overflow-x-hidden">
            <div class="row">
                <div class="col-9 d-flex flex-column gap-3 border-end">
                    <div
                        v-for="(dataset, aid) in state.attributeData"
                        :key="`attribute-data-${aid}`"
                    >
                        {{ translateConcept(dataset.attribute.thesaurus_url) }}
                        {{ dataset.value }}
                        <div
                            class="progress"
                            role="progressbar"
                            :aria-valuenow="`${dataset.certainty || 100}`"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            style="height: 1px"
                        >
                            <div
                                class="progress-bar"
                                :class="getCertaintyClass(dataset.certainty)"
                                :style="`width: ${dataset.certainty || 100}%`"
                                :title="`${dataset.certainty || 100}% certain`"
                            />
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="aspect-ratio-1 rounded d-flex justify-content-center align-items-center bg-secondary-subtle overflow-hidden">
                        <template v-if="!state.map">
                            <div
                                class="w-100 h-100"
                                style="background-image: url('/img/no-map-bg.png'); background-position: center; background-repeat: no-repeat; background-size: cover; filter: blur(3px);"
                            />
                            <span class="position-absolute fw-medium">
                                No Map available
                            </span>
                        </template>
                        <template v-else>
                            <div class="w-100 h-100 d-flex justify-content-center align-items-center fst-italic">
                                Map
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <hr>
            <div>
                <h4>
                    Summary/Description
                </h4>
                <Richtext
                    v-if="state.metadata.metadata.summary"
                    class="bg-secondary bg-opacity-10 rounded font-serif"
                    :classes="'mt-2 px-2 py-1 rounded text-body'"
                    :disabled="true"
                    :value="state.metadata.metadata.summary"
                />
                <span
                    v-else
                    class="fst-italic text-muted"
                >
                    No summary/description available
                </span>
            </div>
            <hr>
            <div>
                <h4>Files</h4>
                <span class="fst-italic text-muted">
                    No files linked with this entry
                </span>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        nextTick,
        reactive,
        ref,
    } from 'vue';

    import {
        useRoute,
    } from 'vue-router';

    import useEntityStore from '@/bootstrap/stores/entity.js';
    import useUserStore from '@/bootstrap/stores/user.js';

    import {
        Popover,
    } from 'bootstrap';

    import {
        getCertaintyClass,
        translateConcept,
    } from '@/helpers/helpers.js';

    import {
        ago,
    } from '@/helpers/filters.js';

    import {
        getEntity,
        getEntityData,
    } from '@/open_api.js';

    import { useI18n } from 'vue-i18n';

    import EntityTypeLabel from '@/components/entity/EntityTypeLabel.vue';

    export default {
        components: {
            EntityTypeLabel,
        },
        setup(props) {
            const { t } = useI18n();
            const route = useRoute();
            const entityStore = useEntityStore();
            const userStore = useUserStore();

            // FUNCTIONS
            const initialize = async _ => {
                const entityData = await getEntity(route.params.id);
                state.entity = entityData.entity;
                state.metadata = entityData.metadata;

                const attributeData = await getEntityData(route.params.id);
                state.attributeData = attributeData;
                state.loaded = true;
                nextTick(_ => {
                    const popup = new Popover(userPopoverRef.value, {
                        placement: 'bottom',
                        title: '<span class="fw-bold">Editors</span',
                        content: userPopoverContent.value,
                        html: true,
                    });
                    popup.show();
                    popup.hide();
                });
            };

            const getAnyUser = id => {
                const user = userStore.getUserBy(id);
                if(!user) {
                    return userStore.deletedUsers.find(du => du.id == id);
                }
                return user;
            };

            const copyOrcidToClipboard = orcid => {
                navigator.clipboard.writeText(orcid);
            };

            // FETCH
            initialize();

            // DATA
            const userPopoverRef = ref();
            const userPopoverContent = ref();
            const state = reactive({
                map: false,
                loaded: false,
                entity: null,
                metadata: null,
                attributeData: null,
                entityType: computed(_ => {
                    if(!state.entity) return;
                    return entityStore.entityTypes[state.entity.entity_type_id];
                }),
                creator: computed(_ => {
                    if(state.metadata?.creator) {
                        return getAnyUser(state.metadata.creator);
                    }
                    return null;
                }),
                editors: computed(_ => {
                    const allEditors = [];
                    if(state.creator) {
                        allEditors.push(state.creator);
                    }
                    if(state.metadata?.editors) {
                        state.metadata.editors.forEach(editor => {
                            if(editor.user_id != state.creator?.id) {
                                allEditors.push(getAnyUser(editor.user_id));
                            }
                        });
                    }

                    return allEditors;
                }),
            });

            // WATCHER

            // RETURN
            return {
                t,
                // HELPERS
                ago,
                getCertaintyClass,
                translateConcept,
                // LOCAL
                getAnyUser,
                copyOrcidToClipboard,
                // PROPS
                // STATE
                state,
                userPopoverRef,
                userPopoverContent,
            };
        }
    };
</script>