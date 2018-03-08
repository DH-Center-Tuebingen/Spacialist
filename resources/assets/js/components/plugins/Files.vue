<template>
    <div class="row">
        <div class="col-sm-6 col-md-4 mb-3" v-for="file in files">
            <div class="card text-center" @click="showFileModal(file)">
                <div class="card-hover">
                    <img class="card-img" src="" style="width: 300px; height: 200px;">
                    <div class="card-img-overlay">
                        <h4 class="card-title">{{file.name}}</h4>
                        <div class="card-text pt-4">
                            <div v-if="file.type == 'XML'">
                                <i class="fas fa-fw fa-file-code fa-5x"></i>
                            </div>
                            <div v-else-if="file.type == 'ZIP'">
                                <i class="fas fa-fw fa-file-archive fa-5x"></i>
                            </div>
                            <div v-else-if="file.type == 'PDF'">
                                <i class="fas fa-fw fa-file-pdf fa-5x"></i>
                            </div>
                            <div v-else-if="file.type == 'Audio'">
                                <i class="fas fa-fw fa-file-audio fa-5x"></i>
                            </div>
                            <div v-else-if="file.type == 'Video'">
                                <i class="fas fa-fw fa-file-video fa-5x"></i>
                            </div>
                            <div v-else-if="file.type == 'Spread'">
                                <i class="fas fa-fw fa-file-excel fa-5x"></i>
                            </div>
                            <div v-else-if="file.type == 'Doc'">
                                <i class="fas fa-fw fa-file-word fa-5x"></i>
                            </div>
                            <div v-else-if="file.type == 'Pres'">
                                <i class="fas fa-fw fa-file-powerpoint fa-5x"></i>
                            </div>
                            <div v-else-if="file.type == '3D'">
                                <i
                                class="fas fa-fw fa-cubes fa-5x"
                                data-fa-transform="shrink-9 down-2"
                                data-fa-mask="fas fa-fw fa-file"></i>
                            </div>
                            <div v-else-if="file.type == 'Text'">
                                <i class="fas fa-fw fa-file-alt fa-5x"></i>
                            </div>
                            <div v-else>
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

        <modal name="file-modal" height="auto" :scrollable="true">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ selectedFile.name }} - Details</h5>
                    <button type="button" class="close" aria-label="Close" v-on:click="hideFileModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
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
    export default {
        mounted() {
            console.log("Files Plugin mounted");
        },
        methods: {
            showFileModal(file) {
                this.selectedFile = Object.assign({}, file);
                this.$modal.show('file-modal');
            },
            hideFileModal() {
                this.$modal.hide('file-modal');
            }
        },
        data() {
            return {
                selectedFile: {},
                files: [
                    {
                        name: 'file1.xml',
                        type: 'XML'
                    },
                    {
                        name: 'file1.jpg',
                        type: 'Image'
                    },
                    {
                        name: 'file1.dae',
                        type: '3D'
                    },
                    {
                        name: 'file1.txt',
                        type: 'Text'
                    },
                    {
                        name: 'file1.zip',
                        type: 'ZIP'
                    },
                    {
                        name: 'file1.pdf',
                        type: 'PDF'
                    },
                    {
                        name: 'file1.mp3',
                        type: 'Audio'
                    },
                    {
                        name: 'file1.avi',
                        type: 'Video'
                    },
                    {
                        name: 'file1.xlsx',
                        type: 'Spread'
                    },
                    {
                        name: 'file1.doc',
                        type: 'Doc'
                    },
                    {
                        name: 'file1.odp',
                        type: 'Pres'
                    },
                ]
            }
        }
    }
</script>
