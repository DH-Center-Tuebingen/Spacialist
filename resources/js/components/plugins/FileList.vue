<template>
    <div class="h-100 d-flex flex-column">
        <div class="d-flex justify-content-start mb-2">
            <span v-if="fileState.total > 0">
                {{
                    $t('plugins.files.list.display', {
                        from: fileState.from,
                        to: fileState.to,
                        total: fileState.total
                    })
                }}
            </span>
            <span v-else>
                {{
                    $t('plugins.files.list.none', {
                        from: fileState.from,
                        to: fileState.to,
                        total: fileState.total
                    })
                }}
            </span>
            <a href="" class="mr-auto ml-2" @click.prevent="grid = !grid">
                <span v-show="grid">
                    <i class="fas fa-fw fa-list"></i>
                </span>
                <span v-show="!grid">
                    <i class="fas fa-fw fa-th"></i>
                </span>
            </a>
            <div class="dropdown" v-if="fileCount">
                <span id="selected-files-actions" class="clickable" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-fw fa-ellipsis-h"></i>
                </span>
                <div class="dropdown-menu" aria-labelledby="selected-files-actions">
                    <a class="dropdown-item" href="#" v-if="$can('export_files')" @click.prevent="exportFiles(selectedFiles)">
                        <i class="fas fa-fw fa-download text-info"></i>
                        <span v-html="$t('plugins.files.header.export.selected', {cnt: fileCount})"></span>
                    </a>
                </div>
            </div>
        </div>
        <div infinite-scroll-disabled="isFetching" v-infinite-scroll="onLoadChunk" class="row col scroll-y-auto">
            <div class="row col align-content-start" v-if="grid">
                <div class="col-md-12 col-lg-6 col-xl-4 mb-3 clickable" v-for="file in files" :title="file.name" @click="onClick(file)" @contextmenu.prevent="$refs.fileMenu.open($event, {file: file})">
                    <div class="card text-center">
                        <div style="height: 200px;">
                            <div class="card-hover-overlay h-100">
                                <div class="text-white">
                                    <i class="fas fa-fw fa-binoculars fa-5x"></i>
                                </div>
                            </div>
                            <div class="card-hover h-100">
                                <img class="card-img w-100 h-100 object-fit-cover" v-if="file.category == 'image'" :src="file.thumb_url">
                                <div class="card-img w-100" v-else></div>
                                <div class="card-img-overlay d-flex flex-column justify-content-end" style="height: 200px;">
                                    <div class="card-text pb-4">
                                        <div v-if="file.category == 'xml'">
                                            <i class="fas fa-fw fa-file-code fa-5x"></i>
                                        </div>
                                        <div v-else-if="file.category == 'html'">
                                            <i
                                            class="fab fa-fw fa-html5 fa-5x"
                                            data-fa-transform="shrink-9 down-2"
                                            data-fa-mask="fas fa-fw fa-file"></i>
                                        </div>
                                        <div v-else-if="file.category == 'archive'">
                                            <i class="fas fa-fw fa-file-archive fa-5x"></i>
                                        </div>
                                        <div v-else-if="file.category == 'pdf'">
                                            <i class="fas fa-fw fa-file-pdf fa-5x"></i>
                                        </div>
                                        <div v-else-if="file.category == 'audio'">
                                            <i class="fas fa-fw fa-file-audio fa-5x"></i>
                                        </div>
                                        <div v-else-if="file.category == 'video'">
                                            <i class="fas fa-fw fa-file-video fa-5x"></i>
                                        </div>
                                        <div v-else-if="file.category == 'spreadsheet'">
                                            <i class="fas fa-fw fa-file-excel fa-5x"></i>
                                        </div>
                                        <div v-else-if="file.category == 'document'">
                                            <i class="fas fa-fw fa-file-word fa-5x"></i>
                                        </div>
                                        <div v-else-if="file.category == 'presentation'">
                                            <i class="fas fa-fw fa-file-powerpoint fa-5x"></i>
                                        </div>
                                        <div v-else-if="file.category == '3d'">
                                            <i
                                            class="fas fa-fw fa-cubes fa-5x"
                                            data-fa-transform="shrink-9 down-2"
                                            data-fa-mask="fas fa-fw fa-file"></i>
                                        </div>
                                        <div v-else-if="file.category == 'dicom'">
                                            <i class="fas fa-fw fa-file-medical-alt fa-5x"></i>
                                        </div>
                                        <div v-else-if="file.category == 'text'">
                                            <i class="fas fa-fw fa-file-alt fa-5x"></i>
                                        </div>
                                        <div v-else-if="file.category == 'undefined'">
                                            <i
                                            class="fas fa-fw fa-question fa-5x"
                                            data-fa-transform="shrink-9 down-2"
                                            data-fa-mask="fas fa-fw fa-file"></i>
                                        </div>
                                    </div>
                                    <h6 class="card-title text-truncate" :class="{shadowed: file.category == 'image'}">
                                        {{ file.name }}
                                    </h6>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="position-absolute top-0 right-0 m-2">
                                <span v-if="showLinks && getFileLinks(file).length" :title="$tc('global.has-links', getFileLinks(file).length, {cnt: getFileLinks(file).length})">
                                    <i class="fas fa-fw fa-link text-info"></i>
                                </span>
                                <span v-if="isFromSubEntity(file)" :title="$tc('global.from-subentity')">
                                    <i class="fas fa-fw fa-sitemap text-info"></i>
                                </span>
                                <span v-if="getFileTags(file).length" :title="$tc('global.has-tags', getFileTags(file).length, {cnt: getFileTags(file).length})">
                                    <i class="fas fa-fw fa-tags text-info"></i>
                                </span>
                            </div>
                            <div class="position-absolute top-0 left-0 m-2" v-if="$can('export_files')">
                                <input type="checkbox" @click.stop="" :id="file.id" :value="file.id" v-model="selectedFiles" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-4 mb-3" v-if="fileState.toLoad">
                    <div class="card text-center">
                        <div class="card-hover">
                            <div class="card-img" style="width: 100%; height: 200px;"></div>
                            <div class="card-img-overlay">
                                <h4 class="card-title">Load {{fileState.toLoad}} more</h4>
                                <div class="card-text pt-4">
                                    <i class="fas fa-fw fa-sync fa-3x" :class="{'fa-spin': isFetching}"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <ul class="list-group mb-2 w-100" v-else>
                <li class="list-group-item px-3 py-2 w-100 d-flex flex-row justify-content-between clickable" v-for="file in files" :title="file.name" @click="onClick(file)" @contextmenu.prevent="$refs.fileMenu.open($event, {file: file})">
                    <div class="d-flex justify-content-start align-items-center">
                        <input type="checkbox" class="mr-2" @click.stop="" :id="file.id" :value="file.id" v-model="selectedFiles" v-if="$can('export_files')" />
                        <div class="mr-2">
                            <div v-if="file.category == 'xml'">
                                <i class="fas fa-fw fa-file-code w-32p h-32p"></i>
                            </div>
                            <div v-else-if="file.category == 'html'">
                                <i
                                class="fab fa-fw fa-html5"
                                data-fa-transform="shrink-9 down-2"
                                data-fa-mask="fas fa-fw fa-file w-32p h-32p"></i>
                            </div>
                            <div v-else-if="file.category == 'archive'">
                                <i class="fas fa-fw fa-file-archive w-32p h-32p"></i>
                            </div>
                            <div v-else-if="file.category == 'pdf'">
                                <i class="fas fa-fw fa-file-pdf w-32p h-32p"></i>
                            </div>
                            <div v-else-if="file.category == 'audio'">
                                <i class="fas fa-fw fa-file-audio w-32p h-32p"></i>
                            </div>
                            <div v-else-if="file.category == 'video'">
                                <i class="fas fa-fw fa-file-video w-32p h-32p"></i>
                            </div>
                            <div v-else-if="file.category == 'spreadsheet'">
                                <i class="fas fa-fw fa-file-excel w-32p h-32p"></i>
                            </div>
                            <div v-else-if="file.category == 'document'">
                                <i class="fas fa-fw fa-file-word w-32p h-32p"></i>
                            </div>
                            <div v-else-if="file.category == 'presentation'">
                                <i class="fas fa-fw fa-file-powerpoint w-32p h-32p"></i>
                            </div>
                            <div v-else-if="file.category == '3d'">
                                <i
                                class="fas fa-fw fa-cubes w-32p h-32p"
                                data-fa-transform="shrink-9 down-2"
                                data-fa-mask="fas fa-fw fa-file"></i>
                            </div>
                            <div v-else-if="file.category == 'dicom'">
                                <i class="fas fa-fw fa-file-medical-alt w-32p h-32p"></i>
                            </div>
                            <div v-else-if="file.category == 'text'">
                                <i class="fas fa-fw fa-file-alt w-32p h-32p"></i>
                            </div>
                            <div v-else-if="file.category == 'image'" class="w-32p h-32p">
                                <img class="w-100 h-100 object-fit-cover" :src="file.thumb_url" />
                            </div>
                            <div v-else-if="file.category == 'undefined'">
                                <i
                                class="fas fa-fw fa-question w-32p h-32p"
                                data-fa-transform="shrink-9 down-2"
                                data-fa-mask="fas fa-fw fa-file"></i>
                            </div>
                        </div>
                        {{ file.name }}
                    </div>
                    <div class="d-flex justify-content-end align-items-center">
                        <span v-if="showLinks && getFileLinks(file).length" :title="$tc('global.has-links', getFileLinks(file).length, {cnt: getFileLinks(file).length})">
                            <i class="fas fa-fw fa-link text-info"></i>
                        </span>
                        <span v-if="isFromSubEntity(file)" class="ml-2" :title="$tc('global.from-subentity')">
                            <i class="fas fa-fw fa-sitemap text-info"></i>
                        </span>
                        <span v-if="getFileTags(file).length" class="ml-2" :title="$tc('global.has-tags', getFileTags(file).length, {cnt: getFileTags(file).length})">
                            <i class="fas fa-fw fa-tags text-info"></i>
                        </span>
                    </div>
                </li>
                <li class="list-group-item w-100 d-flex flex-row justify-content-start align-items-center" v-if="fileState.toLoad">
                    <i class="fas fa-fw fa-sync mr-2 ml-4 w-32p h-32p" :class="{'fa-spin': isFetching}"></i>
                    Load {{fileState.toLoad}} more
                </li>
            </ul>
        </div>

        <vue-context ref="fileMenu">
            <template slot-scope="fileScope" v-if="fileScope.data">
                <li v-for="entry in contextMenu">
                    <a href="" @click.prevent="entry.callback(fileScope.data.file)">
                        <i :class="entry.getIconClasses(fileScope.data.file)">
                            {{ entry.getIconContent(fileScope.data.file) }}
                        </i>
                        {{ entry.getLabel(fileScope.data.file) }}
                    </a>
                </li>
            </template>
        </vue-context>
    </div>
