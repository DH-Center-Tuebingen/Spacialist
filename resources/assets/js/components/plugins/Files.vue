<template>
    <div class="d-flex flex-column h-100">
        <div class="d-flex justify-content-around align-items-center mb-2">
            <button style="button" class="btn btn-outline-secondary" :class="{disabled: !context.id}" @click="setAction('linked')">
                <i class="fas fa-fw fa-link"></i> Linked Files <span class="badge badge-primary" v-show="context.id">{{linkedFiles.files.length}}</span>
            </button>
            <button style="button" class="btn btn-outline-secondary" @click="setAction('unlinked')">
                <i class="fas fa-fw fa-unlink"></i> Unlinked
            </button>
            <button style="button" class="btn btn-outline-secondary" @click="setAction('all')">
                <i class="fas fa-fw fa-copy"></i> All Files
            </button>
            <button style="button" class="btn btn-outline-secondary" @click="setAction('upload')">
                <i class="fas fa-fw fa-upload"></i> Upload Files
            </button>
        </div>
        <div class="col" v-show="isAction('linked')">
            <file-list :files="linkedFiles.files" :on-click="showFileModal" :on-load-chunk="linkedFiles.loadChunk" :file-state="linkedFiles.fileState" :is-fetching="linkedFiles.fetchingFiles" :context-menu="contextMenu"></file-list>
        </div>
        <div class="col" v-show="isAction('unlinked')">
            <file-list :files="unlinkedFiles.files" :on-click="showFileModal" :on-load-chunk="unlinkedFiles.loadChunk" :file-state="unlinkedFiles.fileState" :is-fetching="unlinkedFiles.fetchingFiles" :context-menu="contextMenu"></file-list>
        </div>
        <div class="col" v-show="isAction('all')">
            <file-list :files="allFiles.files" :on-click="showFileModal" :on-load-chunk="allFiles.loadChunk" :file-state="allFiles.fileState" :is-fetching="allFiles.fetchingFiles" :context-menu="contextMenu"></file-list>
        </div>
        <div v-if="isAction('upload')">
            <file-upload class="w-100"
                post-action="/api/file"
                ref="upload"
                v-model="uploadFiles"
                :multiple="true"
                :directory="false"
                :drop="true"
                @input-file="inputFile">
                    <div class="text-center rounded text-light bg-dark px-2 py-5">
                        <h3>Drop Zone</h3>
                        <p>
                            Please drop files here or click on this item.
                        </p>
                    </div>
            </file-upload>
            <ul class="list-group list-group-flush">
                <transition-group name="fade">
                    <li class="list-group-item" v-for="file in uploadFiles" :key="file.id" v-if="!file.success">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span>{{file.name}}</span>
                                <span class="text-lightgray" v-if="!file.error">
                                    {{file.size|bytes}} - {{file.speed|bytes}}/s
                                </span>
                            </div>
                            <button v-show="file.active" type="button" class="btn btn-outline-danger" @click.prevent="abortFileUpload(file)">
                                <i class="fas fa-fw fa-times"></i> Abort
                            </button>
                            <a href="#" v-show="file.error" @click="abortFileUpload(file)">
                                <i class="fas fa-fw fa-times"></i> Clear
                            </a>
                        </div>
                        <div class="progress" style="height: 2px;" v-if="!file.error">
                            <div class="progress-bar" role="progressbar" :style="{width: file.progress+'%'}" :aria-valuenow="file.progress" aria-valuemin="0" aria-valuemax="100">
                                <span class="sr-only">{{file.progress}}</span>
                            </div>
                        </div>
                        <p class="alert alert-danger" v-if="file.error">
                            Error while uploading your file.
                        </p>
                    </li>
                </transition-group>
            </ul>
        </div>

        <modal name="file-modal" width="80%" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ selectedFile.name }} - Details</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideFileModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="row">
                        <div class="col-md-6">
                            <component id="file-container" :is="fileCategoryComponent" :file="selectedFile"></component>
                        </div>
                        <div class="col-md-6">
                            <ul class="nav nav-tabs nav-fill">
                                <li class="nav-item">
                                    <a class="nav-link" :class="{active: modalTab == 'properties'}" @click="modalTab = 'properties'">
                                        <i class="fas fa-fw fa-sliders-h"></i> Properties
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" :class="{active: modalTab == 'links'}" @click="modalTab = 'links'">
                                        <i class="fas fa-fw fa-link"></i> Links
                                    </a>
                                </li>
                                <li class="nav-item" v-if="selectedFile.exif">
                                    <a class="nav-link" :class="{active: modalTab == 'exif'}" @click="modalTab = 'exif'">
                                        <i class="fas fa-fw fa-camera"></i> Exif
                                    </a>
                                </li>
                            </ul>
                            <div class="ml-4 mr-4" v-show="modalTab == 'properties'">
                                <h5 class="mt-3">Properties</h5>
                                <table class="table table-striped table-hover table-sm mb-0">
                                    <tbody>
                                        <tr v-for="p in fileProperties">
                                            <td class="text-left font-weight-bold">
                                                {{p}}
                                            </td>
                                            <td class="text-right text-gray">
                                                {{selectedFile[p]}}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <h5 class="mt-3">File Properties</h5>
                                <table class="table table-striped table-hover table-sm mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-left font-weight-bold">
                                                Created
                                            </td>
                                            <td class="text-right text-gray">
                                                {{selectedFile.created_unix|date}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-left font-weight-bold">
                                                Last Modified
                                            </td>
                                            <td class="text-right text-gray">
                                                {{selectedFile.modified_unix|date}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-left font-weight-bold">
                                                File size
                                            </td>
                                            <td class="text-right text-gray">
                                                {{selectedFile.size|bytes}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                            </td>
                                            <td class="text-right font-weight-bold">
                                                <a :href="selectedFile.url" :download="selectedFile.name" target="_blank">
                                                    <i class="fas fa-fw fa-download text-gray"></i>
                                                    Download
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="mt-3 text-right">
                                    <file-upload
                                        v-show="!replaceFiles.length"
                                        ref="replace"
                                        v-model="replaceFiles"
                                        :directory="false"
                                        :drop="true"
                                        :multiple="false"
                                        :post-action="replaceFileUrl"
                                        @input-file="onReplaceFileSet">
                                            <span class="btn btn-outline-secondary">
                                                <i class="fas fa-fw fa-retweet"></i> Replace File
                                            </span>
                                    </file-upload>
                                    <div class="d-flex justify-content-between align-items-center" v-if="replaceFiles.length">
                                        <span>
                                            Do you want to replace {{selectedFile.name}} ({{selectedFile.size | bytes}}) with {{replaceFiles[0].name}} ({{replaceFiles[0].size | bytes}})?
                                        </span>
                                        <div class="d-flex">
                                            <button type="button" class="btn btn-outline-success" @click="doReplaceFile">
                                                Replace
                                            </button>
                                            <button type="button" class="btn btn-outline-danger ml-2" @click="cancelReplaceFile">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <h5 class="mt-3">Tags</h5>
                            </div>
                            <div v-show="modalTab == 'links'">
                                <ul class="list-group mt-3 ml-4 mr-4">
                                    <li class="list-group-item" v-for="link in selectedFile.contexts">
                                        {{link.name}}
                                    </li>
                                </ul>
                            </div>
                            <div v-show="modalTab == 'exif'">
                                <p class="alert alert-info" v-if="!selectedFile.exif">
                                </p>
                                <table class="table table-striped" v-else>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <i class="fas fa-fw fa-camera"></i>
                                            </td>
                                            <td>
                                                {{ selectedFile.exif.Model }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <i class="far fa-fw fa-circle"></i>
                                            </td>
                                            <td>
                                                {{ selectedFile.exif.Exif.FNumber }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <i class="fas fa-fw fa-circle"></i>
                                            </td>
                                            <td>
                                                <span>
                                                    {{ selectedFile.exif.Exif.FocalLength }} <span v-if="selectedFile.exif.MakMakerNotes">({{    selectedFile.exif.MakerNotes.LensModel }})</span>
                                                </span>
                                                <span v-if="selectedFile.exif.MakerNotes" style="display: block;font-size: 90%;color: gray;">
                                                    {{     selectedFile.exif.MakerNotes.LensType }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <i class="fas fa-fw fa-stopwatch"></i>
                                            </td>
                                            <td>
                                                {{ selectedFile.exif.Exif.ExposureTime }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <i class="fas fa-fw fa-plus"></i>
                                            </td>
                                            <td>
                                                {{ selectedFile.exif.Exif.ISOSpeedRatings }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <!-- EXIF.Flash is hex, trailing 0 means no flash -->
                                                <i class="fas fa-fw fa-bolt"></i>
                                            </td>
                                            <td>
                                                {{ selectedFile.exif.Exif.Flash }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <i class="fas fa-fw fa-clock"></i>
                                            </td>
                                            <td>
                                                {{ selectedFile.exif.Exif.DateTimeOriginal }}
                                            </td>
                                        </tr>
                                        <tr v-if="selectedFile.exif.Makernotes">
                                            <td>
                                                <i class="fas fa-fw fa-sun"></i>
                                            </td>
                                            <td>
                                                {{ selectedFile.exif.MakerNotes.WhiteBalanceSetting }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <i class="fas fa-fw fa-copyright"></i>
                                            </td>
                                            <td>
                                                {{ selectedFile.exif.Copyright }}
                                            </td>
                                        </tr>
                                        <tr v-if="selectedFile.exif.Makernotes">
                                            <td>
                                                <i class="fas fa-fw fa-microchip"></i>
                                            </td>
                                            <td>
                                                {{ selectedFile.exif.MakerNotes.FirmwareVersion }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"     @click="hideFileModal">
                        Close
                    </button>
                </div>
            </div>
        </modal>

        <modal name="delete-file-modal" width="80%" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete {{ contextMenuFile.name }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideDeleteFileModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        Do you really want to delete <i>{{ contextMenuFile.name }}</i>?
                    </p>
                    <p class="alert alert-danger">
                        Please note: If you delete <i>{{ contextMenuFile.name }}</i>, {{ linkCount }} links to entities will be deleted as well.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="deleteFile(contextMenuFile)">
                        <i class="fas fa-fw fa-check"></i> Delete
                    </button>
                    <button type="button" class="btn btn-outline-secondary"     @click="hideDeleteFileModal">
                        Close
                    </button>
                </div>
            </div>
        </modal>

        <modal name="unlink-file-modal" width="80%" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Unlink {{ contextMenuFile.name }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideUnlinkFileModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        Do you really want to unlink <i>{{ contextMenuFile.name }}</i> from <i>{{ contextMenuContext.name }}</i>?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="unlinkFile(contextMenuFile, contextMenuContext)">
                        <i class="fas fa-fw fa-check"></i> Unlink
                    </button>
                    <button type="button" class="btn btn-outline-secondary"     @click="hideUnlinkFileModal">
                        Close
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    import * as screenfull from 'screenfull';

    Vue.component('file-list', require('./FileList.vue'));

    Vue.component('file-image', require('./FileImage.vue'));
    Vue.component('file-audio', require('./FileAudio.vue'));
    Vue.component('file-video', require('./FileVideo.vue'));
    Vue.component('file-pdf', require('./FilePdf.vue'));
    Vue.component('file-xml', require('./FileXml.vue'));
    Vue.component('file-3d', require('./File3d.vue'));
    Vue.component('file-dicom', require('./FileDicom.vue'));
    Vue.component('file-archive', require('./FileArchive.vue'));
    Vue.component('file-text', require('./FileText.vue'));
    Vue.component('file-undefined', require('./FileUndefined.vue'));

    export default {
        props: {
            context: {
                required: false,
                type: Object,
                default: {}
            },
            contextDataLoaded: {
                required: false,
                type: Boolean,
                default: false
            }
        },
        mounted() {
            if(screenfull.enabled) {
                window.addEventListener('keydown', this.toggleFullscreen, false);
            }
        },
        methods: {
            toggleFullscreen: function(event) {
                let elem = document.getElementById('file-container');
                if(!elem) return;
                let k = event.keyCode;
                if(k != 70) return; // 70 = 'f' key
                screenfull.toggle(elem);
            },
            setAction(id) {
                // disable linked tab if no context is selected
                if(id == 'linked' && !this.localContext.id) return;
                this.selectedTopAction = id;
            },
            isAction(id) {
                return this.selectedTopAction == id;
            },
            abortFileUpload(file) {
                this.$refs.upload.remove(file);
            },
            inputFile(newFile, oldFile) {
                // Wait for response
                if(newFile && oldFile && newFile.success && !oldFile.success) {
                    this.filesUploaded++;
                }
                if(newFile && oldFile && newFile.error && !oldFile.error) {
                    this.filesErrored++;
                }
                // Enable automatic upload
                if(Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error) {
                    if(!this.$refs.upload.active) {
                        this.$refs.upload.active = true
                    }
                }
                if(this.filesUploaded + this.filesErrored == this.uploadFiles.length) {
                    if(this.filesUploaded > 0) {
                        this.onFilesUploaded(this.unlinkedFiles);
                        this.onFilesUploaded(this.allFiles);
                        this.filesUploaded = 0;
                        this.filesErrored = 0;
                    }
                }
            },
            doReplaceFile() {
                this.$refs.replace.active = true;
            },
            cancelReplaceFile() {
                this.$refs.replace.active = false;
                this.replaceFiles = [];
            },
            onReplaceFileSet(newFile, oldFile) {
                // Wait for response
                if(newFile && oldFile && newFile.success && !oldFile.success) {
                    console.log(newFile.response);
                    Vue.set(this, 'selectedFile', newFile.response);
                    this.replaceFiles = [];
                    this.$refs.replace.active = false;
                }
            },
            resetFiles(fileType) {
                let arr = this[fileType];
                arr.files = [];
                arr.fileState = {};
                arr.fetchingFiles = false;
                arr.pagination = {};
            },
            getNextFiles(fileType) {
                if(fileType == 'linkedFiles' && !this.context.id) {
                    return;
                }
                let arr = this[fileType];
                arr.fetchingFiles = true;
                if(arr.pagination.current_page && arr.pagination.current_page == arr.pagination.last_page) {
                    return;
                }
                let firstCall;
                let url = arr.apiPrefix;
                if(!Object.keys(arr.pagination).length) {
                    url += arr.apiUrl;
                } else {
                    url += arr.pagination.next_page_url;
                }
                this.getPage(url, arr);
            },
            getPage(pageUrl, filesObj) {
                let vm = this;
                this.$http.get(pageUrl).then(function(response) {
                    let resp = response.data;
                    for(let i=0; i<resp.data.length; i++) {
                        filesObj.files.push(resp.data[i]);
                    }
                    delete resp.data;
                    Vue.set(filesObj, 'pagination', resp);
                    filesObj.fetchingFiles = false;
                    vm.updateFileState(filesObj);
                });
            },
            updateFileState(filesObj) {
                filesObj.fileState.from = filesObj.pagination.from ? 1 : 0,
                filesObj.fileState.to = filesObj.pagination.to,
                filesObj.fileState.total = filesObj.pagination.total,
                filesObj.fileState.toLoad = Math.min(
                    filesObj.pagination.per_page,
                    filesObj.pagination.total-filesObj.pagination.to
                )
            },
            onFilesUploaded(filesObj) {
                // if we never fetched files, wait for user to load
                if(!filesObj.pagination.current_page) {
                    return;
                }
                let url = filesObj.apiPrefix;
                url += filesObj.apiUrl + '?' + filesObj.apiPageParam + '=' + filesObj.pagination.current_page;
                if(filesObj.pagination.to < filesObj.pagination.total) {
                    // remove current page files and reload them
                    let index = filesObj.pagination.from - 1;
                    let howmany = (filesObj.pagination.to - filesObj.pagination.from) + 1;
                    filesObj.files.splice(index, howmany);
                }
                this.updateFileState(filesObj);
                this.getPage(url, filesObj);
            },
            onFileDeleted(file, filesObj) {
                // if we never fetched files, wait for user to load
                if(!filesObj.pagination.current_page) {
                    return;
                }
                let index = filesObj.files.findIndex(f => f.id == file.id);
                // if the file was not in this tab, return
                if(index == -1) return;
                filesObj.pagination.total--;
                filesObj.pagination.to--;
                filesObj.files.splice(index, 1);
                // check if we deleted with only 1 element on last page
                if(filesObj.pagination.from > filesObj.pagination.total) {
                    // if so, set next page url to this page, because we decreased our current page
                    filesObj.pagination.next_page_url = filesObj.apiUrl + '?' + filesObj.apiPageParam + '=' + filesObj.pagination.current_page;
                }
                this.updateFileState(filesObj);
            },
            onFileLinked(file, filesObj) {
                // if we never fetched files, wait for user to load
                if(!filesObj.pagination.current_page) {
                    return;
                }
                filesObj.files.push(file);
                filesObj.pagination.total++;
                filesObj.pagination.to++;
                let count = (filesObj.pagination.to - filesObj.pagination.from) + 1;
                // check if the push created a new (local) page
                if(count > filesObj.pagination.per_page) {
                    filesObj.pagination.current_page++;
                    // check if the push created a new (db) page
                    if(filesObj.pagination.total % filesObj.pagination.per_page == 1) {
                        filesObj.pagination.last_page++;
                        filesObj.pagination.last_page_url = filesObj.apiUrl + '?' + filesObj.apiPageParam + '=' + filesObj.pagination.last_page;
                    }
                    filesObj.pagination.next_page_url = filesObj.apiUrl + '?' + filesObj.apiPageParam + '=' + filesObj.pagination.current_page;
                }
                this.updateFileState(filesObj);
            },
            onFileUnlinked(file, filesObj, linkCount) {
                // if we never fetched files, wait for user to load
                if(!vm.pagination.current_page) {
                    return;
                }
                // if there are still links, do not add to unlinked files
                if(typeof linkCount != 'undefined' && linkCount > 0) {
                    return;
                }
                let index = vm.files.findIndex(f => f.id == file.id);
                // if the file was not in this tab, return
                if(index == -1) return;
                vm.pagination.total--;
                vm.pagination.to--;
                vm.files.splice(index, 1);
                // check if we deleted with only 1 element on last page
                if(vm.pagination.from > vm.pagination.total) {
                    // if so, set next page url to this page, because we decreased our current page
                    vm.pagination.next_page_url = vm.apiUrl + '?' + vm.apiPageParam + '=' + vm.pagination.current_page;
                }
            },
            requestDeleteFile(file) {
                this.contextMenuFile = Object.assign({}, file);
                this.$modal.show('delete-file-modal');
            },
            deleteFile(file) {
                let vm = this;
                let id = file.id;
                vm.$http.delete('/api/file/'+id).then(function(response) {
                    vm.onFileDeleted(file, vm.linkedFiles);
                    vm.onFileDeleted(file, vm.unlinkedFiles);
                    vm.onFileDeleted(file, vm.allFiles);
                    vm.hideDeleteFileModal();
                });
            },
            hideDeleteFileModal() {
                this.$modal.hide('delete-file-modal');
                this.contextMenuFile = {};
            },
            requestUnlinkFile(file, context) {
                let vm = this;
                let id = file.id;
                vm.contextMenuFile = Object.assign({}, file);
                vm.contextMenuContext = Object.assign({}, context);
                vm.$http.get('/api/file/'+id+'/link_count').then(function(response) {
                    vm.linkCount = response.data;
                    vm.$modal.show('unlink-file-modal');
                });
            },
            unlinkFile(file, context) {
                let vm = this;
                let id = file.id;
                let cid = context.id;
                vm.$http.delete('/api/file/'+id+'/link/'+cid).then(function(response) {
                    vm.linkCount--;
                    vm.onFileDeleted(file, vm.linkedFiles);
                    vm.onFileUnlinked(file, vm.unlinkedFiles, vm.linkCount);
                    vm.hideUnlinkFileModal();
                });
            },
            hideUnlinkFileModal() {
                this.$modal.hide('unlink-file-modal');
                this.contextMenuFile = {};
                this.contextMenuContext = {};
                this.linkCount = 0;
            },
            linkFile(file, context) {
                let vm = this;
                let id = file.id;
                let data = {
                    'context_id': context.id
                };
                vm.$http.put('/api/file/'+id+'/link', data).then(function(response) {
                    vm.onFileLinked(file, vm.linkedFiles);
                    vm.onFileDeleted(file, vm.unlinkedFiles);
                });
            },
            showFileModal(file) {
                this.selectedFile = Object.assign({}, file);
                switch(file.category) {
                    case 'image':
                        this.fileCategoryComponent = 'file-image';
                        break;
                    case 'audio':
                        this.fileCategoryComponent = 'file-audio';
                        break;
                    case 'video':
                        this.fileCategoryComponent = 'file-video';
                        break;
                    case 'pdf':
                        this.fileCategoryComponent = 'file-pdf';
                        break;
                    case 'xml':
                    case 'html':
                        this.fileCategoryComponent = 'file-xml';
                        break;
                    case '3d':
                        this.fileCategoryComponent = 'file-3d';
                        break;
                    case 'dicom':
                        this.fileCategoryComponent = 'file-dicom';
                        break;
                    case 'archive':
                        this.fileCategoryComponent = 'file-archive';
                        break;
                    case 'text':
                        this.fileCategoryComponent = 'file-text';
                        break;
                    default:
                        this.fileCategoryComponent = 'file-undefined';
                        break;
                }
                this.$modal.show('file-modal');
            },
            hideFileModal() {
                this.$modal.hide('file-modal');
                this.selectedFile = {};
            }
        },
        data() {
            return {
                selectedTopAction: 'unlinked',
                uploadFiles: [],
                filesUploaded: 0,
                filesErrored: 0,
                linkedFiles: {
                    files: [],
                    fileState: {},
                    fetchingFiles: false,
                    pagination: {},
                    apiPrefix: '/api',
                    apiUrl: '/file/linked',
                    apiPageParam: 'page',
                    loadChunk: () => this.getNextFiles('linkedFiles')
                },
                unlinkedFiles: {
                    files: [],
                    fileState: {},
                    fetchingFiles: false,
                    pagination: {},
                    apiPrefix: '/api',
                    apiUrl: '/file/unlinked',
                    apiPageParam: 'page',
                    loadChunk: () => this.getNextFiles('unlinkedFiles')
                },
                allFiles: {
                    files: [],
                    fileState: {},
                    fetchingFiles: false,
                    pagination: {},
                    apiPrefix: '/api',
                    apiUrl: '/file',
                    apiPageParam: 'page',
                    loadChunk: () => this.getNextFiles('allFiles')
                },
                selectedFile: {},
                replaceFiles: [],
                contextMenuFile: {},
                contextMenuContext: {},
                linkCount: 0,
                fileCategoryComponent: '',
                modalTab: 'properties',
                fileProperties: [
                    'copyright',
                    'description'
                ]
            }
        },
        computed: {
            localContext: function() {
                return Object.assign({}, this.context);
            },
            contextMenu: function() {
                let vm = this;
                let menu = [];
                if(vm.context.id) {
                    if(vm.isAction('linked')) {
                        menu.push({
                            label: 'Unlink from ' + vm.context.name,
                            iconClasses: 'fas fa-fw fa-unlink text-info',
                            iconContent: '',
                            callback: function(file) {
                                vm.requestUnlinkFile(file, vm.context);
                            }
                        });
                    } else {
                        menu.push({
                            label: 'Link to ' + vm.context.name,
                            iconClasses: 'fas fa-fw fa-link text-success',
                            iconContent: '',
                            callback: function(file) {
                                vm.linkFile(file, vm.context);
                            }
                        });
                    }
                }
                menu.push({
                    label: 'Delete',
                    iconClasses: 'fas fa-fw fa-trash text-danger',
                    iconContent: '',
                    callback: function(file) {
                        vm.requestDeleteFile(file);
                    }
                });
                return menu;
            }
        },
        watch: {
            contextDataLoaded: function(newContextDataLoaded, oldContextDataLoaded) {
                if(newContextDataLoaded) {
                    this.linkedFiles.apiUrl = '/file/linked/' + this.context.id;
                    this.resetFiles('linkedFiles');
                    this.getNextFiles('linkedFiles');
                }
            }
        }
    }
</script>
