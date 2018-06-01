<template>
    <div class="h-100 d-flex flex-column">
        Displaying {{fileState.from}}-{{fileState.to}} of {{fileState.total}} files
        <div class="row col px-0 scroll-y-auto" infinite-scroll-disabled="isFetching" v-infinite-scroll="onLoadChunk">
            <div class="col-sm-6 col-md-4 mb-3" v-for="file in files">
                <div class="card text-center" @click="onClick(file)" @contextmenu.prevent="$refs.fileMenu.open($event, {file: file})">
                    <div class="card-hover">
                        <img class="card-img" v-if="file.category == 'image'" :src="file.url" style="height: 200px;">
                        <div class="card-img" v-else style="width: 100%; height: 200px;"></div>
                        <div class="card-img-overlay">
                            <h4 class="card-title text-truncate">{{file.name}}</h4>
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
                                <i class="fas fa-fw fa-sync fa-3x" :class="{'fa-spin': isFetching}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <vue-context ref="fileMenu" class="context-menu-wrapper">
            <ul class="list-group list-group-vue-context" slot-scope="fileScope">
                <li class="list-group-item list-group-item-vue-context" v-for="entry in contextMenu" @click="entry.callback(fileScope.data.file)">
                    <i :class="entry.iconClasses">{{entry.iconContent}}</i> {{entry.label}}
                </li>
            </ul>
        </vue-context>
    </div>
</template>

<script>
    import infiniteScroll from 'vue-infinite-scroll';
    import { VueContext } from 'vue-context';

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
            contextMenu: {
                required: false,
                type: Array
            }
        },
        mounted() {
        },
        methods: {
        },
        data() {
            return {
            }
        }
    }
</script>
