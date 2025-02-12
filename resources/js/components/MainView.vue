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
                <ReferenceTab />
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

    import useEntityStore from '@/bootstrap/stores/entity.js';
    import useSystemStore from '@/bootstrap/stores/system.js';
    import router from '%router';

    import {
        subscribeNotifications,
        subscribeSystemChannel,
        unsubscribeSystemChannel,
        listenToList,
    } from '@/helpers/websocket.js';
    import {
        handleBibliographyCreated,
        handleBibliographyUpdated,
        handleBibliographyDeleted,
        handleEntityCreated,
        handleEntityUpdated,
        handleEntityDeleted,
    } from '@/handlers/system.js';
    import {
        handleNotifications,
    } from '@/handlers/notification.js';

    import useWebSocketConnectionToast from '@/composables/websocket-connection-toast.js';
    import ReferenceTab from './bibliography/ReferenceTab.vue';

    export default {
        components: {
            ReferenceTab,
        },
        setup(props, context) {
            const { t } = useI18n();
            const currentRoute = useRoute();
            const entityStore = useEntityStore();
            const systemStore = useSystemStore();
            useWebSocketConnectionToast();

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
           
            // DATA
            const state = reactive({
                tab: computed(_ => systemStore.mainView.tab),
                tabComponent: computed(_ => {
                    if(state.tab === 'references') {
                        return ReferenceTab;
                    }

                    const plugin = state.tabPlugins.find(p => p.key == state.tab);
                    if(!!plugin) {
                        return plugin.componentTag;
                    } else {
                        return '';
                    }
                }),
                concepts: computed(_ => systemStore.concepts),
                entity: computed(_ => entityStore.selectedEntity),
                entityTypes: computed(_ => entityStore.entityTypes),
                columnPref: computed(_ => systemStore.getPreference('prefs.columns')),
                isDetailLoaded: computed(_ => state.entity?.id > 0),
                tabPlugins: computed(_ => systemStore.getSlotPlugins('tab')),
            });
            const channels = {};

            // ON MOUNTED
            onMounted(_ => {
                console.log('mainview component mounted');
                systemStore.setMainViewTab(currentRoute.query.tab);
                channels.system = subscribeSystemChannel();
                listenToList(channels.system, [
                    handleEntityCreated,
                    handleEntityUpdated,
                    handleEntityDeleted,
                    handleBibliographyCreated,
                    handleBibliographyUpdated,
                    handleBibliographyDeleted,
                ]);
                channels.notification = subscribeNotifications(handleNotifications);
            });

            onBeforeRouteUpdate(async (to, from) => {
                if(to.query.tab !== from.query.tab) {
                    systemStore.setMainViewTab(to.query.tab);
                }
            });
            onBeforeRouteLeave((to, from) => {
                systemStore.setMainViewTab(null);
                unsubscribeSystemChannel();
            });

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                setTab,
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
