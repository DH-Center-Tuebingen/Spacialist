<template>
    <div class="d-flex flex-row align-items-center justify-content-start w-100">
        <div>
            <span v-if="data.isDirectory">
                <i class="fas fa-fw fa-folder"></i>
            </span>
            <span v-else>
                <i class="fas fa-fw fa-file"></i>
            </span>
            <span class="ms-1 font-weight-medium">
                {{ data.cleanFilename }}
            </span>
        </div>
        <div v-if="!data.isDirectory" class="text-secondary ms-auto">
            {{ data.compressedSize | bytes }}/{{ data.uncompressedSize | bytes }}
        </div>
        <div v-else class="text-secondary ms-auto" v-html="$tc('plugins.files.modal.detail.archive.files_in_folder', data.children.length, {cnt: data.children.length})">
        </div>
    </div>
</template>

<script>
export default {
    props: {
        data: {
            required: true,
            type: Object
        }
    }
}
</script>
