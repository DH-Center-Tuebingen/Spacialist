<template>
    <modal :name="id" height="auto" :scrollable="true" @closed="hide" :click-to-close="!!data.id">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" v-if="data.id">{{ $t('main.bibliography.modal.edit.title') }}</h5>
                <h5 class="modal-title" v-else>{{ $t('main.bibliography.modal.new.title') }}</h5>
                <button type="button" class="close" aria-label="Close" @click="hide">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form role="form" id="newBibliographyItemForm" name="newBibliographyItemForm" @submit.prevent="success(data)">
                    <div class="form-group">
                        <label class="col-form-label col-md-3" for="type">{{ $t('global.type') }}:</label>
                        <div class="col-md-9">
                            <multiselect
                                v-model="data.type"
                                label="name"
                                track-by="id"
                                :allowEmpty="false"
                                :closeOnSelect="true"
                                :hideSelected="true"
                                :multiple="false"
                                :options="availableTypes"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                            </multiselect>
                        </div>
                    </div>
                    <div class="form-group" v-for="mandatory in mandatoryFields">
                        <label class="col-form-label col-md-3">{{ mandatory }}:<span style="color: red;">*</span></label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" v-model="data.fields[mandatory]" required/>
                        </div>
                    </div>
                    <div class="form-group" v-for="optional in optionalFields">
                        <label class="col-form-label col-md-3">{{ optional }}:</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" v-model="data.fields[optional]"/>
                        </div>
                    </div>
                </form>
                <h4 class="mt-3">{{ $t('main.bibliography.modal.new.bibtex-code') }}</h4>
                <span v-if="data.type" v-html="this.$options.filters.bibtexify(data.fields, data.type.name)"></span>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success" form="newBibliographyItemForm" v-if="data.id">
                    <i class="fas fa-fw fa-save"></i> {{ $t('global.update') }}
                </button>
                <button type="submit" class="btn btn-success" form="newBibliographyItemForm" v-else>
                    <i class="fas fa-fw fa-plus"></i> {{ $t('global.add') }}
                </button>
                <button type="button" class="btn btn-danger" @click="hide">
                    <i class="fas fa-fw fa-ban"></i> {{ $t('global.cancel') }}
                </button>
            </div>
        </div>
    </modal>
</template>

<script>
    export default {
        props: {
            data: {
                required: true,
                type: Object
            },
            availableTypes: {
                required: true,
                type: Array
            },
            onSuccess: {
                required: false,
                type: Function
            },
            onClose: {
                required: false,
                type: Function
            }
        },
        beforeRouteEnter(to, from, next) {
            next(vm => vm.init());
        },
        beforeRouteUpdate(to, from, next) {
            this.init();
            next();
        },
        methods: {
            init() {
                this.$modal.show(this.id);
            },
            success(data) {
                if(this.onSuccess) {
                    this.onSuccess(this.data);
                }
                this.$modal.hide(this.id)
            },
            hide() {
                if(this.onClose) {
                    this.onClose(this.data);
                }
                this.$modal.hide(this.id);
            }
        },
        data() {
            return {
                id: 'new-bibliography-item-modal'
            }
        },
        computed: {
            mandatoryFields: function() {
                if(this.data.type) {
                    return this.data.type.mandatoryFields;
                }
                return this.availableTypes[0].mandatoryFields;
            },
            optionalFields: function() {
                if(this.data.type) {
                    return this.data.type.optionalFields;
                }
                return this.availableTypes[0].optionalFields;
            },
        }
    }
</script>
