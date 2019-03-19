<template>
    <div class="h-100 d-flex flex-column">
        <ul class="list-inline mb-2">
            <li class="list-inline-item">
                <form class="form-inline" id="bibliography-search-form">
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
                <button type="button" class="btn btn-success" id="bibliography-add-button" @click="showNewItemModal" :disabled="!$can('add_remove_bibliography')">
                    <i class="fas fa-fw fa-plus"></i> {{ $t('main.bibliography.add') }}
                </button>
            </li>
            <li class="list-inline-item">
                <file-upload
                    accept="application/x-bibtex,text/x-bibtex,text/plain"
                    extensions="bib,bibtex"
                    ref="upload"
                    v-model="files"
                    :custom-action="importFile"
                    :directory="false"
                    :disabled="!$can('add_remove_bibliography|edit_bibliography')"
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
        <div class="table-responsive col p-0">
            <table class="table table-sm table-striped table-hover">
                <thead class="thead-light sticky-top">
                    <tr>
                        <th>
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('type')">
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
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('citekey')">
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
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('author')">
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
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('year')">
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
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('title')">
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
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('booktitle')">
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
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('publisher')">
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
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('editor')">
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
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('journal')">
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
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('address')">
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
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('misc')">
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
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('howpublished')">
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
                                <div class="dropdown-menu overlay-all" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="#" @click.prevent="editEntry(entry)" :disabled="!$can('edit_bibliography')">
                                        <i class="fas fa-fw fa-edit text-info"></i> {{ $t('global.edit') }}
                                    </a>
                                    <a class="dropdown-item" href="#" @click.prevent="requestDeleteEntry(entry)" :disabled="!$can('add_remove_bibliography')">
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
            v-can.one="'add_remove_bibliography|edit_bibliography'"
            :data="newItem"
            :available-types="availableTypes"
            :on-success="addBibliographyItem"
            :on-close="onModalClose">
        </router-view>

        <modal name="delete-bibliography-item-modal" height="auto" :scrollable="true" v-can="'add_remove_bibliography'">
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
            $httpQueue.add(() => $http.get('bibliography').then(response => {
                next(vm => vm.init(response.data));
            }));
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
            },
            getNextEntries() {
                if(this.entriesLoaded == this.allEntries.length) return;
                this.entriesLoaded = Math.min(this.entriesLoaded + this.chunkSize, this.allEntries.length);
            },
            addEntry(entry) {
                this.allEntries.push(entry);
                if(this.allEntries.length < this.chunkSize) {
                    this.entriesLoaded++;
                }
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
                if(!this.$can('add_remove_bibliography|edit_bibliography')) return;
                // Wait for response
                if(newFile && oldFile && newFile.success && !oldFile.success) {
                    this.allEntries.push(newFile.response);
                }
                // Enable automatic upload
                if(Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error) {
                    if(!this.$refs.upload.active) {
                        this.$refs.upload.active = true
                    }
                }
            },
            importFile(file, component) {
                let formData = new FormData();
                formData.append('file', file.file);
                return $http.post('bibliography/import', formData);
            },
            exportFile() {
                $httpQueue.add(() => $http.get('bibliography/export').then(response => {
                    const filename = this.$getPreference('prefs.project-name') + '.bibtex';
                    this.$createDownloadLink(response.data, filename, false, response.headers['content-type']);
                }));
            },
            onModalClose() {
                this.$router.push({
                    name: 'bibliography'
                });
            },
            addBibliographyItem(item) {
                const emptyPromise = new Promise(r => r(null));
                if(!this.$can('add_remove_bibliography')) return emptyPromise;
                if(!item.type) return emptyPromise;
                if(!item.fields) return emptyPromise;
                let data = {};
                for(let k in item.fields) {
                    data[k] = item.fields[k];
                }
                data.type = item.type.name;

                if(item.id) {
                    return $httpQueue.add(() => $http.patch(`bibliography/${item.id}`, data).then(response => {
                        let entry = this.allEntries.find(e => e.id == item.id);
                        for(let k in item.fields) {
                            Vue.set(entry, k, item.fields[k]);
                        }
                    }));
                } else {
                    return $httpQueue.add(() => $http.post('bibliography', data).then(response => {
                        this.addEntry(response.data);
                    }));
                }
            },
            editEntry(entry) {
                if(!this.$can('edit_bibliography')) return;
                const type = this.availableTypes.find(t => t.name == entry.type);
                if(!type) return;
                let fields = {};
                type.fields.forEach(f => {
                    if(entry[f]) {
                        fields[f] = entry[f];
                    }
                });
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
                if(!this.$can('add_remove_bibliography')) return;
                $httpQueue.add(() => $http.delete(`bibliography/${entry.id}`).then(response => {
                    const index = this.allEntries.findIndex(e => e.id == entry.id);
                    if(index > -1) {
                        this.allEntries.splice(index, 1);
                    }
                    this.hideDeleteEntryModal();
                }));
            },
            requestDeleteEntry(entry) {
                const vm = this;
                if(!vm.$can('add_remove_bibliography')) return;
                $httpQueue.add(() => vm.$http.get(`bibliography/${entry.id}/ref_count`).then(function(response) {
                    vm.deleteItem = Object.assign({}, entry);
                    vm.deleteItem.count = response.data;
                    vm.$modal.show('delete-bibliography-item-modal');
                }));
            },
            hideDeleteEntryModal() {
                this.deleteItem = {};
                this.$modal.hide('delete-bibliography-item-modal');
            },
            showNewItemModal() {
                if(!this.$can('add_remove_bibliography')) return;
                this.newItem = {
                    fields: {}
                };
                Vue.set(this.newItem, 'type', this.availableTypes[0]);

                this.$router.push({
                    name: 'bibnew'
                });
            }
        },
        data() {
            return {
                allEntries: [],
                entriesLoaded: 0,
                chunkSize: 20,
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
                        fields: [
                            'author', 'title', 'journal', 'year', 'volume', 'number', 'pages', 'month', 'note'
                        ]
                    },
                    {
                        name: 'book',
                        id: 1,
                        fields: [
                            'title', 'publisher', 'year', 'author', 'editor', 'volume', 'number', 'address', 'series', 'edition', 'month', 'note'
                        ]
                    },
                    {
                        name: 'incollection',
                        id: 2,
                        fields: [
                            'author', 'title', 'booktitle', 'publisher', 'year', 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note'
                        ]
                    },
                    {
                        name: 'misc',
                        id: 3,
                        fields: ['author', 'title', 'howpublished', 'month', 'year', 'note'
                        ]
                    },
                    {
                        name: 'booklet',
                        id: 4,
                        fields: [
                            'title', 'author', 'howpublished', 'address', 'month', 'year', 'note'
                        ]
                    },
                    {
                        name: 'conference',
                        id: 5,
                        fields: [
                             'author', 'title', 'booktitle', 'year', 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note'
                        ]
                    },
                    {
                        name: 'inbook',
                        id: 6,
                        fields: [
                            'title', 'publisher', 'year', 'author', 'editor', 'chapter', 'pages', 'volume', 'number', 'series', 'address', 'edition', 'month', 'note'
                        ]
                    },
                    {
                        name: 'inproceedings',
                        id: 7,
                        fields: [
                             'author', 'title', 'booktitle', 'year', 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note'
                        ]
                    },
                    {
                        name: 'manual',
                        id: 8,
                        fields: [
                            'title', 'author', 'organization', 'address', 'edition', 'month', 'year', 'note'
                        ]
                    },
                    {
                        name: 'mastersthesis',
                        id: 9,
                        fields: [
                            'author', 'title', 'school', 'year', 'address', 'month', 'note'
                        ]
                    },
                    {
                        name: 'phdthesis',
                        id: 10,
                        fields: [
                            'author', 'title', 'school', 'year', 'address', 'month', 'note'
                        ]
                    },
                    {
                        name: 'proceedings',
                        id: 11,
                        fields: [
                            'title', 'year', 'editor', 'volume', 'number', 'series', 'address', 'month', 'organization', 'publisher', 'note'
                        ]
                    },
                    {
                        name: 'techreport',
                        id: 12,
                        fields: [
                            'author', 'title', 'institution', 'year', 'number', 'address', 'month', 'note'
                        ]
                    },
                    {
                        name: 'unpublished',
                        id: 13,
                        fields: [
                            'author', 'title', 'note', 'month', 'year'
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
                    filteredEntries = this.allEntries;
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
                const size = Math.min(this.entriesLoaded, filteredEntries.length);
                return _.orderBy(filteredEntries, this.orderColumn, this.orderType).slice(0, size);
            }
        }
    }
</script>
