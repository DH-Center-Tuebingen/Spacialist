<template>
    <div>
        <ul class="list-inline">
            <li class="list-inline-item">
                <form class="form-inline" id="literature-search-form">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-fw fa-search"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" v-model="query" placehoder="Search&ellipsis;">
                        </div>
                    </div>
                </form>
            </li>
            <li class="list-inline-item">
                <button type="button" class="btn btn-success" id="literature-add-button" @click="showNewItemModal">
                    <i class="fas fa-fw fa-plus"></i> New Bibliography Item
                </button>
            </li>
            <li class="list-inline-item">
                <file-upload
                ref="upload"
                v-model="files"
                post-action="/api/bibliography/import"
                :multiple="false"
                :directory="false"
                :drop="true"
                @input-file="inputFile">
                    <span class="btn btn-outline-primary">
                        <i class="fas fa-fw fa-download"></i> Import BibTex File
                    </span>
                </file-upload>
            </li>
            <li class="list-inline-item">
                <a type="button" class="btn btn-outline-primary" href="/api/bibliography/export">
                    <i class="fas fa-fw fa-upload"></i> Export BibTex File
                </a>
            </li>
        </ul>
        <div id="literature-container" class="table-responsive">
            <table class="table table-striped table-hover" id="literature-table">
                <thead class="thead-light">
                    <tr>
                        <td>
                            <a href="#" v-on:click="setOrderColumn('type')">
                                Type
                                <span v-show="orderColumn == 'type'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </td>
                        <td>
                            <a href="#" v-on:click="setOrderColumn('citekey')">
                                Citation Key
                                <span v-show="orderColumn == 'citekey'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </td>
                        <td>
                            <a href="#" v-on:click="setOrderColumn('author')">
                                Author
                                <span v-show="orderColumn == 'author'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </td>
                        <td>
                            <a href="#" v-on:click="setOrderColumn('editor')">
                                Editor
                                <span v-show="orderColumn == 'editor'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </td>
                        <td>
                            <a href="#" v-on:click="setOrderColumn('title')">
                                Title
                                <span v-show="orderColumn == 'title'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </td>
                        <td>
                            <a href="#" v-on:click="setOrderColumn('journal')">
                                Journal
                                <span v-show="orderColumn == 'journal'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </td>
                        <td>
                            <a href="#" v-on:click="setOrderColumn('year')">
                                Year
                                <span v-show="orderColumn == 'year'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </td>
                        <td>
                            literature.bibtex.month
                        </td>
                        <td>
                            literature.bibtex.pages
                        </td>
                        <td>
                            literature.bibtex.volume
                        </td>
                        <td>
                            literature.bibtex.number
                        </td>
                        <td>
                            literature.bibtex.chapter
                        </td>
                        <td>
                            literature.bibtex.edition
                        </td>
                        <td>
                            literature.bibtex.series
                        </td>
                        <td>
                            <a href="#" v-on:click="setOrderColumn('booktitle')">
                                Book Title
                                <span v-show="orderColumn == 'booktitle'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </td>
                        <td>
                            <a href="#" v-on:click="setOrderColumn('publisher')">
                                Publisher
                                <span v-show="orderColumn == 'publisher'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </td>
                        <td>
                            <a href="#" v-on:click="setOrderColumn('address')">
                                Address
                                <span v-show="orderColumn == 'address'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </td>
                        <td>
                            literature.bibtex.note
                        </td>
                        <td>
                            literature.created-at
                        </td>
                        <td>
                            literature.updated-at
                        </td>
                        <td>
                            <a href="#" v-on:click="setOrderColumn('misc')">
                                misc
                                <span v-show="orderColumn == 'misc'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </td>
                        <td>
                            <a href="#" v-on:click="setOrderColumn('howpublished')">
                                howpublished
                                <span v-show="orderColumn == 'howpublished'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </td>
                        <td>
                            literature.bibtex.institution
                        </td>
                        <td>
                            literature.bibtex.organization
                        </td>
                        <td>
                            literature.bibtex.school
                        </td>
                        <td>
                            literature.delete
                        </td>
                        <td>
                            literature.edit
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="entry in orderedList">
                        <td>
                            {{ entry.type }}
                        </td>
                        <td>
                            {{ entry.citekey }}
                        </td>
                        <td>
                            {{ entry.author }}
                        </td>
                        <td>
                            {{ entry.editor }}
                        </td>
                        <td>
                            {{ entry.title }}
                        </td>
                        <td>
                            {{ entry.journal }}
                        </td>
                        <td>
                            {{ entry.year }}
                        </td>
                        <td>
                            {{ entry.month }}
                        </td>
                        <td>
                            {{ entry.pages }}
                        </td>
                        <td>
                            {{ entry.volume }}
                        </td>
                        <td>
                            {{ entry.number }}
                        </td>
                        <td>
                            {{ entry.chapter }}
                        </td>
                        <td>
                            {{ entry.edition }}
                        </td>
                        <td>
                            {{ entry.series }}
                        </td>
                        <td>
                            {{ entry.booktitle }}
                        </td>
                        <td>
                            {{ entry.publisher }}
                        </td>
                        <td>
                            {{ entry.address }}
                        </td>
                        <td>
                            {{ entry.note }}
                        </td>
                        <td>
                            {{ entry.created_at }}
                        </td>
                        <td>
                            {{ entry.updated_at }}
                        </td>
                        <td>
                            {{ entry.misc }}
                        </td>
                        <td>
                            {{ entry.howpublished }}
                        </td>
                        <td>
                            {{ entry.institution }}
                        </td>
                        <td>
                            {{ entry.organization }}
                        </td>
                        <td>
                            {{ entry.school }}
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-danger">
                                <i class="fas fa-fw fa-trash"></i>
                            </button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-info">
                                <i class="fas fa-fw fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <modal name="new-bibliography-item-modal" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add new Item</h5>
                    <button type="button" class="close" aria-label="Close" v-on:click="hideNewItemModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form role="form" @submit.prevent="addBibliographyItem(newItem)">
                        <div class="form-group">
                            <label class="col-form-label col-md-3" for="type">Type:</label>
                            <div class="col-md-9">
                                <multiselect
                                    v-model="newItem.type"
                                    label="name"
                                    :options="availableTypes"
                                    :multiple="false"
                                    :hideSelected="true"
                                    :allowEmpty="false"
                                    :closeOnSelect="true">
                                </multiselect>
                            </div>
                        </div>
                        <div class="form-group" v-for="mandatory in mandatoryFields">
                            <label class="col-form-label col-md-3">{{ mandatory }}:<span style="color: red;">*</span></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" v-model="newItem.fields[mandatory]" required/>
                            </div>
                        </div>
                        <div class="form-group" v-for="optional in optionalFields">
                            <label class="col-form-label col-md-3">{{ optional }}:</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" v-model="newItem.fields[optional]"/>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-fw fa-plus"></i> Add
                        </button>
                    </form>
                    <h4 class="mt-3">BibTeX-Code</h4>
                    <span v-if="newItem.type" v-html="this.$options.filters.bibtexify(newItem.fields, newItem.type.name)"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="hideNewItemModal">
                        <i class="fas fa-fw fa-ban"></i> Cancel
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    export default {
        props: {
            entries: {
                type: Array,
                required: true
            }
        },
        mounted() {},
        methods: {
            setOrderColumn(column) {
                if(this.orderColumn == column) {
                    if(this.orderType == 'asc') {
                        this.orderType = 'desc';
                    } else {
                        this.orderType = 'asc';
                    }
                } else {
                    this.orderColumn = column;
                    this.orderType = 'asc';
                }
            },
            inputFile(newFile, oldFile) {
                // Wait for response
                if(newFile && oldFile && newFile.success && !oldFile.success) {
                    console.log(newFile.response);
                    this.localEntries.push(newFile.response);
                }
                // Enable automatic upload
                if(Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error) {
                    if(!this.$refs.upload.active) {
                        this.$refs.upload.active = true
                    }
                }
            },
            addBibliographyItem(item) {
                if(!item.type) return;
                if(!item.fields) return;
                let data = {};
                // check if all mandatory fields are set
                for(let i=0; i<item.type.mandatoryFields.length; i++) {
                    let k = item.type.mandatoryFields[i];
                    if(item.fields[k] == null || item.fields[k] == '') {
                        return;
                    }
                }
                for(let k in item.fields) {
                    data[k] = item.fields[k];
                }
                data.type = item.type.name;

                let entries = this.localEntries;
                let hideNewItemModal = this.hideNewItemModal;
                this.$http.post('/api/bibliography', data).then(function(response) {
                    entries.push(response.data);
                    hideNewItemModal();
                });
            },
            showNewItemModal() {
                Vue.set(this.newItem, 'type', this.availableTypes[0]);
                this.$modal.show('new-bibliography-item-modal');
            },
            hideNewItemModal() {
                this.$modal.hide('new-bibliography-item-modal');
            }
        },
        data() {
            return {
                localEntries: this.entries.slice(),
                orderColumn: 'author',
                orderType: 'asc',
                query: '',
                files: [],
                newItem: {
                    fields: {}
                },
                availableTypes: [
                    {
                        name: 'article',
                        id: 0,
                        mandatoryFields: [
                            'author', 'title', 'journal', 'year'
                        ],
                        optionalFields: [
                            'volume', 'number', 'pages', 'month', 'note'
                        ]
                    },
                    {
                        name: 'book',
                        id: 1,
                        mandatoryFields: [
                            'title', 'publisher', 'year'
                        ],
                        optionalFields: [
                            'author', 'editor', 'volume', 'number', 'address', 'series', 'edition', 'month', 'note'
                        ]
                    },
                    {
                        name: 'incollection',
                        id: 2,
                        mandatoryFields: [
                            'author', 'title', 'booktitle', 'publisher', 'year'
                        ],
                        optionalFields: [
                            'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note'
                        ]
                    },
                    {
                        name: 'misc',
                        id: 3,
                        mandatoryFields: [
                        ],
                        optionalFields: [
                            'author', 'title', 'howpublished', 'month', 'year', 'note'
                        ]
                    },
                    {
                        name: 'booklet',
                        id: 4,
                        mandatoryFields: [
                            'title'
                        ],
                        optionalFields: [
                            'author', 'howpublished', 'address', 'month', 'year', 'note'
                        ]
                    },
                    {
                        name: 'conference',
                        id: 5,
                        mandatoryFields: [
                             'author', 'title', 'booktitle', 'year'
                        ],
                        optionalFields: [
                            'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note'
                        ]
                    },
                    {
                        name: 'inbook',
                        id: 6,
                        mandatoryFields: [
                            'title', 'publisher', 'year'
                        ],
                        optionalFields: [
                            'author', 'editor', 'chapter', 'pages', 'volume', 'number', 'series', 'address', 'edition', 'month', 'note'
                        ]
                    },
                    {
                        name: 'inproceedings',
                        id: 7,
                        mandatoryFields: [
                             'author', 'title', 'booktitle', 'year'
                        ],
                        optionalFields: [
                            'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note'
                        ]
                    },
                    {
                        name: 'manual',
                        id: 8,
                        mandatoryFields: [
                            'title'
                        ],
                        optionalFields: [
                            'author', 'organization', 'address', 'edition', 'month', 'year', 'note'
                        ]
                    },
                    {
                        name: 'mastersthesis',
                        id: 9,
                        mandatoryFields: [
                            'author', 'title', 'school', 'year'
                        ],
                        optionalFields: [
                            'address', 'month', 'note'
                        ]
                    },
                    {
                        name: 'phdthesis',
                        id: 10,
                        mandatoryFields: [
                            'author', 'title', 'school', 'year'
                        ],
                        optionalFields: [
                            'address', 'month', 'note'
                        ]
                    },
                    {
                        name: 'proceedings',
                        id: 11,
                        mandatoryFields: [
                            'title', 'year'
                        ],
                        optionalFields: [
                            'editor', 'volume', 'number', 'series', 'address', 'month', 'organization', 'publisher', 'note'
                        ]
                    },
                    {
                        name: 'techreport',
                        id: 12,
                        mandatoryFields: [
                            'author', 'title', 'institution', 'year'
                        ],
                        optionalFields: [
                            'number', 'address', 'month', 'note'
                        ]
                    },
                    {
                        name: 'unpublished',
                        id: 13,
                        mandatoryFields: [
                            'author', 'title', 'note'
                        ],
                        optionalFields: [
                            'month', 'year'
                        ]
                    }
                ]
            }
        },
        computed: {
            mandatoryFields: function() {
                if(this.newItem.type) {
                    return this.newItem.type.mandatoryFields;
                }
                return this.availableTypes[0].mandatoryFields;
            },
            optionalFields: function() {
                if(this.newItem.type) {
                    return this.newItem.type.optionalFields;
                }
                return this.availableTypes[0].optionalFields;
            },
            orderedList: function() {
                let query = this.query.toLowerCase();
                let filteredEntries;
                if(!query.length) {
                    filteredEntries = this.localEntries;
                } else {
                    filteredEntries = this.localEntries.filter(function(e) {
                        for(let k in e) {
                            if(e.hasOwnProperty(k) && e[k]) {
                                if(k == 'id') continue;
                                if(k == 'type') continue;
                                if(e[k].toLowerCase().indexOf(query) > -1) {
                                    return true;
                                }
                            }
                        }
                        return false;
                    });
                }
                return _.orderBy(filteredEntries, this.orderColumn, this.orderType);
            }
        }
    }
</script>
