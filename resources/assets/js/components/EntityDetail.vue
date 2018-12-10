<template>
    <div class="h-100 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="mb-0" @mouseenter="onEntityHeaderHover(true)" @mouseleave="onEntityHeaderHover(false)">
                <span v-if="!selectedEntity.editing">
                    {{ selectedEntity.name }}
                    <small>
                        <span v-show="hiddenAttributes > 0" @mouseenter="dependencyInfoHoverOver" @mouseleave="dependencyInfoHoverOut">
                            <i id="dependency-info" class="fas fa-fw fa-xs fa-eye-slash"></i>
                        </span>
                    </small>
                    <a href="#" v-if="entityHeaderHovered" class="text-dark" @click.prevent="enableEntityNameEditing()">
                        <i class="fas fa-fw fa-edit fa-xs"></i>
                    </a>
                </span>
                <form class="form-inline" v-else>
                    <input type="text" class="form-control mr-2" v-model="newEntityName" />
                    <button type="submit" class="btn btn-outline-success mr-2" @click="updateEntityName(selectedEntity, newEntityName)">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                    <button type="reset" class="btn btn-outline-danger" @click="cancelUpdateEntityName()">
                        <i class="fas fa-fw fa-ban"></i>
                    </button>
                </form>
            </h3>
            <span>
                <button type="button" class="btn btn-success" :disabled="!isFormDirty || !$can('duplicate_edit_concepts')" @click="saveEntity(selectedEntity)">
                    <i class="fas fa-fw fa-save"></i> {{ $t('global.save') }}
                </button>
                <button type="button" class="btn btn-danger" :disabled="!$can('delete_move_concepts')" @click="deleteEntity(selectedEntity)">
                    <i class="fas fa-fw fa-trash"></i> {{ $t('global.delete') }}
                </button>
            </span>
        </div>
        <div>
            <i class="fas fa-fw fa-user-edit"></i>
            <span class="font-weight-medium">
                {{ selectedEntity.lasteditor }}
            </span>
            -
            <span>
                {{ (selectedEntity.updated_at || selectedEntity.created_at) | date(undefined, true, true) }}
            </span>
        </div>
        <attributes class="pt-2 col pl-0 pr-2 scroll-y-auto scroll-x-hidden" v-if="dataLoaded" v-can="'view_concept_props'"
            :attributes="selectedEntity.attributes"
            :dependencies="selectedEntity.dependencies"
            :disable-drag="true"
            :on-metadata="showMetadata"
            :metadata-addon="hasReferenceGroup"
            :selections="selectedEntity.selections"
            :values="selectedEntity.data"
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
        beforeRouteUpdate(to, from, next) {
            if(_.isEqual(from.params, to.params)) {
                this.dataLoaded = true;
                next();
            } else {
                const vm = this;
                let loadNext = function() {
                    vm.dataLoaded = false;
                    next();
                }
                if (vm.isFormDirty) {
                    let discardAndContinue = function() {
                        loadNext();
                    };
                    let saveAndContinue = function() {
                        vm.saveEntity(vm.selectedEntity).then(loadNext);
                    };
                    vm.$modal.show(vm.discardModal, {reference: vm.selectedEntity.name, onDiscard: discardAndContinue, onSave: saveAndContinue, onCancel: _ => next(false)})
                } else {
                    loadNext();
                }
            }
        },
        beforeRouteLeave: function(to, from, next) {
            if(_.isEqual(from.params, to.params)) {
                next();
            } else {
                let loadNext = () => {
                    next();
                }
                if(this.isFormDirty) {
                    let discardAndContinue = () => {
                        loadNext();
                    };
                    let saveAndContinue = () => {
                        this.saveEntity(this.selectedEntity).then(loadNext);
                    };
                    this.$modal.show(this.discardModal, {entityName: this.selectedEntity.name, onDiscard: discardAndContinue, onSave: saveAndContinue, onCancel: _ => next(false)})
                } else {
                    loadNext();
                }
            }
        },
        props: {
            selectedEntity: {
                required: true,
                type: Object
            },
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
            init(entity) {},
            getEntityData(entity) {
                if(!this.$can('view_concept_props')) {
                    Vue.set(this.selectedEntity, 'data', {});
                    Vue.set(this.selectedEntity, 'attributes', []);
                    Vue.set(this.selectedEntity, 'selections', {});
                    Vue.set(this.selectedEntity, 'dependencies', []);
                    Vue.set(this.selectedEntity, 'references', []);
                    Vue.set(this, 'dataLoaded', true);
                    return;
                }
                const cid = entity.id;
                const ctid = entity.entity_type_id;
                $httpQueue.add(() => $http.get(`/entity/${cid}/data`).then(response => {
                    // if result is empty, php returns [] instead of {}
                    if(response.data instanceof Array) {
                        response.data = {};
                    }
                    Vue.set(this.selectedEntity, 'data', response.data);
                    return $http.get(`/editor/entity_type/${ctid}/attribute`);
                }).then(response => {
                    this.selectedEntity.attributes = [];
                    let data = response.data;
                    for(let i=0; i<data.attributes.length; i++) {
                        let aid = data.attributes[i].id;
                        if(!this.selectedEntity.data[aid]) {
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
                            Vue.set(this.selectedEntity.data, aid, val);
                        } else {
                            const val = this.selectedEntity.data[aid].value;
                            switch(data.attributes[i].datatype) {
                                case 'date':
                                    const dtVal = new Date(val);
                                    this.selectedEntity.data[aid].value = dtVal;
                                    break;
                            }
                        }
                        this.selectedEntity.attributes.push(data.attributes[i]);
                    }
                    // if result is empty, php returns [] instead of {}
                    if(data.selections instanceof Array) {
                        data.selections = {};
                    }
                    if(data.dependencies instanceof Array) {
                        data.dependencies = {};
                    }
                    Vue.set(this.selectedEntity, 'selections', data.selections);
                    Vue.set(this.selectedEntity, 'dependencies', data.dependencies);

                    const aid = this.$route.params.aid;
                    if(aid) {
                        this.setModalValues(aid);
                    }

                    Vue.set(this, 'dataLoaded', true);
                }));
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
                                    patch.value = this.getCleanValue(data);
                                } else {
                                    // value is empty, therefore it is a remove
                                    patch.op = "remove";
                                }
                            } else {
                                // there has been no entry in the database before, therefore it is an add operation
                                if(data.value && data.value != '') {
                                    patch.op = "add";
                                    patch.value = this.getCleanValue(data);
                                }
                            }
                            patches.push(patch);
                        }
                    }
                }
                return $httpQueue.add(() => $http.patch('/entity/'+cid+'/attributes', patches).then(response => {
                    this.resetFlags();
                    this.$showToast(
                        this.$t('main.entity.toasts.updated.title'),
                        this.$t('main.entity.toasts.updated.msg', {
                            name: entity.name
                        }),
                        'success'
                    );
                    this.setModificationFields(response.data);
                }));
            },
            deleteEntity(entity) {
                this.eventBus.$emit('entity-delete', {
                    entity: entity
                });
            },
            onEntityHeaderHover(hoverState) {
                this.entityHeaderHovered = hoverState;
            },
            enableEntityNameEditing() {
                this.newEntityName = this.selectedEntity.name;
                Vue.set(this.selectedEntity, 'editing', true);
            },
            updateEntityName(entity, name) {
                // If name does not change, just cancel
                if(entity.name == name) {
                    this.cancelUpdateEntityName();
                } else {
                    const data = {
                        name: name
                    };
                    $httpQueue.add(() => $http.patch(`entity/${entity.id}/name`, data).then(response => {
                        this.eventBus.$emit('entity-update', {
                            type: 'name',
                            entity_id: entity.id,
                            value: name
                        });
                        entity.name = name;
                        this.cancelUpdateEntityName();
                    }));
                }
            },
            cancelUpdateEntityName() {
                Vue.set(this.selectedEntity, 'editing', false);
                this.newEntityName = '';
            },
            getCleanValue(entry) {
                if(!entry) return;
                const v = entry.value;
                switch(entry.attribute.datatype) {
                    case 'string-sc':
                        return {
                            id: v.id,
                            concept_url: v.concept_url
                        };
                    case 'string-mc':
                        return v.map(smc => {
                            return {
                                id: smc.id,
                                concept_url: smc.concept_url
                            };
                        });
                    case 'table':
                        return v.map(row => {
                            for(let k in row) {
                                const col = row[k];
                                // if column is object, return necessary fields only
                                if(col.id) {
                                    row[k] = {
                                        id: col.id,
                                        concept_url: col.concept_url
                                    };
                                } else {
                                    row[k] = col;
                                }
                            }
                            return row;
                        });
                    default:
                        return entry.value;
                }
            },
            setModificationFields(entity) {
                if(!this.selectedEntity && !this.selectedEntity.id) return;

                this.selectedEntity.lasteditor = entity.lasteditor;
                this.selectedEntity.updated_at = entity.updated_at;
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
                const attribute = this.selectedEntity.attributes.find(a => a.id == aid);
                this.ref.refs = this.hasReferenceGroup(attribute.thesaurus_url) ? this.selectedEntity.references[attribute.thesaurus_url] : [];
                this.ref.value = this.selectedEntity.data[aid];
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
                if(!this.selectedEntity.references) return false;
                if(!Object.keys(this.selectedEntity.references).length) return false;
                if(!this.selectedEntity.references[group]) return false;
                let count = Object.keys(this.selectedEntity.references[group]).length > 0;
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
                newEntityName: '',
                entityHeaderHovered: false,
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
        },
        watch: {
            'selectedEntity.id': function(newId, oldId) {
                this.getEntityData(this.selectedEntity);
            }
        }
    }
</script>
