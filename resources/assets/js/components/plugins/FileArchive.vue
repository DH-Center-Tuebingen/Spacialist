<template>
    <div class="modal-content-80 pr-2">
        <p class="alert alert-info">
            Click on files to download them without downloading the whole archive first. Download of folders is currently not supported.
        </p>
        <tree class="text-left"
            :data="fileList"
            :draggable="false"
            @change="onSelect"
            @toggle="itemToggle">
        </tree>
    </div>
</template>

<script>
    import * as treeUtility from 'tree-vue-component';
    Vue.component('archive-node', require('./ArchiveNode.vue'));

    export default {
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
            addNodeProperties(item) {
                item.icon = false;
                item.component = 'archive-node';
                item.state = {
                    opened: false,
                    selected: false,
                    disabled: false,
                    loading: false,
                    highlighted: false,
                    openable: (item.children && item.children.length > 0) || false,
                    dropPosition: 0, //TODO set to DropPosition.empty once exported by tree-vue-component
                    dropAllowed: false,
                };
                item.children = item.children || [];
                item.children.forEach(c => {
                    this.addNodeProperties(c);
                });
            },
            setArchiveFileList() {
                const vm = this;
                const id = vm.file.id;
                const url = `/api/file/${id}/archive/list`;
                vm.fileList = [];
                vm.$http.get(url).then(function(response) {
                    response.data.forEach(entry => {
                        vm.addNodeProperties(entry);
                        vm.fileList.push(entry);
                    });
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            onSelect(eventData) {
                const vm = this;
                const selectedFile = eventData.data;
                // Download of folders is not supported
                if(selectedFile.is_directory) return;
                const id = vm.file.id;
                const p = selectedFile.filename;
                const url = '/api/file/'+id+'/archive/download?p='+p;
                vm.$http.get(url).then(function(response) {
                    vm.$createDownloadLink(response.data, selectedFile.clean_filename, true);
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            itemToggle(eventData) {
                const item = eventData.data;
                item.state.opened = !item.state.opened;
            }
        },
        data() {
            return {
                fileList: []
            }
        }
    }
</script>
