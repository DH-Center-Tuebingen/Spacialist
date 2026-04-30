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
                        <template v-if="state.editors.length > 0">
                            <li
                                v-for="editor in state.editors"
                                :key="`user-${editor.id}-editor`"
                                class="list-group-item d-flex flex-row justify-content-between align-items-center gap-3"
                                :class="{ 'fst-italic': editor.deleted_at }"
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
        <div class="d-flex flex-column flex-grow-1 px-3 overflow-hidden">
            <div class="row overflow-hidden">
                <div class="col-9 d-flex flex-column h-100 gap-3 border-end overflow-y-scroll">
                    <div
                        v-for="(dataset, aid) in state.attributeData"
                        :key="`attribute-data-${aid}`"
                    >
                        <span
                            class="fw-bold"
                            @click="state.hiddenAttributes[aid] = !state.hiddenAttributes[aid]"
                        >
                            {{ translateConcept(dataset.attribute.thesaurus_url) }}
                            <span v-show="state.hiddenAttributes[aid]">
                                <i class="fas fa-fw fa-eye-slash" />
                            </span>
                            <span v-show="!state.hiddenAttributes[aid]">
                                <i class="fas fa-fw fa-eye" />
                            </span>
                        </span>
                        <div
                            v-if="!state.hiddenAttributes[aid]"
                            class="d-flex flex-row align-items-center gap-3 rounded bg-secondary bg-opacity-10 p-2"
                        >
                            <Attribute
                                class="flex-grow-1"
                                :data="dataset.attribute"
                                :value-wrapper="dataset"
                                :disabled="true"
                                :hide-links="true"
                                :preview="false"
                            />
                            <IconStat
                                :icon="state.certaintyData[aid].icon"
                                :color="state.certaintyData[aid].type"
                                :icon-only="true"
                                :title="`Certainty: ${dataset?.certainty ? dataset.certainty : '-'}%`"
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
                    v-if="state.metadata?.metadata?.summary"
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
        getCertainty,
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

    import { IconStat } from 'dhc-components';

    import EntityTypeLabel from '@/components/entity/EntityTypeLabel.vue';

    export default {
        components: {
            EntityTypeLabel,
            IconStat,
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
                console.log(attributeData);
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

            const getCertaintyColor = certainty => {
                return getCertainty(certainty).type;
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
                hiddenAttributes: {},
                entityType: computed(_ => {
                    if(!state.entity) return;
                    return entityStore.entityTypes[state.entity.entity_type_id];
                }),
                certaintyData: computed(_ => {
                    const data = {};
                    for(let aid in state.attributeData) {
                        data[aid] = getCertainty(state.attributeData[aid].certainty);
                    }
                    return data;
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
                getCertaintyColor,
                // getAttributeTextRepresentation,
                // PROPS
                // STATE
                state,
                userPopoverRef,
                userPopoverContent,
            };
        }
    };
</script>