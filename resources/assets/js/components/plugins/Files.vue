<template>
    <div>
        Displaying {{fileState.from}}-{{fileState.to}} of {{fileState.total}} files
        <div class="row" v-infinite-scroll="getNextFiles" infinite-scroll-disabled="fetchingFiles">
            <div class="col-sm-6 col-md-4 mb-3" v-for="file in files">
                <div class="card text-center" @click="showFileModal(file)">
                    <div class="card-hover">
                        <img class="card-img" v-if="file.category == 'image'" :src="file.url" style="height: 200px;">
                        <div class="card-img" v-else style="width: 100%; height: 200px;"></div>
                        <div class="card-img-overlay">
                            <h4 class="card-title">{{file.name}}</h4>
                            <div class="card-text pt-4">
                                <div v-if="file.category == 'xml'">
                                    <i class="fas fa-fw fa-file-code fa-5x"></i>
                                </div>
                                <div v-if="file.category == 'html'">
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
                        </div>
                    </div>
                    <div class="card-hover-overlay bg-info">
                        <div class="text-white">
                            <i class="fas fa-fw fa-binoculars fa-5x"></i>
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
                                <i class="fas fa-fw fa-sync fa-5x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
    import VueHighlightJS from 'vue-highlightjs';
    import infiniteScroll from 'vue-infinite-scroll';

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
        components: {
            VueHighlightJS
        },
        directives: {
            infiniteScroll
        },
        mounted() {
        },
        methods: {
            getNextFiles() {
                let vm = this;
                vm.fetchingFiles = true;
                if(vm.pagination.current_page && vm.pagination.current_page == vm.pagination.last_page) {
                    return;
                }
                let firstCall;
                let url = '/api';
                if(!Object.keys(vm.pagination).length) {
                    url += '/file?page=1';
                    firstCall = true;
                } else {
                    url += vm.pagination.next_page_url;
                    firstCall = false;
                }
                vm.$http.get(url).then(function(response) {
                    let resp = response.data;
                    for(let i=0; i<resp.data.length; i++) {
                        vm.files.push(resp.data[i]);
                    }
                    delete resp.data;
                    Vue.set(vm, 'pagination', resp);
                    if(firstCall) {
                        vm.fileState.from = resp.from;
                    }
                    vm.fileState.to = resp.to;
                    vm.fileState.total = resp.total;
                    let toLoad = Math.min(resp.per_page, resp.total-resp.to);
                    vm.fileState.toLoad = toLoad;
                    vm.fetchingFiles = false;
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
                selectedFile: {},
                pagination: {},
                files: [],
                fileCategoryComponent: '',
                fetchingFiles: false,
                fileState: {
                    from: 0,
                    to: 0,
                    total: undefined,
                    toLoad: 0
                },
                modalTab: 'properties',
                fileProperties: [
                    'copyright',
                    'description'
                ]
            }
        }
    }
</script>
