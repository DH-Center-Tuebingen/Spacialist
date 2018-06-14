<template>
    <div class="modal-content-80 pr-2">
        <p class="alert alert-info">
            Click on files to download them without downloading the whole archive first. Download of folders is currently not supported.
        </p>
        <tree-view
            :category="category"
            :css="css"
            :display="display"
            :model="fileList"
            :onSelect="onSelect"
            :openerOpts="openerOpts"
            :selection="selection"
            :strategies="strategies">
        </tree-view>
    </div>
</template>

<script>
    import { TreeView } from '@bosket/vue';

    export default {
        components: {
            'tree-view': TreeView
        },
        props: {
            file: {
                required: true,
                type: Object
            }
        },
        mounted() {
            this.setArchiveFileList();
        },
        methods: {
            setArchiveFileList() {
                const vm = this;
                const id = vm.file.id;
                const url = `/api/file/${id}/archive/list`;
                vm.fileList = [];
                vm.$http.get(url).then(function(response) {
                    for(let i=0; i<response.data.length; i++) {
                        vm.fileList.push(response.data[i]);
                    }
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            onSelect(newSelection) {
                let selectedFile = newSelection[0];
                // Download of folders is not supported
                if(selectedFile.is_directory) return;
                const vm = this;
                const id = vm.file.id;
                const p = selectedFile.filename;
                const url = '/api/file/'+id+'/archive/download?p='+p;
                vm.$http.get(url).then(function(response) {
                    vm.$createDownloadLink(response.data, selectedFile.clean_filename, true);
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            }
        },
        data() {
            return {
                fileList: [],
                // Bosket
                selection: [],
                strategies: {
                    selection: ["single"],
                    click: ["select"],
                    fold: ["opener-control"]
                },
                openerOpts: {
                    position: 'left'
                },
                css: {
                    opener: 'opener mr-2 text-info',
                    item: 'item d-flex w-100'
                },
                category: "children",
                display: (item, inputs) =>
                    <span class="d-flex flex-row align-items-center justify-content-start w-100">
                        <span>
                            {item.is_directory ? <i class="fas fa-fw fa-folder"></i> : <i class="fas fa-fw fa-file"></i>}
                            <span class="ml-1">{item.clean_filename}</span>
                        </span>
                        {!item.is_directory ? <span class="text-secondary ml-auto">{this.$options.filters.bytes(item.compressed_size)}/{this.$options.filters.bytes(item.uncompressed_size)}</span> : <span></span>}
                    </span>
            }
        }
    }
</script>
