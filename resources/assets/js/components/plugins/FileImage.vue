<template>
    <div class="h-100 w-100">
        <img :src="file.url" id="file-container-image" class="modal-image" />
        <button type="button" class="btn btn-sm btn-info position-absolute m-2" v-if="fullscreenHandler" @click="toggleFullscreen">
            <i class="fas fa-fw fa-expand"></i>
        </button>
    </div>
</template>

<script>
    export default {
        props: {
            file: {
                required: true,
                type: Object
            },
            fullscreenHandler: {
                required: false,
                type: Function
            }
        },
        methods: {
            toggleFullscreen() {
                if(!this.fullscreenHandler) return;
                const element = document.getElementById('file-container-image');
                this.fullscreenHandler(element)
            }
        },
        data() {
            return {
            }
        }
    }
</script>
