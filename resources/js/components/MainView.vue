<template>
    <div class="row h-100 overflow-hidden">
        <div :class="`h-100 d-flex flex-column col-md-${state.columnPref.left}`" id="tree-container" v-dcan="'view_concepts'" v-if="state.columnPref.left > 0">
            <entity-tree
                class="col px-0 h-100">
            </entity-tree>
        </div>
        <div :class="`h-100 border-start border-end col-md-${state.columnPref.center}`" id="attribute-container" v-dcan="'view_concepts|view_concept_props'" v-if="state.columnPref.center > 0">
            <router-view>
            </router-view>
        </div>
        <div :class="`h-100 d-flex flex-column col-md-${state.columnPref.right}`" id="addon-container" v-if="state.columnPref.right > 0">
            <ul class="nav nav-tabs">
                <!-- <li class="nav-item" v-for="plugin in $getTabPlugins()">
                    <router-link class="nav-link" :class="{active: tab == plugin.key}" :to="{ query: { tab: plugin.key }}" append>
                        <i class="fas fa-fw" :class="plugin.icon"></i> {{ $t(plugin.label) }}
                    </router-link>
                </li> -->
                <li class="nav-item">
                    <a href="#" class="nav-link" :class="{active: state.tab == 'references', disabled: !state.entity.id}" @click.prevent="setTab('references')">
                        <i class="fas fa-fw fa-bookmark"></i> {{ t('main.entity.references.title') }}
                    </a>
                </li>
            </ul>
            <div class="mt-2 col px-0">
                <!-- <keep-alive>
                    <component
                        :selected-entity="selectedEntity"
                        :entity-data-loaded="dataLoaded"
                        :is="activePlugin"
                        :params="$route.query"
                        v-on:update:link="updateLink">
                    </component>
                </keep-alive> -->
                <div v-show="isTab('references') && !!state.entity.id" class="h-100 scroll-y-auto">
                    <p class="alert alert-info" v-if="!state.hasReferences">
                        {{ t('main.entity.references.empty') }}
                    </p>
                    <div v-else v-for="(referenceGroup, key) in state.entity.references" class="mb-3" :key="key">
                        <h5 class="mb-1 fw-medium">
                            <a href="#" class="text-decoration-none" @click.prevent="showMetadataForReferenceGroup(referenceGroup)">
                                {{ translateConcept(key) }}
                            </a>
                        </h5>
                        <div class="list-group ps-2 w-90">
                            <a class="list-group-item list-group-item-action d-flex flex-row" v-for="(reference, i) in referenceGroup" :key="i">
                                <div class="flex-grow-1">
                                    <blockquote class="blockquote fs-09">
                                        <p class="text-muted">
                                            {{ reference.description }}
                                        </p>
                                    </blockquote>
                                    <figcaption class="blockquote-footer fw-medium mb-0">
                                        {{ reference.bibliography.author }} in <cite :title="reference.bibliography.title">
                                            {{ reference.bibliography.title }} ,{{ reference.bibliography.year }}
                                        </cite>
                                    </figcaption>
                                </div>
                                <div>
                                    <span class="text-muted fw-light small">
                                        {{ date(reference.updated_at) }}
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
    } from 'vue';
    
    import {
        onBeforeRouteUpdate,
    } from 'vue-router';

    import { useI18n } from 'vue-i18n';

    import {
        useRoute,
    } from 'vue-router';

    import store from '../bootstrap/store.js';
    import router from '../bootstrap/router.js';

    import { useToast } from '../plugins/toast.js';

    import {
        translateConcept,
    } from '../helpers/helpers.js';
    import {
        date,
    } from '../helpers/filters.js';
    import {
        canShowReferenceModal,
    } from '../helpers/modal.js';

    export default {
        setup(props, context) {
            const { t } = useI18n();
            const currentRoute = useRoute();
            const toast = useToast();

            // FUNCTIONS
            const setTab = to => {
                router.push({
                    query: {
                        ...currentRoute.query,
                        tab: to,
                    },
                    append: true,
                });
            };
            const isTab = id => {
                return state.tab == id;
            };
            const showMetadataForReferenceGroup = referenceGroup => {
                if(!referenceGroup) return;
                if(!state.entity) return;
                const aid = referenceGroup[0].attribute_id;

                const canOpen = canShowReferenceModal(aid);
                if(canOpen) {
                    router.push({
                        append: true,
                        name: 'entityrefs',
                        query: currentRoute.query,
                        params: {
                            aid: aid,
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

            // DATA
            const state = reactive({
                tab: computed(_ => store.getters.mainView.tab),
                concepts: computed(_ => store.getters.concepts),
                entity: computed(_ => store.getters.entity),
                hasReferences: computed(_ => !!state.entity.references && Object.keys(state.entity.references).length > 0),
                entityTypes: computed(_ => store.getters.entityTypes),
                columnPref: computed(_ => store.getters.preferenceByKey('prefs.columns')),
                users: computed(_ => store.getters.users),
            });

            // ON MOUNTED
            onMounted(_ => {
                console.log("mainview component mounted");
            });

            onBeforeRouteUpdate(async (to, from) => {
                if(to.query.tab !== from.query.tab) {
                    store.dispatch('setMainViewTab', to.query.tab);
                }
            });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                date,
                // LOCAL
                setTab,
                isTab,
                showMetadataForReferenceGroup,
                // STATE
                state,
            };
        }

        // beforeRouteEnter(to, from, next) {
        //     let bibliography, entityData;
        //     $httpQueue.add(() => $http.get('bibliography').then(response => {
        //         bibliography = response.data;
        //         if(to.params.id) {
        //             return $http.get(`/entity/${to.params.id}`);
        //         } else {
        //             return new Promise(resolve => resolve(null));
        //         }
        //     }).then(response => {
        //         entityData = response ? response.data : null;
        //         if(to.params.id) {
        //             return $http.get(`/entity/${to.params.id}/reference`);
        //         } else {
        //             return new Promise(resolve => resolve(null));
        //         }
        //     }).then(response => {
        //         next(vm => vm.init(bibliography, to.query.tab, entityData, response ? response.data : null));
        //     }));
        // },
        // beforeRouteUpdate(to, from, next) {
        //     if(to.query.tab) {
        //         this.setTabOrPlugin(to.query.tab);
        //     }
        //     let loadNext = () => {
        //         if(to.params.id) {
        //             if(to.params.id != from.params.id) {
        //                 this.getNewEntity(to.params.id).then(r => {
        //                     next();
        //                 });
        //             } else {
        //                 next();
        //             }
        //         } else {
        //             this.resetEntity();
        //             next();
        //         }
        //     }
        //     if(this.discardState.dirty) {
        //         let discardAndContinue = () => {
        //             loadNext();
        //         };
        //         let saveAndContinue = () => {
        //             this.discardState.callback(this.selectedEntity).then(loadNext);
        //         };
        //         this.$modal.show(this.discardModal, {reference: this.selectedEntity.name, onDiscard: discardAndContinue, onSave: saveAndContinue, onCancel: _ => next(false)})
        //     } else {
        //         loadNext();
        //     }
        // },
        // mounted() {},
        // methods: {
        //     getNewEntity(id) {
        //         return $httpQueue.add(() => $http.get(`/entity/${id}`).then(response => {
        //             this.selectedEntity = response.data;
        //             return $http.get(`/entity/${id}/reference`);
        //         }).then(response => {
        //             Vue.set(this.selectedEntity, 'references', response.data);
        //         }));
        //     },
        //     resetEntity() {
        //         this.selectedEntity = {};
        //     },
        //     init(bibliography, openTab, entityData, entityReferences) {
        //         this.initFinished = false;
        //         this.bibliography = bibliography;
        //         if(entityData) {
        //             this.selectedEntity = entityData;
        //         }
        //         if(entityReferences) {
        //             if(Array.isArray(entityReferences) && !entityReferences.length) {
        //                 entityReferences = {};
        //             }
        //             Vue.set(this.selectedEntity, 'references', entityReferences);
        //         } else {
        //             Vue.set(this.selectedEntity, 'references', {});
        //         }
        //         if(openTab) {
        //             this.setTabOrPlugin(openTab);
        //         }
        //         EventBus.$on('entity-update', this.handleEntityUpdate);
        //         EventBus.$on('references-updated', this.handleReferenceUpdate);
        //         this.initFinished = true;
        //         return new Promise(r => r(null));
        //     },
        //     setDetailDirty(event) {
        //         this.discardState.dirty = event.isDirty;
        //         this.discardState.callback = event.onDiscard;
        //     },
        //     setReferencesTab() {
        //         if(!this.selectedEntity.id) return;
        //         this.$router.push({
        //             append: true,
        //             query: {
        //                 tab: 'references'
        //             }
        //         });
        //     },
        //     setTabOrPlugin(key) {
        //         if(key == 'references') {
        //             this.setActiveTab('references');
        //         } else {
        //             const plugins = this.$getTabPlugins();
        //             const plugin = plugins.find(p => p.key == key);
        //             if(plugin) {
        //                 this.setActivePlugin(plugin);
        //             }
        //         }
        //     },
        //     setActiveTab: function(tab) {
        //         if(tab == 'references') {
        //             if(!this.selectedEntity.id) return;
        //             this.activePlugin = '';
        //         }
        //         this.tab = tab;
        //     },
        //     setActivePlugin: function(plugin) {
        //         this.setActiveTab(plugin.key);
        //         this.activePlugin = plugin.tag;
        //     },
        //     updateLink(geoId, entityId) {
        //         if(entityId != this.selectedEntity.id) {
        //             return;
        //         }
        //         this.selectedEntity.geodata_id = geoId;
        //     },
        //     handleEntityUpdate(e) {
        //         if(this.selectedEntity && this.selectedEntity.id == e.entity_id) {
        //             switch(e.type) {
        //                 case 'name':
        //                     this.selectedEntity.name = e.value;
        //                 break;
        //                 default:
        //                     vm.$throwError({message: `Unknown event type ${e.type} received.`});
        //             }
        //         }
        //     },
        //     handleReferenceUpdate(e) {
        //         const r = e.reference;
        //         const grp = this.selectedEntity.references[e.group];
        //         switch(e.action) {
        //             case 'add':
        //                 if(!grp) {
        //                     Vue.set(this.selectedEntity.references, e.group, []);
        //                 }
        //                 this.selectedEntity.references[e.group].push(r);
        //                 break;
        //             case 'delete':
        //                 const idx = grp.findIndex(ref => ref.id == r.id);
        //                 if(idx > -1) {
        //                     this.selectedEntity.references[e.group].splice(idx, 1);
        //                 }
        //                 if(!grp.length) {
        //                     Vue.delete(this.selectedEntity.references, e.group);
        //                 }
        //                 break;
        //             case 'edit':
        //                 let ref = grp.find(ref => ref.id == r.id);
        //                 ref.description = r.description;
        //                 ref.updated_at = r.updated_at;
        //                 break;
        //         }
        //     }
        // },
        // data() {
        //     return {
        //         bibliography: {},
        //         initFinished: false,
        //         selectedEntity: {},
        //         referenceModal: {},
        //         dataLoaded: false,
        //         defaultKey: undefined,
        //         plugins: this.$getTabPlugins(),
        //         activePlugin: '',
        //         discardModal: 'discard-changes-modal',
        //         discardState: {
        //             dirty: false,
        //             callback: () => {}
        //         }
        //     }
        // },
        // computed: {
        //     hasReferences: function() {
        //         return this.selectedEntity && this.selectedEntity.references && Object.keys(this.selectedEntity.references).length;
        //     },
        //     tab: {
        //         get() {
        //             if(this.defaultKey) return this.defaultKey;
        //             else if(this.plugins && this.plugins[0]) {
        //                 this.activePlugin = this.plugins[0].tag;
        //                 return this.plugins[0].key;
        //             } else {
        //                 return '';
        //             }
        //         },
        //         set(newValue) {
        //             this.defaultKey = newValue;
        //         }
        //     }
        // }
    }
</script>
