<template>
    <div>
        Displaying {{fileState.from}}-{{fileState.to}} of {{fileState.total}} files
        <div class="row" infinite-scroll-disabled="fetchingFiles" v-infinite-scroll="getNextFiles">
            <div class="col-sm-6 col-md-4 mb-3" v-for="file in files">
                <div class="card text-center" @click="onClick(file)">
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
                                <i class="fas fa-fw fa-sync fa-3x" :class="{'fa-spin': fetchingFiles}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import infiniteScroll from 'vue-infinite-scroll';

    export default {
        directives: {
            infiniteScroll
        },
        props: {
            apiUrl: {
                required: true,
                type: String
            },
            apiPrefix: {
                required: false,
                type: String,
                default: '/api'
            },
            apiPageParam: {
                required: false,
                type: String,
                default: 'page'
            },
            onClick: {
                required: true,
                type: Function
            }
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
                let url = this.apiPrefix;
                if(!Object.keys(vm.pagination).length) {
                    url += this.apiUrl;
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
            }
        },
        data() {
            return {
                pagination: {},
                files: [],
                fetchingFiles: false,
                fileState: {
                    from: 0,
                    to: 0,
                    total: undefined,
                    toLoad: 0
                }
            }
        }
    }
</script>
