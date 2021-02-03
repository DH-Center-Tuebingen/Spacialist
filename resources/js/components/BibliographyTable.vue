<template>
    <div class="h-100 d-flex flex-column">
        <ul class="list-inline mb-2">
            <li class="list-inline-item">
                <form class="" id="bibliography-search-form">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-fw fa-search"></i>
                        </span>
                        <input type="text" class="form-control" @input="debouncedSearch" placehoder="Search&ellipsis;">
                    </div>
                </form>
            </li>
            <li class="list-inline-item">
                <button type="button" class="btn btn-success" id="bibliography-add-button" @click="showNewItemModal" :disabled="!can('add_remove_bibliography')">
                    <i class="fas fa-fw fa-plus"></i> {{ t('main.bibliography.add') }}
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
                    :disabled="!can('add_remove_bibliography|edit_bibliography')"
                    :multiple="false"
                    :drop="true"
                    @input-file="inputFile">
                        <span class="btn btn-outline-primary">
                            <i class="fas fa-fw fa-file-import"></i> {{ t('main.bibliography.import') }}
                        </span>
                </file-upload>
            </li>
            <li class="list-inline-item">
                <button type="button" class="btn btn-outline-primary" @click="exportFile">
                    <i class="fas fa-fw fa-file-export"></i> {{ t('main.bibliography.export') }}
                </button>
            </li>
            <li class="list-inline-item">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="show-all-fields-toggle" v-model="state.showAllFields" />
                    <label class="form-check-label" for="show-all-fields-toggle">
                        {{ t('main.bibliography.show-all-fields') }}
                    </label>
                </div>
            </li>
        </ul>
        <div class="table-responsive flex-grow-1">
            <table class="table table-light table-sm table-striped table-hover">
                <thead class="sticky-top">
                    <tr>
                        <th>
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('type')">
                                {{ t('global.type') }}
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
                                {{ t('main.bibliography.column.cite-key') }}
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
                                {{ t('main.bibliography.column.author') }}
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
                                {{ t('main.bibliography.column.year') }}
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
                                {{ t('main.bibliography.column.title') }}
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
                                {{ t('main.bibliography.column.booktitle') }}
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
                                {{ t('main.bibliography.column.publisher') }}
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
                            {{ t('main.bibliography.column.pages') }}
                        </th>
                        <th>
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('editor')">
                                {{ t('main.bibliography.column.editor') }}
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
                                {{ t('main.bibliography.column.journal') }}
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
                        <th v-if="state.showAllFields">
                            {{ t('main.bibliography.column.month') }}
                        </th>
                        <th v-if="state.showAllFields">
                            {{ t('main.bibliography.column.volume') }}
                        </th>
                        <th v-if="state.showAllFields">
                            {{ t('main.bibliography.column.number') }}
                        </th>
                        <th v-if="state.showAllFields">
                            {{ t('main.bibliography.column.chapter') }}
                        </th>
                        <th v-if="state.showAllFields">
                            {{ t('main.bibliography.column.edition') }}
                        </th>
                        <th v-if="state.showAllFields">
                            {{ t('main.bibliography.column.series') }}
                        </th>
                        <th>
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('address')">
                                {{ t('main.bibliography.column.address') }}
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
                        <th v-if="state.showAllFields">
                            {{ t('main.bibliography.column.note') }}
                        </th>
                        <th>
                            <a href="#" class="text-nowrap" @click.prevent="setOrderColumn('misc')">
                                {{ t('main.bibliography.column.misc') }}
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
                                {{ t('main.bibliography.column.howpublished') }}
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
                        <th v-if="state.showAllFields">
                            {{ t('main.bibliography.column.institution') }}
                        </th>
                        <th v-if="state.showAllFields">
                            {{ t('main.bibliography.column.organization') }}
                        </th>
                        <th v-if="state.showAllFields">
                            {{ t('main.bibliography.column.school') }}
                        </th>
                        <th v-if="state.showAllFields">
                            {{ t('global.created_at') }}
                        </th>
                        <th v-if="state.showAllFields">
                            {{ t('global.updated_at') }}
                        </th>
                        <th>
                            {{ t('global.options') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="entry in state.orderedBibliography" :key="entry.id">
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
                        <td v-if="state.showAllFields">
                            {{ entry.month }}
                        </td>
                        <td v-if="state.showAllFields">
                            {{ entry.volume }}
                        </td>
                        <td v-if="state.showAllFields">
                            {{ entry.number }}
                        </td>
                        <td v-if="state.showAllFields">
                            {{ entry.chapter }}
                        </td>
                        <td v-if="state.showAllFields">
                            {{ entry.edition }}
                        </td>
                        <td v-if="state.showAllFields">
                            {{ entry.series }}
                        </td>
                        <td>
                            {{ entry.address }}
                        </td>
                        <td v-if="state.showAllFields">
                            {{ entry.note }}
                        </td>
                        <td>
                            {{ entry.misc }}
                        </td>
                        <td>
                            {{ entry.howpublished }}
                        </td>
                        <td v-if="state.showAllFields">
                            {{ entry.institution }}
                        </td>
                        <td v-if="state.showAllFields">
                            {{ entry.organization }}
                        </td>
                        <td v-if="state.showAllFields">
                            {{ entry.school }}
                        </td>
                        <td v-if="state.showAllFields">
                            {{ entry.created_at }}
                        </td>
                        <td v-if="state.showAllFields">
                            {{ entry.updated_at }}
                        </td>
                        <td>
                            <div class="dropdown">
                                <span id="dropdownMenuButton" class="clickable" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-fw fa-ellipsis-h"></i>
                                </span>
                                <div class="dropdown-menu overlay-all" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="#" @click.prevent="editEntry(entry)" :disabled="!can('edit_bibliography')">
                                        <i class="fas fa-fw fa-edit text-info"></i> {{ t('global.edit') }}
                                    </a>
                                    <a class="dropdown-item" href="#" @click.prevent="requestDeleteEntry(entry)" :disabled="!can('add_remove_bibliography')">
                                        <i class="fas fa-fw fa-trash text-danger"></i> {{ t('global.delete') }}
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <infinite-loading @infinite="getNextEntries">
                <span slot="spinner"></span>
                <span slot="no-more"></span>
                <span slot="no-results"></span>
            </infinite-loading>
        </div>

        <!-- <router-view
            v-dcan.one="'add_remove_bibliography|edit_bibliography'"
            :data="newItem"
            :available-types="$options.availableTypes"
            :on-success="addBibliographyItem"
            :on-close="onModalClose">
        </router-view>

        <modal name="delete-bibliography-item-modal" height="auto" :scrollable="true" v-dcan="'add_remove_bibliography'">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ t('main.bibliography.modal.delete.title') }}</h5>
                    <button type="button" class="btn-close" aria-label="Close" @click="hideDeleteEntryModal">
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        {{ t('global.delete-name.desc', {name: deleteItem.title}) }}
                    </p>
                    <p class="alert alert-danger">
                        {{
                            t('main.bibliography.modal.delete.alert', {
                                name: deleteItem.title,
                                cnt: deleteItem.count
                            }, deleteItem.count)
                        }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger" @click="deleteEntry(deleteItem)">
                        <i class="fas fa-fw fa-trash"></i> {{ t('global.delete') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary" @click="hideDeleteEntryModal">
                        <i class="fas fa-fw fa-ban"></i> {{ t('global.cancel') }}
                    </button>
                </div>
            </div>
        </modal> -->
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        onMounted,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import store from '../bootstrap/store.js';

    import { bibliographyTypes } from '../helpers/bibliography.js';
    import {
        can,
        _debounce,
        _orderBy,
    } from '../helpers/helpers.js';

    export default {
        setup(props, context) {
            const {
                t
            } = useI18n();
            // FETCH

            // FUNCTIONS
            const setOrderColumn = column => {
                if(state.orderColumn == column) {
                    if(state.orderType == 'asc') {
                        state.orderType = 'desc';
                    } else {
                        state.orderType = 'asc';
                    }
                } else {
                    state.orderColumn = column;
                    state.orderType = 'asc';
                }
            };

            // DATA
            const state = reactive({
                allEntries: computed(_ => store.getters.bibliography),
                entriesLoaded: 0,
                chunkSize: 20,
                orderColumn: 'author',
                orderType: 'asc',
                query: '',
                debounceTimeout: 1000,
                files: [],
                showAllFields: false,
                newItem: {
                    fields: {}
                },
                deleteItem: {},
                bibliographyTypes: bibliographyTypes,
                orderedBibliography: computed(_ => {
                    const query = state.query.toLowerCase();
                    let filteredEntries = [];
                    if(!query.length) {
                        filteredEntries = state.allEntries;
                    } else {
                        filteredEntries = state.allEntries.filter(e => {
                            for(let k in e) {
                                if(e.hasOwnProperty(k) && e[k]) {
                                    if(k == 'id') continue;
                                    if(k == 'type') continue;
                                    if(k == 'user_id') continue;
                                    if(e[k].toLowerCase().indexOf(query) > -1) {
                                        return true;
                                    }
                                }
                            }
                            return false;
                        });
                    }
                    if(state.entriesLoaded === 0) {
                        state.entriesLoaded = state.chunkSize;
                    }
                    const size = Math.min(state.entriesLoaded, filteredEntries.length);
                    return _orderBy(filteredEntries, state.orderColumn, state.orderType).slice(0, size);
                })
            });

            const debouncedSearch = _debounce(e => {
                state.query = e.target.value;
            }, state.debounceTimeout);

            // RETURN
            return {
                t,
                can,
                debouncedSearch,
                setOrderColumn,
                state,
            };
        },
        // beforeRouteEnter(to, from, next) {
        //     $httpQueue.add(() => $http.get('bibliography').then(response => {
        //         next(vm => vm.init(response.data));
        //     }));
        // },
        // created() {
        //     this.debouncedSearch = _debounce(e => {
        //         this.query = e.target.value;
        //     }, this.debounceTimeout);
        // },
        // mounted() {},
        // methods: {
        //     init(entries) {
        //         this.allEntries = entries;
        //     },
        //     getNextEntries($state) {
        //         if(this.entriesLoaded == this.allEntries.length) {
        //             $state.complete();
        //         } else {
        //             this.entriesLoaded = Math.min(this.entriesLoaded + this.chunkSize, this.allEntries.length);
        //             $state.loaded();
        //         }
        //     },
        //     addEntry(entry) {
        //         this.allEntries.push(entry);
        //         if(this.allEntries.length < this.chunkSize) {
        //             this.entriesLoaded++;
        //         }
        //     },
        //     setOrderColumn(column) {
        //         if(this.orderColumn == column) {
        //             if(this.orderType == 'asc') {
        //                 this.orderType = 'desc';
        //             } else {
        //                 this.orderType = 'asc';
        //             }
        //         } else {
        //             this.orderColumn = column;
        //             this.orderType = 'asc';
        //         }
        //     },
        //     inputFile(newFile, oldFile) {
        //         if(!can('add_remove_bibliography|edit_bibliography')) return;
        //         // Wait for response
        //         if(newFile && oldFile && newFile.success && !oldFile.success) {
        //             this.allEntries.push(newFile.response);
        //         }
        //         // Enable automatic upload
        //         if(Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error) {
        //             if(!this.$refs.upload.active) {
        //                 this.$refs.upload.active = true
        //             }
        //         }
        //     },
        //     importFile(file, component) {
        //         let formData = new FormData();
        //         formData.append('file', file.file);
        //         return $http.post('bibliography/import', formData);
        //     },
        //     exportFile() {
        //         $httpQueue.add(() => $http.get('bibliography/export').then(response => {
        //             const filename = this.$getPreference('prefs.project-name') + '.bibtex';
        //             this.$createDownloadLink(response.data, filename, false, response.headers['content-type']);
        //         }));
        //     },
        //     onModalClose() {
        //         this.$router.push({
        //             name: 'bibliography'
        //         });
        //     },
        //     addBibliographyItem(item) {
        //         const emptyPromise = new Promise(r => r(null));
        //         if(!can('add_remove_bibliography')) return emptyPromise;
        //         if(!item.type) return emptyPromise;
        //         if(!item.fields) return emptyPromise;
        //         let data = {};
        //         for(let k in item.fields) {
        //             data[k] = item.fields[k];
        //         }
        //         data.type = item.type.name;

        //         if(item.id) {
        //             return $httpQueue.add(() => $http.patch(`bibliography/${item.id}`, data).then(response => {
        //                 let entry = this.allEntries.find(e => e.id == item.id);
        //                 for(let k in item.fields) {
        //                     Vue.set(entry, k, item.fields[k]);
        //                 }
        //             }));
        //         } else {
        //             return $httpQueue.add(() => $http.post('bibliography', data).then(response => {
        //                 this.addEntry(response.data);
        //             }));
        //         }
        //     },
        //     editEntry(entry) {
        //         if(!can('edit_bibliography')) return;
        //         const type = this.$options.availableTypes.find(t => t.name == entry.type);
        //         if(!type) return;
        //         let fields = {};
        //         type.fields.forEach(f => {
        //             if(entry[f]) {
        //                 fields[f] = entry[f];
        //             }
        //         });
        //         Vue.set(this.newItem, 'fields', fields);
        //         Vue.set(this.newItem, 'type', type);
        //         Vue.set(this.newItem, 'id', entry.id);
        //         this.$router.push({
        //             name: 'bibedit',
        //             params: {
        //                 id: entry.id
        //             }
        //         });
        //     },
        //     deleteEntry(entry) {
        //         if(!can('add_remove_bibliography')) return;
        //         $httpQueue.add(() => $http.delete(`bibliography/${entry.id}`).then(response => {
        //             const index = this.allEntries.findIndex(e => e.id == entry.id);
        //             if(index > -1) {
        //                 this.allEntries.splice(index, 1);
        //             }
        //             this.hideDeleteEntryModal();
        //         }));
        //     },
        //     requestDeleteEntry(entry) {
        //         const vm = this;
        //         if(!vm.can('add_remove_bibliography')) return;
        //         $httpQueue.add(() => vm.$http.get(`bibliography/${entry.id}/ref_count`).then(function(response) {
        //             vm.deleteItem = Object.assign({}, entry);
        //             vm.deleteItem.count = response.data;
        //             vm.$modal.show('delete-bibliography-item-modal');
        //         }));
        //     },
        //     hideDeleteEntryModal() {
        //         this.deleteItem = {};
        //         this.$modal.hide('delete-bibliography-item-modal');
        //     },
        //     showNewItemModal() {
        //         if(!can('add_remove_bibliography')) return;
        //         this.newItem = {
        //             fields: {}
        //         };
        //         Vue.set(this.newItem, 'type', this.$options.availableTypes[0]);

        //         this.$router.push({
        //             name: 'bibnew'
        //         });
        //     }
        // },
        // data() {
        //     return {
        //         allEntries: [],
        //         entriesLoaded: 0,
        //         chunkSize: 20,
        //         orderColumn: 'author',
        //         orderType: 'asc',
        //         query: '',
        //         debouncedSearch: undefined,
        //         debounceTimeout: 1000,
        //         files: [],
        //         showAllFields: false,
        //         newItem: {
        //             fields: {}
        //         },
        //         deleteItem: {},
        //     }
        // },
    }
</script>
