<template>
    <div class="row h-100 of-hidden" v-if="initFinished">
        <div :class="'col-md-'+$getPreference('prefs.columns').left" id="tree-container" class="d-flex flex-column h-100" v-can="'view_concepts'">
            <entity-tree
                class="col px-0"
                :selection-callback="onSetSelectedElement"
                :event-bus="eventBus">
            </entity-tree>
        </div>
        <div :class="'col-md-'+$getPreference('prefs.columns').center" style="border-right: 1px solid #ddd; border-left: 1px solid #ddd;" id="attribute-container" class="h-100" v-can="'view_concepts|view_concept_props'">
            <router-view class="h-100"
                :bibliography="bibliography"
                :event-bus="eventBus"
            >
            </router-view>
        </div>
        <div :class="'col-md-'+$getPreference('prefs.columns').right" id="addon-container" class="d-flex flex-column">
            <ul class="nav nav-tabs">
                <li class="nav-item" v-for="plugin in $getTabPlugins()">
                    <router-link class="nav-link" :class="{active: tab == plugin.key}" :to="{ query: { tab: plugin.key }}" append>
                        <i class="fas fa-fw" :class="plugin.icon"></i> {{ $t(plugin.label) }}
                    </router-link>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" :class="{active: tab == 'references', disabled: !selectedEntity.id}" @click.prevent="setReferencesTab()">
                        <i class="fas fa-fw fa-bookmark"></i> {{ $t('main.entity.references.title') }}
                    </a>
                </li>
            </ul>
            <div class="mt-2 col px-0">
                <keep-alive>
                    <component
                        :entity="selectedEntity"
                        :entity-data-loaded="dataLoaded"
                        :is="activePlugin"
                        :params="$route.query"
                        v-on:update:link="updateLink">
                    </component>
                </keep-alive>
                <div v-show="tab == 'references'" class="h-100 scroll-y-auto">
                    <p class="alert alert-info" v-if="!hasReferences">
                        {{ $t('main.entity.references.empty') }}
                    </p>
                    <div v-else v-for="(referenceGroup, key) in references" class="mb-2">
                        <h5 class="mb-1">
                            <a href="#" @click.prevent="showMetadataForReferenceGroup(referenceGroup)">{{ $translateConcept(key) }}</a>
                        </h5>
                        <div class="list-group">
                            <a class="list-group-item list-group-item-action" v-for="reference in referenceGroup">
                                <blockquote class="blockquote mb-0">
                                    <p class="mb-0">
                                        {{ reference.description }}
                                    </p>
                                    <footer class="blockquote-footer">
                                        {{ reference.bibliography.author }} in <cite :title="reference.bibliography.title">
                                            {{ reference.bibliography.title }} ,{{ reference.bibliography.year }}
                                        </cite>
                                    </footer>
                                </blockquote>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import { mapFields } from 'vee-validate';

    export default {
        beforeRouteEnter(to, from, next) {
            let bibliography;
            $http.get('bibliography').then(response => {
                bibliography = response.data;
                next(vm => vm.init(bibliography, to.query.tab, to.params.id));
            });
        },
        beforeRouteUpdate(to, from, next) {
            if(to.query.tab) {
                this.setTabOrPlugin(to.query.tab);
            }
            if(to.params.id) {
                $http.get(`/entity/${to.params.id}/reference`).then(response => {
                    this.references = response.data;
                });
            } else {
                this.references = [];
            }
            next();
        },
        mounted() {},
        methods: {
            init(bibliography, openTab, initialSelectedId) {
                const vm = this;
                this.initFinished = false;
                this.bibliography = bibliography;
                if(initialSelectedId) {
                    $http.get(`/entity/${initialSelectedId}`).then(response => {
                        vm.selectedEntity = response.data;
                    });
                }
                if(openTab) {
                    this.setTabOrPlugin(openTab);
                }
                this.initFinished = true;
            },
            onSetSelectedElement(id) {
                const vm = this;
                this.attributesLoaded = false;
                this.dataLoaded = false;
                if(!id) {
                    this.selectedEntity = {};
                    this.$router.push({
                        name: 'home',
                        query: this.$route.query
                    });
                    this.dataLoaded = true;
                } else {
                    $http.get(`/entity/${id}`).then(response => {
                        vm.selectedEntity = response.data;
                        // if all extensions are disabled, auto-load references on select
                        if(this.tab == '') {
                            this.tab = 'references';
                        }
                        this.$requestHooks(this.selectedEntity);
                        this.dataLoaded = true;
                        this.$router.push({
                            name: 'entitydetail',
                            params: {
                                id: this.selectedEntity.id
                            },
                            query: this.$route.query
                        });
                    });
                }
            },
            setReferencesTab() {
                if(!this.selectedEntity.id) return;
                this.$router.push({
                    append: true,
                    query: {
                        tab: 'references'
                    }
                });
            },
            setTabOrPlugin(key) {
                if(key == 'references') {
                    this.setActiveTab('references');
                } else {
                    const plugins = this.$getTabPlugins();
                    const plugin = plugins.find(p => p.key == key);
                    if(plugin) {
                        this.setActivePlugin(plugin);
                    }
                }
            },
            setActiveTab: function(tab) {
                if(tab == 'references') {
                    if(!this.selectedEntity.id) return;
                    this.activePlugin = '';
                }
                this.tab = tab;
            },
            setActivePlugin: function(plugin) {
                this.setActiveTab(plugin.key);
                this.activePlugin = plugin.tag;
            },
            showMetadataForReferenceGroup(referenceGroup) {
                if(!referenceGroup) return;
                if(!this.selectedEntity) return;
                const aid = referenceGroup[0].attribute_id;
                this.$router.push({
                    name: 'entityrefs',
                    params: {
                        aid: aid
                    },
                    query: this.$route.query,
                    append: true
                });
            },
            updateLink(geoId, entityId) {
                if (entityId != this.selectedEntity.id) {
                    return;
                }
                this.selectedEntity.geodata_id = geoId;
            }
        },
        data() {
            return {
                bibliography: {},
                initFinished: false,
                selectedEntity: {},
                referenceModal: {},
                references: [],
                dataLoaded: false,
                defaultKey: undefined,
                plugins: this.$getTabPlugins(),
                activePlugin: '',
                eventBus: new Vue()
            }
        },
        computed: {
            hasReferences: function() {
                return this.references && Object.keys(this.references).length;
            },
            tab: {
                get() {
                    if(this.defaultKey) return this.defaultKey;
                    else if(this.plugins && this.plugins[0]) {
                        this.activePlugin = this.plugins[0].tag;
                        return this.plugins[0].key;
                    } else {
                        return '';
                    }
                },
                set(newValue) {
                    this.defaultKey = newValue;
                }
            }
        }
    }
</script>
