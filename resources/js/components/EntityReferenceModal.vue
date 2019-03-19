<template>
    <modal name="entity-references-modal" width="50%" height="80%" :scrollable="true" :draggable="true" :resizable="true" @closed="routeBack">
        <div class="modal-content h-100">
            <div class="modal-header">
                <h5 class="modal-title">{{ $t('main.entity.references.title') }}</h5>
                <button type="button" class="close" aria-label="Close" @click="hideModal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body col col-md-8 offset-md-2 scroll-y-auto">
                <h4>{{ $t('main.entity.references.certainty') }}</h4>
                <div class="progress" @click="setCertainty">
                    <div class="progress-bar" role="progressbar" :class="{'bg-danger': refs.value.certainty <= 25, 'bg-warning': refs.value.certainty <= 50, 'bg-info': refs.value.certainty <= 75, 'bg-success': refs.value.certainty > 75}" :aria-valuenow="refs.value.certainty" aria-valuemin="0" aria-valuemax="100" :style="{width: refs.value.certainty+'%'}">
                        <span class="sr-only">
                            {{ refs.value.certainty }}% certainty
                        </span>
                        {{ refs.value.certainty }}%
                    </div>
                </div>
                <form role="form" class="mt-2" @submit.prevent="onUpdateCertainty">
                    <div class="form-group">
                        <textarea class="form-control" v-model="refs.value.certainty_description" :placeholder="$t('main.entity.references.certaintyc')"></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-outline-success">
                            <i class="fas fa-fw fa-save"></i> {{ $t('main.entity.references.certaintyu') }}
                        </button>
                    </div>
                </form>
                <h4 class="mt-3">{{ $t('main.entity.references.bibliography.title') }}</h4>
                <table class="table table-hover">
                    <tbody>
                        <tr class="d-flex flex-row" v-for="reference in refs.refs">
                            <td class="text-left py-2 col px-0 pl-1">
                                <div class="d-flex flex-column">
                                    <h6>{{ reference.bibliography.title }}</h6>
                                    <span class="mb-0">
                                        {{ reference.bibliography.author }}, <span class="text-muted font-weight-light">{{ reference.bibliography.year}}</span>
                                    </span>
                                </div>
                            </td>
                            <td class="text-right p-2 col">
                                <div class="d-flex flex-column">
                                    <div>
                                        <p class="font-weight-light font-italic mb-0" v-if="editReference.id != reference.id">
                                            {{ reference.description }}
                                        </p>
                                        <div class="d-flex" v-else>
                                            <input type="text" class="form-control mr-1" v-model="editReference.description" />
                                            <button type="button" class="btn btn-outline-success mr-1" @click="onUpdateReference(editReference)">
                                                <i class="fas fa-fw fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" @click="cancelEditReference">
                                                <i class="fas fa-fw fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <span class="text-muted font-weight-light">
                                        {{ reference.updated_at | date(undefined, true, true) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-0 pr-1">
                                <div class="dropdown">
                                    <span id="dropdownMenuButton" class="clickable" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-fw fa-ellipsis-h"></i>
                                    </span>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="#" @click="enableEditReference(reference)">
                                            <i class="fas fa-fw fa-edit text-info"></i> {{ $t('global.edit') }}
                                        </a>
                                        <a class="dropdown-item" href="#" @click="onDeleteReference(reference)">
                                            <i class="fas fa-fw fa-trash text-danger"></i> {{ $t('global.delete') }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <h5>{{ $t('main.entity.references.bibliography.add') }}</h5>
                <form role="form" @submit.prevent="onAddReference(newItem)">
                    <div class="row">
                        <div class="col-md-6">
                            <multiselect
                                label="title"
                                track-by="id"
                                v-model="newItem.bibliography"
                                :closeOnSelect="true"
                                :hideSelected="true"
                                :multiple="false"
                                :options="bibliography"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                                <template slot="singleLabel" slot-scope="props">
                                    <span class="option__desc">
                                        <span class="option__title">
                                            {{ props.option.title }}
                                        </span>
                                    </span>
                                </template>
                                <template slot="option" slot-scope="props">
                                    <div class="option__desc">
                                        <span class="option__title d-block">
                                            {{ props.option.title }}
                                        </span>
                                        <span class="option__small">
                                            {{ props.option.author }}, <span class="text-muted font-weight-light">{{ props.option.year}}</span>
                                        </span>
                                    </div>
                                </template>
                            </multiselect>
                        </div>
                        <div class="col-md-6">
                            <textarea class="form-control" v-model="newItem.description" :placeholder="$t('main.entity.references.bibliography.comment')"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-success col-md-12 mt-2" :disabled="addReferenceDisabled">
                        <i class="fas fa-fw fa-plus"></i> {{ $t('main.entity.references.bibliography.add-button') }}
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="hideModal">
                    <i class="fas fa-fw fa-times"></i> {{ $t('global.close') }}
                </button>
            </div>
        </div>
    </modal>
</template>

<script>
    import { EventBus } from '../event-bus.js';

    export default {
        props: {
            bibliography: {
                required: true,
                type: Array
            },
            refs: {
                required: true,
                type: Object
            }
        },
        beforeRouteEnter(to, from, next) {
            next(vm => {
                vm.init();
            });
        },
        beforeRouteUpdate(to, from, next) {
            vm.init();
            next();
        },
        beforeRouteLeave(to, from, next) {
            this.$modal.hide('entity-references-modal');
            next();
        },
        methods: {
            init() {
                if(!this.refs.value.certainty) {
                    Vue.set(this.refs.value, 'certainty', 100);
                }
                this.initialCertainty.value = this.refs.value.certainty;
                this.initialCertainty.description = this.refs.value.certainty_description;
                const curr = this.$route;
                this.entityId = curr.params.id;
                this.attributeId = curr.params.aid;
                this.$modal.show('entity-references-modal');
            },
            setCertainty(event) {
                const maxSize = event.target.parentElement.scrollWidth; // progress bar width in px
                const clickPos = event.layerX; // in px
                const currentValue = this.refs.value.certainty;
                let value = parseInt(clickPos/maxSize*100);
                const diff = Math.abs(value-currentValue);
                if(diff < 10) {
                    if(value > currentValue) {
                        value = parseInt((value+10)/10)*10;
                    } else {
                        value = parseInt(value/10)*10;
                    }
                } else {
                    value = parseInt((value+5)/10)*10;
                }
                Vue.set(this.refs.value, 'certainty', value);
            },
            onUpdateCertainty() {
                if(!this.$can('duplicate_edit_concepts')) return;
                const data = {
                    certainty: this.refs.value.certainty,
                    certainty_description: this.refs.value.certainty_description
                };
                $httpQueue.add(() => $http.patch(`/entity/${this.entityId}/attribute/${this.attributeId}`, data).then(response => {
                    this.initialCertainty.value = this.refs.value.certainty;
                    this.initialCertainty.description = this.refs.value.certainty_description;
                    const attributeName = this.$translateConcept(this.refs.attribute.thesaurus_url);
                    this.$showToast(
                        this.$t('main.entity.references.toasts.updated-certainty.title'),
                        this.$t('main.entity.references.toasts.updated-certainty.msg', {
                            name: attributeName,
                            i: this.refs.value.certainty,
                            desc: this.refs.value.certainty_description
                        }),
                        'success'
                    );
                }));
            },
            onAddReference(item) {
                if(!this.$can('add_remove_bibliography')) return;
                const data = {
                    bibliography_id: item.bibliography.id,
                    description: item.description
                };
                $httpQueue.add(() => $http.post(`/entity/${this.entityId}/reference/${this.attributeId}`, data).then(response => {
                    EventBus.$emit('references-updated', {
                        action: 'add',
                        reference: response.data,
                        group: this.refs.attribute.thesaurus_url
                    });
                    item.bibliography = {};
                    item.description = '';
                }));
            },
            onDeleteReference(reference) {
                if(!this.$can('add_remove_bibliography')) return;
                const id = reference.id;
                $httpQueue.add(() => $http.delete(`/entity/reference/${id}`).then(response => {
                    const index = this.refs.refs.findIndex(r => r.id == reference.id);
                    if(index > -1) {
                        EventBus.$emit('references-updated', {
                            action: 'delete',
                            reference: reference,
                            group: this.refs.attribute.thesaurus_url
                        });
                    }
                }));
            },
            onUpdateReference(editedReference) {
                if(!this.$can('edit_bibliography')) return;
                const id = editedReference.id;
                let ref = this.refs.refs.find(r => r.id == editedReference.id);
                if(ref.description == editedReference.description) {
                    return;
                }
                const data = {
                    description: editedReference.description
                };
                $httpQueue.add(() => $http.patch(`/entity/reference/${id}`, data).then(response => {
                    EventBus.$emit('references-updated', {
                        action: 'edit',
                        reference: response.data,
                        group: this.refs.attribute.thesaurus_url
                    });
                    this.cancelEditReference();
                }));
            },
            enableEditReference(reference) {
                Vue.set(this, 'editReference', Object.assign({}, reference));
            },
            cancelEditReference() {
                Vue.set(this, 'editReference', {});
            },
            hideModal() {
                this.$modal.hide('entity-references-modal');
            },
            routeBack() {
                this.refs.value.certainty = this.initialCertainty.value;
                this.refs.value.certainty_description = this.initialCertainty.description;
                const curr = this.$route;
                this.$router.push({
                    name: 'entitydetail',
                    params: {
                        id: curr.params.id
                    },
                    query: curr.query
                });
            }
        },
        data() {
            return {
                entityId: 0,
                attributeId: 0,
                editReference: {},
                newItem: {
                    bibliography: {},
                    description: ''
                },
                initialCertainty: {
                    value: 100,
                    description: ''
                }
            }
        },
        computed: {
            addReferenceDisabled() {
                return !this.newItem.bibliography.id || this.newItem.description.length == 0;
            }
        }
    }
</script>
