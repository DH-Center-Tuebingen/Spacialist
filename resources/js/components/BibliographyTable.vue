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
                <form
                    id="bibliography-search-form"
                    class="ms-3"
                    @submit.prevent
                >
                    <div class="input-group">
                        <input
                            type="text"
                            class="form-control"
                            :placeholder="t('global.search')"
                            @input="debouncedSearch($event)"
                        >
                        <span class="input-group-text">
                            <i class="fas fa-fw fa-search" />
                        </span>
                    </div>
                </form>
            </div>
            <div>
                <ul class="list-inline mb-0">
                    <li class="list-inline-item">
                        <div class="form-check form-switch">
                            <input
                                id="show-all-fields-toggle"
                                v-model="state.showAllFields"
                                type="checkbox"
                                class="form-check-input"
                            >
                            <label
                                class="form-check-label"
                                for="show-all-fields-toggle"
                            >
                                {{ t('main.bibliography.show_all_fields') }}
                            </label>
                        </div>
                    </li>
                    <li class="list-inline-item">
                        <button
                            id="bibliography-add-button"
                            type="button"
                            class="btn btn-sm btn-outline-success"
                            :disabled="!can('bibliography_create')"
                            @click="showNewItemModal()"
                        >
                            <i class="fas fa-fw fa-plus" /> {{ t('main.bibliography.add') }}
                        </button>
                    </li>
                    <li class="list-inline-item">
                        <file-upload
                            ref="upload"
                            v-model="state.files"
                            class="btn btn-sm btn-outline-primary clickable"
                            accept=".bib,.bibtex,application/x-bibtex,text/x-bibtex,text/plain"
                            extensions="bib,bibtex"
                            :custom-action="importFile"
                            :directory="false"
                            :disabled="!can('bibliography_write|bibliography_create')"
                            :multiple="false"
                            :drop="true"
                            @input-file="inputFile"
                        >
                            <span>
                                <i class="fas fa-fw fa-file-import" /> {{ t('main.bibliography.import') }}
                            </span>
                        </file-upload>
                    </li>
                    <li class="list-inline-item">
                        <div
                            class="btn-group"
                            role="group"
                        >
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary"
                                @click="exportFile()"
                            >
                                <i class="fas fa-fw fa-file-export" /> {{ t('main.bibliography.export') }}
                            </button>
                            <div
                                class="btn-group"
                                role="group"
                            >
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary dropdown-toggle"
                                    data-bs-toggle="dropdown"
                                />
                                <ul class="dropdown-menu">
                                    <li>
                                        <a
                                            class="dropdown-item"
                                            href="#"
                                            @click.prevent="exportFile('search')"
                                        >
                                            <i class="fas fa-fw fa-file-export" /> {{ t('main.bibliography.export_search') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
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
                            <a
                                href="#"
                                class="text-nowrap"
                                @click.prevent="setOrderColumn('entry_type')"
                            >
                                {{ t('global.type') }}
                                <span v-show="state.orderColumn == 'entry_type'">
                                    <span v-show="state.orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down" />
                                    </span>
                                    <span v-show="state.orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up" />
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a
                                href="#"
                                class="text-nowrap"
                                @click.prevent="setOrderColumn('citekey')"
                            >
                                {{ t('main.bibliography.column.citekey') }}
                                <span v-show="state.orderColumn == 'citekey'">
                                    <span v-show="state.orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down" />
                                    </span>
                                    <span v-show="state.orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up" />
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a
                                href="#"
                                class="text-nowrap"
                                @click.prevent="setOrderColumn('author')"
                            >
                                {{ t('main.bibliography.column.author') }}
                                <span v-show="state.orderColumn == 'author'">
                                    <span v-show="state.orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down" />
                                    </span>
                                    <span v-show="state.orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up" />
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th v-if="state.showAllFields">
                            {{ t('main.bibliography.column.email') }}
                        </th>
                        <th>
                            <a
                                href="#"
                                class="text-nowrap"
                                @click.prevent="setOrderColumn('year')"
                            >
                                {{ t('main.bibliography.column.year') }}
                                <span v-show="state.orderColumn == 'year'">
                                    <span v-show="state.orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down" />
                                    </span>
                                    <span v-show="state.orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up" />
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a
                                href="#"
                                class="text-nowrap"
                                @click.prevent="setOrderColumn('title')"
                            >
                                {{ t('main.bibliography.column.title') }}
                                <span v-show="state.orderColumn == 'title'">
                                    <span v-show="state.orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down" />
                                    </span>
                                    <span v-show="state.orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up" />
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a
                                href="#"
                                class="text-nowrap"
                                @click.prevent="setOrderColumn('booktitle')"
                            >
                                {{ t('main.bibliography.column.booktitle') }}
                                <span v-show="state.orderColumn == 'booktitle'">
                                    <span v-show="state.orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down" />
                                    </span>
                                    <span v-show="state.orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up" />
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a
                                href="#"
                                class="text-nowrap"
                                @click.prevent="setOrderColumn('publisher')"
                            >
                                {{ t('main.bibliography.column.publisher') }}
                                <span v-show="state.orderColumn == 'publisher'">
                                    <span v-show="state.orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down" />
                                    </span>
                                    <span v-show="state.orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up" />
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            {{ t('main.bibliography.column.pages') }}
                        </th>
                        <th>
                            <a
                                href="#"
                                class="text-nowrap"
                                @click.prevent="setOrderColumn('editor')"
                            >
                                {{ t('main.bibliography.column.editor') }}
                                <span v-show="state.orderColumn == 'editor'">
                                    <span v-show="state.orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down" />
                                    </span>
                                    <span v-show="state.orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up" />
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a
                                href="#"
                                class="text-nowrap"
                                @click.prevent="setOrderColumn('journal')"
                            >
                                {{ t('main.bibliography.column.journal') }}
                                <span v-show="state.orderColumn == 'journal'">
                                    <span v-show="state.orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down" />
                                    </span>
                                    <span v-show="state.orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up" />
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
                            <a
                                href="#"
                                class="text-nowrap"
                                @click.prevent="setOrderColumn('address')"
                            >
                                {{ t('main.bibliography.column.address') }}
                                <span v-show="state.orderColumn == 'address'">
                                    <span v-show="state.orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down" />
                                    </span>
                                    <span v-show="state.orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up" />
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th v-if="state.showAllFields">
                            {{ t('main.bibliography.column.note') }}
                        </th>
                        <th v-if="state.showAllFields">
                            {{ t('main.bibliography.column.doi') }}
                        </th>
                        <th v-if="state.showAllFields">
                            {{ t('main.bibliography.column.url') }}
                        </th>
                        <th v-if="state.showAllFields">
                            {{ t('main.bibliography.column.subtype') }}
                        </th>
                        <th>
                            <a
                                href="#"
                                class="text-nowrap"
                                @click.prevent="setOrderColumn('misc')"
                            >
                                {{ t('main.bibliography.column.misc') }}
                                <span v-show="state.orderColumn == 'misc'">
                                    <span v-show="state.orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down" />
                                    </span>
                                    <span v-show="state.orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up" />
                                    </span>
                                </span>
                            </a>
                        </th>
                        <th>
                            <a
                                href="#"
                                class="text-nowrap"
                                @click.prevent="setOrderColumn('howpublished')"
                            >
                                {{ t('main.bibliography.column.howpublished') }}
                                <span v-show="state.orderColumn == 'howpublished'">
                                    <span v-show="state.orderType == 'asc'">
                                        <i class="fas fa-fw fa-sort-down" />
                                    </span>
                                    <span v-show="state.orderType == 'desc'">
                                        <i class="fas fa-fw fa-sort-up" />
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
                            {{ t('global.file') }}
                        </th>
                        <th>
                            {{ t('global.options') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- eslint-disable vue/no-v-html -->
                    <tr
                        v-for="(entry, i) in state.orderedBibliography"
                        :key="entry.id"
                    >
                        <td class="fw-bold">
                            {{ i+1 }}
                        </td>
                        <td>
                            {{ entry.entry_type }}
                        </td>
                        <td v-html="formatBibtexAndShowHighlight(entry.citekey)" />
                        <td v-html="formatBibtexAndShowHighlight(entry.author)" />
                        <td
                            v-if="state.showAllFields"
                            v-html="formatBibtexAndShowHighlight(entry.email)"
                        />
                        <td v-html="formatBibtexAndShowHighlight(entry.year)" />
                        <td v-html="formatBibtexAndShowHighlight(entry.title)" />
                        <td v-html="formatBibtexAndShowHighlight(entry.booktitle)" />
                        <td v-html="formatBibtexAndShowHighlight(entry.publisher)" />
                        <td>
                            {{ entry.pages }}
                        </td>
                        <td v-html="formatBibtexAndShowHighlight(entry.editor)" />
                        <td v-html="formatBibtexAndShowHighlight(entry.journal)" />
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
                        <td
                            v-if="state.showAllFields"
                            v-html="formatBibtexAndShowHighlight(entry.doi)"
                        />
                        <td
                            v-if="state.showAllFields"
                            v-html="formatBibtexAndShowHighlight(entry.url)"
                        />
                        <td v-if="state.showAllFields">
                            {{ entry.type }}
                        </td>
                        <td>
                            {{ entry.misc }}
                        </td>
                        <td v-html="createAnchorFromUrl(entry.howpublished)" />
                        <td
                            v-if="state.showAllFields"
                            v-html="formatBibtexAndShowHighlight(entry.institution)"
                        />
                        <td
                            v-if="state.showAllFields"
                            v-html="formatBibtexAndShowHighlight(entry.organization)"
                        />
                        <td
                            v-if="state.showAllFields"
                            v-html="formatBibtexAndShowHighlight(entry.school)"
                        />
                        <td v-if="state.showAllFields">
                            {{ entry.created_at }}
                        </td>
                        <td v-if="state.showAllFields">
                            {{ entry.updated_at }}
                        </td>
                        <td>
                            <span
                                v-show="!entry.file"
                                class="text-muted"
                                :title="t('global.no_file')"
                            >
                                <i class="fas fa-fw fa-minus" />
                            </span>
                            <span v-show="entry.file">
                                <a
                                    :href="`download/bibliography?path=${entry.file}`"
                                    target="_blank"
                                >
                                    <i class="fas fa-fw fa-search" />
                                </a>
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <span
                                    id="dropdownMenuButton"
                                    class="clickable"
                                    data-bs-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                >
                                    <i class="fas fa-fw fa-ellipsis-vertical" />
                                </span>
                                <div
                                    class="dropdown-menu overlay-all"
                                    aria-labelledby="dropdownMenuButton"
                                >
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                        :disabled="!can('bibliography_write')"
                                        @click.prevent="editItem(entry)"
                                    >
                                        <i class="fas fa-fw fa-edit text-info" /> {{ t('global.edit') }}
                                    </a>
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                        :disabled="!can('bibliography_delete')"
                                        @click.prevent="openDeleteEntryModal(entry)"
                                    >
                                        <i class="fas fa-fw fa-trash text-danger" /> {{ t('global.delete') }}
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <!-- eslint-enable vue/no-v-html -->
                    <tr>
                        <td :colspan="state.maxTableCols">
                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-sm"
                                :disabled="state.allLoaded"
                                @click="getNextEntries()"
                            >
                                <span v-if="!state.allLoaded">
                                    <i class="fas fa-fw fa-sync" />
                                    {{ t('global.list.fetch_next_entries') }}
                                </span>
                                <span v-else>
                                    <i class="fas fa-fw fa-ban" />
                                    {{ t('global.list.no_more_entries') }}
                                </span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import useBibliographyStore from '@/bootstrap/stores/bibliography.js';
    import useSystemStore from '@/bootstrap/stores/system.js';

    import { useToast } from '@/plugins/toast.js';

    import {
        bibliographyTypes,
        formatBibtexText,
    } from '@/helpers/bibliography.js';

    import {
        exportBibtexFile,
    } from '@/api.js';
    import {
        can,
        createDownloadLink,
        createAnchorFromUrl,
        throwError,
        _debounce,
        _orderBy,
    } from '@/helpers/helpers.js';
    import {
        highlight,
    } from '@/helpers/filters.js';
    import {
        showBibliographyEntry,
        showDeleteBibliographyEntry,
    } from '@/helpers/modal.js';

    export default {
        setup(props, context) {
            const { t } = useI18n();
            const toast = useToast();
            const bibliographyStore = useBibliographyStore();
            const systemStore = useSystemStore();
            // FETCH

            const chunkSize = 20;

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

                state.entriesLoaded = Math.min(state.entriesLoaded + chunkSize, state.allEntries.length);
            };
            const showNewItemModal = _ => {
                if(!can('bibliography_create')) return;

                showBibliographyEntry({fields: {}}, _ => {
                    if(state.allEntries.length < chunkSize) {
                        state.entriesLoaded++;
                    }
                });
            };
            const onEntryDeleted = entry => {
                const label = t('main.bibliography.toast.delete.msg', {name: entry.title});
                const title = t('main.bibliography.toast.delete.title');
                toast.$toast(label, title, {
                    channel: 'success',
                });
            };
            const inputFile = (newFile, oldFile) => {
                if(!can('bibliography_write|bibliography_create')) return;

                // Enable automatic upload
                if(!!newFile && (Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error)) {
                    if(!newFile.active) {
                        newFile.active = true;
                    }
                }
            };
            const importFile = (file, component) => {
                return bibliographyStore.import(file.file).then(data => {
                    const label = t('main.bibliography.toast.import.msg', {cnt: data.added, cnt_upd: data.updated});
                    const title = t('main.bibliography.toast.import.title');
                    toast.$toast(label, title, {
                        channel: 'success',
                        duration: 10000,
                    });
                }).catch(error => {
                    throwError(error);
                });
            };
            const exportFile = type => {
                if(!can('bibliography_share')) return;

                let selection = null;
                if(type == 'search' && state.query.length > 0) {
                    selection = state.filteredEntries.map(e => e.id);
                }

                exportBibtexFile(selection).then(data => {
                    const projectName = systemStore.getProjectName(true);
                    const filename = projectName + '.bibtex';
                    createDownloadLink(data, filename, false, 'application/x-bibtex');
                });
            };
            const editItem = data => {
                if(!can('bibliography_write')) return;
                const type = bibliographyTypes.find(t => t.name == data.entry_type);
                if(!type) return;
                let fields = {};
                type.fields.forEach(f => {
                    if(data[f]) {
                        fields[f] = data[f];
                    }
                });
                const item = {
                    fields: fields,
                    entry_type: type,
                    id: data.id,
                    file: data.file,
                    file_url: data.file_url,
                };
                showBibliographyEntry(item);
            };
            const openDeleteEntryModal = entry => {
                if(!can('bibliography_delete')) return;
                showDeleteBibliographyEntry(entry, onEntryDeleted);
            };

            // DATA
            const state = reactive({
                allEntries: computed(_ => bibliographyStore.bibliography),
                entriesLoaded: Math.min(chunkSize, bibliographyStore.bibliography.length),
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
                filteredEntries: computed(_ => {
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
                    return filteredEntries;
                }),
                orderedBibliography: computed(_ => {
                    const size = Math.min(state.entriesLoaded, state.filteredEntries.length);
                    return _orderBy(state.filteredEntries, state.orderColumn, state.orderType).slice(0, size);
                })
            });

            const debouncedSearch = _debounce(e => {
                state.query = e.target.value;
            }, state.debounceTimeout);

            const formatBibtexAndShowHighlight = text => {
                if(!text) return '';
                text = formatBibtexText(text);
                return highlight(text, state.query);
            };

            // RETURN
            return {
                t,
                // HELPERS
                can,
                createAnchorFromUrl,
                // LOCAL
                debouncedSearch,
                editItem,
                exportFile,
                formatBibtexAndShowHighlight,
                getNextEntries,
                importFile,
                inputFile,
                openDeleteEntryModal,
                setOrderColumn,
                showNewItemModal,
                // PROPS
                // STATE
                state,
            };
        },
    };
</script>
