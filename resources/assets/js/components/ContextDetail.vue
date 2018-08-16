<template>
    <div class="h-100 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between">
            <h1>{{ entity.name }}</h1>
            <span>
                <button type="button" class="btn btn-success" :disabled="!isFormDirty || !$can('duplicate_edit_concepts')" @click="saveEntity(entity)">
                    <i class="fas fa-fw fa-save"></i> {{ $t('global.save') }}
                </button>
                <button type="button" class="btn btn-danger" :disabled="!$can('delete_move_concepts')" @click="requestDeleteEntity(entity)">
                    <i class="fas fa-fw fa-trash"></i> {{ $t('global.delete') }}
                </button>
            </span>
        </div>
        <attributes class="pt-2 col pl-0 pr-2 scroll-y-auto scroll-x-hidden" v-if="dataLoaded" v-can="'view_concept_props'"
            :attributes="entity.attributes"
            :dependencies="entity.dependencies"
            :disable-drag="true"
            :on-metadata="showMetadata"
            :metadata-addon="hasReferenceGroup"
            :selections="entity.selections"
            :values="entity.data">
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
            $http.get(`context/${entityId}`).then(response => {
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
                $http.get(`/context/${to.params.id}/data/${from.params.aid}`).then(response => {
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
                    $http.get(`context/${entityId}`).then(response => {
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
            }
        },
        mounted() {},
        methods: {
            init(entity) {
                this.dataLoaded = false;
                this.entity = {};
                this.entity = entity;
                this.getContextData(entity);
            },
            getContextData(entity) {
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
                const ctid = entity.context_type_id;
                vm.$http.get(`/context/${cid}/data`).then(function(response) {
                    // if result is empty, php returns [] instead of {}
                    if(response.data instanceof Array) {
                        response.data = {};
                    }
                    Vue.set(vm.entity, 'data', response.data);
                    return vm.$http.get(`/editor/context_type/${ctid}/attribute`);
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
                    return vm.$http.get(`/context/${cid}/reference`);
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
                const vm = this;
                if(!vm.$can('duplicate_edit_concepts')) return;
                let cid = entity.id;
                var patches = [];
                for(let f in vm.fields) {
                    if(vm.fields.hasOwnProperty(f) && f.startsWith('attribute-')) {
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
                return vm.$http.patch('/context/'+cid+'/attributes', patches).then(function(response) {
                    vm.resetFlags();
                    vm.$showToast('Entity updated', `Data of ${entity.name} successfully updated.`, 'success');
                });
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
                    name: 'contextrefs',
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
