<template>
    <div class="h-100 d-flex flex-column">
        <div class="d-flex flex-row justify-content-between">
            <div class="d-flex flex-row">
                <h3>
                    {{ t('main.bibliography.title') }}
                    <span class="badge bg-secondary fs-50 fw-normal">
                        {{ t('global.list.nr_of_entries', {
                            cnt: state.allEntries.length
                        }, state.allEntries.length) }}
                    </span>
                </h3>
                <form class="ms-3" id="bibliography-search-form">
                    <div class="input-group">
                        <input type="text" class="form-control" @input="debouncedSearch($event)" :placeholder="t('global.search')">
                        <span class="input-group-text">
                            <i class="fas fa-fw fa-search"></i>
                        </span>
                    </div>
                </form>
            </div>
            <div>
                <ul class="list-inline mb-0">
                    <li class="list-inline-item">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="show-all-fields-toggle" v-model="state.showAllFields" />
                            <label class="form-check-label" for="show-all-fields-toggle">
                                {{ t('main.bibliography.show_all_fields') }}
                            </label>
                        </div>
                    </li>
                    <li class="list-inline-item">
                        <button type="button" class="btn btn-outline-success" id="bibliography-add-button" @click="showNewItemModal()" :disabled="!can('bibliography_create')">
                            <i class="fas fa-fw fa-plus"></i> {{ t('main.bibliography.add') }}
                        </button>
                    </li>
                    <li class="list-inline-item">
                        <file-upload
                            class="btn btn-outline-primary clickable"
                            accept="application/x-bibtex,text/x-bibtex,text/plain"
                            extensions="bib,bibtex"
                            ref="upload"
                            v-model="state.files"
                            :custom-action="importFile"
                            :directory="false"
                            :disabled="!can('bibliography_write|bibliography_create')"
                            :multiple="false"
                            :drop="true"
                            @input-file="inputFile">
                                <span>
                                    <i class="fas fa-fw fa-file-import"></i> {{ t('main.bibliography.import') }}
                                </span>
                        </file-upload>
                    </li>
                    <li class="list-inline-item">
                        <button type="button" class="btn btn-outline-primary" @click="exportFile()">
                            <i class="fas fa-fw fa-file-export"></i> {{ t('main.bibliography.export') }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <div class="table-responsive flex-grow-1 mt-2">
            <table class="table table-light table-sm table-striped table-hover">
                <thead class="sticky-top">
                    <tr>
                        <th>
                            #
                        </th>
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
                                {{ t('main.bibliography.column.citekey') }}
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
                    <tr v-for="(entry, i) in state.orderedBibliography" :key="entry.id">
                        <td class="fw-bold">
                            {{ i+1 }}
                        </td>
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
                                    <a class="dropdown-item" href="#" @click.prevent="editItem(entry)" :disabled="!can('bibliography_write')">
                                        <i class="fas fa-fw fa-edit text-info"></i> {{ t('global.edit') }}
                                    </a>
                                    <a class="dropdown-item" href="#" @click.prevent="openDeleteEntryModal(entry)" :disabled="!can('bibliography_delete')">
                                        <i class="fas fa-fw fa-trash text-danger"></i> {{ t('global.delete') }}
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td :colspan="state.maxTableCols">
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="getNextEntries()" :disabled="state.allLoaded">
                                <span v-if="!state.allLoaded">
                                    <i class="fas fa-fw fa-sync"></i>
                                    {{ t('global.list.fetch_next_entries') }}
                                </span>
                                <span v-else>
                                    <i class="fas fa-fw fa-ban"></i>
                                    {{ t('global.list.no_more_entries') }}
                                </span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- <infinite-loading @infinite="getNextEntries">
                <span slot="spinner"></span>
                <span slot="no-more"></span>
                <span slot="no-results"></span>
            </infinite-loading> -->
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        onMounted,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import store from '@/bootstrap/store.js';

    import { useToast } from '@/plugins/toast.js';

    import {
        bibliographyTypes,
    } from '@/helpers/bibliography.js';

    import {
        addOrUpdateBibliographyItem,
        getBibtexFile,
        updateBibliography,
    } from '@/api.js';
    import {
        can,
        createDownloadLink,
        getProjectName,
        _debounce,
        _orderBy,
    } from '@/helpers/helpers.js';
    import {
        showBibliographyEntry,
        showDeleteBibliographyEntry,
    } from '@/helpers/modal.js';

    export default {
        setup(props, context) {
            const { t } = useI18n();
            const toast = useToast();
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
            const getNextEntries = _ => {
                if(state.entriesLoaded === state.allEntries.length) return;

                state.entriesLoaded = Math.min(state.entriesLoaded + state.chunkSize, state.allEntries.length);
            };
            const showNewItemModal = _ => {
                if(!can('bibliography_create')) return;
                
                showBibliographyEntry({
                    fields: {},
                }, addBibliographyItem);
            };
            const addEntry = entry => {
                store.dispatch('addBibliographyItem', entry);

                if(state.allEntries.length < state.chunkSize) {
                    state.entriesLoaded++;
                }
            };
            const updateEntry = entry => {
                store.dispatch('updateBibliographyItem', entry);
            };
            const onEntryDeleted = entry => {
                const label = t('main.bibliography.toast.delete.msg', {name: entry.title});
                const title = t('main.bibliography.toast.delete.title');
                toast.$toast(label, title, {
                    channel: 'success',
                });
            };
            const addEntries = list => {
                for(let i=0; i<list.length; i++) {
                    addEntry(list[i]);
                }
            };
            const addBibliographyItem = item => {
                if(
                    !can('bibliography_create')
                    || !item.type
                    || !item.fields
                ) {
                    return new Promise(r => r(null));
                }

                return addOrUpdateBibliographyItem(item).then(data => {
                    if(item.id) {
                        updateEntry(item);
                    } else {
                        addEntry(data);
                    }
                })
            };
            const inputFile = (newFile, oldFile) => {
                if(!can('bibliography_write|bibliography_create')) return;

                // Enable automatic upload
                if(!!newFile && (Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error)) {
                    if(!newFile.active) {
                        newFile.active = true
                    }
                }
            };
            const importFile = (file, component) => {
                return updateBibliography(file.file).then(data => {
                    addEntries(data);
                });
            };
            const exportFile = _ => {
                if(!can('bibliography_share')) return;

                getBibtexFile().then(data => {
                    const filename = getProjectName(true) + '.bibtex';
                    createDownloadLink(data, filename, false, 'application/x-bibtex');
                });
            };
            const editItem = data => {
                if(!can('bibliography_write')) return;
                const type = bibliographyTypes.find(t => t.name == data.type);
                if(!type) return;
                let fields = {};
                type.fields.forEach(f => {
                    if(data[f]) {
                        fields[f] = data[f];
                    }
                });
                const item = {
                    fields: fields,
                    type: type,
                    id: data.id,
                };
                showBibliographyEntry(item, addBibliographyItem);
            };
            const openDeleteEntryModal = entry => {
                if(!can('bibliography_delete')) return;

                showDeleteBibliographyEntry(entry, onEntryDeleted);
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
                allLoaded: computed(_ => state.allEntries.length === state.entriesLoaded),
                maxTableCols: computed(_ => {
                    if(state.allEntries.length > 0) {
                        return Object.keys(state.allEntries[0]).length;
                    } else {
                        return 0;
                    }
                }),
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
                // HELPERS
                can,
                // LOCAL
                debouncedSearch,
                setOrderColumn,
                getNextEntries,
                showNewItemModal,
                editItem,
                openDeleteEntryModal,
                inputFile,
                importFile,
                exportFile,
                // PROPS
                // STATE
                state,
            };
        },
    }
</script>
