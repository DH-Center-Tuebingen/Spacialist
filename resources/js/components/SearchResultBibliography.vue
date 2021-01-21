<template>
    <div class="d-flex flex-row align-items-center">
        <i class="fas fa-fw fa-book fa-lg mx-2"></i>
        <div class="media col px-0 ps-1">
            <div class="media-body small">
                <h6 class="my-0" style="white-space: pre;">{{ data.title }}</h6>
                <p class="mb-0">
                    {{ data.author }}, <span class="text-muted font-weight-light">{{ data.year}}</span>
                </p>
            </div>
            <div class="ms-3 media-image-placeholder">
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            data: {
                required: true,
                type: Object
            },
        },
        mounted () {},
        methods: {
        },
        data () {
            return {
            }
        }
    }
</script>
