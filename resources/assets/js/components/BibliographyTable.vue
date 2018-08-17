<template>
    <div class="h-100 d-flex flex-column">
        <ul class="list-inline mb-2">
            <li class="list-inline-item">
                <form class="form-inline" id="literature-search-form">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-fw fa-search"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" @input="debouncedSearch" placehoder="Search&ellipsis;">
                        </div>
                    </div>
                </form>
            </li>
            <li class="list-inline-item">
                <button type="button" class="btn btn-success" id="literature-add-button" @click="showNewItemModal" :disabled="!$can('add_remove_literature')">
                    <i class="fas fa-fw fa-plus"></i> {{ $t('main.bibliography.add') }}
                </button>
            </li>
            <li class="list-inline-item">
                <file-upload
                    accept="application/x-bibtex,text/x-bibtex,text/plain"
                    extensions="bib,bibtex"
                    ref="upload"
                    v-model="files"
                    post-action="/api/v1/bibliography/import"
                    :directory="false"
                    :disabled="!$can('add_remove_literature|edit_literature')"
                    :multiple="false"
                    :drop="true"
                    @input-file="inputFile">
                        <span class="btn btn-outline-primary">
                            <i class="fas fa-fw fa-file-import"></i> {{ $t('main.bibliography.import') }}
                        </span>
                </file-upload>
            </li>
            <li class="list-inline-item">
                <button type="button" class="btn btn-outline-primary" @click="exportFile">
                    <i class="fas fa-fw fa-file-export"></i> {{ $t('main.bibliography.export') }}
                </button>
            </li>
            <li class="list-inline-item">
                <div class="clickable" @click="showAllFields = !showAllFields">
                    <span class="align-middle">
                        {{ $t('main.bibliography.show-all-fields') }}
                    </span>
                    <label class="cb-toggle mx-0 my-auto align-middle">
                        <input type="checkbox" id="apply-changes-toggle" v-model="showAllFields" />
                        <span class="slider slider-rounded slider-primary"></span>
                    </label>
                </div>
            </li>
        </ul>
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover">
                <thead class="thead-light sticky-top">
                    <tr>
                        <th>
                            <a href="#" @click="setOrderColumn('type')">
                                {{ $t('global.type') }}
                                <span v-show="orderColumn == 'type'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a href="#" @click="setOrderColumn('citekey')">
                                {{ $t('main.bibliography.column.cite-key') }}
                                <span v-show="orderColumn == 'citekey'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a href="#" @click="setOrderColumn('author')">
                                {{ $t('main.bibliography.column.author') }}
                                <span v-show="orderColumn == 'author'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a href="#" @click="setOrderColumn('year')">
                                {{ $t('main.bibliography.column.year') }}
                                <span v-show="orderColumn == 'year'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a href="#" @click="setOrderColumn('title')">
                                {{ $t('main.bibliography.column.title') }}
                                <span v-show="orderColumn == 'title'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a href="#" @click="setOrderColumn('booktitle')">
                                {{ $t('main.bibliography.column.booktitle') }}
                                <span v-show="orderColumn == 'booktitle'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a href="#" @click="setOrderColumn('publisher')">
                                {{ $t('main.bibliography.column.publisher') }}
                                <span v-show="orderColumn == 'publisher'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            {{ $t('main.bibliography.column.pages') }}
                        </th>
                        <th>
                            <a href="#" @click="setOrderColumn('editor')">
                                {{ $t('main.bibliography.column.editor') }}
                                <span v-show="orderColumn == 'editor'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a href="#" @click="setOrderColumn('journal')">
                                {{ $t('main.bibliography.column.journal') }}
                                <span v-show="orderColumn == 'journal'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th v-if="showAllFields">
                            {{ $t('main.bibliography.column.month') }}
                        </th>
                        <th v-if="showAllFields">
                            {{ $t('main.bibliography.column.volume') }}
                        </th>
                        <th v-if="showAllFields">
                            {{ $t('main.bibliography.column.number') }}
                        </th>
                        <th v-if="showAllFields">
                            {{ $t('main.bibliography.column.chapter') }}
                        </th>
                        <th v-if="showAllFields">
                            {{ $t('main.bibliography.column.edition') }}
                        </th>
                        <th v-if="showAllFields">
                            {{ $t('main.bibliography.column.series') }}
                        </th>
                        <th>
                            <a href="#" @click="setOrderColumn('address')">
                                {{ $t('main.bibliography.column.address') }}
                                <span v-show="orderColumn == 'address'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th v-if="showAllFields">
                            {{ $t('main.bibliography.column.note') }}
                        </th>
                        <th>
                            <a href="#" @click="setOrderColumn('misc')">
                                {{ $t('main.bibliography.column.misc') }}
                                <span v-show="orderColumn == 'misc'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a href="#" @click="setOrderColumn('howpublished')">
                                {{ $t('main.bibliography.column.howpublished') }}
                                <span v-show="orderColumn == 'howpublished'">
                                    <span v-show="orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </span>
                                    <span v-show="orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th v-if="showAllFields">
                            {{ $t('main.bibliography.column.institution') }}
                        </th>
                        <th v-if="showAllFields">
                            {{ $t('main.bibliography.column.organization') }}
                        </th>
                        <th v-if="showAllFields">
                            {{ $t('main.bibliography.column.school') }}
                        </th>
                        <th v-if="showAllFields">
                            {{ $t('global.created-at') }}
                        </th>
                        <th v-if="showAllFields">
                            {{ $t('global.updated-at') }}
                        </th>
                        <th>
                            {{ $t('global.options') }}
                        </th>
                    </tr>
                </thead>
                <tbody v-infinite-scroll="getNextEntries">
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
                            {{ entry.year }}
                        </td>
                        <td>
                            {{ entry.title }}
                        </td>
                        <td>
                            {{ entry.booktitle }}
                        </td>
                        <td>
                            {{ entry.publisher }}
                        </td>
                        <td>
                            {{ entry.pages }}
                        </td>
                        <td>
                            {{ entry.editor }}
                        </td>
                        <td>
                            {{ entry.journal }}
                        </td>
                        <td v-if="showAllFields">
                            {{ entry.month }}
                        </td>
                        <td v-if="showAllFields">
                            {{ entry.volume }}
                        </td>
                        <td v-if="showAllFields">
                            {{ entry.number }}
                        </td>
                        <td v-if="showAllFields">
                            {{ entry.chapter }}
                        </td>
                        <td v-if="showAllFields">
                            {{ entry.edition }}
                        </td>
                        <td v-if="showAllFields">
                            {{ entry.series }}
                        </td>
                        <td>
                            {{ entry.address }}
                        </td>
                        <td v-if="showAllFields">
                            {{ entry.note }}
                        </td>
                        <td>
                            {{ entry.misc }}
                        </td>
                        <td>
                            {{ entry.howpublished }}
                        </td>
                        <td v-if="showAllFields">
                            {{ entry.institution }}
                        </td>
                        <td v-if="showAllFields">
                            {{ entry.organization }}
                        </td>
                        <td v-if="showAllFields">
                            {{ entry.school }}
                        </td>
                        <td v-if="showAllFields">
                            {{ entry.created_at }}
                        </td>
                        <td v-if="showAllFields">
                            {{ entry.updated_at }}
                        </td>
                        <td>
                            <div class="dropdown">
                                <span id="dropdownMenuButton" class="clickable" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-fw fa-ellipsis-h"></i>
                                </span>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="#" @click.prevent="editEntry(entry)" :disabled="!$can('edit_literature')">
                                        <i class="fas fa-fw fa-edit text-info"></i> {{ $t('global.edit') }}
                                    </a>
                                    <a class="dropdown-item" href="#" @click.prevent="requestDeleteEntry(entry)" :disabled="!$can('add_remove_literature')">
                                        <i class="fas fa-fw fa-trash text-danger"></i> {{ $t('global.delete') }}
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <router-view
            v-can.one="'add_remove_literature|edit_literature'"
            :data="newItem"
            :available-types="availableTypes"
            :on-success="addBibliographyItem"
            :on-close="onModalClose">
        </router-view>

        <modal name="delete-bibliography-item-modal" height="auto" :scrollable="true" v-can="'add_remove_literature'">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('main.bibliography.modal.delete.title') }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideDeleteEntryModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        {{ $t('global.delete-name.desc', {name: deleteItem.title}) }}
                    </p>
                    <p class="alert alert-danger">
                        {{
                            $tc('main.bibliography.modal.delete.alert', deleteItem.count, {
                                name: deleteItem.title,
                                cnt: deleteItem.count
                            })
                        }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger" @click="deleteEntry(deleteItem)">
                        <i class="fas fa-fw fa-trash"></i> {{ $t('global.delete') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary" @click="hideDeleteEntryModal">
                        <i class="fas fa-fw fa-ban"></i> {{ $t('global.cancel') }}
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    import infiniteScroll from 'vue-infinite-scroll';
    import debounce from 'debounce';

    export default {
        directives: {
            infiniteScroll
        },
        beforeRouteEnter(to, from, next) {
            $http.get('bibliography').then(response => {
                next(vm => vm.init(response.data));
            });
        },
        created() {
            this.debouncedSearch = debounce(e => {
                this.query = e.target.value;
            }, this.debounceTimeout);
        },
        mounted() {},
        methods: {
            init(entries) {
                this.allEntries = entries;
                const start = this.entriesLoaded;
                const end = Math.min(start + this.chunkSize, this.allEntries.length);
                this.localEntries = this.allEntries.slice(start, end);
                this.entriesLoaded = this.localEntries.length;
            },
            getNextEntries() {
                if(this.entriesLoaded == this.allEntries.length) return;
                const start = this.entriesLoaded;
                const end = Math.min(start + this.chunkSize, this.allEntries.length);
                this.allEntries.slice(start, end).forEach(e => {
                    this.localEntries.push(e);
                });
                this.entriesLoaded = this.localEntries.length;
            },
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
                if(!this.$can('add_remove_literature|edit_literature')) return;
                // Wait for response
                if(newFile && oldFile && newFile.success && !oldFile.success) {
                    this.localEntries.push(newFile.response);
                }
                // Enable automatic upload
                if(Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error) {
                    if(!this.$refs.upload.active) {
                        this.$refs.upload.active = true
                    }
                }
            },
            exportFile() {
                $http.get('bibliography/export').then(response => {
                    const filename = this.$getPreference('prefs.project-name') + '.bibtex';
                    this.$createDownloadLink(response.data, filename, false, response.headers['content-type']);
                });
            },
            onModalClose() {
                this.$router.push({
                    name: 'bibliography'
                });
            },
            addBibliographyItem(item) {
                if(!this.$can('add_remove_literature')) return;
                if(!item.type) return;
                if(!item.fields) return;
                const vm = this;
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

                if(item.id) {
                    vm.$http.patch(`bibliography/${item.id}`, data).then(function(response) {
                        let entry = vm.localEntries.find(e => e.id == item.id);
                        for(let k in item.fields) {
                            entry[k] = item.fields[k];
                        }
                        vm.hideNewItemModal();
                    });
                } else {
                    vm.$http.post('bibliography', data).then(function(response) {
                        vm.localEntries.push(response.data);
                        vm.hideNewItemModal();
                    });
                }
            },
            editEntry(entry) {
                if(!this.$can('edit_literature')) return;
                const type = this.availableTypes.find(t => t.name == entry.type);
                if(!type) return;
                let fields = {};
                for(let k in entry) {
                    if(type.mandatoryFields.includes(k) || type.optionalFields.includes(k)) {
                        fields[k] = entry[k];
                    }
                }
                Vue.set(this.newItem, 'fields', fields);
                Vue.set(this.newItem, 'type', type);
                Vue.set(this.newItem, 'id', entry.id);
                this.$router.push({
                    name: 'bibedit',
                    params: {
                        id: entry.id
                    }
                });
            },
            deleteEntry(entry) {
                const vm = this;
                if(!vm.$can('add_remove_literature')) return;
                vm.$http.delete(`bibliography/${entry.id}`).then(function(response) {
                    const index = vm.localEntries.findIndex(e => e.id == entry.id);
                    if(index > -1) {
                        vm.localEntries.splice(index, 1);
                    }
                    vm.hideDeleteEntryModal();
                });
            },
            requestDeleteEntry(entry) {
                const vm = this;
                if(!vm.$can('add_remove_literature')) return;
                vm.$http.get(`bibliography/${entry.id}/ref_count`).then(function(response) {
                    vm.deleteItem = Object.assign({}, entry);
                    vm.deleteItem.count = response.data;
                    vm.$modal.show('delete-bibliography-item-modal');
                });
            },
            hideDeleteEntryModal() {
                this.deleteItem = {};
                this.$modal.hide('delete-bibliography-item-modal');
            },
            showNewItemModal() {
                if(!this.$can('add_remove_literature')) return;
                this.newItem = {
                    fields: {}
                };
                Vue.set(this.newItem, 'type', this.availableTypes[0]);

                this.$router.push({
                    name: 'bibnew'
                });
            },
            hideNewItemModal() {
                this.$modal.hide('new-bibliography-item-modal');
            }
        },
        data() {
            return {
                allEntries: [],
                entriesLoaded: 0,
                chunkSize: 20,
                localEntries: [],
                orderColumn: 'author',
                orderType: 'asc',
                query: '',
                debouncedSearch: undefined,
                debounceTimeout: 1000,
                files: [],
                showAllFields: false,
                newItem: {
                    fields: {}
                },
                deleteItem: {},
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
            orderedList: function() {
                const query = this.query.toLowerCase();
                let filteredEntries;
                if(!query.length) {
                    filteredEntries = this.localEntries;
                } else {
                    filteredEntries = this.allEntries.filter(function(e) {
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
