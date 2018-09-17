<template>
    <div class="h-100 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between">
            <h1 class="mb-0">
                {{ entity.name }}
                <small>
                    <span v-show="hiddenAttributes > 0" @mouseenter="dependencyInfoHoverOver" @mouseleave="dependencyInfoHoverOut">
                        <i id="dependency-info" class="fas fa-fw fa-xs fa-eye-slash"></i>
                    </span>
                </small>
            </h1>
            <span>
                <button type="button" class="btn btn-success" :disabled="!isFormDirty || !$can('duplicate_edit_concepts')" @click="saveEntity(entity)">
                    <i class="fas fa-fw fa-save"></i> {{ $t('global.save') }}
                </button>
                <button type="button" class="btn btn-danger" :disabled="!$can('delete_move_concepts')" @click="deleteEntity(entity)">
                    <i class="fas fa-fw fa-trash"></i> {{ $t('global.delete') }}
                </button>
            </span>
        </div>
        <div>
            <i class="fas fa-fw fa-user-edit"></i>
            <span class="font-weight-bold">
                {{ entity.lasteditor }}
            </span>
            -
            {{ entity.updated_at || entity.created_at }}
        </div>
        <attributes class="pt-2 col pl-0 pr-2 scroll-y-auto scroll-x-hidden" v-if="dataLoaded" v-can="'view_concept_props'"
            :attributes="entity.attributes"
            :dependencies="entity.dependencies"
            :disable-drag="true"
            :on-metadata="showMetadata"
            :metadata-addon="hasReferenceGroup"
            :selections="entity.selections"
            :values="entity.data"
            v-on:attr-dep-change="updateDependencyCounter">
        </attributes>

        <router-view
            v-can="'view_concept_props'"
            :bibliography="bibliography"
            :refs="ref">
        </router-view>
        <discard-changes-modal :name="discardModal"/>
    </div>
</template>

