<template>
    <div class="d-flex flex-row align-items-center">
        <i class="fas fa-fw fa-file fa-lg mx-2"></i>
        <div class="media col px-0 pl-1">
            <div class="media-body small">
                <h6 class="my-0" style="white-space: pre;">{{ data.name }}</h6>
                Copyright: {{ data.copyright }}
                Description: {{ data.description }}
            </div>
            <img v-if="data.category == 'image'" class="ml-3 media-image" :src="data.url" :alt="data.name" />
            <div v-else class="ml-3 media-image-placeholder">
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