</template>

<script>
    import infiniteScroll from 'vue-infinite-scroll';
    import VueContext from 'vue-context';

    export default {
        components: {
            VueContext
        },
        directives: {
            infiniteScroll
        },
        props: {
            files: {
                required: true,
                type: Array
            },
            entityId: {
                required: false,
                type: Number
            },
            onClick: {
                required: true,
                type: Function
            },
            onLoadChunk: {
                required: true,
                type: Function
            },
            fileState: {
                required: true,
                type: Object
            },
            isFetching: {
                required: true,
                type: Boolean
            },
            showLinks: {
                required: false,
                type: Boolean,
            },
            contextMenu: {
                required: false,
                type: Array
            }
        },
        mounted() {},
        methods: {
            getFileLinks(file) {
                if(!this.showLinks) return [];
                if(!file.entities) {
                    return [];
                }
                return file.entities;
            },
            isFromSubEntity(file) {
                if(!this.entityId || !file || !file.entities) return false;
                return file.entities.findIndex(e => e.id == this.entityId) == -1;
            },
            getFileTags(file) {
                return file.tags;
            },
            exportFiles(fileIds) {
                if(!this.$can('export_files')) return;
                const data = {
                    files: fileIds
                };
                $http.post(`file/export`, data).then(response => {
                    const filename = this.$getPreference('prefs.project-name') + '.zip';
                    this.$createDownloadLink(response.data, filename, true, response.headers['content-type']);
                });
            },
            toggleExportSettings() {
                this.showExportSettings = !this.showExportSettings;
            }
        },
        data() {
            return {
                selectedFiles: [],
                grid: true,
                showExportSettings: false
            }
        },
        computed: {
            fileCount() {
                return this.selectedFiles.length;
            }
        }
    }
</script>
