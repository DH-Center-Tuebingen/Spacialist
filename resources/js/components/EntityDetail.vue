<template>
    <div class="h-100 d-flex flex-column">
        <entity-breadcrumbs class="mb-2 small" :entity="state.entity" v-if="state.showBreadcrumb"></entity-breadcrumbs>
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="mb-0" @mouseenter="onEntityHeaderHover(true)" @mouseleave="onEntityHeaderHover(false)">
                <span v-if="!state.entity.editing">
                    {{ state.entity.name }}
                    <small>
                        <button id="hidden-attributes-icon" v-show="state.hiddenAttributeCount > 0" class="border-0 bg-body text-secondary me-1" data-bs-container="body" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-placement="bottom" :data-bs-content="state.hiddenAttributeListing" data-bs-html="true" data-bs-custom-class="popover-p-2" @mousedown="showHiddenAttributes()" @mouseup="hideHiddenAttributes()">
                            <span v-show="state.hiddenAttributeState">
                                <i class="fas fa-fw fa-xs fa-eye"></i>
                            </span>
                            <span v-show="!state.hiddenAttributeState">
                                <i class="fas fa-fw fa-xs fa-eye-slash"></i>
                            </span>
                        </button>
                        <a href="#" v-if="state.entityHeaderHovered" class="text-secondary" @click.prevent="editEntityName()">
                            <i class="fas fa-fw fa-edit fa-xs"></i>
                        </a>
                    </small>
                </span>
                <form class="d-flex flex-row" v-else @submit.prevent="updateEntityName()">
                    <input type="text" class="form-control form-control-sm me-2" v-model="state.editedEntityName" />
                    <button type="submit" class="btn btn-outline-success btn-sm me-2">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                    <button type="reset" class="btn btn-outline-danger btn-sm" @click="cancelEditEntityName()">
                        <i class="fas fa-fw fa-ban"></i>
                    </button>
                </form>
            </h3>
            <span>
                <button type="submit" form="entity-attribute-form" class="btn btn-success me-2" :disabled="!state.formDirty || !can('duplicate_edit_concepts')" @click.prevent="saveEntity()">
                    <i class="fas fa-fw fa-save"></i> {{ t('global.save') }}
                </button>
                <button type="button" class="btn btn-warning me-2" :disabled="!state.formDirty" @click="resetForm()">
                    <i class="fas fa-fw fa-undo"></i> {{ t('global.reset') }}
                </button>
                <button type="button" class="btn btn-danger" :disabled="!can('delete_move_concepts')" @click="confirmDeleteEntity()">
                    <i class="fas fa-fw fa-trash"></i> {{ t('global.delete') }}
                </button>
            </span>
        </div>
        <div class="d-flex justify-content-between my-2">
            <div>
                <span :style="state.colorStyles">
                    <i class="fas fa-fw fa-circle"></i>
                </span>
                <span>
                    {{ state.entityTypeLabel }}
                </span>
            </div>
            <div>
                <i class="fas fa-fw fa-user-edit"></i>
                <span class="ms-1">
                    {{ date(state.lastModified, undefined, true, true) }}
                </span>
                -
                <a href="#" @click.prevent="showUserInfo(state.entityUser)" class="fw-medium" v-if="state.entity.user">
                    {{ state.entityUser.name }}
                    <user-avatar :user="state.entityUser" :size="20" class="align-middle"></user-avatar>
                </a>
            </div>
        </div>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="active-entity-attributes-tab" href="#" @click.prevent="setDetailPanel('attributes')">
                    {{ t('main.entity.tabs.attributes') }}
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="active-entity-comments-tab" href="#" @click.prevent="setDetailPanel('comments')">
                    {{ t('main.entity.tabs.comments') }}
                    <span class="badge bg-primary">{{ state.entity.comments_count }}</span>
                </a>
            </li>
        </ul>
        <div class="tab-content col ps-0 pe-0 overflow-hidden" id="entity-detail-tab-content">
            <div class="tab-pane fade h-100 show active" id="active-entity-attributes-panel" role="tabpanel">
                <form id="entity-attribute-form" name="entity-attribute-form" class="h-100">
                    <attribute-list
                        class="pt-2 h-100 scroll-y-auto scroll-x-hidden"
                        v-if="state.attributesFetched"
                        v-dcan="'view_concept_props'"
                        :ref="el => setAttrRef(el)"
                        :attributes="state.entityAttributes"
                        :hidden-attributes="state.hiddenAttributeList"
                        :show-hidden="state.hiddenAttributeState"
                        :disable-drag="true"
                        :metadata-addon="hasReferenceGroup"
                        :selections="state.entityTypeSelections"
                        :values="state.entity.data"
                        @dirty="setFormState"
                        @metadata="showMetadata">
                    </attribute-list>
                </form>
            </div>
            <div class="tab-pane fade h-100 d-flex flex-column" id="active-entity-comments-panel" role="tabpanel">
                <div class="mb-auto scroll-y-auto h-100" v-if="state.entity.comments">
                    <div v-if="state.commentsFetching" class="mt-2">
                        <p class="alert alert-info mb-0" v-html="$t('global.comments.fetching')">
                        </p>
                    </div>
                    <div v-else-if="state.commentFetchFailed" class="mt-2">
                        <p class="alert alert-danger mb-0">
                            {{ t('global.comments.fetching_failed') }}
                            <button type="button" class="d-block mt-2 btn btn-xs btn-outline-success" @click="fetchComments">
                                <i class="fas fa-fw fa-sync"></i>
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
                        @added="addComment">
                    </comment-list>
                </div>
            </div>
        </div>
        <router-view
            v-if="state.attributesFetched"
            :entity="state.entity">
        </router-view>
    </div>
