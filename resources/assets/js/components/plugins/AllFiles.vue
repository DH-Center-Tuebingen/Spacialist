<template>
    <file-list :files="files" :on-click="onClick" :on-load-chunk="getNextFiles" :file-state="fileState" :is-fetching="fetchingFiles"></file-list>
</template>

<script>
    export default {
        props: {
            onClick: {
                required: true,
                type: Function
            }
        },
        mounted() {
            let vm = this;
            this.$parent.$on('fileUpdateNeeded', function() {
                // if we never fetched files, wait for user to load
                if(!vm.pagination.current_page) {
                    return;
                }
                let url = vm.apiPrefix;
                if(vm.pagination.to == vm.pagination.total) {
                    url += vm.pagination.next_page_url;
                } else {
                    url += vm.apiUrl + '?' + vm.apiPageParam + '=' + vm.pagination.current_page
                }
                vm.getPage(url);
            });
        },
        methods: {
            getNextFiles() {
                this.fetchingFiles = true;
                if(this.pagination.current_page && this.pagination.current_page == this.pagination.last_page) {
                    return;
                }
                let firstCall;
                let url = this.apiPrefix;
                if(!Object.keys(this.pagination).length) {
                    url += this.apiUrl;
                    firstCall = true;
                } else {
                    url += this.pagination.next_page_url;
                    firstCall = false;
                }
                this.getPage(url, firstCall);
            },
            getPage(pageUrl, isFirstCall) {
                let vm = this;
                vm.$http.get(pageUrl).then(function(response) {
                    let resp = response.data;
                    for(let i=0; i<resp.data.length; i++) {
                        vm.files.push(resp.data[i]);
                    }
                    delete resp.data;
                    Vue.set(vm, 'pagination', resp);
                    if(isFirstCall) {
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
                apiPrefix: '/api',
                apiUrl: '/file',
                apiPageParam: 'page',
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
