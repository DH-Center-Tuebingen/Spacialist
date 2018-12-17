<template>
    <modal :name="id" height="80%" @closed="hide" :click-to-close="!!data.id">
        <div class="modal-content" @paste="handlePasteFromClipboard">
            <div class="modal-header">
                <h5 class="modal-title" v-if="data.id">{{ $t('main.bibliography.modal.edit.title') }}</h5>
                <h5 class="modal-title" v-else>{{ $t('main.bibliography.modal.new.title') }}</h5>
                <button type="button" class="close" aria-label="Close" @click="hide">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body col d-flex flex-column">
                <p class="alert alert-info">
                    <i class="fas fa-lightbulb mr-1"></i> <span class="font-weight-medium">Note</span>
                    <br />
                    You can use <kbd><kbd>ctrl</kbd> + <kbd>v</kbd></kbd> to fill out the fields with a BibTeX entry from your clipboard.
                </p>
                <form role="form" id="newBibliographyItemForm" class="col px-0 scroll-y-auto" name="newBibliographyItemForm" @submit.prevent="success(data)">
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
                    <div class="form-group" v-for="field in typeFields">
                        <label class="col-form-label col-md-3">
                            {{ $t(`main.bibliography.column.${field}`) }}:
                        </label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" :class="$getValidClass(error, field)" v-model="data.fields[field]"/>

                            <div class="invalid-feedback">
                                <span v-for="msg in error[field]">
                                    {{ msg }}
                                </span>
                            </div>
                        </div>
                    </div>
                </form>
                <h5 class="mt-3">
                    {{ $t('main.bibliography.modal.new.bibtex-code') }}
                    <small class="clickable" @click="showBibtexCode = !showBibtexCode">
                        <span v-show="showBibtexCode">
                            <i class="fas fa-fw fa-caret-up"></i>
                        </span>
                        <span v-show="!showBibtexCode">
                            <i class="fas fa-fw fa-caret-down"></i>
                        </span>
                    </small>
                </h5>
                <span v-if="data.type" v-show="showBibtexCode" v-html="this.$options.filters.bibtexify(data.fields, data.type.name)"></span>
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
    const bibtexParse = require('bibtex-parse');

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
            handlePasteFromClipboard(e) {
                let items = e.clipboardData.items;
                for(let i=0; i<items.length; i++) {
                    let c = items[i];
                    if(c.kind == 'string' && c.type == 'text/plain') {
                        c.getAsString(s => {
                            this.fromBibtexEntry(s);
                        });
                    }
                }
            },
            fromBibtexEntry(str) {
                try {
                    const content = bibtexParse.parse(str);
                    // only parse if str contains exactly one entry
                    if(!content || content.entries.length !== 1) return;
                    const entry = content.entries[0];
                    const type = this.availableTypes.find(t => t.name == entry.type);
                    Vue.set(this.data, 'type', type);
                    this.data.fields.citekey = entry.id;
                    for(let k in entry.properties) {
                        const p = entry.properties[k];
                        this.data.fields[k] = p.value;
                    }
                } catch(err) {
                }
            },
            success(data) {
                if(this.onSuccess) {
                    this.onSuccess(this.data).then(response => {
                        this.$modal.hide(this.id)
                    }).catch(e => {
                        this.$getErrorMessages(e, this.error);
                    });
                } else {
                    this.$modal.hide(this.id);
                }
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
                id: 'new-bibliography-item-modal',
                showBibtexCode: true,
                error: {}
            }
        },
        computed: {
            typeFields: function() {
                if(this.data.type) {
                    return this.data.type.fields;
                }
                return this.availableTypes[0].fields;
            }
        }
    }
</script>