</template>

<script>
    import {
        computed,
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
        Popover,
    } from 'bootstrap';

    import store from '../bootstrap/store.js';
    import router from '../bootstrap/router.js';

    import { useToast } from '../plugins/toast.js';

    import { date } from '../helpers/filters.js';
    import {
        getEntityComments,
        patchAttributes,
        patchEntityName,
    } from '../api.js';
    import {
        can,
        getAttribute,
        getEntityColors,
        getEntityType,
        getEntityTypeAttributeSelections,
        getEntityTypeDependencies,
        translateConcept
    } from '../helpers/helpers.js';
    import {
        showDiscard,
        showDeleteEntity,
        showUserInfo,
        canShowReferenceModal,
    } from '../helpers/modal.js';

    export default {
        props: {
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

            // FETCH
            store.dispatch('getEntity', route.params.id).then(_ => {
                getEntityTypeAttributeSelections();
                state.initFinished = true;
                updateAllDependencies();
            });

            // DATA
            const attrRef = ref({});
            const state = reactive({
                colorStyles: computed(_ => {
                    const colors = getEntityColors(state.entity.entity_type_id, 0.75);
                    return {
                        color: colors.backgroundColor
                    };
                }),
                formDirty: false,
                hiddenAttributes: {},
                entityHeaderHovered: false,
                editedEntityName: '',
                initFinished: false,
                commentLoadingState: 'not',
                hiddenAttributeState: false,
                entity: computed(_ => store.getters.entity),
                entityUser: computed(_ => state.entity.user),
                entityAttributes: computed(_ => store.getters.entityTypeAttributes(state.entity.entity_type_id)),
                entityTypeSelections: computed(_ => getEntityTypeAttributeSelections(state.entity.entity_type_id)),
                entityTypeDependencies: computed(_ => getEntityTypeDependencies(state.entity.entity_type_id)),
                attributesFetched: computed(_ => state.initFinished && state.entity.data && !!state.entityAttributes && state.entityAttributes.length > 0),
                entityTypeLabel: computed(_ => {
                    // if(!state.entity) return;
                    const entityType = getEntityType(state.entity.entity_type_id);
                    if(!entityType) return;
                    return translateConcept(entityType.thesaurus_url);
                }),
                hiddenAttributeList: computed(_ => {
                    const keys = Object.keys(state.hiddenAttributes);
                    const values = Object.values(state.hiddenAttributes);
                    const list = [];
                    for(let i=0; i<keys.length; i++) {
                        if(values[i].hide && (!state.hiddenAttributes[values[i].by] || !state.hiddenAttributes[values[i].by].hide)) {
                            list.push(keys[i]);
                        }
                    }
                    return list;
                }),
                hiddenAttributeCount: computed(_ => state.hiddenAttributeList.length),
                hiddenAttributeListing: computed(_ => {
                    let listing = `<div>`;
                    if(!!state.attributesFetched) {
                        const keys = Object.keys(state.hiddenAttributes);
                        const values = Object.values(state.hiddenAttributes);
                        const listGroups = {};
                        for(let i=0; i<keys.length; i++) {
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
                            for(let i=0; i<listGroups[k].length; i++) {
                                const attr = getAttribute(listGroups[k][i]);
                                listing += `<li><span class="fw-bold">${translateConcept(attr.thesaurus_url)}</span></li>`;
                            }
                            listing += `</ol>`;
                        }
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
                showBreadcrumb: computed(_ => {
                    return state.entity.parentIds && state.entity.parentIds.length > 1;
                }),
                lastModified: computed(_ => {
                    return state.entity.updated_at || state.entity.created_at;
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
                    toast.$toast('You have to enter data first, before you can edit metadata.', '', {
                        duration: 2500,
                        autohide: true,
                        channel: 'warning',
                        icon: true,
                        simple: true,
                    });
                }
            };
            const editEntityName = _ => {
                state.editedEntityName = state.entity.name;
                state.entity.editing = true;
            };
            const updateEntityName = _ => {
                // If name does not change, just cancel
                if(state.entity.name == state.editedEntityName) {
                    cancelUpdateEntityName();
                } else {
                    patchEntityName(state.entity.id, state.editedEntityName).then(data => {
                        store.dispatch('updateEntity', {
                            ...data,
                            name: state.editedEntityName,
                        });
                        cancelEditEntityName();
                    });
                }
            };
            const cancelEditEntityName = _ => {
                state.entity.editing = false;
                state.editedEntityName = '';
            };
            const updateDependencyState = (aid, value) => {
                const attrDeps = state.entityTypeDependencies[aid];
                if(!attrDeps) return;
                attrDeps.forEach(ad => {
                    let matches = false;
                    switch(ad.operator) {
                        case '=':
                            matches = value == ad.value;
                            break;
                        case '!=':
                            matches = value != ad.value;
                            break;
                        case '<':
                            matches = value < ad.value;
                            break;
                        case '>':
                            matches = value > ad.value;
                            break;
                    }
                    state.hiddenAttributes[ad.dependant] = {
                        hide: matches,
                        by: aid,
                    };
                });
            };
            const updateAllDependencies = _ => {
                if(!state.entityAttributes) return;
                
                for(let i=0; i<state.entityAttributes.length; i++) {
                    const curr = state.entityAttributes[i];
                    updateDependencyState(curr.id, state.entity.data[curr.id].value);
                }
            };
            const showHiddenAttributes = _ => {
                state.hiddenAttributeState = true;
            };
            const hideHiddenAttributes = _ => {
                state.hiddenAttributeState = false;
            };
            const confirmDeleteEntity = _ => {
                if(!can('delete_move_concepts')) return;

                showDeleteEntity(state.entity.id);
            };
            const setDetailPanel = tab => {
                const query = {
                    view: tab,
                };
                router.push({
                    query: {
                        ...route.query,
                        ...query,
                    }
                });
            };
            const setDetailPanelView = (tab = 'attributes') => {
                let newTab, oldTab, newPanel, oldPanel;
                if(tab === 'comments') {
                    newTab = document.getElementById('active-entity-comments-tab');
                    newPanel = document.getElementById('active-entity-comments-panel');
                    oldTab = document.getElementById('active-entity-attributes-tab');
                    oldPanel = document.getElementById('active-entity-attributes-panel');
                    if(!state.commentsFetched) {
                        fetchComments();
                    }
                } else {
                    newTab = document.getElementById('active-entity-attributes-tab');
                    newPanel = document.getElementById('active-entity-attributes-panel');
                    oldTab = document.getElementById('active-entity-comments-tab');
                    oldPanel = document.getElementById('active-entity-comments-panel');
                }

                oldTab.classList.remove('active');
                newTab.classList.add('active');
                oldPanel.classList.remove('show', 'active');
                newPanel.classList.add('show', 'active');
            };
            const onEntityHeaderHover = hoverState => {
                state.entityHeaderHovered = hoverState;
            };
            const setFormState = e => {
                state.formDirty = e.dirty && e.valid;
                updateDependencyState(e.attribute_id, e.value);
            };
            const fetchComments = _ => {
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
            const saveEntity = _ => {
                if(!can('duplicate_edit_concepts')) return;
                const dirtyValues = attrRef.value.getDirtyValues();
                var patches = [];

                for(let v in dirtyValues) {
                    const aid = v;
                    const data = state.entity.data[aid];
                    const patch = {
                        op: null,
                        value: null,
                        params: {
                            aid: aid,
                        },
                    };
                    if(data.id) {
                        // if data.id exists, there has been an entry in the database, therefore it is a replace/remove operation
                        if(dirtyValues[v] && dirtyValues[v] != '') {
                            // value is set, therefore it is a replace
                            patch.op = 'replace';
                            patch.value = dirtyValues[v];
                            // patch.value = getCleanValue(patch.value, entity.attributes);
                        } else {
                            // value is empty, therefore it is a remove
                            patch.op = "remove";
                        }
                    } else {
                        // there has been no entry in the database before, therefore it is an add operation
                        if(dirtyValues[v] && dirtyValues[v] != '') {
                            patch.op = "add";
                            patch.value = dirtyValues[v];
                            // patch.value = getCleanValue(patch.value, entity.attributes);
                        } else {
                            // there has be no entry in the database before and values are not different (should not happen ;))
                            continue;
                        }
                    }
                    patches.push(patch);
                }
                return patchAttributes(state.entity.id, patches).then(data => {
                    state.formDirty = false;
                    attrRef.value.undirtyList();
                    store.dispatch('updateEntity', data);
                    store.dispatch('updateEntityData', {
                        data: dirtyValues,
                        eid: state.entity.id,
                    });

                    toast.$toast(
                        t('main.entity.toasts.updated.msg', {
                            name: data.name
                        }),
                        t('main.entity.toasts.updated.title'), {
                            channel: 'success',
                            autohide: true,
                            icon: true,
                        },
                    );
                }).catch(error => {
                    const r = error.response;
                    toast.$toast(
                        r.data.error,
                        `${r.status}: ${r.statusText}`, {
                            channel: 'error',
                            autohide: true,
                            icon: true,
                            duration: 5000,
                        },
                    );
                });
            };
            const resetForm = _ => {
                attrRef.value.resetListValues();
            };
            const setAttrRef = el => {
                attrRef.value = el;
            }

            // ON MOUNTED
            onMounted(_ => {
                console.log("entity detail component mounted");
                var hiddenAttrElem = document.getElementById('hidden-attributes-icon');
                if(!!hiddenAttrElem) {
                    new Popover(hiddenAttrElem, {
                        title: _ => t('main.entity.attributes.hidden', {cnt: state.hiddenAttributeCount}, state.hiddenAttributeCount),
                    });
                }
            });
            onBeforeUpdate(_ => {
                attrRef.value = {};
            });

            watch(_ => route.params,
                async (newParams, oldParams) => {
                    if(newParams.id == oldParams.id) return;
                    if(!newParams.id) return;
                    state.initFinished = false;
                    store.dispatch('getEntity', newParams.id).then(_ => {
                        getEntityTypeAttributeSelections();
                        state.initFinished = true;
                        updateAllDependencies();
                    });
                }
            );

            watch(_ => state.entity,
                async (newValue, oldValue) => {
                    if(!newValue && !newValue.id) return;
                    setDetailPanelView(route.query.view);
                }
            );

            watch(_ => route.query.view,
                async (newValue, oldValue) => {
                    if(newValue == oldValue) return;

                    setDetailPanelView(newValue);
                }
            );

            // ON BEFORE LEAVE
            onBeforeRouteLeave(async (to, from) => {
                if(state.formDirty) {
                    showDiscard(to, _ => state.formDirty = false, saveEntity);
                    return false;
                } else {
                    store.dispatch('resetEntity');
                    return true;
                }
            });
            onBeforeRouteUpdate(async (to, from) => {
                if(to.params.id !== route.params.id) {
                    if(state.formDirty) {
                        showDiscard(to, _ => state.formDirty = false, saveEntity);
                        return false;
                    } else {
                        store.dispatch('resetEntity');
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
                // HELPERS
                can,
                date,
                showUserInfo,
                // LOCAL
                hasReferenceGroup,
                showMetadata,
                editEntityName,
                updateEntityName,
                cancelEditEntityName,
                showHiddenAttributes,
                hideHiddenAttributes,
                confirmDeleteEntity,
                setDetailPanel,
                onEntityHeaderHover,
                setFormState,
                addComment,
                saveEntity,
                resetForm,
                setAttrRef,
                // STATE
                attrRef,
                state,
            };
        }
    //     methods: {
    //         getEntityData(entity) {
    //             this.dataLoaded = false;
    //             if(!this.$can('view_concept_props')) {
    //                 Vue.set(this.selectedEntity, 'data', {});
    //                 Vue.set(this.selectedEntity, 'attributes', []);
    //                 Vue.set(this.selectedEntity, 'selections', {});
    //                 Vue.set(this.selectedEntity, 'dependencies', []);
    //                 Vue.set(this.selectedEntity, 'references', []);
    //                 Vue.set(this.selectedEntity, 'comments', []);
    //                 Vue.set(this, 'dataLoaded', true);
    //                 return new Promise(r => r(null));
    //             }
    //             if(!this.selectedEntity.comments || this.selectedEntity.comments_count === 0) {
    //                 Vue.set(this.selectedEntity, 'comments', []);
    //             }
    //             const cid = entity.id;
    //             const ctid = entity.entity_type_id;
    //             return $httpQueue.add(() => $http.get(`/entity/${cid}/data`).then(response => {
    //                 // if result is empty, php returns [] instead of {}
    //                 if(response.data instanceof Array) {
    //                     response.data = {};
    //                 }
    //                 Vue.set(this.selectedEntity, 'data', response.data);
    //                 return $http.get(`/editor/entity_type/${ctid}/attribute`);
    //             }).then(response => {
    //                 this.selectedEntity.attributes = [];
    //                 let data = response.data;
    //                 for(let i=0; i<data.attributes.length; i++) {
    //                     let aid = data.attributes[i].id;
    //                     if(!this.selectedEntity.data[aid]) {
    //                         let val = {};
    //                         switch(data.attributes[i].datatype) {
    //                             case 'dimension':
    //                             case 'epoch':
    //                             case 'timeperiod':
    //                                 val.value = {};
    //                                 break;
    //                             case 'table':
    //                             case 'list':
    //                                 val.value = [];
    //                                 break;
    //                         }
    //                         Vue.set(this.selectedEntity.data, aid, val);
    //                     } else {
    //                         const val = this.selectedEntity.data[aid].value;
    //                         switch(data.attributes[i].datatype) {
    //                             case 'date':
    //                                 const dtVal = new Date(val);
    //                                 this.selectedEntity.data[aid].value = dtVal;
    //                                 break;
    //                         }
    //                     }
    //                     this.selectedEntity.attributes.push(data.attributes[i]);
    //                 }
    //                 // if result is empty, php returns [] instead of {}
    //                 if(data.selections instanceof Array) {
    //                     data.selections = {};
    //                 }
    //                 if(data.dependencies instanceof Array) {
    //                     data.dependencies = {};
    //                 }
    //                 Vue.set(this.selectedEntity, 'selections', data.selections);
    //                 Vue.set(this.selectedEntity, 'dependencies', data.dependencies);

    //                 const aid = this.$route.params.aid;
    //                 this.setReferenceAttribute(aid);
    //                 Vue.set(this, 'dataLoaded', true);
    //                 this.setEntityView();
    //             }));
    //         },
    //         dependencyInfoHoverOver(event) {
    //             if(this.dependencyInfoHovered) {
    //                 return;
    //             }
    //             this.dependencyInfoHovered = true;
    //             $('#dependency-info').popover({
    //                 placement: 'bottom',
    //                 animation: true,
    //                 html: false,
    //                 content: this.$tc('main.entity.attributes.hidden', this.hiddenAttributes, {
    //                     cnt: this.hiddenAttributes
    //                 })
    //             });
    //             $('#dependency-info').popover('show');
    //         },
    //         dependencyInfoHoverOut(event) {
    //             this.dependencyInfoHovered = false;
    //             $('#dependency-info').popover('dispose');
    //         },
    //     },
    }
</script>
