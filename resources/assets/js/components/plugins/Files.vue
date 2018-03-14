<template>
    <div>
        <div class="d-flex justify-content-around align-items-center mb-2">
            <button style="button" class="btn btn-outline-secondary" :class="{disabled: !context.id}" @click="setAction('linked')">
                <i class="fas fa-fw fa-link"></i> Linked Files
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

        <keep-alive>
            <component :is="selectedActionComponent" :on-click="showFileModal" :active-comp="selectedTopAction" :context="context"></component>
        </keep-alive>
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
                    <button type="button" class="close" aria-label="Close" v-on:click="hideFileModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="row">
                        <div class="col-md-6">
                            <component :is="fileCategoryComponent" :file="selectedFile"></component>
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
                    <button type="button" class="btn btn-outline-secondary"     v-on:click="hideFileModal">
                        Close
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    Vue.component('file-list', require('./FileList.vue'));
    Vue.component('file-linked', require('./LinkedFiles.vue'));
    Vue.component('file-unlinked', require('./UnlinkedFiles.vue'));
    Vue.component('file-all', require('./AllFiles.vue'));

    Vue.component('file-image', require('./FileImage.vue'));
    Vue.component('file-audio', require('./FileAudio.vue'));
    Vue.component('file-video', require('./FileVideo.vue'));
    Vue.component('file-pdf', require('./FilePdf.vue'));
    Vue.component('file-xml', require('./FileXml.vue'));
    Vue.component('file-3d', require('./File3d.vue'));
    Vue.component('file-archive', require('./FileArchive.vue'));
    Vue.component('file-text', require('./FileText.vue'));
    Vue.component('file-undefined', require('./FileUndefined.vue'));

    export default {
        props: {
            context: {
                required: false,
                type: Object,
                default: {}
            }
        },
        mounted() {
        },
        methods: {
            setAction(id) {
                // disable linked tab if no context is selected
                if(id == 'linked' && !this.context.id) return;
                switch(id) {
                    case 'linked':
                        this.selectedActionComponent = 'file-linked';
                        break;
                    case 'unlinked':
                        this.selectedActionComponent = 'file-unlinked';
                        break;
                    case 'all':
                        this.selectedActionComponent = 'file-all';
                        break;
                    default:
                        this.selectedActionComponent = '';
                }
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
                        this.$emit('fileUpdateNeeded');
                        this.filesUploaded = 0;
                        this.filesErrored = 0;
                    }
                }
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
            }
        },
        data() {
            return {
                selectedTopAction: 'unlinked',
                selectedActionComponent: 'file-unlinked',
                fileApi: '/file/unlinked',
                uploadFiles: [],
                filesUploaded: 0,
                filesErrored: 0,
                selectedFile: {},
                fileCategoryComponent: '',
                modalTab: 'properties',
                fileProperties: [
                    'copyright',
                    'description'
                ]
            }
        }
    }
</script>
