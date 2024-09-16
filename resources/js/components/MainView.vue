<template>
    <div class="row h-100 overflow-hidden">
        <div
            v-if="state.columnPref.left > 0"
            id="tree-container"
            v-dcan="'entity_read'"
            :class="`h-100 d-flex flex-column col-md-${state.columnPref.left}`"
        >
            <entity-tree class="col px-0 h-100" />
        </div>
        <div
            v-if="state.columnPref.center > 0"
            id="attribute-container"
            v-dcan="'entity_read|entity_data_read'"
            :class="`h-100 border-start border-end col-md-${state.columnPref.center}`"
        >
            <router-view />
            <alert
                v-if="!state.isDetailLoaded"
                :message="t('main.entity.detail_tab_none_selected')"
                :type="'info'"
                :noicon="false"
                :icontext="t('global.information')"
            />
        </div>
        <div
            v-if="state.columnPref.right > 0"
            id="addon-container"
            :class="`h-100 d-flex flex-column col-md-${state.columnPref.right}`"
        >
            <ul class="nav nav-tabs">
                <li
                    v-for="(plugin, i) in state.tabPlugins"
                    :key="i"
                    class="nav-item"
                >
                    <router-link
                        class="nav-link"
                        :class="{ active: state.tab == plugin.key }"
                        :to="{ query: { tab: plugin.key } }"
                        append
                    >
                        <i
                            class="fas fa-fw"
                            :class="plugin.icon"
                        /> {{ t(plugin.label) }}
                    </router-link>
                </li>
                <li class="nav-item">
                    <a
                        href="#"
                        class="nav-link"
                        :class="{ active: state.tab == 'references', disabled: !state.entity.id }"
                        @click.prevent="setTab('references')"
                    >
                        <i class="fas fa-fw fa-bookmark" /> {{ t('main.entity.references.title') }}
                    </a>
                </li>
            </ul>
            <div class="mt-2 col px-0 overflow-hidden">
                <keep-alive>
                    <component :is="state.tabComponent" />
                </keep-alive>
                <div
                    v-show="isTab('references') && !!state.entity.id"
                    class="h-100 overflow-y-auto"
                >
                    <p
                        v-if="!state.hasReferences"
                        class="alert alert-info"
                    >
                        {{ t('main.entity.references.empty') }}
                    </p>
                    <template
                        v-for="(referenceGroup, key) in state.entity.references"
                        v-else
                        :key="key"
                    >
                        <div
                            v-if="referenceGroup.length > 0"
                            class="reference-group"
                        >
                            <h5 class="mb-2 fw-medium">
                                <a
                                    href="#"
                                    class="text-decoration-none"
                                    @click.prevent="showMetadataForReferenceGroup(referenceGroup)"
                                >
                                    {{ translateConcept(key) }}
                                </a>
                            </h5>
                            <div class="list-group w-90">
                                <div
                                    v-for="(reference, i) in referenceGroup"
                                    :key="i"
                                    class="list-group-item pt-0"
                                >
                                    <header class="text-end">
                                        <span class="text-muted fw-light small">
                                            {{ date(reference.updated_at) }}
                                        </span>
                                    </header>
                                    <Quotation :value="reference" />
                                </div>
                            </div>
                        </div>
                    </template>
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
        onBeforeRouteLeave,
    } from 'vue-router';

    import { useI18n } from 'vue-i18n';

    import {
        useRoute,
    } from 'vue-router';

    import store from '@/bootstrap/store.js';
    import useEntityStore from '@/bootstrap/stores/entity.js';
    import router from '%router';

    import {
        translateConcept,
    } from '@/helpers/helpers.js';

    import {
        formatAuthors,
    } from '@/helpers/bibliography.js';

    import {
        date,
    } from '@/helpers/filters.js';

    import {
        canShowReferenceModal,
        showLiteratureInfo,
    } from '@/helpers/modal.js';
    import {
        subscribeTo,
        unsubscribeFrom,
    } from '@/helpers/websocket.js';

    import { useToast } from '@/plugins/toast.js';

    import Quotation from '@/components/bibliography/Quotation.vue';

    export default {
        components: {
            Quotation,
        },
        setup(props, context) {
            const { t } = useI18n();
            const currentRoute = useRoute();
            const toast = useToast();
            const entityStore = useEntityStore();

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
            const openLiteratureInfo = reference => {
                showLiteratureInfo(reference.bibliography.id);
            };

            // DATA
            const state = reactive({
                tab: computed(_ => store.getters.mainView.tab),
                tabComponent: computed(_ => {
                    const plugin = state.tabPlugins.find(p => p.key == state.tab);
                    if(!!plugin) {
                        return plugin.componentTag;
                    } else {
                        return '';
                    }
                }),
                concepts: computed(_ => store.getters.concepts),
                entity: computed(_ => entityStore.selectedEntity),
                hasReferences: computed(_ => {
                    const isNotSet = !state.entity.references;
                    if(isNotSet) return false;

                    const isEmpty = !Object.keys(state.entity.references).length > 0;
                    if(isEmpty) return false;
                    return Object.values(state.entity.references).some(v => v.length > 0);
                }),
                entityTypes: computed(_ => store.getters.entityTypes),
                columnPref: computed(_ => store.getters.preferenceByKey('prefs.columns')),
                isDetailLoaded: computed(_ => state.entity?.id > 0),
                tabPlugins: computed(_ => store.getters.slotPlugins('tab')),
            });

            // ON MOUNTED
            onMounted(_ => {
                console.log('mainview component mounted');
                store.dispatch('setMainViewTab', currentRoute.query.tab);
                subscribeTo(`entity_updates`, 'EntityUpdated', e => {
                    // Only handle event if from different user
                    if(e.user.id == store.getters.user.id) return;
                    console.log("Entity created/updated by a user", e);
                    let message = null;
                    if(e.status == 'added') {
                        message = `A new Entity '${e.entity.name}' has been added by '${e.user.nickname}'!`;
                        entityStore.add(e.entity, true);
                    } else if(e.status == 'updated') {
                        message = `Entity '${e.entity.name}' has been updated by '${e.user.nickname}'!`;
                        entityStore.add(e.entity, true);
                    }

                    if(message) {
                        toast.$toast(message, '', {
                            duration: 2500,
                            autohide: true,
                            channel: 'info',
                            icon: true,
                            simple: true,
                        });
                    }
                }, true);
            });

            onBeforeRouteUpdate(async (to, from) => {
                if(to.query.tab !== from.query.tab) {
                    store.dispatch('setMainViewTab', to.query.tab);
                }
            });
            onBeforeRouteLeave((to, from) => {
                store.dispatch('setMainViewTab', null);
                unsubscribeFrom(`entity_updates`);
            });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                formatAuthors,
                date,
                // LOCAL
                setTab,
                isTab,
                showMetadataForReferenceGroup,
                openLiteratureInfo,
                // STATE
                state,
            };
        }
        // beforeRouteUpdate(to, from, next) {
        //     if(to.query.tab) {
        //         this.setTabOrPlugin(to.query.tab);
        //     }
        // },
        // mounted() {},
        // methods: {
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
        // },
        // data() {
        //     return {
        //         plugins: this.$getTabPlugins(),
        //         activePlugin: '',
        //     }
        // },
        // computed: {
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
    };
</script>
