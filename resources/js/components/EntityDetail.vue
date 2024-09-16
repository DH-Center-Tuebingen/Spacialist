<template>
    <div class="entity-details">
        <EntityDetailHeader
            :entity="state.entity"
            :entity-user="state.entityUser"
            :dirty="formDirty"
            @save="saveEntity"
            @reset="resetForm"
            @delete="confirmDeleteEntity"
        />
        <ul
            id="entity-detail-tabs"
            class="nav nav-tabs"
            role="tablist"
        >
            <li
                v-for="(tg, key) in state.entityGroups"
                :key="`attribute-group-${tg.id}-tab`"
                class="nav-item"
                role="presentation"
            >
                <a
                    :id="`active-entity-attributes-group-${tg.id}-tab`"
                    class="nav-link active-entity-attributes-tab active-entity-detail-tab d-flex gap-2 align-items-center"
                    href="#"
                    @click.prevent="setView(`attributes-${key}`)"
                >
                    <span class="fa-layers fa-fw">
                        <i class="fas fa-fw fa-layer-group" />
                        <span class="fa-layers-counter fa-counter-lg bg-secondary-subtle text-reset">
                            {{ tg.data.length }}
                        </span>
                    </span>
                    <span v-if="key == 'default'">
                        {{ t('main.entity.tabs.default') }}
                    </span>
                    <span v-else>
                        {{ translateConcept(key) }}
                    </span>
                    <div
                        v-if="state.dirtyStates[tg.id]"
                        class="d-flex flex-row gap-2 align-items-center"
                        @mouseover="showTabActions(tg.id, true)"
                        @mouseleave="showTabActions(tg.id, false)"
                    >
                        <i class="fas fa-fw fa-2xs fa-circle text-warning" />
                        <div v-show="state.attributeGrpHovered == tg.id">
                            <a
                                href="#"
                                @click.prevent.stop="saveEntity(`${tg.id}`)"
                            >
                                <i class="fas fa-fw fa-save text-success" />
                            </a>
                            <a
                                href="#"
                                @click.prevent.stop="resetForm(`${tg.id}`)"
                            >
                                <i class="fas fa-fw fa-undo text-warning" />
                            </a>
                        </div>
                    </div>
                </a>
            </li>
            <template v-if="state?.entity?.data?.tabbedChildren">
                <li
                    v-for="child in state.entity.data.tabbedChildren"
                    :key="child.id"
                    class="nav nav-item"
                    role="tablist"
                >
                    <a
                        :id="`active-entity-child-${child.id}-tab`"
                        class="nav-link active-entity-detail-tab d-flex gap-2 align-items-center"
                        href="#"
                        @click.prevent="setEntityView(child)"
                    >
                        <i class="fas fa-fw fa-cube" />
                        {{ child.name }}
                    </a>
                </li>
            </template>
            <!-- empty nav-item to separate metadata and comments from attributes -->
            <li class="nav-item nav-item-list-divider ms-auto" />
            <li
                v-show="can('entity_read')"
                class="nav-item"
                role="presentation"
            >
                <a
                    id="active-entity-metadata-tab"
                    class="nav-link active-entity-detail-tab d-flex gap-2 align-items-center"
                    href="#"
                    @click.prevent="setView('metadata')"
                >
                    <i class="fas fa-fw fa-file-shield" />
                    {{ t('main.entity.tabs.metadata') }}
                    <span
                        v-if="!state.entity.metadata || !state.entity.metadata.licence"
                        :title="t('global.licence_missing')"
                    >
                        <i class="fas fa-exclamation text-warning" />
                    </span>
                </a>
            </li>
            <li
                v-show="can('comments_read')"
                class="nav-item"
                role="presentation"
            >
                <a
                    id="active-entity-comments-tab"
                    class="nav-link active-entity-detail-tab d-flex gap-2 align-items-center"
                    href="#"
                    @click.prevent="setView('comments')"
                >
                    <span class="fa-layers fa-fw">
                        <i class="fas fa-fw fa-comments" />
                        <span class="fa-layers-counter fa-counter-lg bg-secondary-subtle text-reset">
                            {{ state.entity.comments_count }}
                        </span>
                    </span>
                    {{ t('main.entity.tabs.comments') }}
                </a>
            </li>
        </ul>

        <div
            id="entity-detail-tab-content"
            class="tab-content col ps-0 pe-0"
        >
            <!-- ATTRIBUTES VIEW -->

            <div
                v-if="isAttributeView"
                class="tab-pane fade h-100 active-entity-detail-panel active-entity-attributes-panel show active"
                role="tabpanel"
            >
                <form
                    v-if="tabName"
                    class="h-100 container-fluid"
                    @submit.prevent
                    @keydown.ctrl.s="e => handleSaveOnKey(e, `${tabName.id}`)"
                >
                    <attribute-list
                        v-dcan="'entity_data_read'"
                        class="pt-2 h-100 row"
                        :attributes="tabName.data"
                        :hidden-attributes="state.hiddenAttributeList"
                        :show-hidden="state.hiddenAttributeState"
                        :disable-drag="true"
                        :metadata-addon="hasReferenceGroup"
                        :selections="state.entityTypeSelections"
                        :values="state.entity.data"
                        @dirty="e => setFormState(e, tabName.id)"
                        @metadata="showMetadata"
                    />
                </form>
            </div>


            <!-- <div
                v-for="tg in state.entityGroups"
                :id="`active-entity-attributes-panel-${tg.id}`"
                :key="`attribute-group-${tg.id}-panel`"
                class="tab-pane fade h-100 active-entity-detail-panel active-entity-attributes-panel show active"
                role="tabpanel"
            >
                <form
                    :id="`entity-attribute-form-${tg.id}`"
                    :name="`entity-attribute-form-${tg.id}`"
                    class="h-100 container-fluid"
                    @submit.prevent
                    @keydown.ctrl.s="e => handleSaveOnKey(e, `${tg.id}`)"
                >
                    <attribute-list
                        :ref="el => setAttrRefs(el, tg.id)"
                        v-dcan="'entity_data_read'"
                        class="pt-2 h-100 overflow-y-auto row"
                        :attributes="tg.data"
                        :hidden-attributes="state.hiddenAttributeList"
                        :show-hidden="state.hiddenAttributeState"
                        :disable-drag="true"
                        :metadata-addon="hasReferenceGroup"
                        :selections="state.entityTypeSelections"
                        :values="state.entity.data"
                        @dirty="e => setFormState(e, tg.id)"
                        @metadata="showMetadata"
                    />
                </form>
            </div> -->

            <!-- METADATA VIEW -->
            <!-- <div
                v-if="view === 'metadata'"
                v-show="can('entity_read')"
                id="active-entity-metadata-panel"
                class="tab-pane fade h-100 active-entity-detail-panel overflow-hidden"
                role="tabpanel"
            >
                <MetadataTab class="mb-auto scroll-y-auto h-100 pe-2" />
            </div> -->

            <!-- COMMENTS VIEW -->
            <div
                v-if="view === 'comments'"
                v-show="can('comments_read')"
                id="active-entity-comments-panel"
                class="tab-pane fade h-100 active-entity-detail-panel"
                role="tabpanel"
            >
                <div
                    v-if="state.entity.comments"
                    class="mb-auto overflow-y-auto h-100 pe-2"
                >
                    <div
                        v-if="state.commentsFetching"
                        class="mt-2"
                    >
                        <alert
                            class="mb-0"
                            type="info"
                            :message="t('global.comments.fetching')"
                        />
                    </div>
                    <div
                        v-else-if="state.commentFetchFailed"
                        class="mt-2"
                    >
                        <p class="alert alert-danger mb-0">
                            {{ t('global.comments.fetching_failed') }}
                            <button
                                type="button"
                                class="d-block mt-2 btn btn-sm btn-outline-success"
                                @click="fetchComments"
                            >
                                <i class="fas fa-fw fa-sync" />
                                {{ t('global.comments.retry_failed') }}
                            </button>
                        </p>
                    </div>
                    <comment-list
                        v-else
                        :avatar="48"
                        :comments="state.entity.comments"
                        :hide-button="false"
                        :resource="state.resourceInfo"
                        @added="addComment"
                    />
                </div>
            </div>
            <EntityDetail
                v-if="isEntityView"
                class="ps-5 pt-2"
                :entity="state.activeSubEntity"
            />
        </div>
    </div>
