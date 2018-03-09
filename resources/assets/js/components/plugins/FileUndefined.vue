<template>
    <div>
        <p class="alert alert-info">
            Filetype of <span class="font-italic">{{file.name}}</span> is currently not supported by Spacialist.
            If you need support for this kind of filetype, please create an <a href="https://github.com/eScienceCenter/Spacialist/issues/new">issue on GitHub<sup><i class="fas fa-fw fa-external-link-alt"></i></sup></a>
        </p>
        <a :href="file.url" :download="file.name" target="_blank">
            <i class="fas fa-fw fa-download fa-5x"></i>
            <h4>Download {{file.name}}</h4>
        </a>
    </div>
</template>

<script>
    export default {
        props: {
            file: {
                required: true,
                type: Object
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
