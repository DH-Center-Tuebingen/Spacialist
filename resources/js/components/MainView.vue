<template>
    <div class="row h-100 overflow-hidden">
        <div
            v-if="state.columnPref.left > 0"
            id="tree-container"
            v-dcan="'entity_read'"
            :class="`h-100 d-flex flex-column col-md-${state.columnPref.left}`"
        >
            <entity-tree
                id="main-entity-tree"
                class="col px-0 h-100"
            />
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
                                    <div>
                                        <blockquote class="blockquote fs-09 mb-4">
                                            <p class="text-muted">
                                                {{ reference.description }}
                                            </p>
                                        </blockquote>
                                        <figcaption class="blockquote-footer fw-medium mb-0 d-flex gap-1">
                                            <span>
                                                {{ reference.bibliography.author }} in <cite
                                                    :title="reference.bibliography.title"
                                                >
                                                    {{ reference.bibliography.title }} ,{{ reference.bibliography.year }}
                                                </cite>
                                                <a
                                                    href="#"
                                                    class="ms-1"
                                                    @click.prevent="openLiteratureInfo(reference)"
                                                >
                                                    <i class="fas fa-fw fa-info-circle" />
                                                </a>
                                            </span>
                                        </figcaption>
                                    </div>
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
            onBeforeUnmount,
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
        import router from '%router';

        import { useToast } from '@/plugins/toast.js';

        import {
            translateConcept,
            xpath,
        } from '@/helpers/helpers.js';
        import {
            date,
        } from '@/helpers/filters.js';
        import {
            canShowReferenceModal,
            showLiteratureInfo,
        } from '@/helpers/modal.js';
        import {
            fetchChildren,
        } from '@/helpers/tree.js';

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
                let entityTabGroups = null;
                const state = reactive({
                    altCaptured: null,
                    isShortcutMode: false,
                    treeRoot: null,
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
                    entity: computed(_ => store.getters.entity),
                    hasReferences: computed(_ => {
                        const isNotSet = !state.entity.references;
                        if(isNotSet) return false;
                        
                        const isEmpty = !Object.keys(state.entity.references).length > 0;
                        if(isEmpty) return false;
                        return Object.values(state.entity.references).some(v => v.length > 0);
                    }),
                    entityTypes: computed(_ => store.getters.entityTypes),
                    columnPref: computed(_ => store.getters.preferenceByKey('prefs.columns')),
                    isDetailLoaded: computed(_ => store.getters.entity?.id > 0),
                    tabPlugins: computed(_ => store.getters.slotPlugins('tab')),
                });
                const switchTab = next => {
                    if(currentRoute.name != 'entitydetail') return;

                    if(!entityTabGroups || entityTabGroups.children.length == 0) return;

                    let path = `//a[contains(@class,"active-entity-detail-tab") and contains(concat(" ", normalize-space(@class)," ")," active ")]`;

                    if(next) {
                        path = `(${path}/following::a[contains(@class,"active-entity-detail-tab")])[1]`;
                    } else {
                        path = `(${path}/preceding::a[contains(@class,"active-entity-detail-tab")])[last()]`;
                    }
                    let xp = xpath(path, entityTabGroups);
                    let tgt = xp.iterateNext();

                    if(!tgt) {
                        if(next) {
                            path = `(//a[contains(@class,"active-entity-detail-tab")])[1]`;
                        } else {
                            path = `(//a[contains(@class,"active-entity-detail-tab")])[last()]`;
                        }
                    }
                    xp = xpath(path, entityTabGroups);
                    tgt = xp.iterateNext();
                    xp.click();
                };
                const switchEntity = next => {
                    if(currentRoute.name != 'entitydetail') return;

                    const e = state.entity;

                    let xp = null;
                    let path = '';
                    if(next) {
                        path = `//div[@id="tree-node-${e.id}"]/following::div[contains(@class,"entity-tree-node") and count(ancestor::ul/preceding-sibling::a/div[contains(@class,"entity-tree-node") and contains(@class, "node-closed")])=0][1]`;
                    } else {
                        path = `//div[@id="tree-node-${e.id}"]/preceding::div[contains(@class,"entity-tree-node") and count(ancestor::ul/preceding-sibling::a/div[contains(@class,"entity-tree-node") and contains(@class, "node-closed")])=0][1]`;
                    }
                    xp = xpath(path, state.treeRoot);
                    let tgt = xp.iterateNext();

                    if(!tgt) {
                        if(next) {
                            path = `(//div[contains(@class,"entity-tree-node")])[1]`;
                        } else {
                            path = `(//div[contains(@class,"entity-tree-node")])[last()]`;
                        }
                    }

                    xp = xpath(path, state.treeRoot);
                    tgt = xp.iterateNext();
                    tgt.click();
                };
                const switchEntityLevel = open => {
                    if(currentRoute.name != 'entitydetail') return;

                    const e = state.entity;

                    if(open && !e.state.openable) return;
                    if(open && e.state.opened) return;
                    if(open && e.children.length < e.children_count) {
                        e.state.loading = true;
                        fetchChildren(e.id).then(response => {
                            e.children =  response;
                            e.state.loading = false;
                            e.childrenLoaded = true;
                            e.state.opened = !e.state.opened;
                        });
                    } else {
                        e.state.opened = !e.state.opened;
                    }
                    if(!open && e.root_entity_id) {
                        const parent = store.getters.entities[e.root_entity_id];
                        if(parent) {
                            parent.state.opened = false;
                            const {
                                view,
                                ...query
                            } = currentRoute.query;
                            router.push({
                                name: 'entitydetail',
                                params: {
                                    id: parent.id,
                                },
                                query: query,
                            });
                        }
                    }
                };
                const handleKeyboardShortcuts = event => {
                    const key = event.keyCode;

                    if(key == 18) {
                        const now = (new Date()).getTime();
                        if(!state.altCaptured) {
                            state.altCaptured = now;
                            setTimeout(_ => {
                                state.altCaptured = null;
                            }, 300);
                        } else {
                            if(now - state.altCaptured < 250) {
                                state.isShortcutMode = !state.isShortcutMode;
                                if(state.isShortcutMode) {
                                    entityTabGroups = document.getElementById('active-entity-tab-groups');
                                } else {
                                    entityTabGroups = null;
                                }
                            }
                            state.altCaptured = null;
                        }
                        event.preventDefault();
                    } else {
                        state.altCaptured = null;

                        if(!state.isShortcutMode) return;

                        // TODO find proper shortcuts for switching tabs
                        // if(event.ctrlKey && event.altKey && key == 39) {
                        //     switchTab(true);
                        //     event.preventDefault();
                        //     event.stopPropagation();
                        // } else if(event.ctrlKey && event.altKey && key == 37) {
                        //     switchTab(false);
                        //     event.preventDefault();
                        //     event.stopPropagation();
                        // }
                        if(event.ctrlKey && key == 38) {
                            switchEntity(false);
                            event.preventDefault();
                            event.stopPropagation();
                        } else if(event.ctrlKey && key == 40) {
                            switchEntity(true);
                            event.preventDefault();
                            event.stopPropagation();
                        } else if(event.ctrlKey && key == 39) {
                            switchEntityLevel(true);
                            event.preventDefault();
                            event.stopPropagation();
                        } else if(event.ctrlKey && key == 37) {
                            switchEntityLevel(false);
                            event.preventDefault();
                            event.stopPropagation();
                        }
                    }
                };

                // ON MOUNTED
                onMounted(_ => {
                    console.log('mainview component mounted');
                    store.dispatch('setMainViewTab', currentRoute.query.tab);
                    state.treeRoot = document.getElementById('main-entity-tree');
                    window.addEventListener('keydown', handleKeyboardShortcuts);
                });
                onBeforeUnmount(_ => {
                    window.removeEventListener('keydown', handleKeyboardShortcuts);
                });

                onBeforeRouteUpdate(async (to, from) => {
                    if(to.query.tab !== from.query.tab) {
                        store.dispatch('setMainViewTab', to.query.tab);
                    }
                });
                onBeforeRouteLeave((to, from) => {
                    store.dispatch('setMainViewTab', null);
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