</template>

<script>
    import {
        computed,
        nextTick,
        onBeforeUpdate,
        onMounted,
        reactive,
        ref,
        watch,
    } from 'vue';

    import {
        useRoute,
        onBeforeRouteLeave,
        onBeforeRouteUpdate,
    } from 'vue-router';

    import { useI18n } from 'vue-i18n';

    import {
        Alert,
        Popover,
    } from 'bootstrap';

    import store from '@/bootstrap/store.js';
    import router from '%router';

    import { useToast } from '@/plugins/toast.js';
    import {
        getEntityData,
        getEntityReferences,
        getEntityComments,
        patchAttributes,
    } from '@/api.js';
    import {
        can,
        isModerated,
        isArray,
        userId,
        getAttribute,
        getAttributeName,
        getUserBy,
        getEntity,
        getEntityColors,
        getEntityTypeName,
        getEntityTypeAttributeSelections,
        getEntityTypeDependencies,
        getConcept,
        fillEntityData,
        translateConcept,
        _cloneDeep,
    } from '@/helpers/helpers.js';
    import {
        showDiscard,
        showDeleteEntity,
        showUserInfo,
        canShowReferenceModal,
    } from '@/helpers/modal.js';

    import { usePreventNavigation } from '@/helpers/form.js';

    import EntityDetailHeader from './entity/EntityDetailHeader.vue';

    export default {
        components: {
            EntityDetailHeader,
        },
        props: {
            entity: {
                required: true,
                type: Object
            },
            bibliography: {
                required: false,
                type: Array,
                default: () => []
            },
            onDelete: {
                required: false,
                type: Function,
                default: () => {}
            }
        },
        setup(props) {
            const { t } = useI18n();
            const route = useRoute();
            const toast = useToast();


            // DATA
            const attrRefs = ref({});
            const state = reactive({
                activeSubEntity: null,
                tabType: 'attribute',
                colorStyles: computed(_ => {
                    const colors = getEntityColors(state.entity.entity_type_id);
                    return {
                        color: colors.backgroundColor
                    };
                }),
                formDirty: computed(_ => {
                    for(let k in state.dirtyStates) {
                        if(state.dirtyStates[k]) return true;
                    }
                    return false;
                }),
                dirtyStates: {},
                attributeGrpHovered: null,
                hiddenAttributes: {},
                entityHeaderHovered: false,
                entityMetadata: {},
                initFinished: false,
                commentLoadingState: 'not',
                metadataTabLoaded: false,
                hiddenAttributeState: false,
                attributesInTabs: true,
                routeQuery: computed(_ => route.query),
                entity: computed(_ => props.entity ?? store.getters.entity),
                entityUser: computed(_ => state.entity.user),
                entityAttributes: computed(_ => store.getters.entityTypeAttributes(state.entity.entity_type_id)),
                entityGroups: computed(_ => {
                    if(!state.entityAttributes) {
                        return state.entityAttributes;
                    }

                    if(state.attributesInTabs) {
                        const tabGroups = {};
                        let currentGroup = 'default';
                        let currentGroupId = 'default';
                        let currentUnnamedGroupCntr = 1;
                        state.entityAttributes.forEach(a => {
                            if(a.is_system && a.datatype == 'system-separator') {
                                if(!a.pivot.metadata || !a.pivot.metadata.title) {
                                    currentGroup = t(`main.entity.tabs.untitled_group`, { cnt: currentUnnamedGroupCntr });
                                    currentUnnamedGroupCntr++;
                                } else {
                                    currentGroup = translateConcept(a.pivot.metadata.title);
                                }
                                currentGroupId = a.pivot.id;
                                return;
                            }
                            if(!tabGroups[currentGroup]) {
                                tabGroups[currentGroup] = {
                                    id: currentGroupId,
                                    data: []
                                };
                            }
                            tabGroups[currentGroup].data.push(a);
                        });

                        return tabGroups;
                    } else {
                        return {
                            default: {
                                id: 'default',
                                data: state.entityAttributes,
                            },
                        };
                    }
                }),
                entityTypeSelections: computed(_ => getEntityTypeAttributeSelections(state.entity.entity_type_id)),
                entityTypeDependencies: computed(_ => getEntityTypeDependencies(state.entity.entity_type_id)),
                hasAttributeLinks: computed(_ => state.entity.attributeLinks && state.entity.attributeLinks.length > 0),
                groupedAttributeLinks: computed(_ => {
                    if(!state.hasAttributeLinks) return {};

                    const groups = {};
                    state.entity.attributeLinks.forEach(l => {
                        if(!groups[l.id]) {
                            groups[l.id] = {
                                ...l,
                                attribute_urls: [translateConcept(l.attribute_url)],
                            };
                        } else {
                            groups[l.id].attribute_urls.push(translateConcept(l.attribute_url));
                        }
                    });
                    return groups;
                }),
                hiddenAttributeList: computed(_ => {
                    const keys = Object.keys(state.hiddenAttributes);
                    const values = Object.values(state.hiddenAttributes);
                    const list = [];
                    for(let i = 0; i < keys.length; i++) {
                        if(values[i].hide && (!state.hiddenAttributes[values[i].by] || !state.hiddenAttributes[values[i].by].hide)) {
                            list.push(keys[i]);
                        }
                    }
                    return list;
                }),
                hiddenAttributeCount: computed(_ => state.hiddenAttributeList.length),
                hiddenAttributeListing: computed(_ => {
                    let listing = `<div>`;
                    const keys = Object.keys(state.hiddenAttributes);
                    const values = Object.values(state.hiddenAttributes);
                    const listGroups = {};
                    for(let i = 0; i < keys.length; i++) {
                        const k = keys[i];
                        const v = values[i];
                        if(v.hide && (!state.hiddenAttributes[v.by] || !state.hiddenAttributes[v.by].hide)) {
                            if(!listGroups[v.by]) {
                                listGroups[v.by] = [];
                            }
                            listGroups[v.by].push(k);
                        }
                    }
                    for(let k in listGroups) {
                        const grpAttr = getAttribute(k);
                        listing += `<span class="text-muted fw-light fs-6"># ${translateConcept(grpAttr.thesaurus_url)}</span>`;
                        listing += `<ol class="mb-0">`;
                        // const data = state.entity.data[keys[i]];
                        for(let i = 0; i < listGroups[k].length; i++) {
                            const attr = getAttribute(listGroups[k][i]);
                            listing += `<li><span class="fw-bold">${translateConcept(attr.thesaurus_url)}</span></li>`;
                        }
                        listing += `</ol>`;
                    }
                    listing += `</div>`;
                    return listing;
                }),
                resourceInfo: computed(_ => {
                    if(!state.entity) return {};

                    return {
                        id: state.entity.id,
                        type: 'entity',
                    };
                }),
                commentsFetching: computed(_ => {
                    return state.commentLoadingState === 'fetching';
                }),
                commentsFetched: computed(_ => {
                    return state.commentLoadingState === 'fetched';
                }),
                commentFetchFailed: computed(_ => {
                    return state.commentLoadingState === 'failed';
                }),
            });

            usePreventNavigation(_ => state.formDirty);

            // FUNCTIONS
            const hasReferenceGroup = group => {
                if(!state.entity.references) return false;
                if(!Object.keys(state.entity.references).length) return false;
                if(!state.entity.references[group]) return false;
                return Object.keys(state.entity.references[group]).length > 0;
            };
            const showMetadata = e => {
                const attribute = e.element;
                const canOpen = canShowReferenceModal(attribute.id);
                if(canOpen) {
                    router.push({
                        append: true,
                        name: 'entityrefs',
                        query: route.query,
                        params: {
                            aid: attribute.id,
                        },
                    });
                } else {
                    const msg = t('main.entity.references.toasts.cannot_edit_metadata.msg');
                    toast.$toast(msg, '', {
                        duration: 2500,
                        autohide: true,
                        channel: 'warning',
                        icon: true,
                        simple: true,
                    });
                }
            };

            const updateDependencyState = (aid, value) => {
                const attrDeps = state.entityTypeDependencies[aid];
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

            const showHiddenAttributes = _ => {
                state.hiddenAttributeState = true;
            };
            const hideHiddenAttributes = _ => {
                state.hiddenAttributeState = false;
            };
            const confirmDeleteEntity = _ => {
                if(!can('entity_delete')) return;

                showDeleteEntity(state.entity.id);
            };


            const view = ref('attributes-default');

            const setEntityView = async (subEntity) => {
                view.value = 'entity';
                state.activeSubEntity = subEntity;

                if(!subEntity.data) {
                    state.activeSubEntity.data = await getEntityData(subEntity.id);

                    // LAZYLY COPIED FROM THE STORE, AS THE STOER HAS SIDEEFFECTS
                    fillEntityData(state.activeSubEntitydata, state.activeSubEntityentity_type_id);
                    state.activeSubEntityreferences = await getEntityReferences(subEntity.id) || {};
                    for(let k in state.activeSubEntitydata) {
                        const curr = state.activeSubEntitydata[k];
                        if(curr.attribute) {
                            const key = curr.attribute.thesaurus_url;
                            if(!state.activeSubEntityreferences[key]) {
                                state.activeSubEntityreferences[key] = [];
                            }
                        }
                    }
                }
            };

            const setView = (_view) => {
                view.value = _view;
            };

            const isEntityView = computed(() => {
                return view.value.startsWith('entity');
            });

            const isAttributeView = computed(() => {
                return view.value.startsWith('attributes');
            });

            const tabName = computed(() => {
                if(!state.entityGroups) return null;
                return state.entityGroups[view.value.split('-')[1]];
            });

            // const setView = view => {
            //     $emit("update:view", view);
            // };


            // const setViewView = (tab = 'attributes-default') => {

            //     const [tabType, tabId] = tab.split('-');

            //     state.tabType = tabType;

            //     let newTab, oldTabs, newPanel, oldPanels;
            //     if(tab === 'comments') {
            //         newTab = document.getElementById('active-entity-comments-tab');
            //         newPanel = document.getElementById('active-entity-comments-panel');
            //         if(!state.commentsFetched) {
            //             fetchComments();
            //         }
            //     } else if(tab === 'metadata') {
            //         newTab = document.getElementById('active-entity-metadata-tab');
            //         newPanel = document.getElementById('active-entity-metadata-panel');
            //     } else if(tabType === 'entity') {
            //         newTab = document.getElementById(`active-entity-child-${tabId}-tab`);
            //         newPanel = document.getElementById(`active-entity-child-${tabId}`);
            //     } else {
            //         newTab = document.getElementById(`active-entity-attributes-group-${tabId}-tab`);
            //         newPanel = document.getElementById(`active-entity-attributes-panel-${tabId}`);
            //     }
            //     oldTabs = document.getElementsByClassName('active-entity-detail-tab');
            //     oldPanels = document.getElementsByClassName('active-entity-detail-panel');

            //     oldTabs.forEach(t => t.classList.remove('active'));
            //     if(newTab) newTab.classList.add('active');
            //     oldPanels.forEach(p => p.classList.remove('show', 'active'));
            //     if(newPanel) newPanel.classList.add('show', 'active');
            // };

            const onEntityHeaderHover = hoverState => {
                state.entityHeaderHovered = hoverState;
            };

            const showTabActions = (grp, status) => {
                state.attributeGrpHovered = status ? grp : null;
            };
            const setFormState = (e, grp) => {
                state.dirtyStates[grp] = e.dirty && e.valid;
                updateDependencyState(e.attribute_id, e.value);
            };
            const getDirtyValues = grp => {
                const list = grp ? grp.split(',') : Object.keys(attrRefs.value);
                let values = {};
                list.forEach(g => {
                    values = {
                        ...values,
                        ...attrRefs.value[g].getDirtyValues(),
                    };
                });
                return values;
            };
            const undirtyList = grp => {
                const list = grp ? grp.split(',') : Object.keys(attrRefs.value);
                list.forEach(g => {
                    attrRefs.value[g].undirtyList();
                });
            };
            const resetListValues = grp => {
                const list = grp ? grp.split(',') : Object.keys(attrRefs.value);
                list.forEach(g => {
                    attrRefs.value[g].resetListValues();
                });
            };
            const resetDirtyStates = grp => {
                const list = grp ? grp.split(',') : Object.keys(attrRefs.value);
                list.forEach(g => {
                    state.dirtyStates[g] = false;
                });
            };

            const fetchComments = _ => {
                if(!can('comments_read')) return;

                state.commentLoadingState = 'fetching';
                getEntityComments(state.entity.id).then(comments => {
                    store.dispatch('setEntityComments', comments);
                    state.commentLoadingState = 'fetched';
                }).catch(e => {
                    state.commentLoadingState = 'failed';
                });
            };

            const addComment = event => {
                const comment = event.comment;
                const replyTo = event.replyTo;
                if(replyTo) {
                    const op = state.entity.comments.find(c => c.id == replyTo);
                    if(op.replies) {
                        op.replies.push(comment);
                    }
                    op.replies_count++;
                } else {
                    if(!state.entity.comments) {
                        state.entity.comments = [];
                    }
                    state.entity.comments.push(comment);
                    state.entity.comments_count++;
                }
            };

            const handleSaveOnKey = (e, grp) => {
                if(!can('entity_data_write')) return;
                if(e.altKey) return;

                e.preventDefault();
                if(e.shiftKey) {
                    if(!state.formDirty) return;
                    saveEntity();
                } else {
                    if(!state.dirtyStates[grp]) return;
                    saveEntity(grp);
                }
            };

            const saveEntity = grps => {
                if(!can('entity_data_write')) return;

                const dirtyValues = getDirtyValues(grps);
                const patches = [];
                const moderations = [];

                for(let v in dirtyValues) {
                    const aid = v;
                    const data = state.entity.data[aid];
                    const type = getAttribute(aid)?.datatype;
                    const patch = {
                        op: null,
                        value: null,
                        params: {
                            aid: aid,
                        },
                    };
                    if(data.id) {
                        // if data.id exists, there has been an entry in the database, therefore it is a replace/remove operation
                        if(
                            (dirtyValues[v] && dirtyValues[v] != '')
                            ||
                            (type == 'boolean' && dirtyValues[v] === false)
                        ) {
                            // value is set, therefore it is a replace
                            patch.op = 'replace';
                            patch.value = dirtyValues[v];
                            // patch.value = getCleanValue(patch.value, entity.attributes);
                        } else {
                            // value is empty, therefore it is a remove
                            patch.op = 'remove';
                        }
                    } else {
                        // there has been no entry in the database before, therefore it is an add operation
                        if(dirtyValues[v] && dirtyValues[v] != '') {
                            patch.op = 'add';
                            patch.value = dirtyValues[v];
                            // patch.value = getCleanValue(patch.value, entity.attributes);
                        } else {
                            // there has be no entry in the database before and values are not different (should not happen ;))
                            continue;
                        }
                    }
                    patches.push(patch);
                    moderations.push(aid);
                }
                return patchAttributes(state.entity.id, patches).then(data => {
                    undirtyList(grps);
                    store.dispatch('updateEntity', data.entity);
                    store.dispatch('updateEntityData', {
                        data: dirtyValues,
                        new_data: data.added_attributes,
                        eid: state.entity.id,
                        sync: !isModerated(),
                    });
                    if(isModerated()) {
                        store.dispatch('updateEntityDataModerations', {
                            entity_id: state.entity.id,
                            attribute_ids: moderations,
                            state: 'pending',
                        });
                    }

                    resetDirtyStates(grps);

                    toast.$toast(
                        t('main.entity.toasts.updated.msg', {
                            name: data.entity.name
                        }),
                        t('main.entity.toasts.updated.title'),
                        {
                            channel: 'success',
                            autohide: true,
                            icon: true,
                        },
                    );
                }).catch(error => {
                    let response = error.response;

                    if(!response) {
                        response = {
                            data: {
                                error: t('global.errors.unknown')
                            },
                            status: 417,
                            statusText: 'Could not decode response'
                        };
                    }

                    toast.$toast(
                        response.data.error,
                        `${response.status}: ${response.statusText}`, {
                        channel: 'error',
                        autohide: true,
                        icon: true,
                        duration: 5000,
                    },
                    );
                });
            };
            const resetForm = grps => {
                resetListValues(grps);
                resetDirtyStates(grps);
            };
            const setAttrRefs = (el, grp) => {
                attrRefs.value[grp] = el;
            };

            // ON MOUNTED
            onMounted(_ => {
                console.log('entity detail component mounted');
                let hiddenAttrElem = document.getElementById('hidden-attributes-icon');
                if(!!hiddenAttrElem) {
                    new Popover(hiddenAttrElem, {
                        title: _ => t('main.entity.attributes.hidden', { cnt: state.hiddenAttributeCount }, state.hiddenAttributeCount),
                        content: state.hiddenAttributeListing,
                    });
                }
            });
            onBeforeUpdate(_ => {
                attrRefs.value = {};
                state.commentLoadingState = 'not';
            });

            watch(_ => state.hiddenAttributeCount,
                async (newCount, oldCount) => {
                    if(newCount > 0) {
                        let hiddenAttrElem = document.getElementById('hidden-attributes-icon');
                        if(!!hiddenAttrElem) {
                            new Popover(hiddenAttrElem, {
                                title: _ => t('main.entity.attributes.hidden', { cnt: state.hiddenAttributeCount }, state.hiddenAttributeCount),
                                content: state.hiddenAttributeListing,
                            });
                        }
                    }
                }
            );


            watch(_ => state.entity,
                async (newValue, oldValue) => {
                    if(!newValue || !newValue.id) return;
                    nextTick(_ => {
                        // setViewView(route.query.view);
                        const eid = state.entity.id;
                        const treeElem = document.getElementById(`tree-node-${eid}`);
                        if(treeElem) {
                            treeElem.scrollIntoView({
                                behavior: 'smooth',
                                inline: 'start',
                            });
                        }
                    });
                }
            );

            watch(_ => route.query.view,
                async (newValue, oldValue) => {
                    if(route.name != 'entitydetail') return;
                    if(newValue == oldValue) return;

                    // nextTick(_ => {
                    //     setViewView(newValue);
                    // });
                }
            );

            // ON BEFORE LEAVE
            onBeforeRouteLeave(async (to, from) => {
                if(state.formDirty) {
                    showDiscard(to, resetDirtyStates, saveEntity);
                    return false;
                } else {
                    store.dispatch('resetEntity');
                    return true;
                }
            });
            onBeforeRouteUpdate(async (to, from) => {
                if(to.params.id !== route.params.id) {
                    if(state.formDirty) {
                        showDiscard(to, resetDirtyStates, saveEntity);
                        return false;
                    } else {
                        state.hiddenAttributes = {};
                        // store.dispatch('resetEntity');
                        return true;
                    }
                } else {
                    // if not id changed, but query, we do not need discard modal
                    return true;
                }
            });

            // RETURN
            return {
                t,
                route,
                // HELPERS
                can,
                userId,
                getUserBy,
                showUserInfo,
                getAttributeName,
                getEntityTypeName,
                getEntityColors,
                translateConcept,
                getEntity,
                // LOCAL
                hasReferenceGroup,
                showMetadata,
                showHiddenAttributes,
                hideHiddenAttributes,
                confirmDeleteEntity,
                setView,
                setEntityView,
                isAttributeView,
                isEntityView,
                tabName,
                onEntityHeaderHover,
                showTabActions,
                setFormState,
                fetchComments,
                addComment,
                handleSaveOnKey,
                saveEntity,
                resetForm,
                setAttrRefs,
                // STATE
                attrRefs,
                state,
                view,
            };
        }
    };
</script>
