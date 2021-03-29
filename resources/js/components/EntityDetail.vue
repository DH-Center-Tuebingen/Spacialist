<template>
    <div class="h-100 d-flex flex-column">
        <entity-breadcrumbs class="mb-2 small" :entity="state.entity" v-if="state.showBreadcrumb"></entity-breadcrumbs>
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="mb-0" @mouseenter="onEntityHeaderHover(true)" @mouseleave="onEntityHeaderHover(false)">
                <span v-if="!state.entity.editing">
                    {{ state.entity.name }}
                    <small>
                        <span v-show="state.hiddenAttributes > 0" @mouseenter="dependencyInfoHoverOver" @mouseleave="dependencyInfoHoverOut">
                            <i id="dependency-info" class="fas fa-fw fa-xs fa-eye-slash"></i>
                        </span>
                    </small>
                    <a href="#" v-if="state.entityHeaderHovered" class="text-dark" @click.prevent="enableEntityNameEditing()">
                        <i class="fas fa-fw fa-edit fa-xs"></i>
                    </a>
                </span>
                <form class="form-inline" v-else>
                    <input type="text" class="form-control me-2" v-model="newEntityName" />
                    <button type="submit" class="btn btn-outline-success me-2" @click="updateEntityName(state.entity, newEntityName)">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                    <button type="reset" class="btn btn-outline-danger" @click="cancelUpdateEntityName()">
                        <i class="fas fa-fw fa-ban"></i>
                    </button>
                </form>
            </h3>
            <span>
                <button type="submit" form="entity-attribute-form" class="btn btn-success me-2" :disabled="!state.formDirty || !can('duplicate_edit_concepts')">
                    <i class="fas fa-fw fa-save"></i> {{ t('global.save') }}
                </button>
                <button type="button" class="btn btn-warning me-2" :disabled="!state.formDirty" @click="resetForm()">
                    <i class="fas fa-fw fa-undo"></i> {{ t('global.reset') }}
                </button>
                <button type="button" class="btn btn-danger" :disabled="!can('delete_move_concepts')" @click="deleteEntity(state.entity)">
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
                <a class="nav-link active" id="active-entity-attributes-tab" href="#" @click.prevent="setEntityView('attributes')">
                    {{ t('main.entity.tabs.attributes') }}
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="active-entity-comments-tab" href="#" @click.prevent="setEntityView('comments')">
                    {{ t('main.entity.tabs.comments') }}
                    <span class="badge bg-primary">{{ state.entity.comments_count }}</span>
                </a>
            </li>
        </ul>
        <div class="tab-content col ps-0 pe-0 overflow-hidden" id="myTabContent">
            <div class="tab-pane fade h-100 show active" id="active-entity-attributes-panel" role="tabpanel">
                <form id="entity-attribute-form" name="entity-attribute-form" class="h-100" @submit.prevent="saveEntity(state.entity)">
                    <attribute-list
                        class="pt-2 h-100 scroll-y-auto scroll-x-hidden"
                        v-if="state.attributesFetched"
                        v-dcan="'view_concept_props'"
                        :ref="el => setAttrRef(el)"
                        :attributes="state.entityAttributes"
                        :dependencies="state.entity.dependencies"
                        :disable-drag="true"
                        :metadata-addon="hasReferenceGroup"
                        :selections="state.entityTypeSelections"
                        :values="state.entity.data"
                        @dirty="setFormState"
                        @metadata="showMetadata"
                        @attr-dep-change="updateDependencyCounter">
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
                            {{ $t('global.comments.fetching_failed') }}
                            <button type="button" class="d-block mt-2 btn btn-xs btn-outline-success" @click="fetchComments">
                                <i class="fas fa-fw fa-sync"></i>
                                {{ $t('global.comments.retry_failed') }}
                            </button>
                        </p>
                    </div>
                    <comment-list
                        v-else
                        :avatar="48"
                        :comments="state.entity.comments"
                        :resource="state.resourceInfo"
                        @edited="editComment"
                        @on-delete="deleteComment"
                        @added="addComment">
                    </comment-list>
                </div>
            </div>
        </div>

        <!-- <router-view
            v-dcan="'view_concept_props'"
            :bibliography="bibliography"
            :refs="attributeReferences">
        </router-view> -->
    </div>
