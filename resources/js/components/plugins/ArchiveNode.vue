<template>
    <div class="d-flex flex-row align-items-center justify-content-start w-100">
        <span>
            <span v-if="data.is_directory">
                <i class="fas fa-fw fa-folder"></i>
            </span>
            <span v-else>
                <i class="fas fa-fw fa-file"></i>
            </span>
            <span class="ml-1">
                {{ data.clean_filename }}
            </span>
        </span>
        <span v-if="!data.is_directory" class="text-secondary ml-auto">
            {{ data.compressed_size | bytes }}/{{ data.uncompressed_size | bytes }}
        </span>
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