<script>
    export default {
        beforeRouteEnter(to, from, next) {
            const entityId = to.params.id;
            $http.get(`entity/${entityId}`).then(response => {
                next(vm => {
                    vm.init(response.data)
                    vm.eventBus.$emit('entity-change', {
                        type: 'enter',
                        from: from,
                        to: to
                    });
                });
            });
        },
        beforeRouteUpdate(to, from, next) {
            if(to.params.id == from.params.id && to.name == from.name) {
                next();
            } else if(to.params.aid) { // do not reload data if reference modal is opened
                this.setModalValues(to.params.aid);
                next();
            } else if(!to.params.aid && from.params.aid) {
                $http.get(`/entity/${to.params.id}/data/${from.params.aid}`).then(response => {
                    // if result is empty, php returns [] instead of {}
                    if(response.data instanceof Array) {
                        response.data = {};
                    }
                    Vue.set(this.entity.data, from.params.aid, response.data[from.params.aid]);
                    next();
                });
            } else {
                const vm = this;
                const entityId = to.params.id;
                let loadNext = function() {
                    $http.get(`entity/${entityId}`).then(response => {
                        vm.init(response.data);
                        next();
                    });
                    vm.eventBus.$emit('entity-change', {
                        type: 'update',
                        from: from,
                        to: to
                    });
                };
                if (vm.isFormDirty) {
                    let discardAndContinue = function() {
                        loadNext();
                    };
                    let saveAndContinue = function() {
                        vm.saveEntity(vm.entity).then(loadNext);
                    };
                    vm.$modal.show(vm.discardModal, {entityName: vm.entity.name, onDiscard: discardAndContinue, onSave: saveAndContinue, onCancel: _ => next(false)})
                } else {
                    loadNext();
                }
            }
        },
        beforeRouteLeave: function(to, from, next) {
            const vm = this;
            let loadNext = function() {
                next();
                vm.eventBus.$emit('entity-change', {
                    type: 'leave',
                    from: from,
                    to: to
                })
            }
            if (vm.isFormDirty) {
                let discardAndContinue = function() {
                    loadNext();
                };
                let saveAndContinue = function() {
                    vm.saveEntity(this.entity).then(loadNext);
                };
                vm.$modal.show(vm.discardModal, {entityName: vm.entity.name, onDiscard: discardAndContinue, onSave: saveAndContinue, onCancel: _ => next(false)})
            } else {
                loadNext();
            }
        },
        props: {
            bibliography: {
                required: false,
                type: Array,
                default: () => []
            },
            eventBus: {
                required: true,
                type: Object
            },
            onDelete: {
                required: false,
                type: Function,
                default: () => {}
            }
        },
        mounted() {},
        methods: {
            init(entity) {
                this.dataLoaded = false;
                this.entity = {};
                this.entity = entity;
                this.getEntityData(entity);
            },
            getEntityData(entity) {
                const vm = this;
                if(!vm.$can('view_concept_props')) {
                    Vue.set(vm.entity, 'data', {});
                    Vue.set(vm.entity, 'attributes', []);
                    Vue.set(vm.entity, 'selections', {});
                    Vue.set(vm.entity, 'dependencies', []);
                    Vue.set(vm.entity, 'references', []);
                    Vue.set(vm, 'dataLoaded', true);
                    return;
                }
                const cid = entity.id;
                const ctid = entity.entity_type_id;
                vm.$http.get(`/entity/${cid}/data`).then(function(response) {
                    // if result is empty, php returns [] instead of {}
                    if(response.data instanceof Array) {
                        response.data = {};
                    }
                    Vue.set(vm.entity, 'data', response.data);
                    return vm.$http.get(`/editor/entity_type/${ctid}/attribute`);
                }).then(function(response) {
                    vm.entity.attributes = [];
                    let data = response.data;
                    for(let i=0; i<data.attributes.length; i++) {
                        let aid = data.attributes[i].id;
                        if(!vm.entity.data[aid]) {
                            let val = {};
                            switch(data.attributes[i].datatype) {
                                case 'dimension':
                                case 'epoch':
                                    val.value = {};
                                    break;
                                case 'table':
                                case 'list':
                                    val.value = [];
                                    break;
                            }
                            Vue.set(vm.entity.data, aid, val);
                        } else {
                            const val = vm.entity.data[aid].value;
                            switch(data.attributes[i].datatype) {
                                case 'date':
                                    const dtVal = new Date(val);
                                    vm.entity.data[aid].value = dtVal;
                                    break;
                            }
                        }
                        vm.entity.attributes.push(data.attributes[i]);
                    }
                    // if result is empty, php returns [] instead of {}
                    if(data.selections instanceof Array) {
                        data.selections = {};
                    }
                    if(data.dependencies instanceof Array) {
                        data.dependencies = {};
                    }
                    Vue.set(vm.entity, 'selections', data.selections);
                    Vue.set(vm.entity, 'dependencies', data.dependencies);
                    return vm.$http.get(`/entity/${cid}/reference`);
                }).then(function(response) {
                    let data = response.data;
                    if(data instanceof Array) {
                        data = {};
                    }
                    Vue.set(vm.entity, 'references', data);

                    const aid = vm.$route.params.aid;
                    if(aid) {
                        setModalValues(aid)
                    }

                    Vue.set(vm, 'dataLoaded', true);
                });
            },
            saveEntity(entity) {
                if(!this.$can('duplicate_edit_concepts')) return;
                let cid = entity.id;
                var patches = [];
                for(let f in this.fields) {
                    if(this.fields.hasOwnProperty(f) && f.startsWith('attribute-')) {
                        if(this.fields[f].dirty) {
                            let aid = Number(f.replace(/^attribute-/, ''));
                            let data = entity.data[aid];
                            var patch = {};
                            patch.params = {};
                            patch.params.aid = aid;
                            patch.params.cid = cid;
                            if(data.id) {
                                // if data.id exists, there has been an entry in the database, therefore it is a replace/remove operation
                                patch.params.id = data.id;
                                if(data.value && data.value != '') {
                                    // value is set, therefore it is a replace
                                    patch.op = "replace";
                                    patch.value = data.value;
                                } else {
                                    // value is empty, therefore it is a remove
                                    patch.op = "remove";
                                }
                            } else {
                                // there has been no entry in the database before, therefore it is an add operation
                                if(data.value && data.value != '') {
                                    patch.op = "add";
                                    patch.value = data.value;
                                }
                            }
                            patches.push(patch);
                        }
                    }
                }
                return $http.patch('/entity/'+cid+'/attributes', patches).then(response => {
                    this.resetFlags();
                    this.$showToast(
                        this.$t('main.entity.toasts.updated.title'),
                        this.$t('main.entity.toasts.updated.msg', {
                            name: entity.name
                        }),
                        'success'
                    );
                });
            },
            deleteEntity(entity) {
                // this.onDelete(this.afterDelete, entity, entity.path);
            },
            afterDelete(entity) {
                this.eventBus.$emit('entity-delete', {
                    entity: entity
                });
            },
            updateDependencyCounter(event) {
                this.hiddenAttributes = event.counter;
            },
            dependencyInfoHoverOver(event) {
                if(this.dependencyInfoHovered) {
                    return;
                }
                this.dependencyInfoHovered = true;
                $('#dependency-info').popover({
                    placement: 'bottom',
                    animation: true,
                    html: false,
                    content: this.$tc('main.entity.attributes.hidden', this.hiddenAttributes, {
                        cnt: this.hiddenAttributes
                    })
                });
                $('#dependency-info').popover('show');
            },
            dependencyInfoHoverOut(event) {
                this.dependencyInfoHovered = false;
                $('#dependency-info').popover('dispose');
            },
            setModalValues(aid) {
                const attribute = this.entity.attributes.find(a => a.id == aid);
                this.ref.refs = this.entity.references[attribute.thesaurus_url];
                this.ref.value = this.entity.data[aid];
                this.ref.attribute = attribute;
            },
            showMetadata(attribute) {
                this.$router.push({
                    append: true,
                    name: 'entityrefs',
                    params: {
                        aid: attribute.id
                    },
                    query: this.$route.query
                });
            },
            hasReferenceGroup: function(group) {
                if(!this.entity.references) return false;
                if(!Object.keys(this.entity.references).length) return false;
                if(!this.entity.references[group]) return false;
                let count = Object.keys(this.entity.references[group]).length > 0;
                return count > 0;
            },
            resetFlags() {
                this.$validator.fields.items.forEach(field => {
                    field.reset();
                });
            },
        },
        data() {
            return {
                entity: {},
                dataLoaded: false,
                dependencyInfoHovered: false,
                hiddenAttributes: 0,
                ref: {
                    refs: {},
                    value: {},
                    attribute: {}
                },
                discardModal: 'discard-changes-modal'
            }
        },
        computed: {
            isFormDirty: function() {
                return Object.keys(this.fields).some(key => this.fields[key].dirty);
            }
        }
    }
</script>