</template>

<script>
    // import { EventBus } from '../event-bus.js';
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

    import store from '../bootstrap/store.js';
    import router from '../bootstrap/router.js';

    import { date } from '../helpers/filters.js';
    import {
        getEntityComments,
    } from '../api.js';
    import {
        can,
        getEntityColors,
        getEntityType,
        getEntityTypeAttributeSelections,
        getEntityTypeDependencies,
        getUserBy,
        translateConcept
    } from '../helpers/helpers.js';
    import {
        showDiscard,
        showUserInfo,
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

            // FETCH
            store.dispatch('getEntity', route.params.id).then(_ => {
                getEntityTypeAttributeSelections();
                state.initFinished = true;
            });
            // onBeforeRouteUpdate((to, from) => {
            //     console.log(to);
            //     if(!this.$can('view_concept_props')) {
            //         store.dispatch('hideEntityData');
            //         Vue.set(this.selectedEntity, 'data', {});
            //         Vue.set(this.selectedEntity, 'attributes', []);
            //         Vue.set(this.selectedEntity, 'selections', {});
            //         Vue.set(this.selectedEntity, 'dependencies', []);
            //         Vue.set(this.selectedEntity, 'references', []);
            //         Vue.set(this.selectedEntity, 'comments', []);
            //     }
            //     if(!store.entity.comments || store.entity.comments_count === 0) {
            //         Vue.set(store.entity, 'comments', []);
            //     }
            //     const cid = store.entity.id;
            //     const ctid = store.entity.entity_type_id;
            //     return $httpQueue.add(() => $http.get(`/entity/${cid}/data`).then(response => {
            //         // if result is empty, php returns [] instead of {}
            //         if(response.data instanceof Array) {
            //             response.data = {};
            //         }
            //         Vue.set(store.entity, 'data', response.data);
            //         return $http.get(`/editor/entity_type/${ctid}/attribute`);
            //     }).then(response => {
            //         store.entity.attributes = [];
            //         let data = response.data;
            //         for(let i=0; i<data.attributes.length; i++) {
            //             let aid = data.attributes[i].id;
            //             if(!store.entity.data[aid]) {
            //                 let val = {};
            //                 switch(data.attributes[i].datatype) {
            //                     case 'dimension':
            //                     case 'epoch':
            //                     case 'timeperiod':
            //                         val.value = {};
            //                         break;
            //                     case 'table':
            //                     case 'list':
            //                         val.value = [];
            //                         break;
            //                 }
            //                 Vue.set(store.entity.data, aid, val);
            //             } else {
            //                 const val = store.entity.data[aid].value;
            //                 switch(data.attributes[i].datatype) {
            //                     case 'date':
            //                         const dtVal = new Date(val);
            //                         store.entity.data[aid].value = dtVal;
            //                         break;
            //                 }
            //             }
            //             store.entity.attributes.push(data.attributes[i]);
            //         }
            //         // if result is empty, php returns [] instead of {}
            //         if(data.selections instanceof Array) {
            //             data.selections = {};
            //         }
            //         if(data.dependencies instanceof Array) {
            //             data.dependencies = {};
            //         }
            //         Vue.set(store.entity, 'selections', data.selections);
            //         Vue.set(store.entity, 'dependencies', data.dependencies);

            //         const aid = this.$route.params.aid;
            //         this.setReferenceAttribute(aid);
            //         Vue.set(this, 'dataLoaded', true);
            //         this.setEntityView();
            //     }));
            // });

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
                hiddenAttributes: 0,
                entityHeaderHovered: false,
                initFinished: false,
                commentLoadingState: 'not',
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
                router.push({
                    append: true,
                    name: 'entityrefs',
                    params: {
                        aid: attribute.id
                    },
                    query: route.query
                });
            };
            const updateDependencyCounter = event => {
                state.hiddenAttributes = event.counter;
            };
            const setEntityView = tab => {
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
                console.log("TODO: Implement saveEntity method");
                state.formDirty = false;
                attrRef.value.undirtyList();
                return new Promise(r => r(null));
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
            });
            onBeforeUpdate(_ => {
                attrRef.value = {};
            });

            watch(_ => route.params,
                async newParams => {
                    state.initFinished = false;
                    if(!newParams.id) return;
                    store.dispatch('getEntity', newParams.id).then(_ => {
                        getEntityTypeAttributeSelections();
                        state.initFinished = true;
                    });
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
                updateDependencyCounter,
                setEntityView,
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

    // export default {
    //     beforeRouteEnter(to, from, next) {
    //         next(vm => vm.getEntityData(vm.selectedEntity));
    //     },
    //     beforeRouteUpdate(to, from, next) {
    //         if(to.params.id != from.params.id) {
    //             this.reset();
    //             this.getEntityData(this.selectedEntity).then(r => {
    //                 next();
    //             });
    //         } else {
    //             if(to.params.aid) {
    //                 this.setReferenceAttribute(to.params.aid);
    //             }
    //             next();
    //         }
    //     },
    //     methods: {
    //         reset() {
    //             this.commentLoadingState = 'not';
    //         },
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
    //         saveEntity(entity) {
    //             if(!this.$can('duplicate_edit_concepts')) return;
    //             let cid = entity.id;
    //             var patches = [];
    //             for(let f in this.fields) {
    //                 if(this.fields.hasOwnProperty(f) && f.startsWith('attribute-')) {
    //                     if(this.fields[f].dirty) {
    //                         let aid = Number(f.replace(/^attribute-/, ''));
    //                         let data = entity.data[aid];
    //                         var patch = {};
    //                         patch.params = {};
    //                         patch.params.aid = aid;
    //                         patch.params.cid = cid;
    //                         if(data.id) {
    //                             // if data.id exists, there has been an entry in the database, therefore it is a replace/remove operation
    //                             patch.params.id = data.id;
    //                             if(data.value && data.value != '') {
    //                                 // value is set, therefore it is a replace
    //                                 patch.op = "replace";
    //                                 patch.value = data.value;
    //                                 patch.value = this.getCleanValue(data, entity.attributes);
    //                             } else {
    //                                 // value is empty, therefore it is a remove
    //                                 patch.op = "remove";
    //                             }
    //                         } else {
    //                             // there has been no entry in the database before, therefore it is an add operation
    //                             if(data.value && data.value != '') {
    //                                 patch.op = "add";
    //                                 data.attribute = entity.attributes.find(a => a.id == aid);
    //                                 patch.value = this.getCleanValue(data, entity.attributes);
    //                             } else {
    //                                 // there has be no entry in the database before and values are not different (should not happen ;))
    //                                 continue;
    //                             }
    //                         }
    //                         patches.push(patch);
    //                     }
    //                 }
    //             }
    //             return $httpQueue.add(() => $http.patch('/entity/'+cid+'/attributes', patches).then(response => {
    //                 this.resetFlags();
    //                 this.$showToast(
    //                     this.$t('main.entity.toasts.updated.title'),
    //                     this.$t('main.entity.toasts.updated.msg', {
    //                         name: entity.name
    //                     }),
    //                     'success'
    //                 );
    //                 this.setModificationFields(response.data);
    //             }).catch(error => {
    //                 const r = error.response;
    //                 this.$showToast(
    //                     `${r.status}: ${r.statusText}`,
    //                     r.data.error,
    //                     'error',
    //                     5000
    //                 );
    //             })
    //         );
    //         },
    //         deleteEntity(entity) {
    //             EventBus.$emit('entity-delete', {
    //                 entity: entity
    //             });
    //         },
    //         onEntityHeaderHover(hoverState) {
    //             this.entityHeaderHovered = hoverState;
    //         },
    //         enableEntityNameEditing() {
    //             this.newEntityName = this.selectedEntity.name;
    //             Vue.set(this.selectedEntity, 'editing', true);
    //         },
    //         updateEntityName(entity, name) {
    //             // If name does not change, just cancel
    //             if(entity.name == name) {
    //                 this.cancelUpdateEntityName();
    //             } else {
    //                 const data = {
    //                     name: name
    //                 };
    //                 $httpQueue.add(() => $http.patch(`entity/${entity.id}/name`, data).then(response => {
    //                     EventBus.$emit('entity-update', {
    //                         type: 'name',
    //                         entity_id: entity.id,
    //                         value: name
    //                     });
    //                     const d = response.data;
    //                     entity.name = d.name;
    //                     entity.user_id = d.user_id;
    //                     entity.updated_at = d.updated_at;
    //                     entity.user = d.user;
    //                     this.cancelUpdateEntityName();
    //                 }));
    //             }
    //         },
    //         cancelUpdateEntityName() {
    //             Vue.set(this.selectedEntity, 'editing', false);
    //             this.newEntityName = '';
    //         },
    //         getComment(list, id) {
    //             if(!list || list.length == 0) return;
    //             for(let i=0; i<list.length; i++) {
    //                 if(list[i].id == id) {
    //                     return list[i];
    //                 }
    //                 const gotIt = this.getComment(list[i].replies, id);
    //                 if(gotIt) return gotIt;
    //             }
    //         },
    //         getCleanValue(entry, attributes) {
    //             if(!entry) return;
    //             const v = entry.value;
    //             switch(entry.attribute.datatype) {
    //                 case 'string-sc':
    //                     return {
    //                         id: v.id,
    //                         concept_url: v.concept_url
    //                     };
    //                 case 'string-mc':
    //                     return v.map(smc => {
    //                         return {
    //                             id: smc.id,
    //                             concept_url: smc.concept_url
    //                         };
    //                     });
    //                 case 'table':
    //                     return v.map(row => {
    //                         for(let k in row) {
    //                             const col = row[k];
    //                             const aid = entry.attribute.id;
    //                             const tattr = attributes.find(a => a.id == aid);
    //                             const attr = tattr.columns[k];
    //                             // return necessary fields only
    //                             switch(attr.datatype) {
    //                                 case 'string-sc':
    //                                     row[k] = {
    //                                         id: col.id,
    //                                         concept_url: col.concept_url
    //                                     };
    //                                     break;
    //                                 case 'entity':
    //                                     row[k] = {
    //                                         id: col.id,
    //                                         name: col.name
    //                                     };
    //                                     break;
    //                                 default:
    //                                     row[k] = col;
    //                                     break;
    //                             }
    //                         }
    //                         return row;
    //                     });
    //                 default:
    //                     return entry.value;
    //             }
    //         },
    //         setModificationFields(entity) {
    //             if(!this.selectedEntity && !this.selectedEntity.id) return;

    //             this.selectedEntity.user = entity.user;
    //             this.selectedEntity.updated_at = entity.updated_at;
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
    //         setReferenceAttribute(aid) {
    //             this.referenceAttribute = aid;
    //         },
    //         resetFlags() {
    //             this.$validator.fields.items.forEach(field => {
    //                 field.reset();
    //             });
    //         },
    //     },
    //     data() {
    //         return {
    //             newEntityName: '',
    //             entityHeaderHovered: false,
    //             dataLoaded: false,
    //             dependencyInfoHovered: false,
    //             hiddenAttributes: 0,
    //             referenceAttribute: null,
    //         }
    //     },
    //     computed: {
    //         isFormDirty() {
    //             return Object.keys(this.fields).some(key => this.fields[key].dirty) && !this.errors.any();
    //         },
    //         hasData() {
    //             return this.dataLoaded &&
    //                 !!this.selectedEntity &&
    //                 !!this.selectedEntity.attributes &&
    //                 !!this.selectedEntity.selections
    //         },
    //         attributeReferences() {
    //             let data = {
    //                 refs: [],
    //                 value: {},
    //                 attribute: {}
    //             };
    //             if(!this.selectedEntity.attributes) return data;
    //             if(this.referenceAttribute) {
    //                 const attribute = this.selectedEntity.attributes.find(a => a.id == this.referenceAttribute);
    //                 if(!attribute) return data;
    //                 data.refs = this.hasReferenceGroup(attribute.thesaurus_url) ? this.selectedEntity.references[attribute.thesaurus_url] : [];
    //                 data.value = this.selectedEntity.data[this.referenceAttribute];
    //                 data.attribute = attribute;
    //             }
    //             return data;
    //         },
    //     },
    //     watch: {
    //         isFormDirty(newDirty, oldDirty) {
    //             if(newDirty != oldDirty) {
    //                 this.$emit('detail-updated', {
    //                     isDirty: newDirty,
    //                     onDiscard: newDirty ? this.saveEntity : entity => {}
    //                 });
    //             }
    //         }
    //     }
    }
</script>
