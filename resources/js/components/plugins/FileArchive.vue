<template>
    <div class="h-100 d-flex flex-column">
        <p class="alert alert-info">
            {{ $t('plugins.files.modal.detail.archive.info') }}
        </p>
        <tree class="text-left col px-0 scroll-y-auto"
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
                if(this.isFetching) return;
                const id = this.file.id;
                const url = `/file/${id}/archive/list`;
                this.isFetching = true;
                $httpQueue.add(() => $http.get(url).then(response => {
                    this.fileList = [];
                    response.data.forEach(entry => {
                        this.addNodeProperties(entry);
                        this.fileList.push(entry);
                    });
                    this.isFetching = false;
                }));
            },
            onSelect(eventData) {
                const vm = this;
                const selectedFile = eventData.data;
                // Download of folders is not supported
                if(selectedFile.is_directory) return;
                const id = vm.file.id;
                const p = selectedFile.filename;
                const url = '/file/'+id+'/archive/download?p='+p;
                $httpQueue.add(() => vm.$http.get(url).then(function(response) {
                    vm.$createDownloadLink(response.data, selectedFile.clean_filename, true);
                }));
            },
            itemToggle(eventData) {
                const item = eventData.data;
                item.state.opened = !item.state.opened;
            }
        },
        data() {
            return {
                fileList: [],
                isFetching: false
            }
        },
        watch: {
            file(newFile, oldFile) {
                this.setArchiveFileList();
            }
        }
    }
</script>
