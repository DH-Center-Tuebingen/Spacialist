<template>
    <modal name="entity-references-modal" width="50%" :scrollable="true" :draggable="true" :resizable="true" @before-open="initModal">
        <div class="modal-content h-100">
            <div class="modal-header">
                <h5 class="modal-title">References</h5>
                <button type="button" class="close" aria-label="Close" @click="hideModal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body col col-md-8 offset-md-2 scroll-y-auto" v-if="active">
                <h4>Certainty</h4>
                <div class="progress" @click="setCertainty">
                    <div class="progress-bar" role="progressbar" :class="{'bg-danger': attributeValue.possibility <= 25, 'bg-warning': attributeValue.possibility <= 50, 'bg-info': attributeValue.possibility <= 75, 'bg-success': attributeValue.possibility > 75}" :aria-valuenow="attributeValue.possibility" aria-valuemin="0" aria-valuemax="100" :style="{width: attributeValue.possibility+'%'}">
                        <span class="sr-only">
                            {{ attributeValue.possibility }}% certainty
                        </span>
                        {{ attributeValue.possibility }}%
                    </div>
                </div>
                <form role="form" class="mt-2" @submit.prevent="onUpdateCertainty">
                    <div class="form-group">
                        <textarea class="form-control" v-model="attributeValue.possibility_description" placeholder="Certainty Comment"></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-outline-success">
                            <i class="fas fa-fw fa-save"></i> Update Certainty
                        </button>
                    </div>
                </form>
                <h4 class="mt-3">References</h4>
                <table class="table table-hover">
                    <tbody>
                        <tr class="d-flex flex-row" v-for="reference in references">
                            <td class="text-left py-2 col px-0 pl-1">
                                <h6>{{ reference.bibliography.title }}</h6>
                                <p class="mb-0">
                                    {{ reference.bibliography.author }}, <span class="text-muted font-weight-light">{{ reference.bibliography.year}}</span>
                                </p>
                            </td>
                            <td class="text-right p-2 col">
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
                            </td>
                            <td class="px-0 pr-1">
                                <div class="dropdown">
                                    <span id="dropdownMenuButton" class="clickable" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-fw fa-ellipsis-h"></i>
                                    </span>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="#" @click="enableEditReference(reference)">
                                            <i class="fas fa-fw fa-edit text-info"></i> Edit
                                        </a>
                                        <a class="dropdown-item" href="#" @click="onDeleteReference(reference)">
                                            <i class="fas fa-fw fa-trash text-danger"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <h5>Add new Reference</h5>
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
                                :options="bibliography">
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
                            <textarea class="form-control" v-model="newItem.description" placeholder="Reference Comment"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-success col-md-12 mt-2" :disabled="addReferenceDisabled">
                        <i class="fas fa-fw fa-plus"></i> Add Reference
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="hideModal">
                    <i class="fas fa-fw fa-times"></i> Close
                </button>
            </div>
        </div>
    </modal>
</template>

<script>
    export default {
        methods: {
            initModal(event) {
                for(let p in event.params) {
                    this[p] = event.params[p];
                }
            },
            setCertainty(event) {
                const maxSize = event.target.parentElement.scrollWidth; // progress bar width in px
                const clickPos = event.layerX; // in px
                const currentValue = this.attributeValue.possibility;
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
                Vue.set(this.attributeValue, 'possibility', value);
            },
            onUpdateCertainty() {
                this.updateCertainty(this);
            },
            onAddReference(item) {
                this.addReference(item, this).then(newItem => {
                    this.references.push(newItem);
                })
            },
            onDeleteReference(reference) {
                this.deleteReference(reference, this).then(index => {
                    this.references.splice(index, 1);
                });
            },
            onUpdateReference(editedReference) {
                this.updateReference(editedReference, this).then(_ => {
                    this.cancelEditReference();
                });
            },
            enableEditReference(reference) {
                Vue.set(this, 'editReference', Object.assign({}, reference));
            },
            cancelEditReference() {
                Vue.set(this, 'editReference', {});
            },
            hideModal() {
                this.$modal.hide('entity-references-modal');
            }
        },
        data() {
            return {
                attribute: {},
                attributeValue: {},
                references: [],
                active: false,
                editReference: {},
                newItem: {
                    bibliography: {},
                    description: ''
                },
                updateCertainty: undefined,
                addReference: undefined,
                deleteReference: undefined,
                updateReference: undefined
            }
        },
        computed: {
            addReferenceDisabled: function() {
                return !this.newItem.bibliography.id || this.newItem.description.length == 0;
            },
        }
    }
</script>
